<?php

namespace Jrluis\LaravelOracleLogs;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class OracleLogsDriver extends AbstractProcessingHandler
{
    protected OracleCloudLogsClient $client;
    protected array $config;
    protected array $logBuffer = [];
    protected int $bufferSize;
    protected int $flushInterval;

    public function __construct(OracleCloudLogsClient $client, array $config = [])
    {
        $this->client = $client;
        $this->config = $config;
        $this->bufferSize = $config['buffer_size'] ?? 100;
        $this->flushInterval = $config['flush_interval'] ?? 5; // seconds

        parent::__construct(
            $config['level'] ?? Logger::DEBUG,
            $config['bubble'] ?? true
        );

        // Register shutdown function to flush remaining logs
        register_shutdown_function([$this, 'flush']);
    }

    /**
     * Write the log record to Oracle Cloud Logs
     */
    protected function write(LogRecord $record): void
    {
        $logEntry = $this->client->formatLogEntry(
            $record->level->getName(),
            $record->message,
            $this->formatContext($record)
        );

        $this->logBuffer[] = $logEntry;

        // Flush if buffer is full or it's time to flush
        if (count($this->logBuffer) >= $this->bufferSize) {
            $this->flush();
        }
    }

    /**
     * Format the log record context
     */
    protected function formatContext(LogRecord $record): array
    {
        return $record->context;
    }

    /**
     * Flush buffered logs to Oracle Cloud Logs
     */
    public function flush(): void
    {
        if (empty($this->logBuffer)) {
            return;
        }

        try {
            $this->client->sendBatchLogs($this->logBuffer);
            $this->logBuffer = [];
        } catch (\Exception $e) {
            // Log error to prevent infinite loops
            error_log("Oracle Cloud Logs Driver: Failed to flush Oracle Cloud Logs: " . $e->getMessage());
        }
    }

    /**
     * Handle batch processing for better performance
     */
    public function handleBatch(array $records): void
    {
        $logEntries = [];

        foreach ($records as $record) {
            $logEntries[] = $this->client->formatLogEntry(
                $record->level->getName(),
                strtoupper($record->level->getName()) . ': ' . $record->message,
                $this->formatContext($record)
            );
        }

        try {
            $this->client->sendBatchLogs($logEntries);
        } catch (\Exception $e) {
            error_log("Failed to send batch to Oracle Cloud Logs: " . $e->getMessage());
        }
    }

    /**
     * Get the current buffer size
     */
    public function getBufferSize(): int
    {
        return count($this->logBuffer);
    }

    /**
     * Clear the log buffer
     */
    public function clearBuffer(): void
    {
        $this->logBuffer = [];
    }

    /**
     * Set buffer size
     */
    public function setBufferSize(int $size): void
    {
        $this->bufferSize = $size;
    }

    /**
     * Set flush interval
     */
    public function setFlushInterval(int $seconds): void
    {
        $this->flushInterval = $seconds;
    }
}
