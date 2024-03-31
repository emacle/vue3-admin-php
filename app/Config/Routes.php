<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('apix/v2/sys', ['namespace' => 'App\Controllers\Apix\V2\Sys'], function ($routes) {
    // $routes->resource('user');  // 优先下面精准增删除改查+options 6个方法
    $routes->get('user', 'User::index');
    $routes->get('user/(.*)', 'User::show/$1');
    $routes->post('user', 'User::create');
    $routes->put('user/(.*)', 'User::update/$1');
    $routes->delete('user/(.*)', 'User::delete/$1');
    $routes->options('user', 'User::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收

    // $routes->get('blog', 'Blog::index', ['filter' => 'AuthCheck']);  // 过滤器优先在Config/Filter里全局定义+排除
    $routes->resource('blog');
    $routes->options('blog', 'Blog::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收
    $routes->resource('dept');
    $routes->options('dept', 'Dept::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收

    //  Create REST API Route for employee Controllers
    $routes->resource('employee');
    $routes->options('employee', 'Employee::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收
});
