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

use Apli\Router\ContainerTrait;
use Apli\Router\Exception\MethodNotAllowedException;
use Apli\Router\Exception\NotFoundException;
use Apli\Router\HttpException;
use Apli\Router\Route;
use Apli\Router\Strategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonStrategy implements Strategy
{
    use ContainerTrait;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $responseFactory;

    /**
     * Construct.
     *
     * @param \Psr\Http\Message\ResponseInterface $responseFactory
     */
    public function __construct(ResponseInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Route                  $route
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request)
    {
        $response = call_user_func_array($route->getCallable($this->getContainer()), [$request, $route->getVars()]);
        if (is_array($response)) {
            $body = json_encode($response);
            $response = $this->responseFactory->createResponse();
            $response = $response->withStatus(200);
            $response->getBody()->write($body);
        }
        if ($response instanceof ResponseInterface && !$response->hasHeader('content-type')) {
            $response = $response->withAddedHeader('content-type', 'application/json');
        }
        return $response;
    }

    /**
     * @param NotFoundException $exception
     * @return MiddlewareInterface
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
     * @return \Psr\Http\Server\MiddlewareInterface
     */
    protected function buildJsonResponseMiddleware(HttpException $exception)
    {
        return new class($this->responseFactory->createResponse(), $exception) implements MiddlewareInterface
        {
            protected $response;
            protected $exception;

            public function __construct(ResponseInterface $response, HttpException $exception)
            {
                $this->response = $response;
                $this->exception = $exception;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface
            {
                return $this->exception->buildJsonResponse($this->response);
            }
        };
    }

    /**
     * @param MethodNotAllowedException $exception
     * @return MiddlewareInterface
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception)
    {
        return $this->buildJsonResponseMiddleware($exception);
    }

    /**
     * @return MiddlewareInterface
     */
    public function getExceptionHandler()
    {
        return new class($this->responseFactory->createResponse()) implements MiddlewareInterface
        {
            protected $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface
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
