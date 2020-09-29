<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file UrlParser.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 13:36
 */

namespace Apli\Router;

use Exception;
use function call_user_func;
use function is_array;
use function trim;

/**
 * Class Router
 * @package Apli\Router
 */
class Router
{
    use RoutesSet, UrlParser, ParamTypes;

    /**
     * @var array
     */
    private $invalidRouteErrorHandler;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $_SERVER['REQUEST_METHOD'] = $this->getRequestMethod();

        $this->invalidRouteErrorHandler = [
            $this,
            'noProcessorFoundErrorHandler'
        ];

        $this->initDefaultParamTypes();
    }

    /**
     * @return string
     */
    protected function getRequestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * @param string $route
     * @param $callback
     * @param string $requestMethod
     * @param string $routeName
     * @throws Exception
     */
    public function addRoute(
        string $route,
        $callback,
        $requestMethod = 'GET',
        string $routeName = ''): void
    {
        $route = trim($route, '/');

        if ($route == '*') {
            $this->universalRouteWasAdded = true;
        }

        if (is_array($requestMethod)) {
            foreach ($requestMethod as $method) {
                $this->addRoute($route, $callback, $method, $routeName);
            }
        } else {
            $this->validateRequestMethod($requestMethod);

            $this->routes[$requestMethod][$route] = $callback;
            // register route name
            $this->registerRouteName($routeName, $route);
        }
    }

    /**
     * @param string $route
     * @throws Exception
     */
    public function noProcessorFoundErrorHandler(string $route)
    {
        throw (new Exception(
            'The processor was not found for the route ' . $route . ' in ' . $this->getAllRoutesTrace()));
    }

    /**
     * @param callable $function
     * @return array
     */
    public function setNoProcessorFoundErrorHandler(callable $function)
    {
        $oldErrorHandler = $this->invalidRouteErrorHandler;

        $this->invalidRouteErrorHandler = $function;

        return $oldErrorHandler;
    }

    /**
     * @param mixed $route
     * @return mixed|bool|string|void
     * @throws Exception
     */
    public function callRoute($route)
    {
        $route = Utils::prepareRoute($route);
        $requestMethod = $this->getRequestMethod();
        $this->validateRequestMethod($requestMethod);
        $routesForMethod = $this->getRoutesForMethod($requestMethod);

        if (($result = $this->findStaticRouteProcessor($routesForMethod, $route)) !== false) {
            return $result;
        }

        if (($result = $this->findDynamicRouteProcessor($routesForMethod, $route)) !== false) {
            return $result;
        }

        call_user_func($this->invalidRouteErrorHandler, $route);
    }

    /**
     * @param $route
     * @return false|mixed
     */
    public function getCallback($route)
    {
        $route = Utils::prepareRoute($route);
        $requestMethod = $this->getRequestMethod();
        $routesForMethod = $this->getRoutesForMethod($requestMethod);

        if (($result = $this->getStaticRouteProcessor($routesForMethod, $route)) !== false) {
            return $result;
        }

        if (($result = $this->getDynamicRouteProcessor($routesForMethod, $route)) !== false) {
            return $result;
        }

        call_user_func($this->invalidRouteErrorHandler, $route);
    }
}
