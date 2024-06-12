# [vue3-admin-php-demo](http://pocoyo.rr.nu:10000)

# vue-php-admin 项目说明
通用后台角色权限管理系统, 基于 [v3-admin-vite](https://github.com/un-pany/v3-admin-vite) 和 [PHP CodeIgniter 4.5.0](https://github.com/codeigniter4/framework) RESTful 实现，采用前后端分离架构的权限管理系统，PHP快速开发平台，目标是搭建一套简洁易用的快速解决方案，可以帮助用户有效降低项目开发难度和成本。

以 [v3-admin-vite](https://github.com/un-pany/v3-admin-vite) 前端模板为基础，修改动态路由部分，实现菜单路由可根据后端角色进行动态加载。
后端路由权限基于 php-jwt 使用 PHP CI4 AuthFilter 作token及权限认证。

V3 Admin Vite 基于 Vue3、TypeScript、Element Plus、Pinia 和 Vite 等主流技术。

## 主要功能
- [x] 1. 系统登录：系统用户登录，`jwt token方式`
- [x] 2. 用户管理：新建用户，修改用户，删除用户，查询用户
- [x] 3. 角色管理：新建角色，修改角色，删除角色，查询角色
- [x] 4. 菜单管理：新建菜单，修改菜单，删除菜单，查询菜单
- [x] 5. 部门管理：新建部门，修改部门，删除部门，查询部门
- [x] 6. **JWT生成访问与刷新token， access_token过期后，根据refreshtoken刷新访问token，实现无缝刷新功能。refreshtoken 加入计数器,在有效期内接口调用超过一定次数自动续期** 
- [x] 7. 图标管理：加入svg图标
- [ ] 8. 图形验证码（`gregwar/captcha` 包生成）
- [ ] 9. 系统日志
- [x] 10. 简易请假审批流程
 
## 使用说明
### 目录结构
v3-admin-vite/ 前端模板

**CodeIgniter4 Config文件：** 
```
CodeIgniter4/app/Config
├── App.php
├── Cors.php      #配置跨域
├── Database.php  #数据库配置
├── Filters.php   #定义路由白名单，'AuthCheck'=>'except'里面路由不会进行权限验证
├── Routes.php    #定义路由

CodeIgniter4/app/Filters/
└── AuthCheckFilter.php  # 动态权限认证

CodeIgniter4/writable/logs/
├── index.html
└── log-2024-05-12.log   # 查看错误日志
```

**CodeIgniter4 RESTful API：**
```
CodeIgniter4/app/Controllers/
├── Api
│   └── V2
│       └── Sys
│           ├── Dept.php
│           ├── Employee.php
│           ├── Menu.php
│           ├── Role.php
│           └── User.php
├── BaseController.php
└── Home.php
```

### 前端

  1. 一键安装 .vscode 目录中推荐的插件
  2. node 版本 18.x 或 20+
  3. pnpm 版本 8.x 或最新版

进入项目目录
```sh
cd v3-admin-vite
```
  安装依赖
```sh
pnpm i
```

启动服务

```sh
pnpm dev
```

### 后端
   **php 8.1+**

1. composer 安装PHP依赖包

```sh
cd CodeIgniter4

# 根据composer.json 初始安装所有插件包
composer install

# 根据实际修改 app.baseURL , postman测试 http://{app.baseURL} API配置正确
cp env .env
```
2.创建数据库 vueadminv2, 使用root用户导入 vueadminv2-{date}.sql 文件

3.后端数据库连接配置 修改配置文件

`cat CodeIgniter4\app\Database.php`

```php
public array $medoodb = [
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'vueadminv2',
    // 根据实际配置
    'username' => 'vueadmin',
    'password' => 'vueadmin',
    'error' => PDO::ERRMODE_EXCEPTION,
    // ERRMODE_EXCEPTION PDO will throw a PDOException, and all following codes will be terminated, 
    // quickly pointing the finger at potential problem areas in your code.
];
```

### 生产环境部署
**Nginx配置**
```sh
server {
    listen 10000;
    server_name pocoyo.rr.nu;

    root /var/www/vue3-admin-php/v3-admin-vite/dist/;
    index index.html;

    location ^~ /api/v2 {
        proxy_pass http://pocoyo.rr.nu:10001;
    }
    location / {
        # 防止直接刷新页面(地址栏回车)服务端会直接报 404 错误。
        try_files $uri $uri/ /index.html;
    }
}

server {
    listen  10001;
    server_name pocoyo.rr.nu;

    root  /var/www/vue3-admin-php/CodeIgniter4/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;

        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
    # Enable browser caching of static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
        expires max;
        log_not_found off;
    }

    error_page 404 /index.php;

    # deny access to hidden files such as .htaccess
    location ~ /\. {
        deny all;
    }

}
```

## 角色权限说明
1. 这里将权限抽象成三种权限（可扩展更多），菜单类权限（包括控件按钮），角色类权限（用户可分配的角色），部门数据类权限（用户可查看的部门数据），参考 [角色权限组+资源分配](https://blog.csdn.net/qiuziqiqi/article/details/65437123)
2. 前端添加菜单，角色，部门的时候，后端生成对应的权限，写入 `sys_perm` 表，系统的超级管理员角色自动拥有了所有权限（也可根据具体业务需要进行设计）
3. 用户->角色->权限
4. 数据权限： 实际开发中，需要设置用户只能查看哪些部门的数据，这种情况一般称为数据权限。数据权限需要在对应的业务类型表里加入部门数据字段来进行sql条件限制
   在（系统管理-角色管理）设置需要数据权限的角色, 添加了角色授权范围 
   全部数据权限/部门数据权限/部门及以下数据权限/仅本人数据权限/自定数据权限
   业务代码逻辑可先根据授权范围，来处理来判断角色拥有的部门数据权限，全部数据权限则sql语句不做限制，
   部门数据及以下数据权限，及本人数据需要对sql语句做限制，自定义数据权限，则sql语句加入自定义的部门限制条件即可

 ![角色权限](CodeIgniter4/public/role_perm.png)

这里设计控件按钮操作权限与sys_menu表中path字段为一一对应，也即与route一一对应，通过前置过滤器 AuthCheckFilter.php 进行权限判断认证，`不必在每个路由前添加权限字符判断` eg

```sh
/sys/user/get		2  控件按钮类型为2
/sys/user/post		2
/sys/user/put		2
/sys/user/delete	2

用户拥有接口 /sys/user/get 的权限则会拥有/sys/user/**/get 的所有的权限，
// 'api/v2/sys/user/repasswd',  put ,如果有 /sys/user/put 权限则也会拥有 /sys/user/repasswd/put 重置密码的权限
// 'api/v2/sys/user/roleoptions', get
```

权限判断逻辑:

1. 先获取token，为空，返回401错误；不为空继续
2. JWT解析token获取userId,根据userId获取用户所属的角色拥有的控件权限即sys_menu表中 path字段
3. 根据当前请求uri,获取接口URI，$request->getUri()->getPath();  // string(19) "/apix/v2/sys/user/1"
4. 步骤2、3的值进行比较 str_contains 判断是否拥有权限，无权限则返回401错误，有则继续进入路由函数执行代码逻辑
5. 接步骤2，token解析出错，超时、异常等，返回前端，前面可根据refreshtoken继续进行新accesstoken获取，获取成功后前端实现无缝刷新；如果refreshtoken也过期，则前端跳转至登录页面，强制重新登录。（refreshtoken一般比 accesstoken 过期时间要长）

这里比较粗略，见 AuthCheckFilter.php `str_contains($uri_short, $uri_db)` 也可以限制更精确，缺点是用前端角色权限勾选过多。

同时针对不需要进行权限认证的route，可以 app/Config/Filters.php 里指定例外
```sh
 'AuthCheck' => ['except' => [
                '/',
                'api/v2/sys/user/login',
                'api/v2/sys/user/logout',
                'api/v2/sys/user/info',
                'api/v2/sys/user/refreshtoken',  // 此接口必须为例外
```


## 简易请假审批流程
完成STAFF请假流程审批，其余流程需要根据实际情况进行代码补充
参考 [OA审批系统 oa审批流程数据库设计](https://blog.51cto.com/u_13539/8490872)

## 数据库表说明

| Tables_in_vueadminv2 | 说明                                      |
|---------------------:|----------------------------------------- |
| sys_user             | 系统用户表                                 |
| sys_menu             | 系统菜单表                                 |
| sys_role             | 系统角色表                                 |
| sys_dept             | 系统部门表                                 |
| sys_perm             | 系统权限表                                 |
| sys_role_perm        | 角色权限关系表                              |
| sys_user_role        | 用户角色对应关系（可一对多）                  |
| sys_perm_type        | 权限类型（暂时未用到）                       |
| adm_audit_role       | 审批角色表                                 |
| adm_leave_form       | 请假流程表单                               |
| adm_process_flow     | 审批任务流程表                              |
| adm_notice           | 消息通知表                                 |
| keys                 | 未用                                      |
| logs                 | 未用                                      |
| article              | 测试                                      |

## RESTful
 - 使用 catfan/medoo 实现 **复杂分页过滤排序**
    
    前端GET请求参数与使用的 table 组件有关

```
GET /articles?currentPage=1&limit=30&sort=-id&fields=id,title,author&query=~author,title&author=888&title=world

size:  每页记录数，后台会配置默认值
currentPage: 第几页，后台会配置默认值
sort:   支持多个参数 &sort=-id,+author => id降序 author 升序
fileds: 指定要获取的显示字段 => 降低网络流量
query:  支持多个参数 &query=~author,title => author like 模糊查询， title精确查询 &author=888&title=world 需要配合query参数才有意义
```

## 前端目录树
## 多级菜单配置

## 特性
  - Vue3：采用 Vue3 + script setup 最新的 Vue3 组合式 API
  - Element Plus：Element UI 的 Vue3 版本
  - Pinia: 传说中的 Vuex5
  - Vite：真的很快
  - Vue Router：路由路由
  - TypeScript：JavaScript 语言的超集
  - PNPM：更快速的，节省磁盘空间的包管理工具
 
