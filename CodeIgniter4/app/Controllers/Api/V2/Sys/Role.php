<?php

namespace App\Controllers\Api\V2\Sys;

use CodeIgniter\RESTful\ResourceController;
use Config\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nette\Utils\Strings;
use Exception;
use PDO;

class Role extends ResourceController
{
    private $Medoodb;
    public function __construct()
    {
        $this->Medoodb = \Config\Services::medoo();
    }
    // 查
    public function index()
    {
        // $this->request->getVar(); // 该方法首先尝试从 POST 数据中获取参数值,如果不存在,则尝试从 GET 参数中获取。
        // GET /role?offset=1&limit=20&fields=id,username,email,listorder&sort=-listorder,+id&query=~username,status&username=admin&status=1
        // fields: 显示字段参数过滤配置,不设置则为全部
        $fields = $this->request->getVar('fields');
        $fields ? $columns = explode(",", $fields) : $columns = "*";
        // 显示字段过滤配置结束

        // GET /role?offset=1&limit=20&fields=id,username,email,listorder&sort=-listorder,+id&query=~username,status&username=admin&status=1
        // 分页参数配置
        $limit = $this->request->getVar('size') ? $this->request->getVar('size') : 10;
        $offset = $this->request->getVar('currentPage') ?  ($this->request->getVar('currentPage') - 1) *  $limit : 0; // 第几页
        $where = [
            "LIMIT" => [$offset, $limit]
        ];
        // 分页参数配置结束

        // GET /role?offset=1&limit=20&fields=id,username,email,listorder&sort=-listorder,+id&query=~username,status&username=admin&status=1
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

        // GET /role?offset=1&limit=20&fields=id,username,email,listorder&sort=-listorder,+id&query=~username,status&username=admin&status=1
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
        $RoleArr = $this->Medoodb->select(
            "sys_role",
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
        $total = $this->Medoodb->count("sys_role",  $wherecnt);

        $response = [
            "code" => 20000,
            "data" => [
                'list' => $RoleArr,
                'total' => $total,
                // "sql" => $sqlCmd
            ]
        ];
        return $this->respond($response);
    }
    // 增
    public function create()
    {
        $parms = get_object_vars($this->request->getVar());

        $hasRole = $this->Medoodb->has('sys_role', ['name' => $parms['name']]);
        if ($hasRole) {
            $response = [
                "code" => 20000,
                "type" => 'error',
                'message' => '角色名称（' . $parms['name'] . '）已存在'
            ];
            return $this->respond($response, 200);
        }

        $parms['create_time'] = time();

        $this->Medoodb->insert("sys_role", $parms);
        $role_id = $this->Medoodb->id();
        if (!$role_id) {
            $response = [
                "code" => 20000,
                "type" => 'error',
                "message" => '角色（' . $parms['name'] . '）新增失败'
            ];
            return $this->respond($response, 200);
        }

        // 生成该角色对应的权限: sys_perm, 权限类型为: role, 生成唯一的 perm_id
        $this->Medoodb->insert("sys_perm", ['perm_type' => 'role', "r_id" => $role_id]);
        $perm_id = $this->Medoodb->id();
        if (!$perm_id) {
            $response = [
                "code" => 20000,
                "type" => 'error',
                "message" => $this->request->getPath() . ' 生成该角色对应的权限: sys_perm, 失败...' .
                    json_encode(['perm_type' => 'role', "r_id" => $role_id])
            ];
            return $this->respond($response, 200);
        }

        // 超级管理员角色(1) 自动拥有该权限perm_id
        $this->Medoodb->insert("sys_role_perm", ["role_id" => 1, "perm_id" => $perm_id]);
        $role_perm_id = $this->Medoodb->id();

        if (!$role_perm_id) {
            $response = [
                "code" => 20000,
                "type" => 'error',
                "message" => $this->request->getPath() . ' 超级管理员角色自动拥有该权限: perm_id, sys_role_perm, 失败...' .
                    json_encode(["role_id" => 1, "perm_id" => $perm_id])
            ];
            return $this->respond($response, 200);
        }

        $response = [
            "code" => 20000,
            "type" => 'success',
            "message" => '角色（' . $parms['name'] . '）新增成功'
        ];
        return $this->respondCreated($response);
    }
    // 改
    public function update($id = null)
    {
        // $id类型可以在Routes.php中定义  $routes->put('user/(.*)', 'User::update/$1'); 默认$1是字符串
        // $id = intval($id);
        // 处理更新用户资源的逻辑
        $parms = get_object_vars($this->request->getVar()); // 获取表单参数，类型为数组

        $id = $parms['id']; // $id 从payload中获取，不从/sys/role/3 uri里取，效果一样
        unset($parms['id']);

        // 参数检验/数据预处理
        // 超级管理员角色不允许修改
        if ($id == 1) {
            $response = [
                "code" => 20000,
                "type" => 'error',
                "message" =>  '超级管理员角色（' . $id . '）不允许修改'
            ];
            return $this->respond($response, 200);
        }

        $hasRole = $this->Medoodb->has('sys_role', ['id' => $id]);
        if (!$hasRole) {
            $response = [
                "code" => 20404,
                "type" => 'error',
                'message' => '角色（' . $parms['name'] . '）数据表sys_role中不存在'
            ];
            return $this->respond($response, 200);
        }

        $result = $this->Medoodb->update('sys_role', $parms, ["id" => $id]);

        if ($result->rowCount() > 0) {
            $response = [
                "code" => 20000,
                "type" => 'success',
                "message" => '角色（' . $parms['name'] . '）更新成功'
            ];
            return $this->respond($response);
        } else {
            $response = [
                "code" => 20204,
                "type" => 'info',
                "message" => '角色数据未更新'
            ];
            // 对于 PUT 请求,如果数据未发生变化,遵循 HTTP 规范的做法是返回 204 No Content 状态码
            // 返回204时，与前端service.ts约定冲突
            return $this->respond($response);
        }
    }
    // 删
    public function delete($id = null)
    {
        $id = intval($id);
        // 参数检验/数据预处理
        // 超级管理员角色不允许删除
        if ($id == 1) {
            $response = [
                "code" => 20000,
                "type" => 'error',
                "message" => '超级管理员角色不允许删除'
            ];
            // todo: DELETE/UPDATE操作，返回响应体为空？403
            return $this->respond($response, 200);
        }

        // 处理删除角色资源的逻辑
        $hasRole = $this->Medoodb->has('sys_role', ['id' => $id]);
        if ($hasRole) {
            // 删除外键关联表 sys_role_perm, sys_perm, sys_role 次序有先后
            // 1. 根据 该角色id及 类型 'role' 在 sys_perm 表中查找 perm_id
            // 2. 删除 sys_role_perm 中perm_id记录
            // 3. 删除 sys_perm 中 perm_type='role' and r_id = role_id 记录,即第1步中获取的 perm_id， 一一对应
            // 4. 删除 sys_user_role 中 该角色id的记录
            // 5. 删除 sys_role 中 id = role_id 的记录

            $perm_id =  $this->Medoodb->get('sys_perm', 'id', ['perm_type' => 'role', 'r_id' => $id]);
            $this->Medoodb->delete('sys_role_perm', ['perm_id' => $perm_id]);
            $this->Medoodb->delete('sys_perm', ['id' => $perm_id]);
            $this->Medoodb->delete('sys_user_role', ['role_id' => $id]);
            $result = $this->Medoodb->delete('sys_role', ['id' => $id]);

            if ($result->rowCount() > 0) {
                $response = [
                    "code" => 20000,
                    "type" => 'success',
                    "message" => '角色删除成功'
                ];
                return $this->respondDeleted($response);
            } else {
                $response = [
                    "code" => 20403,
                    "type" => 'error',
                    "message" => '角色删除失败'
                ];
                return $this->respond($response);
            }
        } else {
            // return $this->failNotFound('No employee found');
            $response = [
                "code" => 20404,
                "type" => 'error',
                'message' => '角色（' . $id . '）不存在'
            ];
            return $this->respond($response);
        }
    }

    // 获取所有菜单并加入对应的权限id 不需权限验证
    public function allmenus()
    {
        // $sql = "SELECT
        //             p.id perm_id,
        //             m.*
        //         FROM
        //             sys_menu m,
        //             sys_perm p
        //         WHERE
        //             p.perm_type = 'menu'
        //         AND p.r_id = m.id
        //         ORDER BY
        //             m.listorder";
        // $MenuArr = $this->Medoodb->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $MenuArr = $this->Medoodb->select(
            "sys_menu(m)",
            [
                "[><]sys_perm(p)" => [
                    "id" => "r_id",
                    "AND" => [
                        "perm_type" => "menu"
                    ]
                ]
            ],
            [
                "p.id(perm_id)",
                "m.id",
                "m.pid",
                "m.title",
                "m.type",
                "m.path",
                "m.icon"
            ],
            [
                "ORDER" => [
                    "m.listorder" => "ASC"
                ]
            ]
        );
        // $sqlCmd = $this->Medoodb->log()[0];
        // var_dump($sqlCmd);
        // var_dump($MenuArr);
        // return;
        $MenuTreeObj = new \BlueM\Tree(
            $MenuArr,
            ['rootId' => 0, 'id' => 'id', 'parent' => 'pid']
        );
        $MenuTree = $this->_dumpBlueMTreeNodes($MenuTreeObj->getRootNodes());

        $response = [
            "code" => 20000,
            "data" => [
                "list" => $MenuTree
            ],
        ];
        return $this->respond($response);
    }
    // 获取该角色并加入对应的权限id 不需权限验证
    public function rolemenus()
    {
        $RoleId = $this->request->getVar('id');
        $sql = "SELECT
                    p.id perm_id,
                    m.*
                FROM
                    sys_menu m,
                    sys_perm p,
                    sys_role_perm rp
                WHERE
                    rp.perm_id = p.id
                AND p.perm_type = 'menu'
                AND p.r_id = m.id
                AND rp.role_id = " . $RoleId . "
                ORDER BY
                    m.listorder";
        $RoleMenusArr = $this->Medoodb->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $response = [
            "code" => 20000,
            "data" => [
                "list" => $RoleMenusArr
            ],
        ];
        return $this->respond($response);
    }
    public function saveroleperm()
    {
        $parms = get_object_vars($this->request->getVar());
        $id = $parms['roleId'];
        // 超级管理员角色不允许修改
        if ($parms['roleId'] == 1) {
            $response = [
                "code" => 20000,
                "type" => 'error',
                "message" =>  '超级管理员角色（' . $id . '）不允许修改'
            ];
            return $this->respond($response, 200);
        }

        var_dump($parms);
        return;
        $RoleMenusArr = $this->Medoodb->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $response = [
            "code" => 20000,
            "data" => [
                "list" => $RoleMenusArr
            ],
        ];
        return $this->respond($response);
    }

    public function allroles()
    {
    }
    public function alldepts()
    {
    }

    public function roleroles()
    {
        // $this->request->getVar('id')
    }
    public function roledepts()
    {
        // $this->request->getVar('id')
    }

    /**
     * 遍历 BlueM\Tree 树对象，将数据格式化成 vue-router 结构的路由树或菜单树
     */
    private function _dumpBlueMTreeNodes($node)
    {
        $tree = array();

        foreach ($node as $k => $v) {
            $valArr = $v->toArray(); // 获取本节点属性数组
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
