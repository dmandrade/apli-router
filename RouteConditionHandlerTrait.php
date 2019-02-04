<?php
/**
 *  Copyright (c) 2018 Danilo Andrade
 *
 *  This file is part of the apli project.
 *
 * @project apli
 * @file RouteConditionHandlerTrait.php
 * @author Danilo Andrade <danilo@webbingbrasil.com.br>
 * @date 27/08/18 at 10:26
 */

/**
 * Created by PhpStorm.
 * User: Danilo
 * Date: 25/08/2018
 * Time: 14:17
 */

namespace Apli\Router;


trait RouteConditionHandlerTrait
{

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var int
     */
    protected $port;

    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the host.
     *
     * @param string $host
     *
     * @return self
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Set the scheme.
     *
     * @param string $scheme
     *
     * @return self
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Get the port.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the port.
     *
     * @param int $port
     *
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }
}
