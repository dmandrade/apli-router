<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file RoutesSet.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 13:35
 */

namespace Apli\Router;

use Exception;
use function trim;
use function count;
use function implode;
use function array_keys;
use function file_put_contents;
use function var_export;

/**
 * Trait RoutesSet
 * @package Apli\Router
 */
trait RoutesSet
{

    /**
     * List of routes by request method
     */
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
        'OPTION' => [],
        'PATCH' => []
    ];
    /**
     * This flag rises when we add route / * /
     *
     * @var bool
     */
    protected $universalRouteWasAdded = false;
    /**
     * Route names
     *
     * @var array
     */
    private $routeNames = [];

    /**
     * Clear all route data
     */
    public function clear()
    {
        $this->universalRouteWasAdded = false;

        $this->routeNames = [];

        foreach (self::getListOfSupportedRequestMethods() as $requestMethod) {
            $this->routes[$requestMethod] = [];
        }

        $this->cachedRegExps = [];

        $this->middleware = [];
    }

    /**
     * @return string[]
     */
    public static function getListOfSupportedRequestMethods(): array
    {
        return [
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'OPTION',
            'PATCH'
        ];
    }

    /**
     * @param string $route
     * @return bool
     */
    public function routeExists(string $route): bool
    {
        $route = trim($route, '/');

        foreach (self::getListOfSupportedRequestMethods() as $requestMethod) {
            if (isset($this->routes[$requestMethod][$route])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Method returns all available routes
     */
    public function getAllRoutesTrace()
    {
        $trace = [];

        foreach (self::getListOfSupportedRequestMethods() as $requestMethod) {
            if (count($this->routes[$requestMethod]) > 0) {
                $trace[] = $requestMethod . ':' . implode(', ', array_keys($this->routes[$requestMethod]));
            }
        }

        return implode('; ', $trace);
    }

    /**
     * @param string $route
     * @param callable $object
     * @param string $method
     */
    public function addGetRoute(string $route, callable $object, string $method): void
    {
        $this->routes['GET'][trim($route, '/')] = [
            $object,
            $method
        ];
    }

    /**
     * @param string $route
     * @param callable $object
     * @param string $method
     */
    public function addPostRoute(string $route, callable $object, string $method): void
    {
        $this->routes['POST'][trim($route, '/')] = [
            $object,
            $method
        ];
    }

    /**
     * @param string $routeName
     * @return string
     * @throws Exception
     */
    public function getRouteByName(string $routeName): string
    {
        if ($this->routeNameExists($routeName) === false) {
            throw (new Exception('Route with name ' . $routeName . ' does not exist'));
        }

        return $this->routeNames[$routeName];
    }

    /**
     * @param string $routeName
     * @return bool
     */
    protected function routeNameExists(string $routeName): bool
    {
        return isset($this->routeNames[$routeName]);
    }

    /**
     * @param string $filePath
     */
    public function dumpOnDisk(string $filePath = './cache/cache.php'): void
    {
        file_put_contents(
            $filePath,
            '<?php return ' .
            var_export(
                [
                    0 => $this->routes,
                    1 => $this->routeNames,
                    2 => $this->cachedRegExps,
                    3 => $this->cachedParameters
                ],
                true) . ';');
    }

    /**
     * @param string $filePath
     */
    public function loadFromDisk(string $filePath = './cache/cache.php'): void
    {
        list ($this->routes, $this->routeNames, $this->cachedRegExps, $this->cachedParameters) = require($filePath);
    }

    /**
     * @param string $requestMethod
     * @throws Exception
     */
    protected function validateRequestMethod(string $requestMethod): void
    {
        if (isset($this->routes[$requestMethod]) === false) {
            throw (new Exception('Unsupported request method'));
        }
    }

    /**
     * @param string $requestMethod
     * @return array
     */
    protected function &getRoutesForMethod(string $requestMethod): array
    {
        return $this->routes[$requestMethod];
    }

    /**
     * @param string $routeName
     * @param string $route
     */
    protected function registerRouteName(string $routeName, string $route): void
    {
        if ($routeName != '') {
            $this->routeNames[$routeName] = $route;
        }
    }
}
