<?php
/**
 *  Copyright (c) 2018 Danilo Andrade.
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file ApplicationStrategy.php
 *
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:45.
 */

namespace Apli\Router\Strategy;

use Apli\Router\ContainerTrait;
use Apli\Router\Exception\Exception;
use Apli\Router\Route;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ApplicationStrategy extends AbstractStrategy
{
    use ContainerTrait;

    /**
     * @param Route                  $route
     * @param ServerRequestInterface $request
     *
     * @throws NotFoundExceptionInterface
     *
     * @return ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());

        $response = $controller($request, $route->getParameters());
        return $this->applyDefaultResponseHeaders($response);
    }

    /**
     * @param Exception $exception
     * @return MiddlewareInterface
     */
    public function getExceptionMiddlewareDecorator(Exception $exception): MiddlewareInterface
    {
        return $this->throwThrowableMiddleware($exception);
    }

    /**
     * Return a middleware that simply throws an error.
     *
     * @param Throwable $error
     *
     * @return MiddlewareInterface
     */
    protected function throwThrowableMiddleware(Throwable $error): MiddlewareInterface
    {
        return new class($error) implements MiddlewareInterface {
            protected $error;

            public function __construct(Throwable $error)
            {
                $this->error = $error;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                throw $this->error;
            }
        };
    }

    /**
     * @return MiddlewareInterface
     */
    public function getExceptionHandler(): MiddlewareInterface
    {
        return $this->getThrowableHandler();
    }

    /**
     * @return MiddlewareInterface
     */
    public function getThrowableHandler(): MiddlewareInterface
    {
        return new class() implements MiddlewareInterface {
            /**
             * {@inheritdoc}
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                return $requestHandler->handle($request);
            }
        };
    }
}
