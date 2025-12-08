<?php
return [
    'paths' => ['api/*'],
    // Methods I want to allow for CORS:
    'allowed_methods' => ['POST', 'GET', 'OPTIONS', 'PUT', 'DELETE'],
    // Origins from where I allow access without CORS interference:
    'allowed_origins' => ['http://localhost:8080', 'http://localhost:8000', 'http://localhost:8081', 'http://localhost:8082', 'http://localhost:8083', 'http://localhost:8084', 'https://mindfulme.pusatandalan.com', 'https://app-mindfulme.pusatandalan.com', 'https://api.mindfulme.pusatandalan.com'],

    'allowed_origins_patterns' => [],

    // Headers I want to allow to receive in frontend requests:
    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept'],

    'exposed_headers' => [],
    // Don't perform preflight until 1 hour has passed:
    'max_age' => 3600,
    // Indicates if the browser can send cookies (works with tokens):
    'supports_credentials' => true
];