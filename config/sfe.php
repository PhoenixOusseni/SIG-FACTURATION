<?php

return [
    'url' => env('SFE_BRIDGE_URL', 'http://127.0.0.1:8000'),
    'timeout' => (int) env('SFE_BRIDGE_TIMEOUT', 10),
    'health_check' => env('SFE_AUTOMATIC_HEALTH_CHECK', true),
    'automatic_signing' => env('SFE_AUTOMATIC_INVOICE_SIGNING', false),
];
