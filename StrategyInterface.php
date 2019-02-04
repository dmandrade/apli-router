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

use Apli\Router\Exception\MethodNotAllowedException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request);

    /**
     * Get a middleware that will decorate a NotFoundException.
     *
     * @param NotFoundExceptionInterface $exception
     *
     * @return MiddlewareInterface
     */
    public function getNotFoundDecorator(NotFoundExceptionInterface $exception);

    /**
     * Get a middleware that will decorate a NotAllowedException.
     *
     * @param MethodNotAllowedException $exception
     *
     * @return MiddlewareInterface
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception);

    /**
     * Get a middleware that acts as an exception handler, it should wrap the rest of the
     * middleware stack and catch eny exceptions.
     *
     * @return MiddlewareInterface
     */
    public function getExceptionHandler();
}
