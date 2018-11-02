<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Controller\AuthorizeController;
use Controller\EventController;

// Routes

$app->get('/authorize[/{email}]', function (Request $request, Response $response, array $args) use ($app) {
	$controller = new AuthorizeController($app);
	return $controller->authorize($request, $response, $args);
})->setName('authorize');

$app->get('/event/{tag}/{email}', function(Request $request, Response $response, array $args) use ($app) {
	$controller = new EventController($app);
	return $controller->addEvent($request, $response, $args);
});