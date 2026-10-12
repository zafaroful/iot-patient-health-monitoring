<?php
// Copy this file to config.php and fill in your values.
// config.php is git-ignored, never commit your real token.
return [
    // Blynk device Auth Token (Device > Device Info in the Blynk console)
    'blynk_token' => 'YOUR_BLYNK_AUTH_TOKEN',

    // Blynk server for your region, e.g. blynk.cloud, sgp1.blynk.cloud, fra1.blynk.cloud
    'blynk_host'  => 'blynk.cloud',

    // Virtual pins, must match include/config.h
    'pins' => [
        'heartRate'   => 'v0',
        'spo2'        => 'v1',
        'bodyTemp'    => 'v2',
        'roomTemp'    => 'v3',
        'humidity'    => 'v4',
    ],

    // Save a row to the log at most once every N seconds
    'log_interval' => 30,
];
