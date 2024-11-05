<?php

namespace Core;

class Route
{
    private static $routes = [];
    private static $currentModule = null;

    public static function setCurrentModule($module)
    {
        self::$currentModule = $module;
    }
    public static function get($path, $callback)
    {
        self::$routes['GET'][$path] = ['callback' => $callback, 'module' => self::$currentModule];
    }

    public static function post($path, $callback)
    {
        self::$routes['POST'][$path] = ['callback' => $callback, 'module' => self::$currentModule];
    }

    public static function put($path, $callback)
    {
        self::$routes['PUT'][$path] = ['callback' => $callback, 'module' => self::$currentModule];
    }

    public static function delete($path, $callback)
    {
        self::$routes['DELETE'][$path] = ['callback' => $callback, 'module' => self::$currentModule];
    }

    public static function getRoutes()
    {
        return self::$routes;
    }
}
