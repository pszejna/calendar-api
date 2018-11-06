<?php

namespace Controller;

use \Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractController
{
	/** @var ContainerInterface  */
	protected $container;
	/** @var \Slim\App  */
	protected $app;
	/** @var LoggerInterface  */
	protected $logger;

	public function __construct(\Slim\App $app) {
		$this->container = $app->getContainer();
		$this->app = $app;
		$this->logger = $this->container->get('logger');
	}
}
