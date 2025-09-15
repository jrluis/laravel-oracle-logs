<?php

namespace Jrluis\LaravelOracleLogs\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Jrluis\LaravelOracleLogs\OracleLogsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            OracleLogsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup Oracle Logs configuration
        $app['config']->set('logging.channels.oracle', [
            'driver' => 'oracle',
            'endpoint' => 'https://logging.test.oci.oraclecloud.com',
            'log_group_id' => 'ocid1.loggroup.test',
            'log_id' => 'ocid1.log.test',
            'region' => 'us-test-1',
            'tenancy_id' => 'ocid1.tenancy.test',
            'user_id' => 'ocid1.user.test',
            'fingerprint' => 'test:fingerprint',
            'private_key' => '-----BEGIN PRIVATE KEY-----\ntest\n-----END PRIVATE KEY-----',
            'passphrase' => '',
            'level' => 'debug',
            'bubble' => true,
            'buffer_size' => 10,
            'flush_interval' => 1,
        ]);
    }
}
