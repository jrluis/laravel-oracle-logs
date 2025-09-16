<?php

namespace Jrluis\LaravelOracleLogs\Tests\Unit;

use Jrluis\LaravelOracleLogs\OracleCloudLogsClient;
use Jrluis\LaravelOracleLogs\Tests\TestCase;

class OracleCloudLogsClientTest extends TestCase
{
    public function test_can_create_client_instance()
    {
        $config = [
            'endpoint' => 'https://logging.test.oci.oraclecloud.com',
            'log_group_id' => 'ocid1.loggroup.test',
            'log_id' => 'ocid1.log.test',
            'region' => 'us-test-1',
            'tenancy_id' => 'ocid1.tenancy.test',
            'user_id' => 'ocid1.user.test',
            'fingerprint' => 'test:fingerprint',
            'private_key' => '-----BEGIN PRIVATE KEY-----\ntest\n-----END PRIVATE KEY-----',
            'passphrase' => '',
        ];

        $client = new OracleCloudLogsClient($config);

        $this->assertInstanceOf(OracleCloudLogsClient::class, $client);
    }

    public function test_can_format_log_entry()
    {
        $config = [
            'endpoint' => 'https://logging.test.oci.oraclecloud.com',
            'log_group_id' => 'ocid1.loggroup.test',
            'log_id' => 'ocid1.log.test',
            'region' => 'us-test-1',
            'tenancy_id' => 'ocid1.tenancy.test',
            'user_id' => 'ocid1.user.test',
            'fingerprint' => 'test:fingerprint',
            'private_key' => '-----BEGIN PRIVATE KEY-----\ntest\n-----END PRIVATE KEY-----',
            'passphrase' => '',
        ];

        $client = new OracleCloudLogsClient($config);
        $logEntry = $client->formatLogEntry('info', 'Test message', ['key' => 'value']);

        $this->assertArrayHasKey('id', $logEntry);
        $this->assertArrayHasKey('time', $logEntry);
        $this->assertArrayHasKey('data', $logEntry);
        $this->assertEquals('Test message', $logEntry['data']);
        $this->assertIsString($logEntry['id']);
        $this->assertIsString($logEntry['time']);
    }
}
