<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file ApplicationStrategy.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:45
 */

namespace Apli\Router\Strategy;

use Apli\Http\Message\Response;
use Apli\Http\Message\ServerRequest;
use Apli\Http\Server\Middleware;
use Apli\Http\Server\RequestHandler;
use Apli\Router\ContainerTrait;
use Apli\Router\Exception\Exception;
use Apli\Router\Exception\MethodNotAllowedException;
use Apli\Router\Exception\NotFoundException;
use Apli\Router\Route;
use Apli\Router\Strategy;

class ApplicationStrategy implements Strategy
{
    use ContainerTrait;

    /**
     * @param Route                  $route
     * @param ServerRequest $request
     * @return Response
     */
    public function invokeRouteCallable(Route $route, ServerRequest $request)
    {
        return call_user_func_array($route->getCallable($this->getContainer()), [$request, $route->getVars()]);
    }

    /**
     * @param NotFoundException $exception
     * @return Middleware
     */
    public function getNotFoundDecorator(NotFoundException $exception)
    {
        return $this->throwExceptionMiddleware($exception);
    }

    /**
     * Return a middleware that simply throws and exception.
     *
     * @param \Exception $exception
     *
     * @return Middleware
     */
    protected function throwExceptionMiddleware(Exception $exception)
    {
        return new class($exception) implements Middleware
        {
            protected $exception;

            public function __construct(Exception $exception)
            {
                $this->exception = $exception;
            }

            /**
             * @param ServerRequest $request
             * @param RequestHandler $requestHandler
             * @return Response
             */
            public function process(
                ServerRequest $request,
                RequestHandler $requestHandler
            )
            {
                throw $this->exception;
            }
        };
    }

    /**
     * @param MethodNotAllowedException $exception
     * @return Middleware
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception)
    {
        return $this->throwExceptionMiddleware($exception);
    }

    /**
     * @return Middleware
     */
    public function getExceptionHandler()
    {
        return new class implements Middleware
        {
            /**
             * @param ServerRequest  $request
             * @param RequestHandler $requestHandler
             * @return Response
             */
            public function process(
                ServerRequest $request,
                RequestHandler $requestHandler
            )
            {
                try {
                    return $requestHandler->handle($request);
                } catch (Exception $e) {
                    throw $e;
                }
            }
        };
    }
}
