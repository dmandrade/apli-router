<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file CommandRouterType.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 14:39
 */

namespace Apli\Router\ParamTypes;

/**
 * Class AbstractParamType
 * @package Apli\Router\ParamTypes
 */
abstract class AbstractParamType
{
    /**
     * Regexp for parameter name
     *
     * @var string
     */
    const PARAMETER_NAME_REGEXP = '[a-zA-Z0-9_\-]*';

    /**
     * @return string
     */
    abstract public static function searchRegExp(): string;

    /**
     * @return string
     */
    abstract public static function parserRegExp(): string;
}
