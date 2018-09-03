<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file Strategy.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:46
 */

namespace Apli\Router;

use Apli\Http\Message\Response;
use Apli\Http\Message\ServerRequest;
use Apli\Http\Server\Middleware;
use Apli\Router\Exception\MethodNotAllowedException;
use Apli\Router\Exception\NotFoundException;

/**
 * Interface Strategy
 * @package Apli\Router\Strategy
 */
interface Strategy
{

    /**
     * Invoke the route callable based on the strategy.
     *
     * @param Route                  $route
     * @param ServerRequest $request
     *
     * @return Response
     */
    public function invokeRouteCallable(Route $route, ServerRequest $request);

    /**
     * Get a middleware that will decorate a NotFoundException
     *
     * @param NotFoundException $exception
     *
     * @return Middleware
     */
    public function getNotFoundDecorator(NotFoundException $exception);

    /**
     * Get a middleware that will decorate a NotAllowedException
     *
     * @param MethodNotAllowedException $exception
     *
     * @return Middleware
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception);

    /**
     * Get a middleware that acts as an exception handler, it should wrap the rest of the
     * middleware stack and catch eny exceptions.
     *
     * @return Middleware
     */
    public function getExceptionHandler();
}
