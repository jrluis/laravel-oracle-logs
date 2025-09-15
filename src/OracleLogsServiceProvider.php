<?php

namespace Jrluis\LaravelOracleLogs;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;

class OracleLogsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/oracle-logs.php',
            'logging.channels.oracle'
        );

        $this->app->singleton(OracleCloudLogsClient::class, function ($app) {
            $config = $app['config']['logging.channels.oracle'];

            return new OracleCloudLogsClient([
                'endpoint' => $config['endpoint'],
                'log_group_id' => $config['log_group_id'],
                'log_id' => $config['log_id'],
                'region' => $config['region'],
                'tenancy_id' => $config['tenancy_id'],
                'user_id' => $config['user_id'],
                'fingerprint' => $config['fingerprint'],
                'private_key' => $config['private_key'],
                'passphrase' => $config['passphrase'] ?? '',
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/oracle-logs.php' => config_path('oracle-logs.php'),
        ], 'oracle-logs-config');

        // Register the custom log driver
        Log::extend('oracle', function ($app, $config) {
            $client = $app[OracleCloudLogsClient::class];

            $logger = new Logger('oracle');
            $handler = new OracleLogsDriver($client, $config);

            $logger->pushHandler($handler);

            return $logger;
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            OracleCloudLogsClient::class,
        ];
    }
}
