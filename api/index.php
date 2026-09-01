<?php

// Tentukan root path project Laravel
$_ENV['APP_BASE_PATH'] = dirname(__DIR__);

// Forward request ke public/index.php bawaan Laravel
require_once __DIR__ . '/../public/index.php';