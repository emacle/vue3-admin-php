<?php

namespace App\Controllers\Api\V2\Sys;

use CodeIgniter\RESTful\ResourceController;
use Config\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nette\Utils\Strings;
use Exception;
use PDO;

class User extends ResourceController
{
    private $Medoodb;
    public function __construct()
    {
        $this->Medoodb = \Config\Services::medoo();
    }

    #region 登录
    public function login()
    {
        $username = $this->request->getVar('username'); // POST param
        $password = $this->request->getVar('password'); // POST param

        $result = $this->Medoodb->select('sys_user', '*', [
            "username" => $username,
            "password" => md5($password)
        ]);

        if (empty($result)) {
            $response = [
                "code" => 60204,
                "type" => 'error',
                "message" => 'Account and password are incorrect.',
                "data" => []
            ];
            return $this->respond($response);
        } else {
            // 用户名密码正确 生成token 返回
            $userInfo = $result[0];

            $time = time(); //当前时间
            $appConfig = config(App::class); // 获取app/Config/App.php文件夹里变量

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
                "message" => "login successful",
                "data" => [
                    "token" => JWT::encode($access_token, $appConfig->jwt_key, 'HS256'), //生成access_tokenToken,
                    "refresh_token" => JWT::encode($refresh_token, $appConfig->jwt_key, 'HS256') //生成refresh_token,
                ]
            ];

            return $this->respond($response);
        }
    }
    #endregion

    #region 获取用户信息
    public function info()
    {
        // /sys/user/info 不用认证但是需要提取出 access_token 中的 user_id 来拉取用户信息
        try {
            $Bearer = $this->request->getHeaderLine('Authorization');
            list($Token) = sscanf($Bearer, 'Bearer %s');
            if (is_null($Token)) {
                $response = [
                    "code" => 50014,
                    "message" => 'token is null',
                    "data" => []
                ];
                return $this->respond($response, 401);
            }
            $appConfig = config(App::class); // 获取app/Config/App.php文件夹里变量
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
                "name" => "zhangsan",
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
    #endregion

    #region 刷新token
    public function refreshtoken()
    {
        try {
            // 此处 $Token 应为refresh token 在前端 request 拦截器中做了修改
            // 刷新token接口需要在控制器内作权限验证,比较特殊,不能使用hook ManageAuth来验证
            $Bearer = $this->request->getHeaderLine('Authorization');
            list($Token) = sscanf($Bearer, 'Bearer %s');
            $appConfig = config(App::class); // 获取app/Config/App.php文件夹里变量

            $decoded = JWT::decode($Token, new Key($appConfig->jwt_key, 'HS256')); //HS256方式，这里要和签发的时候对应

            // $decoded = JWT::decode($Token, config_item('jwt_key'), ['HS256']); //HS256方式，这里要和签发的时候对应
            //            stdClass Object
            //            (
            //                [iss] => http://www.helloweba.net
            //                [aud] => http://www.helloweba.net
            //                [iat] => 1577668094
            //                [nbf] => 1577668094
            //                [exp] => 1577668094
            //                [user_id] => 2
            //                [count] => 0
            //            )

            $time = time(); //当前时间
            // 公用信息
            $payload = [
                'iat' => $time, //签发时间
                'nbf' => $time, //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
                'user_id' => $decoded->user_id, //自定义信息，不要定义敏感信息, 一般只有 userId 或 username
            ];

            $access_token = $payload;
            $access_token['scopes'] = 'role_access'; //token标识，请求接口的token
            $access_token['exp'] = $time + $appConfig->jwt_access_token_exp; //access_token过期时间,这里设置2个小时
            $new_access_token =  JWT::encode($access_token, $appConfig->jwt_key, 'HS256'); //生成access_tokenToken
            //        {
            //          "iss": "http://pocoyo.org",
            //          "aud": "http://emacs.org",
            //          "iat": 1577757920,
            //          "nbf": 1577757920,
            //          "user_id": "1",
            //          "scopes": "role_refresh",
            //          "exp": 1577758100,
            //          "count": 0
            //        }

            $count = $decoded->count + 1;
            if ($count > $appConfig->jwt_refresh_count) { // 在刷新token期间 {多次} 请求刷新token则表示活跃,可以重新生成刷新token以免刷新token过期后登录
                $refresh_token = $payload;
                $refresh_token['scopes'] = 'role_refresh'; //token标识，刷新access_token
                $refresh_token['exp'] = $time + $appConfig->jwt_refresh_token_exp;
                $refresh_token['count'] = 0; // 重置刷新TOKEN计数
                $new_refresh_token = JWT::encode($refresh_token, $appConfig->jwt_key, 'HS256'); // 这里可以根据需要重新生成 refresh_token
            } else { // 保持refresh_token过期时间及其他共公用信息,仅自增计数器
                $decoded->count++;
                $new_refresh_token = JWT::encode(json_decode(json_encode($decoded), true), $appConfig->jwt_key, 'HS256');
            }

            $response = [
                "code" => 20000,
                "message" => "success refresh token",
                "data" => [
                    "token" => $new_access_token,
                    "refresh_token" => $new_refresh_token
                ]
            ];
            return $this->respond($response);
        } catch (\Firebase\JWT\ExpiredException $e) {  // access_token过期
            $response = [
                "code" => 50015,
                "message" => $e->getMessage() . ' refresh_token过期, 请重新登录',
                "data" => []
            ];
            return $this->respond($response, 401);
        } catch (Exception $e) {  //其他错误
            $response = [
                "code" => 50015,
                "message" => $e->getMessage(),
                "data" => []
            ];
            return $this->respond($response, 401);
        }
    }
    #endregion

    #region 查
    public function index()
    {
        // $this->request->getVar(); // 该方法首先尝试从 POST 数据中获取参数值,如果不存在,则尝试从 GET 参数中获取。
        // GET /users?offset=1&limit=20&fields=id,username,email,listorder&sort=-listorder,+id&query=~username,status&username=admin&status=1
        // fields: 显示字段参数过滤配置,不设置则为全部
        $fields = $this->request->getVar('fields');
        $fields ? $columns = explode(",", $fields) : $columns = "*";
        // 显示字段过滤配置结束

        // GET /users?offset=1&limit=20&fields=id,username,email,listorder&sort=-listorder,+id&query=~username,status&username=admin&status=1
        // 分页参数配置
        $limit = $this->request->getVar('size') ? $this->request->getVar('size') : 10;
        $offset = $this->request->getVar('currentPage') ?  ($this->request->getVar('currentPage') - 1) *  $limit : 0; // 第几页
        $where = [
            "LIMIT" => [$offset, $limit]
        ];
        // 分页参数配置结束

        // GET /users?offset=1&limit=20&fields=id,username,email,listorder&sort=-listorder,+id&query=~username,status&username=admin&status=1
        // 存在排序参数则 获取排序参数 加入 $where，否则不添加ORDER条件
        $sort = $this->request->getVar('sort');
        if ($sort) {
            $where["ORDER"] = [];
            $sortArr = explode(",", $sort);
            foreach ($sortArr as $k => $v) {
                if (str_starts_with($v, '-')) { // true DESC
                    $key = Strings::substring($v, 1); //  去 '-'
                    $where["ORDER"][$key] = "DESC";
                } else {
                    $key = Strings::substring($v, 1); //  去 '+'
                    $where["ORDER"][$key] = "ASC";
                }
            }
        }
        // 排序参数结束

        // GET /users?offset=1&limit=20&fields=id,username,email,listorder&sort=-listorder,+id&query=~username,status&username=admin&status=1
        // 指定条件模糊或搜索查询,author like %zhangsan%, status=1 此时 total $wherecnt 条件也要发生变化
        // 查询字段及字段值获取
        // 如果存在query 参数以,分隔，且每个参数的有值才会增加条件
        $query = $this->request->getVar('query');
        if ($query) { // 存在才进行过滤,否则不过滤
            $queryArr = explode(",", $query);
            foreach ($queryArr as $k => $v) {
                if (str_starts_with($v, '~')) { // true   query=~username&status=1 以~开头表示模糊查询
                    $tmpKey = Strings::substring($v, 1); // username

                    $tmpValue = $this->request->getVar($tmpKey);
                    if (!is_null($tmpValue)) {
                        $where[$tmpKey . '[~]'] = $tmpValue;
                    }
                } else {
                    $tmpValue = $this->request->getVar($v);
                    if (!is_null($tmpValue)) {
                        $where[$v] = $tmpValue;
                    }
                }
            }
        }
        // 查询字段及字段值获取结束

        // 执行查询
        $UserArr = $this->Medoodb->select(
            "sys_user",
            $columns,
            $where
        );

        $sqlCmd = $this->Medoodb->log()[0];

        // 捕获错误信息
        if ($this->Medoodb->error) { // 如果出错 否则为NULL
            $response = [
                "code" => 20400,
                "sql" => $sqlCmd,
                "message" => $this->Medoodb->error
            ];
            return $this->respond($response, 400);
        }
    
        // 获取记录总数
        $wherecnt = array_diff_key($where, array_flip(["LIMIT", "ORDER"])); // 查询total去除排序字段，提高查询效率
        $total = $this->Medoodb->count("sys_user", $wherecnt);

        // 遍历该用户所属角色信息
        foreach ($UserArr as $k => $v) {
            $UserArr[$k]['role'] = [];
            // 获取用户角色
            // $RoleArr = $this->Medoodb->select(
            //     'sys_role',
            //     [
            //         "[><]sys_user_role" => ["sys_role.id" => "role_id"] // 表sys_role内联表sys_user_role
            //     ],
            //     [
            //         "@sys_role.id",
            //         "sys_role.name"

            //     ],
            //     [
            //         "sys_role.status" => 1,
            //         "sys_user_role.user_id" => $v['id'],
            //     ]
            // );

            // foreach ($RoleArr as $kk => $vv) {
            //     // array_push($UserArr[$k]['role'], intval($vv['id'])); // 字符串转数字 前端treeselect value与option 的id 必须类型一致
            //     array_push($UserArr[$k]['role'], (string)$vv['id']); // 数字转字符串 el-select value与option 的id 必须类型一致
            // }		

            $RoleArr = $this->Medoodb->select(
                'sys_user_role',
                'role_id [String]',
                [
                    "user_id" => $v['id']
                ]
            );
            $UserArr[$k]['role'] = $RoleArr;
        }

        // 获取用户所属部门信息
        foreach ($UserArr as $k => $v) {
            $DeptArr = [];
            // var_dump($v['dept_id']); return;
            if (isset($v['dept_id'])) {
                $DeptArr = $this->Medoodb->get(
                    'sys_dept',
                    '*',
                    [
                        "id" => $v['dept_id']
                    ]
                );
            }
            $UserArr[$k]['dept'] = empty($DeptArr) ? (object)[] : $DeptArr;
        }

        $response = [
            "code" => 20000,
            "data" => [
                'list' => $UserArr,
                'total' => $total,
                // "sql" => $sqlCmd
            ]
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
    #endregion

    #region 增
    public function create()
    {
        // --data-raw '{
        //     "username": "test",
        //     "password": "test",
        //      "tel": "",
        //     "email": "1@test.com",
        //     "status": 1,
        //     "listorder": 1000
        // }'
        $parms = get_object_vars($this->request->getVar());

        // 参数数据预处理
        $RoleArr = [];
        $DeptArr = [];
        if (isset($parms['role'])) {
            $RoleArr = $parms['role'];
            unset($parms['role']);    // 剔除role数组
        }
        if (isset($parms['dept'])) {
            $DeptArr = $parms['dept'];
            unset($parms['dept']);    // 剔除role数组
        }
        // 加入新增时间
        $parms['create_time'] = time();
        $parms['password'] = md5($parms['password']);

        $this->Medoodb->insert("sys_user", $parms);
        $user_id = $this->Medoodb->id();

        if (!$user_id) {
            $response = [
                "code" => 20403, // 403 的响应，表示禁止访问。告诉客户端某个操作是不允许的
                "type" => 'error',
                "message" => $parms['username'] . ' - 用户新增失败'
            ];
            return $this->respond($response);
        }

        // 处理关联角色
        $failed = false;
        $failedArr = [];
        foreach ($RoleArr as $k => $v) {
            $arr = ['user_id' => $user_id, 'role_id' => $v];
            $this->Medoodb->insert("sys_user_role", $arr);
            $ret = $this->Medoodb->id();

            if (!$ret) {
                $failed = true;
                array_push($failedArr, $arr);
            }
        }

        if ($failed) {
            $response = [
                "code" => 20403, // 403 的响应，表示禁止访问。告诉客户端某个操作是不允许的
                "type" => 'error',
                "message" => '用户关联角色失败 ' . json_encode($failedArr)
            ];
            return $this->respond($response);
        }

        // 处理关联部门
        $failed = false;
        $failedArr = [];
        foreach ($DeptArr as $k => $v) {
            $arr = ['user_id' => $user_id, 'dept_id' => $v];
            $this->Medoodb->insert("sys_user_dept", $arr);
            $ret = $this->Medoodb->id();

            if (!$ret) {
                $failed = true;
                array_push($failedArr, $arr);
            }
        }

        if ($failed) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => '用户关联部门失败 ' . json_encode($failedArr)
            ];
            return $this->respond($response);
        }

        $response = [
            "code" => 20000,
            "type" => 'success',
            "message" => $parms['username'] . ' - 用户新增成功'
        ];

        return $this->respondCreated($response);
    }
    #endregion

    #region 改
    public function update($id = null)
    {
        // $id类型可以在Routes.php中定义  $routes->put('user/(.*)', 'User::update/$1'); 默认$1是字符串
        $id = intval($id);
        // 处理更新用户资源的逻辑
        $parms = get_object_vars($this->request->getVar()); // 获取表单参数，类型为数组
        // 参数检验/数据预处理
        if (isset($parms['dept'])) {
            unset($parms['dept']);  // 剔除关联sys_dept中部门信息
        }
        // 超级管理员角色不允许修改
        if ($id == 1) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => $parms['username'] . ' - 超级管理员用户不允许修改'
            ];
            return $this->respond($response);
        }

        $hasUser = $this->Medoodb->has('sys_user', ['id' => $id]);
        if (!$hasUser) {
            $response = [
                "code" => 20404,
                "type" => 'error',
                'message' => '用户id（' . $id . '）不存在'
            ];
            return $this->respond($response, 404);
        }

        $RoleArr = [];
        if (isset($parms['role'])) {
            foreach ($parms['role'] as $k => $v) {
                $RoleArr[$k] = ['user_id' => $id, 'role_id' => $v];
            }
            unset($parms['role']);  // 剔除role数组
        }

        // 处理角色数组编辑操作
        $RoleSqlArr = $this->Medoodb->select(
            'sys_user_role',
            ['user_id', 'role_id'],
            [
                "user_id" => $id
            ]
        );

        $AddArr = array_diff_assoc2($RoleArr, $RoleSqlArr);
        // var_dump('------------只存在于前台传参 做添加操作-------------');
        // var_dump($AddArr);
        $failed = false;
        $failedArr = [];
        foreach ($AddArr as $k => $v) {
            $this->Medoodb->insert("sys_user_role", $v);
            $ret = $this->Medoodb->id();
            if (!$ret) {
                $failed = true;
                array_push($failedArr, $v);
            }
        }

        if ($failed) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => '用户添加关联角色失败 ' . json_encode($failedArr)
            ];
            $this->respond($response);
        }
        $DelArr = array_diff_assoc2($RoleSqlArr, $RoleArr);
        // var_dump('------------只存在于后台数据库 删除操作-------------');
        // var_dump($DelArr);
        $failed = false;
        $failedArr = [];
        foreach ($DelArr as $k => $v) {
            $result = $this->Medoodb->delete("sys_user_role", $v);

            if (!$result->rowCount()) {
                $failed = true;
                array_push($failedArr, $v);
            }
        }
        if ($failed) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => '用户删除关联角色失败 ' . json_encode($failedArr)
            ];
            $this->respond($response);
        }

        // 添加用户放在最后，先添加角色处理，失败后直接提前返回
        $where = ["id" => $id];
        $result = $this->Medoodb->update('sys_user', $parms, $where);

        if ($result->rowCount() > 0) {
            $response = [
                "code" => 20000,
                "type" => 'success',
                "message" => '用户（' . $parms['username'] . '）更新成功'
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                "code" => 20204,
                "type" => 'info',
                "message" => '用户数据未更新'
            ];
            return $this->respond($response);
        }
    }
    #endregion

    #region 删
    public function delete($id = null)
    {
        $id = intval($id);
        // 参数检验/数据预处理
        // 超级管理员用户不允许删除
        if ($id == 1) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => '超级管理员不允许删除'
            ];
            // todo: DELETE/UPDATE操作，返回响应体为空？
            return $this->respond($response);
        }

        // 处理删除用户资源的逻辑
        $hasUser = $this->Medoodb->has('sys_user', ['id' => $id]);
        if ($hasUser) {
            // 删除外键关联表 sys_user_role, sys_user_dept
            $this->Medoodb->delete('sys_user_role', ['user_id' => $id]);
            $this->Medoodb->delete('sys_user_dept', ['user_id' => $id]);
            $result = $this->Medoodb->delete('sys_user', ['id' => $id]);
            if ($result->rowCount() > 0) {
                $response = [
                    "code" => 20000,
                    "type" => 'success',
                    "message" => '删除成功'
                ];
                return $this->respondDeleted($response);
            } else {
                $response = [
                    "code" => 20403,
                    "type" => 'error',
                    "message" => '删除失败'
                ];
                return $this->respond($response);
            }
        } else {
            // return $this->failNotFound('No employee found');
            $response = [
                "code" => 20404,
                "type" => 'error',
                'message' => '用户id（' . $id . '）不存在'
            ];
            return $this->respond($response, 404);
        }
    }
    #endregion

    #region 重置密码，路由白名单
    public function repasswd($id = null)
    {
        $parms = get_object_vars($this->request->getVar()); // 获取表单参数，类型为数组
        // TODO: 后端使用Validator包进行参数密码复杂度校验与前端保持一致
        // use Respect\Validation\Validator as v;
        // use Respect\Validation\Exceptions\ValidationException;
        // try {
        //     // 使用check 来捕获异常信息 https://respect-validation.readthedocs.io/en/2.0/rules/AnyOf/
        //     v::keySet(
        //         v::key('passwordOrig', v::notEmpty()),
        //         v::key('password', v::regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[\S]{8,}$/')),
        //         v::key('rePassword', v::notEmpty())
        //     )->check($parms);
        //     v::keyValue('password_confirmation', 'equals', 'password')->check($parms);
        // } catch (ValidationException $e) {
        $userId =  getUserIdByToken($this->request->getHeaderLine('Authorization'));

        // 原密码校验
        $has = $this->Medoodb->has(
            'sys_user',
            [
                'id' => $userId,
                'password' => md5($parms['passwordOrig'])
            ]
        );
        if (!$has) {
            $response = [
                "code" => 20400,
                "type" => 'error',
                "message" => '原密码不正确'
            ];
            return $this->respond($response, 200);
        }

        // 更新密码
        $result = $this->Medoodb->update(
            'sys_user',
            ['password' => md5($parms['password'])],
            ['id' => $userId]
        );

        $result->rowCount() ? $response = [
            "code" => 20000,
            "type" => 'success',
            "message" => '密码更新成功'
        ] : $response = [
            "code" => 20204,
            "type" => 'error',
            "message" => '密码未更新'
        ];
        return $this->respond($response);
    }
    #endregion

    #region 路由白名单
    public function roleoptions()
    {
        // 根据useId 获取该用户拥有的角色权限选项，这里还是要简化，不必根据用户ID来获取对应的权限，防止前端显示数字id
        // $userId =  getUserIdByToken($this->request->getHeaderLine('Authorization'));
        // $sql = "SELECT
        //             CAST(r.id AS CHAR) AS value,
        //             r.name AS label
        //         FROM
        //             sys_role r
        //         INNER JOIN (
        //             SELECT
        //                 DISTINCT p.r_id AS role_id
        //             FROM
        //                 sys_perm AS p
        //             INNER JOIN
        //                 sys_role_perm AS rp ON p.id = rp.perm_id
        //             INNER JOIN
        //                 sys_user_role AS ur ON rp.role_id = ur.role_id
        //             WHERE
        //                 p.perm_type = 'role'
        //                 AND ur.user_id = :userId
        //         ) t ON t.role_id = r.id";
        // $RoleOptionsArr = $this->Medoodb->query($sql, [':userId' => $userId])->fetchAll(PDO::FETCH_ASSOC);
        // $sql = "SELECT
        //             CAST(r.id AS CHAR) AS value,
        //             r.name AS label
        //         FROM
        //             sys_role r";
        // $RoleOptionsArr = $this->Medoodb->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $RoleOptionsArr = $this->Medoodb->select('sys_role', [
            "id (value) [String]",
            "name (label)"
        ]);

        $response = [
            "code" => 20000,
            "data" => [
                "list" => $RoleOptionsArr
            ],
        ];
        return $this->respond($response);
    }
    #endregion

    #region 路由白名单，获取所有部门,此接口为用户管理中选择所有部门的接口，与角色选项不同不需要根据权限来设置
    public function deptoptions()
    {
        // 该查询需要在 MySQL 8.0 或更高版本中运行,因为它使用了递归公用表表达式 (RCTE) 特性
        $sql = "WITH RECURSIVE cte AS (
                SELECT id, pid, name, aliasname, listorder, status
                FROM sys_dept
                UNION ALL
                SELECT d.id, d.pid, d.name, d.aliasname, d.listorder, d.status
                FROM sys_dept d
                INNER JOIN cte c ON d.id = c.pid
              )
              SELECT DISTINCT id as value, pid, name as label, aliasname, listorder, status FROM cte;";
        // 执行查询
        $DeptArr = $this->Medoodb->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $DeptTreeObj = new \BlueM\Tree(
            $DeptArr,
            ['rootId' => 0, 'id' => 'value', 'parent' => 'pid']
        );
        $allDeptsTreeArr = $this->_dumpBlueMTreeNodes_dept($DeptTreeObj->getRootNodes());

        $response = [
            "code" => 20000,
            "data" => [
                "list" => $allDeptsTreeArr
            ],
        ];
        return $this->respond($response);
    }
    #endregion

    #region 私有函数
    /**
     * 遍历 BlueM\Tree 树对象，将数据格式化部门树
     */
    private function _dumpBlueMTreeNodes_dept($node)
    {
        $tree = array();

        foreach ($node as $k => $v) {
            $valArr = $v->toArray(); // 获取本节点属性数组
            // BlueM\Tree 对象 多余去除
            unset($valArr['parent']);

            if ($v->hasChildren()) { // 存在 children 则构造 children key，否则不添加
                $valArr['children'] = $this->_dumpBlueMTreeNodes_dept($v->getChildren());
            }

            $tree[] = $valArr;     // 循环数组添加元素 属于同一层级
        }

        return $tree;
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
                'svgIcon' => $valArr['icon'],
                'keepAlive' => true, // 前端默认缓存所有页面
                'alwaysShow' => $v->countChildren() ? true : false
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
    #endregion
}
