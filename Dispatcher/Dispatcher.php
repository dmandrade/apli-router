<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file Dispatcher.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:40
 */

namespace Apli\Router\Dispatcher;

use Apli\Http\Message\Response;
use Apli\Http\Message\ServerRequest;
use Apli\Http\Server\RequestHandler;
use Apli\Router\Exception\MethodNotAllowedException;
use Apli\Router\Exception\NotFoundException;
use Apli\Router\MiddlewareTrait;
use Apli\Router\Route;
use Apli\Router\StrategyTrait;

class Dispatcher extends GroupDispatcher implements RequestHandler
{
    use StrategyTrait, MiddlewareTrait;

    /**
     * Dispatch the current route.
     *
     * @param ServerRequest $request
     *
     * @return Response
     */
    public function dispatchRequest(ServerRequest $request)
    {
        $match = $this->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($match[0]) {
            case self::NOT_FOUND:
                $this->setNotFoundDecoratorMiddleware();
                break;
            case self::METHOD_NOT_ALLOWED:
                $allowed = (array)$match[1];
                $this->setMethodNotAllowedDecoratorMiddleware($allowed);
                break;
            case self::FOUND:
                $match[1]->setVars($match[2]);
                $this->setFoundMiddleware($match[1]);
                break;
        }

        return $this->handle($request);
    }

    /**
     * Handle a not found route.
     *
     * @return void
     */
    protected function setNotFoundDecoratorMiddleware()
    {
        $middleware = $this->getStrategy()->getNotFoundDecorator(new NotFoundException());
        $this->prependMiddleware($middleware);
    }

    /**
     * Handles a not allowed route.
     *
     * @param array $allowed
     *
     * @return void
     */
    protected function setMethodNotAllowedDecoratorMiddleware(array $allowed)
    {
        $middleware = $this->getStrategy()->getMethodNotAllowedDecorator(
            new MethodNotAllowedException($allowed)
        );
        $this->prependMiddleware($middleware);
    }

    /**
     * Handle dispatching of a found route.
     *
     * @param Route $route
     *
     * @return void
     */
    protected function setFoundMiddleware(Route $route)
    {
        if (!is_null($route->getStrategy())) {
            $route->setStrategy($this->getStrategy());
        }

        // wrap entire dispatch process in exception handler
        $this->prependMiddleware($route->getStrategy()->getExceptionHandler());
        // add group and route specific niddlewares
        if ($group = $route->getParentGroup()) {
            $this->middlewares($group->getMiddlewareStack());
        }
        $this->middlewares($route->getMiddlewareStack());
        // add actual route to end of stack
        $this->middleware($route);
    }

    /**
     * @param ServerRequest $request
     * @return Response
     */
    public function handle(ServerRequest $request)
    {
        $middleware = $this->shiftMiddleware();

        if (is_null($middleware)) {
            throw new OutOfBoundsException('Reached end of middleware stack. Does your controller return a response?');
        }

        return $middleware->process($request, $this);
    }
}
