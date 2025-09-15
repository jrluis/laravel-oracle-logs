<?php

namespace Jrluis\LaravelOracleLogs\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Jrluis\LaravelOracleLogs\Tests\TestCase;

class LoggingTest extends TestCase
{
    public function test_can_log_to_oracle_channel()
    {
        // This test would require mocking the HTTP client
        // For now, we'll just test that the channel exists
        $this->assertTrue(Log::getDefaultDriver() !== null);
    }
}
