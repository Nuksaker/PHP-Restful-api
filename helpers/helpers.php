<?php
if (!function_exists('dd')) {
    function dd($var, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('ddd')) {
    function ddd($var, $message = '', $statusCode = 200)
    {
        $output = [
            'message' => $message,
            'debug_info' => $var,
            'type' => gettype($var),
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];

        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
