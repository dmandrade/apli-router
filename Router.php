<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file Router.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:04
 */

namespace Apli\Router;

use Apli\Router\DataGenerator\GroupGenerator;
use Apli\Router\Dispatcher\Dispatcher;
use Apli\Router\Parser\Std;
use Apli\Router\Strategy\ApplicationStrategy;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router implements RouteCollection
{
    use StrategyTrait, RouteCollectionTrait;

    /**
     * @var RouteParser
     */
    protected $routeParser;

    /**
     * @var DataGenerator
     */
    protected $dataGenerator;

    /**
     * @var Route[]
     */
    protected $routes = [];

    /**
     * @var Route[]
     */
    protected $namedRoutes = [];

    /**
     * @var RouteGroup[]
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $patternMatchers = [
        '/{(.+?):number}/' => '{$1:[0-9]+}',
        '/{(.+?):word}/' => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/' => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/' => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}'
    ];

    /**
     * Constructs a route collector.
     *
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser = null, DataGenerator $dataGenerator = null)
    {
        $this->routeParser = is_null($routeParser) ? new Std() : $routeParser;
        $this->dataGenerator = is_null($dataGenerator) ? new GroupGenerator() : $dataGenerator;
    }

    /**
     * @param string $method
     * @param string $path
     * @param        $handler
     * @return Route
     */
    public function map($method, $path, $handler)
    {
        $path = sprintf('/%s', ltrim($path, '/'));
        $route = new Route($method, $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    /**
     * Add a group of routes to the collection.
     *
     * @param string   $prefix
     * @param callable $group
     *
     * @return RouteGroup
     */
    public function group($prefix, callable $group)
    {
        $group = new RouteGroup($prefix, $group, $this);
        $this->groups[] = $group;
        return $group;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        if (is_null($this->getStrategy())) {
            $this->setStrategy(new ApplicationStrategy());
        }

        $this->prepRoutes($request);

        return (new Dispatcher($this->getData()))
            ->setStrategy($this->getStrategy())
            ->dispatchRequest($request);
    }

    /**
     * Prepare all routes, build name index and filter out none matching
     * routes before being passed off to the parser.
     *
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    protected function prepRoutes(ServerRequestInterface $request)
    {
        $this->processGroups($request);
        $this->buildNameIndex();

        /** @var Route[] $routes */
        $routes = array_merge(array_values($this->routes), array_values($this->namedRoutes));

        foreach ($routes as $key => $route) {
            // check for scheme condition
            if (!is_null($route->getScheme()) && $route->getScheme() !== $request->getUri()->getScheme()) {
                continue;
            }
            // check for domain condition
            if (!is_null($route->getHost()) && $route->getHost() !== $request->getUri()->getHost()) {
                continue;
            }
            // check for port condition
            if (!is_null($route->getPort()) && $route->getPort() !== $request->getUri()->getPort()) {
                continue;
            }
            if (is_null($route->getStrategy())) {
                $route->setStrategy($this->getStrategy());
            }
            $this->addRoute($route);
        }
    }

    /**
     * Process all groups, and determine if we are using a group's strategy.
     *
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    protected function processGroups(ServerRequestInterface $request)
    {
        $activePath = $request->getUri()->getPath();
        foreach ($this->groups as $key => $group) {
            // we want to determine if we are technically in a group even if the
            // route is not matched so exceptions are handled correctly
            if (strncmp($activePath, $group->getPrefix(), strlen($group->getPrefix())) === 0
                && !is_null($group->getStrategy())
            ) {
                $this->setStrategy($group->getStrategy());
            }
            unset($this->groups[$key]);
            $group();
        }
    }

    /**
     * Build an index of named routes.
     *
     * @return void
     */
    protected function buildNameIndex()
    {
        foreach ($this->routes as $key => $route) {
            if (!is_null($route->getName())) {
                unset($this->routes[$key]);
                $this->namedRoutes[$route->getName()] = $route;
            }
        }
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string          $route
     * @param mixed           $handler
     */
    protected function addRoute(Route $route)
    {
        $path = $this->parseRoutePath($route->getPath());
        $routeDatas = $this->routeParser->parse($path);
        foreach ($routeDatas as $routeData) {
            $this->dataGenerator->addRoute($route, $routeData);
        }
    }

    /**
     * Convenience method to convert pre-defined key words in to regex strings.
     *
     * @param string $path
     *
     * @return string
     */
    protected function parseRoutePath($path)
    {
        return preg_replace(array_keys($this->patternMatchers), array_values($this->patternMatchers), $path);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData()
    {
        return $this->dataGenerator->getData();
    }

    /**
     * Get named route.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException when no route of the provided name exists.
     *
     * @return Route
     */
    public function getNamedRoute($name)
    {
        $this->buildNameIndex();
        if (array_key_exists($name, $this->namedRoutes)) {
            return $this->namedRoutes[$name];
        }
        throw new InvalidArgumentException(sprintf('No route of the name (%s) exists', $name));
    }

    /**
     * Add a convenient pattern matcher to the internal array for use with all routes.
     *
     * @param string $alias
     * @param string $regex
     *
     * @return self
     */
    public function addPatternMatcher($alias, $regex)
    {
        $pattern = '/{(.+?):'.$alias.'}/';
        $regex = '{$1:'.$regex.'}';
        $this->patternMatchers[$pattern] = $regex;
        return $this;
    }
}
