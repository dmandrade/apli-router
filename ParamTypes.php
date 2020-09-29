<?php
/*
 *  Copyright (c) 2020 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file RouteTypes.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 26/09/20 at 13:38
 */

namespace Apli\Router;

use function array_merge;

/**
 * Trait ParamTypes
 * @package Apli\Router
 */
trait ParamTypes
{

    /**
     * Supported types of URL parameters
     *
     * @var array
     */
    private $types = [];

    /**
     * @param string $typeName
     * @param string $className
     */
    public function addParamType(string $typeName, string $className): void
    {
        $this->types = array_merge([
            $typeName => $className
        ], $this->types);
    }

    /**
     * Init types
     */
    private function initDefaultParamTypes(): void
    {
        $this->types['i'] = '\Apli\Router\ParamTypes\IntegerParamType';
        $this->types['a'] = '\Apli\Router\ParamTypes\CommandParamType';
        $this->types['il'] = '\Apli\Router\ParamTypes\IntegerListParamType';
        $this->types['s'] = '\Apli\Router\ParamTypes\StringParamType';
        $this->types['fp'] = '\Apli\Router\ParamTypes\FixPointNumberParamType';
    }
}
