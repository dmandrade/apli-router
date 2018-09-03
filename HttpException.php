<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file HttpException.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 14:28
 */

namespace Apli\Router;

use Apli\Http\Message\Response;

interface HttpException
{

    /**
     * Return the status code of the http exceptions
     *
     * @return integer
     */
    public function getStatusCode();

    /**
     * Return an array of headers provided when the exception was thrown
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Accepts a response object and builds it in to a json representation of the exception.
     *
     * @param  Response $response
     *
     * @return Response
     */
    public function buildJsonResponse(Response $response);
}
