<?php
/**
 *  Copyright (c) 2018 Danilo Andrade.
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file JsonStrategy.php
 *
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 15:40.
 */

namespace Apli\Router\Strategy;

use Apli\Router\ContainerTrait;
use Apli\Router\Exception\Exception;
use Apli\Router\HttpExceptionInterface;
use Apli\Router\Route;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonStrategy extends AbstractStrategy
{
    use ContainerTrait;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Construct.
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;

        $this->addDefaultResponseHeader('content-type', 'application/json');
    }

    /**
     * @param Route                  $route
     * @param ServerRequestInterface $request
     *
     * @throws NotFoundExceptionInterface
     *
     * @return mixed|ResponseInterface
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());
        $response = $controller($request, $route->getParameters());

        if ($this->isJsonEncodable($response)) {
            $body = json_encode($response);
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write($body);
        }

        return $this->applyDefaultResponseHeaders($response);
    }

    /**
     * Check if the response can be converted to JSON.
     *
     * Arrays can always be converted, objects can be converted if they're not a response already
     *
     * @param mixed $response
     *
     * @return bool
     */
    protected function isJsonEncodable($response): bool
    {
        if ($response instanceof ResponseInterface) {
            return false;
        }

        return is_array($response) || is_object($response);
    }

    /**
     * @param Exception $exception
     * @return MiddlewareInterface
     */
    public function getExceptionMiddlewareDecorator(Exception $exception): MiddlewareInterface
    {
        return $this->buildJsonResponseMiddleware($exception);
    }

    /**
     * Return a middleware that simply throws and exception.
     *
     * @param HttpExceptionInterface $exception
     *
     * @return MiddlewareInterface
     */
    protected function buildJsonResponseMiddleware(HttpExceptionInterface $exception): MiddlewareInterface
    {
        return new class($this->responseFactory->createResponse(), $exception) implements MiddlewareInterface {
            protected $response;
            protected $exception;

            /**
             *  constructor.
             *
             * @param ResponseInterface      $response
             * @param HttpExceptionInterface $exception
             */
            public function __construct(ResponseInterface $response, HttpExceptionInterface $exception)
            {
                $this->response = $response;
                $this->exception = $exception;
            }

            /**
             * @param ServerRequestInterface  $request
             * @param RequestHandlerInterface $handler
             *
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $this->exception->buildJsonResponse($this->response);
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
        return new class($this->responseFactory->createResponse()) implements MiddlewareInterface {
            protected $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                try {
                    return $requestHandler->handle($request);
                } catch (Throwable $exception) {
                    $finalResponse = $this->response;
                    if ($exception instanceof HttpExceptionInterface) {
                        return $exception->buildJsonResponse($finalResponse);
                    }
                    $finalResponse->getBody()->write(json_encode([
                        'status_code'   => 500,
                        'reason_phrase' => $exception->getMessage(),
                    ]));

                    return $finalResponse
                        ->withAddedHeader('content-type', 'application/json')
                        ->withStatus(500, strtok($exception->getMessage(), "\n"));
                }
            }
        };
    }
}
