<?php

namespace App\Controllers\Apix\V2\Sys;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

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
        //    $decoded = JWT::decode($Token, config_item('jwt_key'), ['HS256']); //HS256方式，这里要和签发的时候对应
        //     print_r($decoded);
        //            stdClass Object
        //            (
        //                [iss] => http://pocoyo.org
        //    [aud] => http://emacs.org
        //    [iat] => 1577348490
        //    [nbf] => 1577348490
        //    [data] => stdClass Object
        //            (
        //                [user_id] => 1
        //            [username] => admin
        //        )
        //
        //    [scopes] => role_access
        //            [exp] => 1577355690
        //)
        // $result = $this->some_model();

        // $result = $this->User_model->getUserInfo($jwt_obj->user_id);

        $result = $this->Medoodb->select('sys_user', '*', [
            "id" => $jwt_obj->user_id
        ]);

        if (empty($result)) {
            // 获取用户信息失败
            $response = [
                "code" => 50008,
                "message" => 'Login failed, unable to get user details.'
            ];
            return $this->respond($response);
        } else {
            // 获取用户信息成功
            $info1 = $result[0];
            
            $MenuTreeArr = $this->permission->getPermission($jwt_obj->user_id, 'menu', false);
            $asyncRouterMap = $this->permission->genVueRouter($MenuTreeArr, 'id', 'pid', 0);
            $CtrlPerm = $this->permission->getMenuCtrlPerm($jwt_obj->user_id);
    
            $info2 = [
                // "roles" => ["admin", "editor"],
                "roles" => $this->User_model->getUserRolesByUserId($jwt_obj->user_id),
                "introduction" => "I am a super administrator",
                // "avatar" => "https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif",
                "name" => "pocoyo",
                "identify" => "110000000000000000",
                "phone" => "13888888888",
                "ctrlperm" => $CtrlPerm,
                //                "ctrlperm" => [
                //                    [
                //                        "path" => "/sys/menu/view"
                //                    ],
                //                    [
                //                        "path" => "/sys/menu/add"
                //                    ],
                //                    [
                //                        "path" => "/sys/menu/download"
                //                    ]
                //                ],
                "asyncRouterMap" => $asyncRouterMap
            ];
            $info = array_merge($info1, $info2);
            return $this->respond($info1);

            $message = [
                "code" => 20000,
                "data" => $info,
                "_SERVER" => $_SERVER,
                "_GET" => $_GET
            ];
            $this->response($message, RestController::HTTP_OK);
        }

    
        // 获取用户信息成功
        if ($result['success']) {
            $info1 = $result['userinfo'];
            // 附加信息2
            $info2 = [
                // "roles" => ["admin", "editor"],
                "roles" => $this->User_model->getUserRolesByUserId($jwt_obj->user_id),
                "introduction" => "I am a super administrator",
                // "avatar" => "https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif",
                "name" => "pocoyo",
                "identify" => "110000000000000000",
                "phone" => "13888888888",
                "ctrlperm" => $CtrlPerm,
                //                "ctrlperm" => [
                //                    [
                //                        "path" => "/sys/menu/view"
                //                    ],
                //                    [
                //                        "path" => "/sys/menu/add"
                //                    ],
                //                    [
                //                        "path" => "/sys/menu/download"
                //                    ]
                //                ],
                "asyncRouterMap" => $asyncRouterMap
                //                "asyncRouterMap" => [
                //                [
                //                    "path" => '/sys',
                //                    "name" => 'sys',
                //                    "meta" => [
                //                        "title" => "系统管理",
                //                        "icon" => "sysset2"
                //                    ],
                //                    "component" => 'Layout',
                //                    "redirect" => '/sys/menu',
                //                    "children" => [
                //                        [
                //                            "path" => '/sys/menu',
                //                            "name" => 'menu',
                //                            "meta" => [
                //                                "title" => "菜单管理",
                //                                "icon" => "menu1"
                //                            ],
                //                            "component" => 'sys/menu/index',
                //                            "redirect" => '',
                //                            "children" => [
                //
                //                            ]
                //                        ],
                //                        [
                //                            "path" => '/sys/user',
                //                            "name" => 'user',
                //                            "meta" => [
                //                                "title" => "用户管理",
                //                                "icon" => "user"
                //                            ],
                //                            "component" => 'pdf/index',
                //                            "redirect" => '',
                //                            "children" => [
                //
                //                            ]
                //                        ],
                //                        [
                //                            "path" => '/sys/icon',
                //                            "name" => 'icon',
                //                            "meta" => [
                //                                "title" => "图标管理",
                //                                "icon" => "icon"
                //                            ],
                //                            "component" => 'svg-icons/index',
                //                            "redirect" => '',
                //                            "children" => [
                //
                //                            ]
                //                        ]
                //                    ]
                //                ],
                //                    [
                //                        "path" => '/sysx',
                //                        "name" => 'sysx',
                //                        "meta" => [
                //                            "title" => "其他管理",
                //                            "icon" => "plane"
                //                        ],
                //                        "component" => 'Layout',
                //                        "redirect" => '',
                //                        "children" => [
                //
                //                        ]
                //                    ]
                //                ]
            ];

            $info = array_merge($info1, $info2);

            $message = [
                "code" => 20000,
                "data" => $info,
                "_SERVER" => $_SERVER,
                "_GET" => $_GET
            ];
            $this->response($message, RestController::HTTP_OK);
        } else {
            $message = [
                "code" => 50008,
                "message" => 'Login failed, unable to get user details.'
            ];

            $this->response($message, RestController::HTTP_OK);
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
}
