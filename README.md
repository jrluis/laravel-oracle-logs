# Laravel Oracle Cloud Logs Driver

A Laravel log driver that stores application logs in Oracle Cloud Logs service. This package provides seamless integration between Laravel's logging system and Oracle Cloud Infrastructure (OCI) Logging service.

## Features

- **Easy Integration**: Simple setup with Laravel's built-in logging system
- **Batch Processing**: Efficiently sends logs in batches to reduce API calls
- **Buffering**: Configurable buffer size and flush intervals for optimal performance
- **Authentication**: Secure authentication using OCI API keys with Hitrov OCI signer
- **Error Handling**: Robust error handling to prevent logging failures from affecting your application
- **Laravel 9+ Support**: Compatible with Laravel 9, 10, and 11
- **Reliable Signing**: Uses the battle-tested Hitrov OCI API PHP Request Sign library for OCI authentication

## Installation

1. Install the package via Composer:

```bash
composer require jrluis/laravel-oracle-logs
```

This will automatically install the required dependencies:
- `hitrov/oci-api-php-request-sign` - For OCI API request signing
- `guzzlehttp/guzzle` - For HTTP client functionality

2. Publish the configuration file:

```bash
php artisan vendor:publish --provider="Jrluis\LaravelOracleLogs\OracleLogsServiceProvider" --tag="oracle-logs-config"
```

3. Add the service provider to your `config/app.php` (if not using auto-discovery):

```php
'providers' => [
    // ...
    Jrluis\LaravelOracleLogs\OracleLogsServiceProvider::class,
],
```

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file:

```env
# Oracle Cloud Logs Configuration
ORACLE_LOGS_ENDPOINT=https://logging.us-ashburn-1.oci.oraclecloud.com
ORACLE_LOGS_GROUP_ID=ocid1.loggroup.oc1.xxxxx
ORACLE_LOGS_ID=ocid1.log.oc1.xxxxx
ORACLE_LOGS_REGION=us-ashburn-1
ORACLE_LOGS_TENANCY_ID=ocid1.tenancy.oc1.xxxxx
ORACLE_LOGS_USER_ID=ocid1.user.oc1.xxxxx
ORACLE_LOGS_FINGERPRINT=xx:xx:xx:xx:xx:xx:xx:xx:xx:xx:xx:xx:xx:xx:xx:xx
ORACLE_LOGS_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"
ORACLE_LOGS_PASSPHRASE=your_passphrase_if_any

# Optional: Driver Configuration
ORACLE_LOGS_LEVEL=debug
ORACLE_LOGS_BUFFER_SIZE=100
ORACLE_LOGS_FLUSH_INTERVAL=5
```

### Logging Configuration

Add the Oracle Cloud Logs driver to your `config/logging.php`:

```php
'channels' => [
    // ... other channels

    'oracle' => [
        'driver' => 'oracle',
        'level' => env('LOG_LEVEL', 'debug'),
        'bubble' => true,
        'buffer_size' => 100,
        'flush_interval' => 5,
    ],
],
```

## Usage

### Basic Usage

Once configured, you can use the Oracle Cloud Logs driver just like any other Laravel log channel:

```php
use Illuminate\Support\Facades\Log;

// Log to Oracle Cloud Logs
Log::channel('oracle')->info('User logged in', ['user_id' => 123]);
Log::channel('oracle')->error('Database connection failed', ['error' => $exception->getMessage()]);
Log::channel('oracle')->debug('Processing payment', ['order_id' => 456]);
```

### Using as Default Log Channel

You can set Oracle Cloud Logs as your default log channel by updating your `.env` file:

```env
LOG_CHANNEL=oracle
```

### Advanced Usage

You can also access the driver directly for more control:

```php
use Jrluis\LaravelOracleLogs\OracleCloudLogsClient;

$client = app(OracleCloudLogsClient::class);

// Send a single log entry
$client->sendLog([
    'time' => now()->toISOString(),
    'data' => [
        'level' => 'INFO',
        'message' => 'Custom log message',
        'context' => ['key' => 'value']
    ]
]);
```

## Configuration Options

### Driver Configuration

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `level` | string | `debug` | Minimum log level to capture |
| `bubble` | boolean | `true` | Whether to bubble up to other handlers |
| `buffer_size` | integer | `100` | Number of logs to buffer before sending |
| `flush_interval` | integer | `5` | Seconds between automatic flushes |

### Oracle Cloud Configuration

| Option | Type | Required | Description |
|--------|------|----------|-------------|
| `endpoint` | string | Yes | Oracle Cloud Logs API endpoint |
| `log_group_id` | string | Yes | OCID of the log group |
| `log_id` | string | Yes | OCID of the log |
| `region` | string | Yes | Oracle Cloud region |
| `tenancy_id` | string | Yes | OCID of your tenancy |
| `user_id` | string | Yes | OCID of the API user |
| `fingerprint` | string | Yes | Fingerprint of the API key |
| `private_key` | string | Yes | Private key content |
| `passphrase` | string | No | Passphrase for encrypted private key |

## Setting Up Oracle Cloud Infrastructure

### 1. Create a Log Group and Log

1. Log in to the Oracle Cloud Console
2. Navigate to **Logging** > **Log Groups**
3. Create a new log group
4. Create a new log within the log group
5. Note down the OCIDs for both the log group and log

### 2. Create an API Key

1. Navigate to **Identity** > **Users**
2. Select your user or create a new one
3. Go to **API Keys** section
4. Click **Add API Key**
5. Generate a new key pair or upload your public key
6. Note down the fingerprint

### 3. Set Up IAM Policies

Create a policy to allow the user to write to logs:

```json
{
  "statements": [
    {
      "effect": "Allow",
      "action": "log-ingestion:WriteLogs",
      "resource": "loggroup:ocid1.loggroup.oc1.xxxxx"
    }
  ]
}
```

## Performance Considerations

- **Batch Processing**: The driver automatically batches log entries to reduce API calls
- **Buffering**: Configure `buffer_size` based on your log volume
- **Flush Intervals**: Adjust `flush_interval` to balance between performance and log delivery speed
- **Error Handling**: Failed log deliveries won't affect your application's performance

## Troubleshooting

### Common Issues

1. **Authentication Errors**: Verify your API key, fingerprint, and private key are correct
2. **Permission Errors**: Ensure your user has the necessary IAM policies
3. **Network Issues**: Check your endpoint URL and network connectivity
4. **Buffer Issues**: Adjust buffer size if you're experiencing memory issues

### Debug Mode

Enable debug logging to troubleshoot issues:

```php
Log::channel('oracle')->debug('Debug message', ['context' => 'value']);
```

### Checking Logs

You can verify logs are being sent by checking the Oracle Cloud Console or using the OCI CLI:

```bash
oci logging log-entry list --log-group-id <log-group-id> --log-id <log-id>
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support, please open an issue on the GitHub repository or contact the development team.
