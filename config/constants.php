<?php

// Check if running in CLI context
if (php_sapi_name() == 'cli') {
    // For CLI context, set a default value for $host
    $host = 'localhost';
} else {
    // For web context, use the actual HTTP_HOST if available, otherwise default to  'localhost'
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
}

if ($host === '34.68.123.137' || $host === '34.68.123.137') {
    return [
        'PRO_CODE_URL' => 'https://aims.officeos.in',
    ];
} else {
    return [
        'PRO_CODE_URL' => 'https://aims.officeos.in',
    ];
}
