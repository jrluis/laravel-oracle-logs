<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Oracle Cloud Logs Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Oracle Cloud Logs service integration.
    | You can obtain these values from your Oracle Cloud Console.
    |
    */

    'endpoint' => env('ORACLE_LOGS_ENDPOINT', 'https://logging.us-ashburn-1.oci.oraclecloud.com'),
    
    'log_group_id' => env('ORACLE_LOGS_GROUP_ID'),
    
    'log_id' => env('ORACLE_LOGS_ID'),
    
    'region' => env('ORACLE_LOGS_REGION', 'us-ashburn-1'),
    
    'tenancy_id' => env('ORACLE_LOGS_TENANCY_ID'),
    
    'user_id' => env('ORACLE_LOGS_USER_ID'),
    
    'fingerprint' => env('ORACLE_LOGS_FINGERPRINT'),
    
    'private_key' => env('ORACLE_LOGS_PRIVATE_KEY'),
    
    'passphrase' => env('ORACLE_LOGS_PASSPHRASE', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Log Driver Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Oracle Cloud Logs driver behavior.
    |
    */
    
    'level' => env('ORACLE_LOGS_LEVEL', 'debug'),
    
    'bubble' => env('ORACLE_LOGS_BUBBLE', true),
    
    'buffer_size' => env('ORACLE_LOGS_BUFFER_SIZE', 100),
    
    'flush_interval' => env('ORACLE_LOGS_FLUSH_INTERVAL', 5),
];
