<?php
/**
 *  Copyright (c) 2018 Danilo Andrade.
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file AbstractDispatcher.php
 *
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:42.
 */

namespace Apli\Router\Dispatcher;

use Apli\Router\DispatcherInterface;

/**
 * Class AbstractDispatcher
 * @package Apli\Router\Dispatcher
 */
abstract class AbstractDispatcher implements DispatcherInterface
{
    /** @var array */
    protected $staticRouteMap = [];

    /** @var array */
    protected $variableRouteData = [];

    /**
     * @param string $httpMethod
     * @param string $uri
     * @return array
     */
    public function dispatch(string $httpMethod, string $uri): array
    {
        if ($uri !== '/' && !preg_match('/(.*)+\.[\w]{1,4}/', $uri)) {
            $uri = rtrim($uri, '/');
        }

        if ($httpMethod === 'HEAD') {
            $httpMethod = 'GET';
        }

        $varRouteData = $this->variableRouteData;
        $result = $this->getRouteForMethod($httpMethod, $uri);

        if (($result[0] === self::NOT_FOUND) && isset($varRouteData[$httpMethod])) {
            $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
        }

        // If nothing else matches, try fallback routes
        if ($result[0] === self::NOT_FOUND) {
            $result = $this->getRouteForMethod('*', $uri);
            if (($result[0] === self::NOT_FOUND) && isset($varRouteData['*'])) {
                $result = $this->dispatchVariableRoute($varRouteData['*'], $uri);
            }
        }

        if ($result[0] === self::NOT_FOUND) {
            $allowedMethods = $this->getAllowedMethods($httpMethod, $uri);

            // If there are no allowed methods the route simply does not exist
            if ($allowedMethods) {
                return [self::METHOD_NOT_ALLOWED, $allowedMethods];
            }
        }

        return $result;
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     * @return array
     */
    protected function getAllowedMethods(string $httpMethod, string $uri): array
    {
        $allowedMethods = [];
        foreach ($this->staticRouteMap as $method => $uriMap) {
            if ($method !== $httpMethod && isset($uriMap[$uri])) {
                $allowedMethods[] = $method;
            }
        }
        foreach ($this->variableRouteData as $method => $routeData) {
            if ($method === $httpMethod) {
                continue;
            }
            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result[0] === self::FOUND) {
                $allowedMethods[] = $method;
            }
        }

        return $allowedMethods;
    }

    /**
     * @param $uri
     * @return array
     */
    protected function fallbackRoute($uri): array
    {
        $result = $this->getRouteForMethod('*', $uri);
        if (($result[0] === self::NOT_FOUND) && isset($this->variableRouteData['*'])) {
            $result = $this->dispatchVariableRoute($this->variableRouteData['*'], $uri);
        }

        return $result;
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     * @return array
     */
    protected function getRouteForMethod(string $httpMethod, string $uri): array
    {
        if (isset($this->staticRouteMap[$httpMethod][$uri])) {
            $handler = $this->staticRouteMap[$httpMethod][$uri];

            return [self::FOUND, $handler, []];
        }

        return [self::NOT_FOUND];
    }

    /**
     * @param $routeData
     * @param $uri
     * @return array
     */
    abstract protected function dispatchVariableRoute($routeData, $uri): array;
}
