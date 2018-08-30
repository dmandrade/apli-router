<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file MiddlewareTrait.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 15:24
 */

namespace Apli\Router;

use Psr\Http\Server\MiddlewareInterface;

trait MiddlewareTrait
{

    /**
     * @var \Psr\Http\Server\MiddlewareInterface[]
     */
    protected $middleware = [];

    /**
     * @param array $middlewares
     * @return self
     */
    public function middlewares(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->middleware($middleware);
        }
        return $this;
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function middleware(MiddlewareInterface $middleware)
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function prependMiddleware(MiddlewareInterface $middleware)
    {
        array_unshift($this->middleware, $middleware);
        return $this;
    }

    /**
     * @return MiddlewareInterface
     */
    public function shiftMiddleware()
    {
        return array_shift($this->middleware);
    }

    /**
     * @return iterable
     */
    public function getMiddlewareStack()
    {
        return $this->middleware;
    }
}
