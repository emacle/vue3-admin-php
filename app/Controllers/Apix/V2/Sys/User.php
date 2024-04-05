<?php

namespace App\Controllers\Apix\V2\Sys;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use PDO;

class User extends ResourceController
{
    private $Medoodb;
    public function __construct()
    {
        $this->Medoodb = \Config\Services::medoo();
    }

    public function login()
    {
        $username = $this->request->getVar('username'); // POST param
        $password = $this->request->getVar('password'); // POST param

        $result = $this->Medoodb->select('sys_user', '*', [
            "AND" => [
                "username" => $username,
                "password" => md5($password)
            ]
        ]);

        if (empty($result)) {
            $response = [
                "code" => 60204,
                "message" => 'Account and password are incorrect.'
            ];
            return $this->respond($response);
        } else {
            // 用户名密码正确 生成token 返回
            $userInfo = $result[0];

            $time = time(); //当前时间
            $appConfig = config('App'); // 获取app/Config/文件夹里变量，如config('Pager')

            // 公用信息
            $payload = [
                'iat' => $time, //签发时间
                'nbf' => $time, //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
                'user_id' => $userInfo['id'], //自定义信息，不要定义敏感信息, 一般只有 userId 或 username
            ];

            $access_token = $payload;
            $access_token['scopes'] = 'role_access'; //token标识，请求接口的token
            $access_token['exp'] = $time + $appConfig->jwt_access_token_exp; //access_token过期时间,这里设置2个小时

            $refresh_token = $payload;
            $refresh_token['scopes'] = 'role_refresh'; //token标识，刷新access_token
            $refresh_token['exp'] = $time + $appConfig->jwt_refresh_token_exp; //refresh_token,这里设置30天
            $refresh_token['count'] = 0; // 刷新TOKEN计数, 在刷新token期间多次请求刷新token则表示活跃,可以重新生成刷新token以免刷新token过期后登录

            $response = [
                "code" => 20000,
                "data" => [
                    "token" => JWT::encode($access_token, $appConfig->jwt_key, 'HS256'), //生成access_tokenToken,
                    "refresh_token" => JWT::encode($refresh_token, $appConfig->jwt_key, 'HS256') //生成refresh_token,
                ]
            ];

            return $this->respond($response);
        }
    }

    public function info()
    {
        // /sys/user/info 不用认证但是需要提取出 access_token 中的 user_id 来拉取用户信息
        $Bearer = $this->request->getHeaderLine('Authorization');
        list($Token) = sscanf($Bearer, 'Bearer %s');
        $appConfig = config('App'); // 获取app/Config/文件夹里变量，如config('Pager')

        try {
            $jwt_obj = JWT::decode($Token, new Key($appConfig->jwt_key, 'HS256')); //HS256方式，这里要和签发的时候对应
        } catch (\Firebase\JWT\ExpiredException $e) {  // access_token过期
            $response = [
                "code" => 50014,
                "message" => $e->getMessage()
            ];
            return $this->respond($response, 401);
        } catch (Exception $e) {  //其他错误
            $response = [
                "code" => 50015,
                "message" => $e->getMessage()
            ];
            return $this->respond($response, 401);
        }

        $userInfo = $this->Medoodb->select('sys_user', '*', [
            "id" => $jwt_obj->user_id
        ]);

        if (empty($userInfo)) {
            // 获取用户信息失败
            $response = [
                "code" => 50008,
                "message" => 'Login failed, unable to get user details.'
            ];
            return $this->respond($response);
        } else {
            // 获取用户信息成功
            $info1 = $userInfo[0];

            // 获取用户控件权限 sys_menu.type = 2
            $CtrlPerm = $this->Medoodb->query(
                "SELECT basetbl.path
                    FROM (
                        SELECT p.*
                        FROM sys_user_role ur
                        JOIN sys_role_perm rp ON rp.role_id = ur.role_id
                        JOIN sys_perm p ON p.id = rp.perm_id
                        JOIN sys_role r ON r.id = ur.role_id
                        WHERE ur.user_id = :userId
                            AND r.status = 1
                            AND p.perm_type = 'menu'
                    ) t
                    LEFT JOIN sys_menu basetbl ON t.r_id = basetbl.id
                    WHERE basetbl.type = 2",
                [
                    ':userId' => $jwt_obj->user_id
                ]
            )->fetchAll(PDO::FETCH_ASSOC);

            // 生成Vue路由 $asyncRouterMap
            $rtblname = $this->Medoodb->select('sys_perm_type', 'r_table', [
                "type" => 'menu'
            ]);
            if (empty($rtblname)) {
                var_dump($this->request->getPath() . ' 获取基础表失败...');
                return;
            }
            $basetable = $rtblname[0];
            $query = "SELECT DISTINCT t.id AS perm_id, basetbl.*
                      FROM (
                          SELECT p.*
                          FROM sys_user_role ur
                          INNER JOIN sys_role_perm rp ON ur.role_id = rp.role_id
                          INNER JOIN sys_perm p ON rp.perm_id = p.id
                          INNER JOIN sys_role r ON ur.role_id = r.id
                          WHERE ur.user_id = :userId
                          AND r.status = 1
                          AND p.perm_type = 'menu'
                      ) t
                      LEFT JOIN $basetable basetbl ON t.r_id = basetbl.id
                      WHERE basetbl.type != 2
                      ORDER BY basetbl.listorder";
            $params = [":userId" => $jwt_obj->user_id];
            $MenuTreeArr = $this->Medoodb->query($query, $params)->fetchAll(PDO::FETCH_ASSOC);
            // 将SQL查询出的一维树形结构生成 BlueM\Tree 树对象结构，方便后续遍历操作
            $MenuTreeObj = new \BlueM\Tree(
                $MenuTreeArr,
                ['rootId' => 0, 'id' => 'id', 'parent' => 'pid']
            );
            // 从 $MenuTreeObj 对象中根节点遍历，生成vue路由菜单
            $asyncRouterMap = $this->_dumpBlueMTreeNodes($MenuTreeObj->getRootNodes());

            // 获取用户角色
            $roles = $this->Medoodb->select(
                'sys_role',
                [
                    "[><]sys_user_role" => ["sys_role.id" => "role_id"] // 表sys_role内联表sys_user_role
                ],
                [
                    "@sys_role.id", // @ = DISTINCT
                    "sys_role.name"
                ],
                [
                    "sys_role.status" => 1,
                    "sys_user_role.user_id" => $jwt_obj->user_id,
                ]
            );

            $info2 = [
                // "roles" => ["admin", "editor"],
                "roles" => $roles,
                "introduction" => "I am a super administrator",
                // "avatar" => "https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif",
                "name" => "pocoyo",
                "identify" => "110000000000000000",
                "phone" => "13888888888",
                "ctrlperm" => $CtrlPerm,
                //    "ctrlperm" => [
                //        [
                //            "path" => "/sys/menu/view"
                //        ],
                //    ],
                "asyncRouterMap" => $asyncRouterMap
            ];
            $info = array_merge($info1, $info2);

            $response = [
                "code" => 20000,
                "data" => $info,
                "_SERVER" => $_SERVER,
                "_GET" => $_GET
            ];
            return $this->respond($response);
        }
    }

    public function index()
    {
        $data = $this->Medoodb->select('sys_user', '*');

        // echo json_encode($data);
        $response = [
            "code" => 20000,
            "data" => $data,
        ];
        return $this->respond($response);
    }

    public function show($id = null)
    {
        // 处理获取指定用户资源的逻辑
        $data = [];
        if ($data) {
            return $this->respond($data);
        } else {
            return $this->failNotFound('No employee found');
        }
    }

    public function create()
    {
        // 处理创建用户资源的逻辑
        $response = [
            'status' => 201,
            'error' => null,
            'messages' => [
                'success' => 'Employee created successfully'
            ]
        ];
        return $this->respondCreated($response);
    }

    public function update($id = null)
    {
        // 处理更新用户资源的逻辑
    }

    public function delete($id = null)
    {
        // 处理删除用户资源的逻辑
        //
        echo $id;
    }

    /**
     * 遍历 BlueM\Tree 树对象，将数据格式化成 vue-router 结构的路由树或菜单树
     */
    private function _dumpBlueMTreeNodes($node)
    {
        $tree = array();

        foreach ($node as $k => $v) {
            $valArr = $v->toArray(); // 获取本节点属性数组

            // 构造 vue-admin 路由结构 meta
            $valArr['meta'] = [
                'title' => $valArr['title'],
                'icon' => $valArr['icon']
            ];
            // 删除组合成meta的元素title,icon 多余去除
            unset($valArr['title']);
            unset($valArr['icon']);

            // BlueM\Tree 对象 多余去除
            unset($valArr['parent']);

            if ($v->hasChildren()) { // 存在 children 则构造 children key，否则不添加
                $valArr['children'] = $this->_dumpBlueMTreeNodes($v->getChildren());
            }

            $tree[] = $valArr;     // 循环数组添加元素 属于同一层级
        }

        return $tree;
    }
}
