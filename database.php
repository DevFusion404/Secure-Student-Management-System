<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student-management-system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

if (!function_exists('xssEscape')) {
	// XSS: Encode untrusted values before rendering them in HTML text or attributes.
	function xssEscape($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}
?>