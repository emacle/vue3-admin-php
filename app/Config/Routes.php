<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// 这将创建一个 RESTful API，用于处理 /api/posts 路径的 GET、POST、PUT、DELETE 请求。
$routes->resource('api/posts');

$routes->group('apix/v2/sys', ['namespace' => 'App\Controllers\Apix\V2\Sys'], function ($routes) {

    //  Create REST API Route for employee Controllers
    $routes->resource('employee');
    $routes->options('employee', 'Dept::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收
});
