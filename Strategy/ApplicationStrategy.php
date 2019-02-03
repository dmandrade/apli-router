<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file ApplicationStrategy.php
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:45
 */

namespace Apli\Router\Strategy;

use Apli\Router\ContainerTrait;
use Apli\Router\Exception\MethodNotAllowedException;
use Apli\Router\Exception\NotFoundException;
use Apli\Router\Route;
use Apli\Router\StrategyInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationStrategy extends AbstractStrategy
{
    use ContainerTrait;

    /**
     * @param Route                  $route
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request)
    {
        $controller = $route->getCallable($this->getContainer());

        $response = $controller($request, $route->getVars());
        $response = $this->applyDefaultResponseHeaders($response);

        return $response;
    }

    /**
     * @param \Psr\Container\NotFoundExceptionInterface $exception
     * @return MiddlewareInterface|RequestHandlerInterface
     */
    public function getNotFoundDecorator(NotFoundExceptionInterface $exception)
    {
        return $this->throwExceptionMiddleware($exception);
    }

    /**
     * @param MethodNotAllowedException $exception
     * @return MiddlewareInterface
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception)
    {
        return $this->throwThrowableMiddleware($exception);
    }

    /**
     * Return a middleware that simply throws an error
     *
     * @param \Throwable $error
     *
     * @return \Psr\Http\Server\MiddlewareInterface
     */
    protected function throwThrowableMiddleware(Throwable $error)
    {
        return new class($error) implements MiddlewareInterface
        {
            protected $error;

            public function __construct(Throwable $error)
            {
                $this->error = $error;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface
            {
                throw $this->error;
            }
        };
    }

    /**
     * @return MiddlewareInterface
     */
    public function getExceptionHandler()
    {
        return $this->getThrowableHandler();
    }

    /**
     * @return MiddlewareInterface|__anonymous@2837
     */
    public function getThrowableHandler()
    {
        return new class implements MiddlewareInterface
        {
            /**
             * {@inheritdoc}
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface
            {
                try {
                    return $requestHandler->handle($request);
                } catch (Throwable $e) {
                    throw $e;
                }
            }
        };
    }
}
