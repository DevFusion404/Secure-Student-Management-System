<?php

declare(strict_types=1);

$tests = require __DIR__ . '/tests.php';
$passed = 0;
$failed = 0;
$skipped = 0;

foreach ($tests as $name => $test) {
    try {
        $test();
        echo "PASS  {$name}\n";
        $passed++;
    } catch (RegressionSkip $exception) {
        echo "SKIP  {$name}: {$exception->getMessage()}\n";
        $skipped++;
    } catch (Throwable $exception) {
        echo "FAIL  {$name}: {$exception->getMessage()}\n";
        $failed++;
    }
}

echo "\n{$passed} passed, {$failed} failed, {$skipped} skipped\n";
exit($failed === 0 ? 0 : 1);

