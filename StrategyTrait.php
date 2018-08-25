<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 *  @project apli
 *  @file StrategyTrait.php
 *  @author Danilo Andrade <danilo@webbingbrasil.com.br>
 *  @date 25/08/18 at 19:02
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 13:46
 */

namespace Apli\Router;

trait StrategyTrait
{

    /**
     * @var Strategy
     */
    protected $strategy;

    /**
     * Set the strategy.
     *
     * @param Strategy $strategy
     *
     * @return self
     */
    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * Gets the strategy.
     *
     * @return Strategy
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}
