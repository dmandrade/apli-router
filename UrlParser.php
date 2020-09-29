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
use function array_keys;
use function strpos;
use function preg_replace;
use function implode;
use function preg_match_all;
use function str_replace;
use function preg_match;
use function call_user_func_array;
use function is_callable;
use function is_array;
use function call_user_func;
use function method_exists;
use function trim;
use function array_search;
/**
 * Trait UrlParser
 * @package Apli\Router
 */
trait UrlParser
{

    /**
     * Parsed parameters of the calling router
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Called route
     *
     * @var string
     */
    protected $calledRoute = '';

    /**
     * Cache for regular expressions
     *
     * @var array
     */
    private $cachedRegExps = [];

    /**
     * Cached parameters for route
     *
     * @var array
     */
    private $cachedParameters = [];

    /**
     * Middleware for routes processing
     *
     * @var array
     */
    private $middleware = [];

    /**
     * Method warms cache
     */
    public function warmCache(): void
    {
        foreach (self::getListOfSupportedRequestMethods() as $requestMethod) {
            $routesForMethod = $this->getRoutesForMethod($requestMethod);

            foreach (array_keys($routesForMethod) as $routerPattern) {
                // may be it is static route?
                if (strpos($routerPattern, '[') === false) {
                    // it is static route, so skip it
                    continue;
                }

                $this->_getRouteMatcherRegExPattern($routerPattern);

                $this->_getParameterNames($routerPattern);
            }
        }
    }

    /**
     * @param string $routerPattern
     * @return string
     */
    private function _getRouteMatcherRegExPattern(string $routerPattern): string
    {
        // try read from cache
        if (isset($this->cachedRegExps[$routerPattern])) {
            return $this->cachedRegExps[$routerPattern];
        }

        // parsing routes
        $compiledRouterPattern = $routerPattern;
        foreach ($this->types as $typeClass) {
            $compiledRouterPattern = preg_replace(
                '/' . $typeClass::searchRegExp() . '/',
                $typeClass::parserRegExp(),
                $compiledRouterPattern);
        }

        // final setup + save in cache
        $this->cachedRegExps[$routerPattern] = $compiledRouterPattern;

        return $compiledRouterPattern;
    }

    /**
     * @param string $routerPattern
     * @return array
     */
    private function _getParameterNames(string $routerPattern): array
    {
        if (isset($this->cachedParameters[$routerPattern])) {
            return $this->cachedParameters[$routerPattern];
        }

        $regExPattern = [];

        foreach (array_keys($this->types) as $typeName) {
            $regExPattern[] = $typeName;
        }

        $regExPattern = '\[(' . implode('|', $regExPattern) . '):([a-zA-Z0-9_\-]+)\]';

        $names = [];
        preg_match_all('/' . str_replace('/', '\\/', $regExPattern) . '/', $routerPattern, $names);

        $return = [];

        foreach ($names[2] as $name) {
            $return[] = $name;
        }

        $this->cachedParameters[$routerPattern] = $return;

        return $return;
    }

    /**
     * @param array $processors
     * @param string $route
     * @return false|mixed
     * @throws Exception
     */
    public function findDynamicRouteProcessor(array &$processors, string $route)
    {
        $processor = $this->getDynamicRouteProcessor($processors, $route);

        if ($processor === false) {
            return false;
        }

        return $this->executeHandler($processor, $route);
    }

    /**
     * @param array $processors
     * @param string $route
     * @return false|mixed
     */
    protected function getDynamicRouteProcessor(array &$processors, string $route)
    {
        $values = [];

        foreach ($processors as $pattern => $processor) {
            // may be it is static route?
            $regExPattern = $this->_getRouteMatcherRegExPattern($pattern);

            // try match
            $pattern = '/^' . str_replace('/', '\\/', $regExPattern) . '$/';
            if (preg_match($pattern, $route, $values)) {
                // fetch parameter names
                $names = $this->_getParameterNames($pattern);

                $this->parameters = [];
                foreach ($names as $i => $name) {
                    $this->parameters[$name] = $values[$i + 1];
                }

                $this->calledRoute = $pattern;

                return $processor;
            }
        }

        // match was not found
        return false;
    }

    /**
     * @param $processor
     * @param string $route
     * @return mixed
     * @throws Exception
     */
    protected function executeHandler($processor, string $route)
    {
        if($this->isIvokable($processor)) {
            $processor = new $processor();
            return (new $processor)(...$this->getMiddlewareResult($route));
        }

        if ($this->isFunction($processor)) {
            return call_user_func_array($processor, $this->getMiddlewareResult($route));
        }

        $functionName = is_array($processor) ? $processor[1] : null;

        if ($this->canBeCalled($processor, $functionName)) {
            // passing route path and parameters
            return call_user_func_array($processor, $this->getMiddlewareResult($route));
        }

        $callableDescription = Utils::getCallableDescription($processor);

        if ($this->methodDoesNotExists($processor, $functionName)) {
            throw (new Exception("'$callableDescription' does not exists"));
        } else {
            throw (new Exception("'$callableDescription' must be callable entity"));
        }
    }

    private function isIvokable($processor): bool
    {
        return is_string($processor) && class_exists($processor) && method_exists($processor, '__invoke');
    }

    /**
     * @param $processor
     * @return bool
     */
    private function isFunction($processor): bool
    {
        return is_callable($processor) && is_array($processor) === false;
    }

    /**
     * @param string $route
     * @return array
     */
    private function getMiddlewareResult(string $route): array
    {
        return isset($this->middleware[$this->calledRoute]) ? call_user_func(
            $this->middleware[$this->calledRoute],
            $route,
            $this->parameters) : [
            $route,
            $this->parameters
        ];
    }

    /**
     * @param $processor
     * @param string|null $functionName
     * @return bool
     */
    private function canBeCalled($processor, ?string $functionName): bool
    {
        return is_callable($processor) &&
            (method_exists($processor[0], $functionName) || isset($processor[0]->$functionName));
    }

    /**
     * @param $processor
     * @param string|null $functionName
     * @return bool
     */
    private function methodDoesNotExists($processor, ?string $functionName): bool
    {
        return isset($processor[0]) && method_exists($processor[0], $functionName) === false;
    }

    /**
     * @param string $router
     * @param callable $middleware
     */
    public function registerMiddleware(string $router, callable $middleware): void
    {
        $this->middleware[trim($router, '/')] = $middleware;
    }

    /**
     * @param $processors
     * @param string $route
     * @return false|mixed
     * @throws Exception
     */
    public function findStaticRouteProcessor(&$processors, string $route)
    {
        $processor = $this->getStaticRouteProcessor($processors, $route);

        if ($processor === false) {
            return false;
        }

        return $this->executeHandler($processor, $route);
    }

    /**
     * @param $processors
     * @param string $route
     * @return false|mixed
     */
    protected function getStaticRouteProcessor(&$processors, string $route)
    {
        if (isset($processors[$route])) {
            $processor = $this->getExactRouteHandlerOrUniversal($processors, $route);
        } elseif (isset($processors['*'])) {
            $processor = $processors['*'];
        } else {
            return false;
        }

        return $processor;
    }

    /**
     * @param $processors
     * @param string $route
     * @return mixed
     */
    protected function getExactRouteHandlerOrUniversal(&$processors, string $route)
    {
        $this->calledRoute = $route;

        if ($this->universalRouteWasAdded) {
            $allRoutes = array_keys($processors);

            if (array_search('*', $allRoutes) <= array_search($route, $allRoutes)) {
                $processor = $processors['*'];
                $this->calledRoute = '*';
            } else {
                $processor = $processors[$route];
            }
        } else {
            $processor = $processors[$route];
        }

        return $processor;
    }

    /**
     * @param string $name
     * @return string
     * @throws Exception
     */
    public function getParam(string $name): string
    {
        if (isset($this->parameters[$name]) === false) {
            throw (new Exception('Parameter ' . $name . ' was not found in route', -1));
        }

        return $this->parameters[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasParam(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    public function reverse(string $routeName, array $parameters = []): string
    {
        $route = $this->getRouteByName($routeName);

        foreach ($parameters as $name => $value) {
            $route = preg_replace('/\[([A-Za-z_\-])\:' . $name . ']/', $value, $route);
        }

        return $route;
    }

    /**
     * @param string $routeName
     * @return string
     */
    public abstract function getRouteByName(string $routeName): string;
}
