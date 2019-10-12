<?php
/**
 *  Copyright (c) 2018 Danilo Andrade.
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file Exception.php
 *
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 14:27.
 */

namespace Apli\Router\Exception;

use Exception as BaseException;
use Apli\Router\HttpExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class Exception extends BaseException implements HttpExceptionInterface
{
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $status;

    /**
     * Constructor.
     *
     * @param int           $status
     * @param string        $message
     * @param BaseException $previous
     * @param array         $headers
     * @param int           $code
     */
    public function __construct(
        $status,
        $message = null,
        BaseException $previous = null,
        array $headers = [],
        $code = 0
    ) {
        $this->headers = $headers;
        $this->message = $message;
        $this->status = $status;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function buildJsonResponse(ResponseInterface $response): ResponseInterface
    {
        $this->headers['content-type'] = 'application/json';
        foreach ($this->headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }
        if ($response->getBody()->isWritable()) {
            $response->getBody()->write(json_encode([
                'status_code'   => $this->status,
                'reason_phrase' => $this->message,
            ]));
        }

        return $response->withStatus($this->status, $this->message);
    }
}
