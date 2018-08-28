<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file NotFoundException.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 25/08/18 at 14:24
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 14:24
 */
namespace Apli\Router\Exception;

use Exception;
use Apli\Router\Exception\Exception as HttpException;

class NotFoundException extends HttpException
{
    /**
     * Constructor
     *
     * @param string      $message
     * @param \Exception $previous
     * @param integer     $code
     */
    public function __construct($message = 'Not Found', Exception $previous = null, $code = 0)
    {
        parent::__construct(404, $message, $previous, [], $code);
    }
}
