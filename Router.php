<?php
/**
 *  Copyright (c) 2018 Danilo Andrade.
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file Router.php
 *
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:04.
 */

namespace Apli\Router;

use Apli\Router\DataGenerator\GroupGenerator;
use Apli\Router\Dispatcher\Dispatcher;
use Apli\Router\Parser\Std;
use Apli\Router\Strategy\ApplicationStrategy;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Router
 * @package Apli\Router
 */
class Router implements RouteCollectionInterface
{
    use StrategyTrait, RouteGroupTrait;

    /**
     * @var RouteParserInterface
     */
    protected $routeParser;

    /**
     * @var DataGeneratorInterface
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
     * @var array
     */
    protected static $patternMatchers = [
        '/{(.+?):number}/'        => '{$1:[0-9]+}',
        '/{(.+?):word}/'          => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/'          => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/'          => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}',
    ];

    /**
     * Constructs a route collector.
     *
     * @param RouteParserInterface   $routeParser
     * @param DataGeneratorInterface $dataGenerator
     */
    public function __construct(RouteParserInterface $routeParser = null, DataGeneratorInterface $dataGenerator = null)
    {
        $this->routeParser = $routeParser ?? new Std();
        $this->dataGenerator = $dataGenerator ?? new GroupGenerator();
    }

    /**
     * @return RouteCollectionInterface
     */
    protected function getRouteCollection(): RouteCollectionInterface
    {
        return $this;
    }

    /**
     * @param string $method
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function map($method, $path, $handler): Route
    {
        $route = new Route($method, $this->preparePath($path), $handler);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->getStrategy() === null) {
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
    protected function prepRoutes(ServerRequestInterface $request): void
    {
        $this->processGroups($request);
        $this->buildNameIndex();

        /** @var Route[] $allRoutes */
        $allRoutes = array_merge(array_values($this->routes), array_values($this->namedRoutes));

        foreach ($allRoutes as $route) {
            if (!$this->allowAddRoute([
                $route->getScheme() => [null, $request->getUri()->getScheme()],
                $route->getHost() => [null, $request->getUri()->getHost()],
                $route->getPort() => [null, $request->getUri()->getPort()],
            ])) {
                continue;
            }

            if ($route->getStrategy() === null) {
                $route->setStrategy($this->getStrategy());
            }
            $this->addRoute($route);
        }
    }

    /**
     * @param $conditions
     * @return bool
     */
    protected function allowAddRoute($conditions): bool
    {
        foreach ($conditions as $parameter => $values) {
            if (in_array($parameter, $values, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build an index of named routes.
     *
     * @return void
     */
    protected function buildNameIndex(): void
    {
        foreach ($this->routes as $key => $route) {
            if ($route->getName() !== null) {
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
     * @param Route $route
     */
    protected function addRoute(Route $route): void
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
    protected function parseRoutePath($path): string
    {
        return preg_replace(array_keys(self::$patternMatchers), array_values(self::$patternMatchers), $path);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->dataGenerator->getData();
    }

    /**
     * Get named route.
     *
     * @param string $name
     *
     * @throws InvalidArgumentException when no route of the provided name exists.
     *
     * @return Route
     */
    public function getNamedRoute($name): Route
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
    public function addPatternMatcher($alias, $regex): self
    {
        $pattern = '/{(.+?):'.$alias.'}/';
        $regex = '{$1:'.$regex.'}';
        self::$patternMatchers[$pattern] = $regex;

        return $this;
    }

    /**
     * @param array $patternMatchers
     * @return Router
     */
    public function setPatternMatchers(array $patternMatchers): self
    {
        self::$patternMatchers = $patternMatchers;
        return $this;
    }
}
