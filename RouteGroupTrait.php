<?php
/**
 *  Copyright (c) 2019 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file RouteGroupTrait.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 18/04/19 at 11:52
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 14:15.
 */

namespace Apli\Router;

use Psr\Http\Message\ServerRequestInterface;
use function strlen;

trait RouteGroupTrait
{
    use RouteCollectionTrait;

    /**
     * @var RouteGroup[]
     */
    protected $groups = [];


    /**
     * Add a group of routes to the collection.
     *
     * @param string   $prefix
     * @param callable $callback
     *
     * @return RouteGroup
     */
    public function group(string $prefix, callable $callback) : RouteGroup
    {
        $group = new RouteGroup($prefix, $callback, $this);
        $this->groups[] = $group;

        return $group;
    }

    /**
     * Process all groups, and determine if we are using a group's strategy.
     *
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    protected function processGroups(ServerRequestInterface $request): void
    {
        $activePath = rtrim($request->getUri()->getPath(), '/').'/';
        foreach ($this->groups as $key => $group) {
            $path = $group->getPath() . '/';
            // we want to determine if we are technically in a group even if the
            // route is not matched so exceptions are handled correctly
            if (strncmp($activePath, $path, strlen($path)) === 0) {
                if($group->getStrategy() !== null) {
                    $this->setStrategy($group->getStrategy());
                }
                $group($request);
            }
            unset($this->groups[$key]);
        }
    }
}
