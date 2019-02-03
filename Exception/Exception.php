<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file Exception.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 14:27
 */

namespace Apli\Router\Exception;

use Psr\Http\Message\ResponseInterface;
use Apli\Router\HttpExceptionInterface;

class Exception extends \Exception implements HttpExceptionInterface
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
     * @var integer
     */
    protected $status;

    /**
     * Constructor.
     *
     * @param integer    $status
     * @param string     $message
     * @param \Exception $previous
     * @param array      $headers
     * @param integer    $code
     */
    public function __construct(
        $status,
        $message = null,
        \Exception $previous = null,
        array $headers = [],
        $code = 0
    )
    {
        $this->headers = $headers;
        $this->message = $message;
        $this->status = $status;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function buildJsonResponse(ResponseInterface $response)
    {
        $this->headers['content-type'] = 'application/json';
        foreach ($this->headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }
        if ($response->getBody()->isWritable()) {
            $response->getBody()->write(json_encode([
                'status_code' => $this->status,
                'reason_phrase' => $this->message
            ]));
        }
        return $response->withStatus($this->status, $this->message);
    }
}
