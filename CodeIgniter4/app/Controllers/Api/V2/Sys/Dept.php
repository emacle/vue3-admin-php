<?php

namespace App\Controllers\Api\V2\Sys;

use CodeIgniter\RESTful\ResourceController;
use Config\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nette\Utils\Strings;
use Exception;
use PDO;

class Dept extends ResourceController
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
        $name = $this->request->getVar('name');
        $status = $this->request->getVar('status');
        if (!isset($name) && !isset($status)) {
            $sql = "SELECT id, pid, name, aliasname, listorder, status FROM sys_dept order by listorder asc";
        } else {
            $statusSql = "";
            if (isset($status)) {
                $statusSql = "status = " . $status . " AND ";
            }
            // 该查询需要在 MySQL 8.0 或更高版本中运行,因为它使用了递归公用表表达式 (RCTE) 特性
            $sql = "WITH RECURSIVE cte AS (
                SELECT id, pid, name, aliasname, listorder, status
                FROM sys_dept
                WHERE $statusSql name LIKE '%" . $name . "%'
                UNION ALL
                SELECT d.id, d.pid, d.name, d.aliasname, d.listorder, d.status
                FROM sys_dept d
                INNER JOIN cte c ON d.id = c.pid
              )
              SELECT DISTINCT * FROM cte;";
        }
        // 执行查询
        $DeptArr = $this->Medoodb->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $DeptTreeObj = new \BlueM\Tree(
            $DeptArr,
            ['rootId' => 0, 'id' => 'id', 'parent' => 'pid']
        );
        $DeptTreeArr = $this->_dumpBlueMTreeNodes($DeptTreeObj->getRootNodes());

        $response = [
            "code" => 20000,
            "data" => [
                'list' => $DeptTreeArr,

            ]
        ];
        return $this->respond($response);
    }

    // 增
    public function create()
    {
        $parms = get_object_vars($this->request->getVar());
        // 参数检验/数据预处理
        // var_dump($parms);
        // return;

        $this->Medoodb->insert("sys_dept", $parms);
        $dept_id = $this->Medoodb->id();
        if (!$dept_id) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => '部门（' . $parms['name'] . '）新增失败'
            ];
            return $this->respond($response);
        }

        // 生成该部门对应的权限: sys_perm, 权限类型为: dept, 生成唯一的 perm_id
        $this->Medoodb->insert("sys_perm", ['perm_type' => 'dept', "r_id" => $dept_id]);
        $perm_id = $this->Medoodb->id();
        if (!$perm_id) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => $this->request->getPath() . ' 生成该部门对应的权限: sys_perm, 失败...' .
                    json_encode(['perm_type' => 'dept', "r_id" => $dept_id])
            ];
            return $this->respond($response);
        }
        // 超级管理员角色（1）自动拥有该权限 perm_id
        $this->Medoodb->insert("sys_role_perm", ["role_id" => 1, "perm_id" => $perm_id]);
        $role_perm_id = $this->Medoodb->id();
        if (!$role_perm_id) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => $this->request->getPath() . ' 超级管理员角色自动拥有该部门权限: perm_id, sys_role_perm, 失败...' .
                    json_encode(["role_id" => 1, "perm_id" => $perm_id])
            ];
            return $this->respond($response);
        }

        $response = [
            "code" => 20000,
            "type" => 'success',
            "message" => '部门（' . $parms['name'] . '）新增成功'
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

        $id = $parms['id']; // $id 从payload中获取，不从/sys/menu/3 uri里取，效果一样
        unset($parms['id']);

        // 参数检验/数据预处理
        $hasDept = $this->Medoodb->has('sys_dept', ['id' => $id]);
        if (!$hasDept) {
            $response = [
                "code" => 20404,
                "type" => 'error',
                'message' => '部门（' . $parms['name'] . '）数据表sys_dept中不存在'
            ];
            return $this->respond($response);
        }

        if ($id == $parms['pid']) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => '上级部门不能为自身'
            ];
            return $this->respond($response);
        }

        $hasChild = $this->Medoodb->has('sys_dept', ['pid' => $id]);
        // 存在子节点 则禁用状态 则无法禁用
        if ($hasChild && $parms['status'] == 0) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => '存在子部门不能禁用'
            ];
            return $this->respond($response);
        }

        $result = $this->Medoodb->update('sys_dept', $parms, ["id" => $id]);

        if ($result->rowCount() > 0) {
            $response = [
                "code" => 20000,
                "type" => 'success',
                "message" => '部门（' . $parms['name'] . '）更新成功'
            ];
            return $this->respond($response);
        } else {
            $response = [
                "code" => 20204,
                "type" => 'info',
                "message" => '部门数据未更新'
            ];
            // 对于 PUT 请求,如果数据未发生变化,遵循 HTTP 规范的做法是返回 204 No Content 状态码
            // 返回204时，与前端service.ts约定冲突
            return $this->respond($response);
        }
    }
    // 删
    public function delete($id = null)
    {
        // 参数检验/数据预处理
        // 处理删除部门资源的逻辑
        $hasDept = $this->Medoodb->has('sys_dept', ['id' => $id]);
        if ($hasDept) {
            $hasChild = $this->Medoodb->has('sys_dept', ['pid' => $id]);
            // 存在子节点
            if ($hasChild) {
                $response = [
                    "code" => 20403,
                    "type" => 'error',
                    "message" => '存在子部门不能删除'
                ];
                return $this->respond($response);
            }

            // 删除外键关联表 sys_role_perm, sys_perm, sys_dept 次序有先后
            // 1. 根据 该dept id及 类型 'dept' 在 sys_perm 表中查找 perm_id
            // 2. 删除 sys_role_perm 中perm_id记录
            // 3. 删除 sys_perm 中 perm_type='menu' and r_id = menu_id 记录,即第1步中获取的 perm_id， 一一对应
            // 4. 删除 sys_dept 中 id = menu_id 的记录
            $perm_id =  $this->Medoodb->get('sys_perm', 'id', ['perm_type' => 'dept', 'r_id' => $id]);
            $this->Medoodb->delete('sys_role_perm', ['perm_id' => $perm_id]);
            $this->Medoodb->delete('sys_perm', ['id' => $perm_id]);
            $result = $this->Medoodb->delete('sys_dept', ['id' => $id]);

            if ($result->rowCount() > 0) {
                $response = [
                    "code" => 20000,
                    "type" => 'success',
                    "message" => '部门删除成功'
                ];
                return $this->respondDeleted($response);
            } else {
                $response = [
                    "code" => 20403,
                    "type" => 'error',
                    "message" => '部门删除失败'
                ];
                return $this->respond($response);
            }
        } else {
            $response = [
                "code" => 20404,
                "type" => 'error',
                'message' => '部门（' . $id . '）不存在'
            ];
            return $this->respond($response);
        }
    }

    /**
     * 遍历 BlueM\Tree 树对象，将数据格式化部门树
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
