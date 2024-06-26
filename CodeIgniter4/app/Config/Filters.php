<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;
use \App\Filters\AuthCheckFilter;

class Filters extends BaseFilters
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, class-string|list<class-string>>
     *
     * [filter_name => classname]
     * or [filter_name => [classname1, classname2, ...]]
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'AuthCheck'     => AuthCheckFilter::class,
    ];

    /**
     * List of special required filters.
     *
     * The filters listed here are special. They are applied before and after
     * other kinds of filters, and always applied even if a route does not exist.
     *
     * Filters set by default provide framework functionality. If removed,
     * those functions will no longer work.
     *
     * @see https://codeigniter.com/user_guide/incoming/filters.html#provided-filters
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps', // Force Global Secure Requests
            'pagecache',  // Web Page Caching
        ],
        'after' => [
            'pagecache',   // Web Page Caching
            'performance', // Performance Metrics
            // toobar ci 4.5.1 cors 冲突解决
            'toolbar',     // Debug Toolbar
        ],
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array<string, array<string, array<string, string>>>|array<string, list<string>>
     */
    public array $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
            'invalidchars',
            'cors',
            'AuthCheck' => ['except' => [
                '/',
                'api/v2/sys/employee/*',
                'api/v2/sys/employee',
                'api/v2/sys/user/login',
                'api/v2/sys/user/logout',
                'api/v2/sys/user/info',
                // 'api/v2/sys/user/*', // TODO: 权限完善后需在此处删除 AuthCheckFilter.php

                // AuCheckFilter str_contains($uri_short, $uri_db)
                // 用户拥有接口 /sys/user/get 的权限则会拥有/sys/user/**/get 的所有的权限
                // 限制太精确会使用前端操作勾选过多 TODO:
                'api/v2/sys/user/refreshtoken',  // 此接口须为例外
                // 'api/v2/sys/user/repasswd',
                // 'api/v2/sys/user/roleoptions',
                // 'api/v2/sys/user/deptoptions',
                'api/v2/sys/user/githubauth',
                'api/v2/sys/user/giteeauth',

                // 'api/v2/sys/role/allmenus',
                // 'api/v2/sys/role/allroles',
                // 'api/v2/sys/role/alldepts',
                // 'api/v2/sys/role/rolemenus',
                // 'api/v2/sys/role/roleroles',
                // 'api/v2/sys/role/roledepts',
                // 'api/v2/sys/role/saveroleperm',
                // '/sys/role/allmenus/get',
                // '/sys/role/alldepts/get',
                // '/sys/role/rolemenu/post',
                // '/sys/role/rolerole/post',
                // '/sys/role/roledept/post',
                // '/sys/menu/treeoptions/get',
            ]], // route / 不应用该filter
        ],
        'after' => [
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'POST' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [];
}
