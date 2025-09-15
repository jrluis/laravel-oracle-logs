<?php

/**
 * Example usage of the Laravel Oracle Cloud Logs Driver
 * 
 * This file demonstrates how to use the Oracle Cloud Logs driver
 * in a Laravel application.
 */

// Example 1: Basic logging
use Illuminate\Support\Facades\Log;

// Log an info message
Log::channel('oracle')->info('User logged in successfully', [
    'user_id' => 123,
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...'
]);

// Log an error
Log::channel('oracle')->error('Database connection failed', [
    'error' => 'Connection timeout',
    'database' => 'production',
    'retry_count' => 3
]);

// Log debug information
Log::channel('oracle')->debug('Processing payment', [
    'order_id' => 456,
    'amount' => 99.99,
    'currency' => 'USD',
    'payment_method' => 'credit_card'
]);

// Example 2: Using different log levels
$levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

foreach ($levels as $level) {
    Log::channel('oracle')->{$level}("This is a {$level} message", [
        'timestamp' => now()->toISOString(),
        'level' => $level
    ]);
}

// Example 3: Structured logging for application events
Log::channel('oracle')->info('Order created', [
    'event' => 'order.created',
    'order_id' => 789,
    'customer_id' => 456,
    'total_amount' => 149.99,
    'currency' => 'USD',
    'items' => [
        ['product_id' => 1, 'quantity' => 2, 'price' => 49.99],
        ['product_id' => 2, 'quantity' => 1, 'price' => 50.00]
    ],
    'metadata' => [
        'source' => 'web',
        'version' => '1.0.0',
        'environment' => app()->environment()
    ]
]);

// Example 4: Exception logging
try {
    // Some risky operation
    throw new \Exception('Something went wrong');
} catch (\Exception $e) {
    Log::channel('oracle')->error('Exception occurred', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

// Example 5: Performance monitoring
$startTime = microtime(true);

// Simulate some work
usleep(100000); // 100ms

$endTime = microtime(true);
$duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

Log::channel('oracle')->info('Performance metric', [
    'metric' => 'request_duration',
    'value' => $duration,
    'unit' => 'milliseconds',
    'endpoint' => '/api/orders',
    'method' => 'POST'
]);

// Example 6: Using the client directly for custom scenarios
use Jrluis\LaravelOracleLogs\OracleCloudLogsClient;

$client = app(OracleCloudLogsClient::class);

// Send a custom log entry
$customLogEntry = $client->formatLogEntry('info', 'Custom log message', [
    'custom_field' => 'custom_value',
    'source' => 'custom_script'
]);

// Note: In a real application, you would typically use Log::channel('oracle')
// instead of calling the client directly, as the driver handles batching and
// error handling automatically.
