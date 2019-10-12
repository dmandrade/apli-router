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

use function call_user_func;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RouteGroup
 *
 * @package Apli\Router
 */
class RouteGroup implements RouteCollectionInterface
{
    use StrategyTrait, RouteGroupTrait, RouteConditionHandlerTrait, MiddlewareTrait;

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
    public function __construct(string $prefix, callable $callback, RouteCollectionInterface $collection)
    {
        $this->callback = $callback;
        $this->collection = $collection;
        $this->prefix = $this->preparePath($prefix);
    }

    /**
     * @return RouteCollectionInterface
     */
    protected function getRouteCollection(): RouteCollectionInterface
    {
        return $this;
    }

    /**
     * Return the prefix of the group.
     *
     * @return string
     */
    public function getPrefix() : string
    {
        return (string) $this->prefix;
    }

    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return ($this->collection instanceof self ? $this->collection->getPath() : '').$this->getPrefix();
    }

    /**
     * Process the group and ensure routes are added to the collection.
     * @param ServerRequestInterface $request
     */
    public function __invoke(ServerRequestInterface $request)
    {
        call_user_func($this->callback, $this);
        $this->processGroups($request);
    }

    /**
     * @param string $method
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function map($method, $path, $handler) : Route
    {
        $route = $this->collection->map($method, $this->preparePath($path), $handler);
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
        if ($route->getStrategy() === null && $this->getStrategy() !== null) {
            $route->setStrategy($this->getStrategy());
        }

        return $route;
    }
}
