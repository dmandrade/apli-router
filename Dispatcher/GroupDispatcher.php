<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file GroupDispatcher.php
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:43
 */

namespace Apli\Router\Dispatcher;


class GroupDispatcher extends AbstractDispatcher
{
    /**
     * GroupDispatcher constructor.
     * @param $data
     */
    public function __construct($data)
    {
        list($this->staticRouteMap, $this->variableRouteData) = $data;
    }

    /**
     * @param $routeData
     * @param $uri
     * @return array|mixed[]
     */
    protected function dispatchVariableRoute($routeData, $uri)
    {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }
            list($handler, $varNames) = $data['routeMap'][count($matches)];
            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return [self::FOUND, $handler, $vars];
        }
        return [self::NOT_FOUND];
    }
}
