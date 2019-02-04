<?php
/**
 *  Copyright (c) 2018 Danilo Andrade.
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file RouteGroup.php
 *
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

namespace Apli\Router;

class RouteGroup
{
    use StrategyTrait, RouteCollectionTrait, RouteConditionHandlerTrait, MiddlewareTrait;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var RouteCollectionInterface
     */
    protected $collection;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param string                   $prefix
     * @param callable                 $callback
     * @param RouteCollectionInterface $collection
     */
    public function __construct($prefix, callable $callback, RouteCollectionInterface $collection)
    {
        $this->callback = $callback;
        $this->collection = $collection;
        $this->prefix = sprintf('/%s', ltrim($prefix, '/'));
    }

    /**
     * Return the prefix of the group.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Process the group and ensure routes are added to the collection.
     *
     * @return void
     */
    public function __invoke()
    {
        call_user_func_array($this->callback, [$this]);
    }

    /**
     * @param string $method
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function map($method, $path, $handler)
    {
        $path = ($path === '/') ? $this->prefix : $this->prefix.sprintf('/%s', ltrim($path, '/'));
        $route = $this->collection->map($method, $path, $handler);
        $route->setParentGroup($this);
        if ($host = $this->getHost()) {
            $route->setHost($host);
        }
        if ($scheme = $this->getScheme()) {
            $route->setScheme($scheme);
        }
        if ($port = $this->getPort()) {
            $route->setPort($port);
        }
        foreach ($this->getMiddlewareStack() as $middleware) {
            $route->middleware($middleware);
        }
        if (is_null($route->getStrategy()) && !is_null($this->getStrategy())) {
            $route->setStrategy($this->getStrategy());
        }

        return $route;
    }
}
