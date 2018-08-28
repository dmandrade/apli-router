<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file ApplicationStrategy.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 25/08/18 at 13:45
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:45
 */

namespace Apli\Router\Strategy;

use Apli\Router\ContainerTrait;
use Apli\Router\Exception\Exception;
use Apli\Router\Exception\MethodNotAllowedException;
use Apli\Router\Exception\NotFoundException;
use Apli\Router\Route;
use Apli\Router\Strategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationStrategy implements Strategy
{
    use ContainerTrait;

    /**
     * @param Route                  $route
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request)
    {
        return call_user_func_array($route->getCallable($this->getContainer()), [$request, $route->getVars()]);
    }

    /**
     * @param NotFoundException $exception
     * @return MiddlewareInterface
     */
    public function getNotFoundDecorator(NotFoundException $exception)
    {
        return $this->throwExceptionMiddleware($exception);
    }

    /**
     * @param MethodNotAllowedException $exception
     * @return MiddlewareInterface
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception)
    {
        return $this->throwExceptionMiddleware($exception);
    }
    /**
     * Return a middleware that simply throws and exception.
     *
     * @param \Exception $exception
     *
     * @return \Psr\Http\Server\MiddlewareInterface
     */
    protected function throwExceptionMiddleware(Exception $exception)
    {
        return new class($exception) implements MiddlewareInterface
        {
            protected $exception;
            public function __construct(Exception $exception)
            {
                $this->exception = $exception;
            }

            /**
             * @param ServerRequestInterface  $request
             * @param RequestHandlerInterface $requestHandler
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ) : ResponseInterface {
                throw $this->exception;
            }
        };
    }

    /**
     * @return MiddlewareInterface
     */
    public function getExceptionHandler()
    {
        return new class implements MiddlewareInterface
        {
            /**
             * @param ServerRequestInterface  $request
             * @param RequestHandlerInterface $requestHandler
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ) : ResponseInterface {
                try {
                    return $requestHandler->handle($request);
                } catch (Exception $e) {
                    throw $e;
                }
            }
        };
    }
}
