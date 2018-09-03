<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file JsonStrategy.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 15:40
 */

namespace Apli\Router\Strategy;

use Apli\Http\Message\Response;
use Apli\Http\Message\ResponseFactory;
use Apli\Http\Message\ServerRequest;
use Apli\Http\Server\Middleware;
use Apli\Http\Server\RequestHandler;
use Apli\Router\ContainerTrait;
use Apli\Router\Exception\MethodNotAllowedException;
use Apli\Router\Exception\NotFoundException;
use Apli\Router\HttpException;
use Apli\Router\Route;
use Apli\Router\Strategy;

class JsonStrategy implements Strategy
{
    use ContainerTrait;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * Construct.
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Route                  $route
     * @param ServerRequest $request
     * @return Response
     */
    public function invokeRouteCallable(Route $route, ServerRequest $request)
    {
        $response = call_user_func_array($route->getCallable($this->getContainer()), [$request, $route->getVars()]);
        if (is_array($response)) {
            $body = json_encode($response);
            $response = $this->responseFactory->createResponse();
            $response = $response->withStatus(200);
            $response->getBody()->write($body);
        }
        if ($response instanceof Response && !$response->hasHeader('content-type')) {
            $response = $response->withAddedHeader('content-type', 'application/json');
        }
        return $response;
    }

    /**
     * @param NotFoundException $exception
     * @return Middleware
     */
    public function getNotFoundDecorator(NotFoundException $exception)
    {
        return $this->buildJsonResponseMiddleware($exception);
    }

    /**
     * Return a middleware that simply throws and exception.
     *
     * @param \Exception $exception
     *
     * @return Middleware
     */
    protected function buildJsonResponseMiddleware(HttpException $exception)
    {
        return new class($this->responseFactory->createResponse(), $exception) implements Middleware
        {
            protected $response;
            protected $exception;

            public function __construct(Response $response, HttpException $exception)
            {
                $this->response = $response;
                $this->exception = $exception;
            }

            public function process(
                ServerRequest $request,
                RequestHandler $requestHandler
            )
            {
                return $this->exception->buildJsonResponse($this->response);
            }
        };
    }

    /**
     * @param MethodNotAllowedException $exception
     * @return Middleware
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception)
    {
        return $this->buildJsonResponseMiddleware($exception);
    }

    /**
     * @return Middleware
     */
    public function getExceptionHandler()
    {
        return new class($this->responseFactory->createResponse()) implements Middleware
        {
            protected $response;

            public function __construct(Response $response)
            {
                $this->response = $response;
            }

            public function process(
                ServerRequest $request,
                RequestHandler $requestHandler
            )
            {
                try {
                    return $requestHandler->handle($request);
                } catch (Exception $exception) {
                    $response = $this->response;
                    if ($exception instanceof HttpException) {
                        return $exception->buildJsonResponse($response);
                    }
                    $response->getBody()->write(json_encode([
                        'status_code' => 500,
                        'reason_phrase' => $exception->getMessage()
                    ]));
                    $response = $response->withAddedHeader('content-type', 'application/json');
                    return $response->withStatus(500, strtok($exception->getMessage(), "\n"));
                }
            }
        };
    }
}
