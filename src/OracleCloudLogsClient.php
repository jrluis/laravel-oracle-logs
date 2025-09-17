<?php

namespace Jrluis\LaravelOracleLogs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Hitrov\OCI\Signer;

class OracleCloudLogsClient
{
    protected string $endpoint;
    protected string $logGroupId;
    protected string $logId;
    protected string $region;
    protected string $tenancyId;
    protected string $userId;
    protected string $fingerprint;
    protected string $privateKey;
    protected string $passphrase;

    public function __construct(array $config)
    {
        $this->endpoint = $config['endpoint'] ?? 'https://logging.us-ashburn-1.oci.oraclecloud.com';
        $this->logGroupId = $config['log_group_id'];
        $this->logId = $config['log_id'];
        $this->region = $config['region'] ?? 'us-ashburn-1';
        $this->tenancyId = $config['tenancy_id'];
        $this->userId = $config['user_id'];
        $this->fingerprint = $config['fingerprint'];
        $this->privateKey = $config['private_key'];
        $this->passphrase = $config['passphrase'] ?? '';
    }

    /**
     * Create a new HTTP client instance
     */
    protected function createClient(): Client
    {
        return new Client([
            'base_uri' => $this->endpoint,
            'timeout' => 30,
            'headers' => [
                'user-agent' => 'Laravel-Oracle-Logs/1.0',
                'content-type' => 'application/json',
                'accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Generate authentication headers for Oracle Cloud Logs API using Hitrov signer
     */
    protected function generateAuthHeaders(string $method, string $path, string $body = ''): array
    {
        try {
            $signer = new Signer();
            $keyProvider = new PrivateKeyProvider(
                $this->privateKey,
                $this->tenancyId,
                $this->userId,
                $this->fingerprint
            );
            $signer->setKeyProvider($keyProvider);

            $url = $this->endpoint . $path;
            $headers = $signer->getHeaders($url, $method, $body);

            // Convert array of header strings to associative array
            $headerArray = [];
            foreach ($headers as $header) {
                if (strpos($header, ':') !== false) {
                    [$key, $value] = explode(':', $header, 2);
                    $headerArray[trim($key)] = trim($value);
                }
            }


            return $headerArray;
        } catch (\Exception $e) {
            error_log("Failed to generate auth headers: " . $e->getMessage());
            throw new \RuntimeException('Failed to generate authentication headers: ' . $e->getMessage());
        }
    }

    /**
     * Send log entry to Oracle Cloud Logs
     */
    public function sendLog(array $logEntry): bool
    {
        try {
            $client = $this->createClient();
            $path = "/20200831/logs/{$this->logId}/actions/push";
            $body = json_encode($this->getLogEntryBatch( $logEntry));
            $headers = $this->generateAuthHeaders('POST', $path, $body);

            $response = $client->post($path, [
                'headers' => $headers,
                'body' => $body
            ]);

            $success = $response->getStatusCode() === 200;

            if (!$success) {
                error_log("Oracle Cloud Logs: Unexpected response code from Oracle Cloud Logs - Status: {$response->getStatusCode()}, Response: " . $response->getBody()->getContents());
            }

            return $success;
        } catch (RequestException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'No status code';
            error_log("Oracle Cloud Logs: Request failed - Error: {$e->getMessage()}, Response: {$responseBody}, Status: {$statusCode}");
            return false;
        } catch (\Exception $e) {
            error_log("Oracle Cloud Logs: Error - {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send multiple log entries in a batch
     */
    public function sendBatchLogs(array $logEntries): bool
    {
        if (empty($logEntries)) {
            return true;
        }

        try {
            $client = $this->createClient();
            $path = "/20200831/logs/{$this->logId}/actions/push";
            $body = json_encode($this->getLogEntryBatch( $logEntries));
            $headers = $this->generateAuthHeaders('POST', $path, $body);

            $response = $client->post($path, [
                'headers' => $headers,
                'body' => $body
            ]);

            $success = $response->getStatusCode() === 200;

            if (!$success) {
                error_log("Oracle Cloud Logs: Unexpected response code from Oracle Cloud Logs - Status: {$response->getStatusCode()}, Response: " . $response->getBody()->getContents());
            }

            return $success;
        } catch (RequestException $e) {
            $count = count($logEntries);
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'No status code';
            error_log("Oracle Cloud Logs: Batch request failed - Error: {$e->getMessage()}, Count: {$count}, Response: {$responseBody}, Status: {$statusCode}");
            return false;
        } catch (\Exception $e) {
            $count = count($logEntries);
            error_log("Oracle Cloud Logs: Batch error - Error: {$e->getMessage()}, Count: {$count}");
            return false;
        }
    }

    public function getLogEntryBatch(array $logEntries): array
    {
        return [
            'specversion' => "1",
            'logEntryBatches' => [[
                'defaultlogentrytime' => now()->toISOString(),
                'source' => gethostname(),
                'subject' => './storage/logs/laravel.log',
                'type' => 'laravel',
                'entries' => $logEntries
            ]]
        ];
    }

    public function guidv4($data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }


    /**
     * Format log entry for Oracle Cloud Logs
     */
    public function formatLogEntry(string $level, string $message, array $context = []): array
    {
        $timestamp = now()->toISOString();

        return [
            'id' => $this->guidv4(),
            'time' => $timestamp,
            'data' => strtoupper($level) . ': ' . $message . ' ' . json_encode($context)
        ];
    }

    /**
     * Test the connection to Oracle Cloud Logs
     */
    public function testConnection(): bool
    {
        try {
            $testEntry = $this->formatLogEntry('info', 'Connection test from Laravel Oracle Logs');
            return $this->sendLog($testEntry);
        } catch (\Exception $e) {
            error_log("Oracle Cloud Logs: Connection test failed - Error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get the current configuration (without sensitive data)
     */
    public function getConfig(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'log_group_id' => $this->logGroupId,
            'log_id' => $this->logId,
            'region' => $this->region,
            'tenancy_id' => $this->tenancyId,
            'user_id' => $this->userId,
            'fingerprint' => $this->fingerprint,
            'has_private_key' => !empty($this->privateKey),
            'has_passphrase' => !empty($this->passphrase),
        ];
    }

    /**
     * Check if the client is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->tenancyId) &&
               !empty($this->userId) &&
               !empty($this->fingerprint) &&
               !empty($this->privateKey) &&
               !empty($this->logId) &&
               !empty($this->logGroupId);
    }
}
