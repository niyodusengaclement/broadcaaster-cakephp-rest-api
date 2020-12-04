<?php
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::defaultRouteClass(DashedRoute::class);

Router::extensions(['json', 'xml']);

Router::scope('/', function (RouteBuilder $routes) {
    $routes->resources('Reports');
    $routes->resources('Users');

    $routes->fallbacks('InflectedRoute');
});