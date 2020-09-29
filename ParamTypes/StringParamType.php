<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file StringRouterType.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 14:45
 */

namespace Apli\Router\ParamTypes;

/**
 * Class StringParamType
 * @package Apli\Router\ParamTypes
 */
class StringParamType extends AbstractParamType
{
    /**
     * @return string
     */
    public static function searchRegExp(): string
    {
        return '(\[s:'.self::PARAMETER_NAME_REGEXP.'\])';
    }

    /**
     * @return string
     */
    public static function parserRegExp(): string
    {
        return '([a-z0-9A-Z_\-.@%&:;,\s]+)';
    }
}
