<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file MethodNotAllowedException.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 14:30
 */

namespace Apli\Router\Exception;

use Apli\Router\Exception\Exception as HttpException;
use Exception;

class MethodNotAllowedException extends HttpException
{

    /**
     * Constructor
     *
     * @param array      $allowed
     * @param string     $message
     * @param \Exception $previous
     * @param integer    $code
     */
    public function __construct(array $allowed = [], $message = 'Method Not Allowed', Exception $previous = null, $code = 0)
    {
        $headers = [
            'Allow' => implode(', ', $allowed)
        ];
        parent::__construct(405, $message, $previous, $headers, $code);
    }
}
