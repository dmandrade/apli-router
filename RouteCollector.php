<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file RouteCollector.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 25/08/18 at 13:56
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:56
 */

namespace Apli\Router;


class RouteCollector
{

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var RouteCollectionInterface
     */
    protected $collection;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param string                        $prefix
     * @param callable                      $callback
     * @param RouteCollection $collection
     */
    public function __construct(string $prefix, callable $callback, RouteCollectionInterface $collection)
    {
        $this->callback   = $callback;
        $this->collection = $collection;
        $this->prefix     = sprintf('/%s', ltrim($prefix, '/'));
    }
}
