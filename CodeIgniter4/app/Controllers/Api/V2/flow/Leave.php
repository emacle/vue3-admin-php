<?php

namespace App\Controllers\Api\V2\Flow;

use CodeIgniter\RESTful\ResourceController;
use Config\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nette\Utils\Strings;
use Exception;
use PDO;

class Leave extends ResourceController
{
    private $Medoodb;
    public function __construct()
    {
        $this->Medoodb = \Config\Services::medoo();
    }

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
        $limit = $this->request->getVar('limit') ? $this->request->getVar('limit') : 10;
        $offset = $this->request->getVar('offset') ?  ($this->request->getVar('offset') - 1) *  $limit : 0; // 第几页
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
        $wherecnt = []; // 计算total使用条件，默认为全部
        $query = $this->request->getVar('query');
        if ($query) { // 存在才进行过滤,否则不过滤
            $queryArr = explode(",", $query);
            foreach ($queryArr as $k => $v) {
                if (str_starts_with($v, '~')) { // true   query=~username&status=1 以~开头表示模糊查询
                    $tmpKey = Strings::substring($v, 1); // username

                    $tmpValue = $this->request->getVar($tmpKey);
                    if (!is_null($tmpValue)) {
                        $where[$tmpKey . '[~]'] = $tmpValue;
                        $wherecnt[$tmpKey . '[~]'] = $tmpValue;
                    }
                } else {
                    $tmpValue = $this->request->getVar($v);
                    if (!is_null($tmpValue)) {
                        $where[$v] = $tmpValue;
                        $wherecnt[$v] = $tmpValue;
                    }
                }
            }
        }
        // 查询字段及字段值获取结束

        // 执行查询
        $UserArr = $this->Medoodb->select(
            "adm_leave_form",
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
        $total = $this->Medoodb->count("adm_leave_form",  $wherecnt);

        // // 遍历该用户所属角色信息
        // foreach ($UserArr as $k => $v) {
        //     $UserArr[$k]['role'] = [];
        //     $RoleArr = $this->Medoodb->select(
        //         'sys_user_role',
        //         'role_id [String]',
        //         [
        //             "user_id" => $v['id']
        //         ]
        //     );
        //     $UserArr[$k]['role'] = $RoleArr;
        // }

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

    #region 路由白名单，根据useId 获取该用户拥有的角色权限选项
    public function roleoptions()
    {
        $userId = $this->request->getVar('userId');
        $sql = "SELECT
                    CAST(r.id AS CHAR) AS value,
                    r.name AS label
                FROM
                    sys_role r
                INNER JOIN (
                    SELECT
                        DISTINCT p.r_id AS role_id
                    FROM
                        sys_perm AS p
                    INNER JOIN
                        sys_role_perm AS rp ON p.id = rp.perm_id
                    INNER JOIN
                        sys_user_role AS ur ON rp.role_id = ur.role_id
                    WHERE
                        p.perm_type = 'role'
                        AND ur.user_id = :userId
                ) t ON t.role_id = r.id";
        $RoleOptionsArr = $this->Medoodb->query($sql, [':userId' => $userId])->fetchAll(PDO::FETCH_ASSOC);

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