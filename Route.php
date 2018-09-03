<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file Route.php
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

use Apli\Http\Message\Response;
use Apli\Http\Message\ServerRequest;
use Apli\Http\Server\Middleware;
use Apli\Http\Server\RequestHandler;
use Psr\Container\ContainerInterface;

class Route implements Middleware
{
    use StrategyTrait, RouteConditionHandlerTrait, MiddlewareTrait;

    /**
     * @var callable|string
     */
    protected $handler;

    /**
     * @var RouteGroup
     */
    protected $group;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $regex;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var array
     */
    protected $vars = [];

    /**
     * Route constructor.
     * @param string $method
     * @param string $path
     * @param        $handler
     */
    public function __construct($method, $path, $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @param string $regex
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    }

    /**
     * Tests whether this route matches the given string.
     *
     * @param string $str
     *
     * @return bool
     */
    public function matches($str)
    {
        $regex = '~^'.$this->regex.'$~';
        return (bool)preg_match($regex, $str);
    }

    /**
     * @param ServerRequest  $request
     * @param RequestHandler $requestHandler
     * @return Response
     */
    public function process(ServerRequest $request, RequestHandler $requestHandler)
    {
        return $this->getStrategy()->invokeRouteCallable($this, $request);
    }

    /**
     * Get the callable.
     *
     * @param ContainerInterface $container
     *
     * @throws \RuntimeException
     *
     * @return callable
     */
    public function getCallable(ContainerInterface $container = null)
    {
        $callable = $this->handler;
        if (is_string($callable) && strpos($callable, '::') !== false) {
            $callable = explode('::', $callable);
        }
        if (is_array($callable) && isset($callable[0]) && is_object($callable[0])) {
            $callable = [$callable[0], $callable[1]];
        }
        if (is_array($callable) && isset($callable[0]) && is_string($callable[0])) {
            $class = (!is_null($container) && $container->has($callable[0]))
                ? $container->get($callable[0])
                : new $callable[0];
            $callable = [$class, $callable[1]];
        }
        if (is_string($callable) && method_exists($callable, '__invoke')) {
            $callable = (!is_null($container) && $container->has($callable))
                ? $container->get($callable)
                : new $callable;
        }
        if (!is_callable($callable)) {
            throw new InvalidArgumentException('Could not resolve a callable for this route');
        }
        return $callable;
    }

    /**
     * Return vars to be passed to route callable.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * Set vars to be passed to route callable.
     *
     * @param array $vars
     *
     * @return self
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
        return $this;
    }

    /**
     * Get the parent group.
     *
     * @return RouteGroup
     */
    public function getParentGroup()
    {
        return $this->group;
    }

    /**
     * Set the parent group.
     *
     * @param RouteGroup $group
     *
     * @return self
     */
    public function setParentGroup(RouteGroup $group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the methods.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
