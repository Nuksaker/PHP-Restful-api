<?php
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/';

    // Convert namespace separators to directory separators
    $class = str_replace('\\', '/', $class);

    // Remove 'App/' from the beginning of the class name
    $class = str_replace('App/', '', $class);

    $file = $baseDir . $class . '.php';

    if (file_exists($file)) {
        require_once $file;
        return true;
    }

    return false;
});
