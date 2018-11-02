<?php

namespace Controller;

use \Psr\Container\ContainerInterface;

abstract class AbstractController
{
	/** @var ContainerInterface  */
	protected $container;
	/** @var \Slim\App  */
	protected $app;

	public function __construct(\Slim\App $app) {
		$this->container = $app->getContainer();
		$this->app = $app;
	}
}
