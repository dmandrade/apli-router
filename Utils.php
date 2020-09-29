<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file Utils.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 14:26
 */

namespace Apli\Router;

use function is_array;
use function implode;
use function trim;
use function is_string;
use function is_object;
use function get_class;
use function serialize;

class Utils
{

    /**
     * @param $route
     * @return string
     */
    public static function prepareRoute($route): string
    {
        if (is_array($route) && $route[0] === '') {
            $route = $_SERVER['REQUEST_URI'];
        }

        if (is_array($route)) {
            $route = implode('/', $route);
        }

        return trim($route, '/');
    }

    /**
     * @param $processor
     * @return string
     */
    public static function getCallableDescription($processor): string
    {
        if (is_string($processor)) {
            return $processor;
        } elseif (isset($processor[0]) && isset($processor[1])) {
            if (is_object($processor[0])) {
                return get_class($processor[0]) . '::' . $processor[1];
            } elseif (is_string($processor[0])) {
                return $processor[0] . '::' . $processor[1];
            }
        }

        return serialize($processor);
    }
}
