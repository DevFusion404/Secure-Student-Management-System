<?php
/*
 * Request validation helpers. Each request-handling page supplies its own
 * allow-list of fields and expected formats before using request data.
 */

function inputValidationFail($message = 'Invalid request input.', $statusCode = 400) {
    if (!headers_sent()) {
        http_response_code($statusCode);
    }
    exit($message);
}

function validateInputString($value, $field, $maxLength) {
    if (!is_string($value)
        || strlen($value) > $maxLength
        || preg_match('//u', $value) !== 1
        || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
        inputValidationFail('Invalid ' . $field . '.', 422);
    }
    return trim($value);
}

function validateInputDate($value, $field) {
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    $errors = DateTime::getLastErrors();
    if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        inputValidationFail('Invalid ' . $field . '.', 422);
    }
}

function validateInputTime($value, $field) {
    foreach (array('!H:i', '!H:i:s') as $format) {
        $time = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();
        if ($time !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return;
        }
    }
    inputValidationFail('Invalid ' . $field . '.', 422);
}

function validateInputValue($field, $value, $rule) {
    $limits = array(
        'text' => 255, 'longtext' => 5000, 'name' => 50, 'email' => 254,
        'password' => 128, 'contact' => 25, 'nic' => 20, 'username' => 50,
        'token' => 64, 'code' => 2048
    );
    $value = validateInputString($value, $field, $limits[$rule] ?? 255);

    if ($rule === 'id' && !preg_match('/^(?:0|[A-Za-z0-9][A-Za-z0-9_-]{0,24})$/', $value)) {
        inputValidationFail('Invalid ' . $field . '.', 422);
    }
    if ($rule === 'name' && !preg_match('/^[\p{L}][\p{L}\p{M} .\'-]{0,49}$/u', $value)) {
        inputValidationFail('Invalid ' . $field . '.', 422);
    }
    if ($rule === 'email' && (!filter_var($value, FILTER_VALIDATE_EMAIL) || strlen($value) > 254)) {
        inputValidationFail('Invalid email.', 422);
    }
    if ($rule === 'date') {
        validateInputDate($value, $field);
    }
    if ($rule === 'time') {
        validateInputTime($value, $field);
    }
    if ($rule === 'gender' && !in_array($value, array('Male', 'Female'), true)) {
        inputValidationFail('Invalid gender.', 422);
    }
    if ($rule === 'role' && !in_array($value, array('Teacher', 'Parent', 'Student'), true)) {
        inputValidationFail('Invalid role.', 422);
    }
    if ($rule === 'audience' && !in_array($value, array('All', 'Student', 'Parent'), true)) {
        inputValidationFail('Invalid notice audience.', 422);
    }
    if ($rule === 'day' && !in_array($value, array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), true)) {
        inputValidationFail('Invalid day.', 422);
    }
    if ($rule === 'grade' && !preg_match('/^[A-F][+-]?$/', $value)) {
        inputValidationFail('Invalid grade.', 422);
    }
    if ($rule === 'marks' && (!is_numeric($value) || (float) $value < 0 || (float) $value > 100)) {
        inputValidationFail('Invalid marks.', 422);
    }
    if ($rule === 'capacity' && (!ctype_digit($value) || (int) $value < 1 || (int) $value > 10000)) {
        inputValidationFail('Invalid capacity.', 422);
    }
    if ($rule === 'contact' && !preg_match('/^[0-9+() -]{7,25}$/', $value)) {
        inputValidationFail('Invalid contact number.', 422);
    }
    if ($rule === 'nic' && !preg_match('/^[A-Za-z0-9-]{5,20}$/', $value)) {
        inputValidationFail('Invalid national identity card number.', 422);
    }
    if ($rule === 'username' && !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $value)) {
        inputValidationFail('Invalid username.', 422);
    }
    if ($rule === 'token' && !preg_match('/^[a-f0-9]{64}$/i', $value)) {
        inputValidationFail('Invalid CSRF token.', 400);
    }
    if ($rule === 'action' && !preg_match('/^[A-Za-z_]{1,30}$/', $value)) {
        inputValidationFail('Invalid form action.', 422);
    }
    if ($rule === 'attendance' && !in_array($value, array('Present', 'Absent'), true)) {
        inputValidationFail('Invalid attendance status.', 422);
    }
}

function validateRequestFields($getRules, $postRules) {
    foreach (array('_GET' => array($_GET, $getRules), '_POST' => array($_POST, $postRules)) as $sourceName => $sourceData) {
        $values = $sourceData[0];
        $rules = $sourceData[1];
        if (count($values) > 50) {
            inputValidationFail('Too many request fields.', 400);
        }
        foreach ($values as $field => $value) {
            if (!is_string($field) || !array_key_exists($field, $rules)) {
                inputValidationFail('Unexpected request field.', 400);
            }
            $rule = $rules[$field];
            if (is_array($value)) {
                if (!in_array($rule, array('id-list', 'attendance-list'), true) || count($value) > 500) {
                    inputValidationFail('Invalid request field.', 400);
                }
                $itemRule = $rule === 'id-list' ? 'id' : 'attendance';
                foreach ($value as $item) {
                    validateInputValue($field, $item, $itemRule);
                }
                continue;
            }
            validateInputValue($field, $value, $rule);
        }
    }
}