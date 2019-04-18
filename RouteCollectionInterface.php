<?php
/**
 *  Copyright (c) 2018 Danilo Andrade.
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file RouteCollection.php
 *
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:33.
 */

namespace Apli\Router;

/**
 * Interface RouteCollection.
 */
interface RouteCollectionInterface
{
    /**
     * Add a route to the map.
     *
     * @param string          $method
     * @param string          $path
     * @param callable|string $handler
     *
     * @return Route
     */
    public function map($method, $path, $handler);

    /**
     * Add a route that responds to GET HTTP method.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return Route
     */
    public function get($path, $handler);

    /**
     * Add a route that responds to POST HTTP method.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return Route
     */
    public function post($path, $handler);

    /**
     * Add a route that responds to PUT HTTP method.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return Route
     */
    public function put($path, $handler);

    /**
     * Add a route that responds to PATCH HTTP method.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return Route
     */
    public function patch($path, $handler);

    /**
     * Add a route that responds to DELETE HTTP method.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return Route
     */
    public function delete($path, $handler);

    /**
     * Add a route that responds to HEAD HTTP method.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return Route
     */
    public function head($path, $handler);

    /**
     * Add a route that responds to OPTIONS HTTP method.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return Route
     */
    public function options($path, $handler);

    /**
     * Add a group of routes to the collection.
     *
     * @param string   $prefix
     * @param callable $callback
     *
     * @return RouteGroup
     */
    public function group(string $prefix, callable $callback);
}
