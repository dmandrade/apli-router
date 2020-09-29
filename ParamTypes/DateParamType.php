<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file DateRouterType.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 14:40
 */

namespace Apli\Router\ParamTypes;

/**
 * Class DateParamType
 * @package Apli\Router\ParamTypes
 */
class DateParamType extends AbstractParamType
{
    /**
     * @return string
     */
    public static function searchRegExp(): string
    {
        return '(\[date:'.self::PARAMETER_NAME_REGEXP.'\])';
    }

    /**
     * @return string
     */
    public static function parserRegExp(): string
    {
        return '([0-9]{4}-[0-9]{2}-[0-9]{2})';
    }
}
