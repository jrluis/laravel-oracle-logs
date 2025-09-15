# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of Laravel Oracle Cloud Logs Driver
- Support for Laravel 9, 10, and 11
- Batch processing for efficient log delivery
- Configurable buffer size and flush intervals
- Secure authentication using OCI API keys
- Comprehensive error handling
- Full documentation and examples

### Features
- **OracleCloudLogsClient**: Core client for interacting with Oracle Cloud Logs API
- **OracleLogsDriver**: Monolog handler for Laravel logging integration
- **OracleLogsServiceProvider**: Service provider for easy Laravel integration
- **Configuration**: Flexible configuration system with environment variables
- **Testing**: Unit and feature tests for reliability
- **Documentation**: Comprehensive README with setup and usage instructions

### Technical Details
- Uses Guzzle HTTP client for API communication
- Implements OCI signature-based authentication
- Supports both encrypted and unencrypted private keys
- Automatic log batching and buffering
- Graceful error handling to prevent application failures
- Compatible with Laravel's built-in logging system
