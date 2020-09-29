<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file IntegerListRouterType.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 14:44
 */

namespace Apli\Router\ParamTypes;

/**
 * Class IntegerListParamType
 * @package Apli\Router\ParamTypes
 */
class IntegerListParamType extends AbstractParamType
{
    /**
     * @return string
     */
    public static function searchRegExp(): string
    {
        return '(\[il:'.self::PARAMETER_NAME_REGEXP.'\])';
    }

    /**
     * @return string
     */
    public static function parserRegExp(): string
    {
        return '([0-9,]+)';
    }
}
