<?php
/**
 *  Copyright (c) 2018 Danilo Andrade.
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file Strategy.php
 *
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:46.
 */

namespace Apli\Router;

use Apli\Router\Exception\{Exception, MethodNotAllowedException, NotFoundException};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\MiddlewareInterface;

/**
 * Interface Strategy.
 */
interface StrategyInterface
{
    /**
     * Invoke the route callable based on the strategy.
     *
     * @param Route                  $route
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface;

    /**
     * Get a middleware that will decorate a NotFoundException.
     *
     * @param Exception $exception
     *
     * @return MiddlewareInterface
     */
    public function getExceptionMiddlewareDecorator(Exception $exception): MiddlewareInterface;

    /**
     * Get a middleware that acts as an exception handler, it should wrap the rest of the
     * middleware stack and catch eny exceptions.
     *
     * @return MiddlewareInterface
     */
    public function getExceptionHandler(): MiddlewareInterface;
}
