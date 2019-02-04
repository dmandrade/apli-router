<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file NotFoundException.php
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 14:24
 */

namespace Apli\Router\Exception;

use Apli\Router\Exception\Exception as HttpException;
use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends HttpException implements NotFoundExceptionInterface
{
    /**
     * Constructor
     *
     * @param string     $message
     * @param \Exception $previous
     * @param integer    $code
     */
    public function __construct($message = 'Not Found', Exception $previous = null, $code = 0)
    {
        parent::__construct(404, $message, $previous, [], $code);
    }
}
