<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// v4.5.0 filter顺序变化
$routes->group('api/v2/sys', ['namespace' => 'App\Controllers\Api\V2\Sys'], function ($routes) {
    // $routes->resource('user');  // 优先下面精准增删除改查+options 6个方法
    $routes->get('user', 'User::index');
    $routes->get('user/info', 'User::info'); // get 与user/(.*) 冲突，必须放在上面，优先选择
    $routes->get('user/(.*)', 'User::show/$1');
    $routes->post('user', 'User::create');
    $routes->put('user/(.*)', 'User::update/$1');
    $routes->delete('user/(.*)', 'User::delete/$1');
    $routes->options('user', 'User::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收

    $routes->options('user/login', 'User::login_options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收
    $routes->post('user/login', 'User::login');
    $routes->post('user/logout', 'User::logout');
    $routes->post('user/refreshtoken', 'User::refreshtoken');

    $routes->post('user/githubauth', 'User::githubauth');
    $routes->post('user/giteeauth', 'User::giteeauth');

    $routes->get('role', 'Role::index');
    $routes->post('role', 'Role::create');
    $routes->put('role/(.*)', 'Role::update/$1');
    $routes->delete('role/(.*)', 'Role::delete/$1');
    $routes->options('role', 'Role::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收

    $routes->get('menu', 'Menu::index');
    $routes->post('menu', 'Menu::create');
    $routes->put('menu/(.*)', 'Menu::update/$1');
    $routes->delete('menu/(.*)', 'Menu::delete/$1');
    $routes->options('menu', 'Menu::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收
    
    $routes->get('dept', 'Dept::index');
    $routes->post('dept', 'Dept::create');
    $routes->put('dept/(.*)', 'Dept::update/$1');
    $routes->delete('dept/(.*)', 'Dept::delete/$1');
    $routes->options('dept', 'Dept::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收

    // $routes->get('blog', 'Blog::index', ['filter' => 'AuthCheck']);  // 过滤器优先在Config/Filter里全局定义+排除
    // $routes->resource('blog');
    // $routes->options('blog', 'Blog::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收
    // $routes->resource('dept');
    // $routes->options('dept', 'Dept::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收

    //  Create REST API Route for employee Controllers
    // $routes->resource('employee');
    // $routes->options('employee', 'Employee::options'); // $routes->resource 不会创建options请求，但是cors里必须要有options的请求接收
});		

// // 白名单里的uri不认证
// $config['jwt_white_list'] = [
//     '/sys/user/login/post',
//     '/sys/user/logout/post',
//     '/sys/user/refreshtoken/post', // 刷新token接口需要在控制器内作权限验证,比较特殊
//     '/sys/user/githubauth/get', // github认证免授权
//     '/sys/user/giteeauth/get', // gitee码云认证免授权
//     // 下面接口uri 可以在菜单权限里面添加,再分配给对应角色即可, 方便/安全?
//     // 如果不想在前端菜单里添加,也可以直接在后端在控制器里单独做token验证,不用做权限认证
//     // 参考/sys/user/refreshtoken
//     '/sys/user/info/get',
//     '/sys/user/list/get',
//     '/sys/user/getroleoptions/get',
//     '/sys/user/getdeptoptions/get',
//     '/sys/user/password/put',
//     '/sys/role/allroles/get',
//     '/sys/role/allmenus/get',
//     '/sys/role/alldepts/get',
//     '/sys/role/rolemenu/post',
//     '/sys/role/rolerole/post',
//     '/sys/role/roledept/post',
//     '/sys/menu/treeoptions/get',

//     // 以下均为测试接口
//     'rest_server/get', // http://cirest.com:8890/rest_server 接口不认证 uri_string => rest_server
//     'welcome/get', // http://cirest.com:8890/welcome 接口不认证
//     '/example/users/get',
//     '/example/users/post',
//     '/example/users/delete',
//     '/sys/user/testapi/get', // 测试api接口不认证 http://cirest.com:8890/api/v2/sys/user/testapi         uri_string => api/v2/sys/user/testapi
//     '/sys/log/dbrestore/post',
// ];