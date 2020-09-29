<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file IntegerRouterType.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 14:45
 */

namespace Apli\Router\ParamTypes;

/**
 * Class IntegerParamType
 * @package Apli\Router\ParamTypes
 */
class IntegerParamType extends AbstractParamType
{
    /**
     * @return string
     */
    public static function searchRegExp(): string
    {
        return '(\[i:'.self::PARAMETER_NAME_REGEXP.'+\])';
    }

    /**
     * @return string
     */
    public static function parserRegExp(): string
    {
        return '([-+]{0,1}[0-9]+)';
    }
}
