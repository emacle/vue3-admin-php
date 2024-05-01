<?php

namespace App\Controllers\Api\V2\Sys;

use CodeIgniter\RESTful\ResourceController;
use Config\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nette\Utils\Strings;
use Exception;
use PDO;

class Menu extends ResourceController
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
        $title = $this->request->getVar('title');

        if (!isset($title)) { // title参数为空


        } else {
        }

        // 执行查询
        $MenuArr = $this->Medoodb->select(
            "sys_menu",
            "*",
            [
                "status" => 1,
                "ORDER" => ["listorder" => "ASC"]
            ]
        );
        $MenuTreeObj = new \BlueM\Tree(
            $MenuArr,
            ['rootId' => 0, 'id' => 'id', 'parent' => 'pid']
        );
        $MenuTreeArr = $this->_dumpBlueMTreeNodes($MenuTreeObj->getRootNodes());

        // $query = "SELECT DISTINCT t.id AS perm_id, basetbl.*
        // FROM (
        //     SELECT p.*
        //     FROM sys_user_role ur
        //     INNER JOIN sys_role_perm rp ON ur.role_id = rp.role_id
        //     INNER JOIN sys_perm p ON rp.perm_id = p.id
        //     INNER JOIN sys_role r ON ur.role_id = r.id
        //     WHERE ur.user_id = :userId
        //     AND r.status = 1
        //     AND p.perm_type = 'menu'
        // ) t
        // LEFT JOIN $basetable basetbl ON t.r_id = basetbl.id
        // WHERE basetbl.type != 2
        // ORDER BY basetbl.listorder";
        // $params = [":userId" => $jwt_obj->user_id];
        // $MenuTreeArr = $this->Medoodb->query($query, $params)->fetchAll(PDO::FETCH_ASSOC);
        // // 将SQL查询出的一维树形结构生成 BlueM\Tree 树对象结构，方便后续遍历操作
        // $MenuTreeObj = new \BlueM\Tree(
        //     $MenuTreeArr,
        //     ['rootId' => 0, 'id' => 'id', 'parent' => 'pid']
        // );
        // // 从 $MenuTreeObj 对象中根节点遍历，生成vue路由菜单
        // $asyncRouterMap = $this->_dumpBlueMTreeNodes($MenuTreeObj->getRootNodes());


        $response = [
            "code" => 20000,
            "data" => [
                'list' => $MenuTreeArr,

            ]
        ];
        return $this->respond($response);
    }

    // 增
    public function create()
    {
        $parms = get_object_vars($this->request->getVar());
        // 参数检验/数据预处理
        // 菜单类型为目录
        if (!$parms['type']) {
            $parms['component'] = 'Layout';
        }
        $hasMenu = $this->Medoodb->has('sys_menu', ['path' => $parms['path']]);
        if ($hasMenu) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                'message' => '菜单路由（' . $parms['path'] . '）已存在，禁止添加'
            ];
            return $this->respond($response);
        }

        $this->Medoodb->insert("sys_menu", $parms);
        $menu_id = $this->Medoodb->id();
        if (!$menu_id) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => '菜单（' . $parms['title'] . '）新增失败'
            ];
            return $this->respond($response);
        }

        // 生成该菜单对应的权限: sys_perm, 权限类型为: menu, 生成唯一的 perm_id
        $this->Medoodb->insert("sys_perm", ['perm_type' => 'menu', "r_id" => $menu_id]);
        $perm_id = $this->Medoodb->id();
        if (!$perm_id) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => $this->request->getPath() . ' 生成该角色对应的权限: sys_perm, 失败...' .
                    json_encode(['perm_type' => 'menu', "r_id" => $menu_id])
            ];
            return $this->respond($response);
        }
        // 超级管理员角色自动拥有该权限 perm_id
        $this->Medoodb->insert("sys_role_perm", ["role_id" => 1, "perm_id" => $perm_id]);
        $role_perm_id = $this->Medoodb->id();
        if (!$role_perm_id) {
            $response = [
                "code" => 20403,
                "type" => 'error',
                "message" => $this->request->getPath() . ' 超级管理员角色自动拥有该权限: perm_id, sys_role_perm, 失败...' .
                    json_encode(["role_id" => 1, "perm_id" => $perm_id])
            ];
            return $this->respond($response);
        }

        $response = [
            "code" => 20000,
            "type" => 'success',
            "message" => '菜单（' . $parms['title'] . '）新增成功'
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
        $hasMenu = $this->Medoodb->has('sys_menu', ['id' => $id]);
        if (!$hasMenu) {
            $response = [
                "code" => 20404,
                "type" => 'error',
                'message' => '菜单（' . $parms['title'] . '）数据表sys_menu中不存在'
            ];
            return $this->respond($response);
        }

        $result = $this->Medoodb->update('sys_menu', $parms, ["id" => $id]);

        if ($result->rowCount() > 0) {
            $response = [
                "code" => 20000,
                "type" => 'success',
                "message" => '菜单（' . $parms['title'] . '）更新成功'
            ];
            return $this->respond($response);
        } else {
            $response = [
                "code" => 20204,
                "type" => 'info',
                "message" => '菜单数据未更新'
            ];
            // 对于 PUT 请求,如果数据未发生变化,遵循 HTTP 规范的做法是返回 204 No Content 状态码
            // 返回204时，与前端service.ts约定冲突
            return $this->respond($response);
        }
    }
    // 删 注意级联删除？？？
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
        $hasMenu = $this->Medoodb->has('sys_menu', ['id' => $id]);
        if ($hasMenu) {
            // 删除外键关联表 sys_menu_perm, sys_perm, sys_menu 次序有先后
            // 1. 根据 该角色id及'menu' 在 sys_perm 表中查找 perm_id
            // 2. 删除 sys_menu_perm 中perm_id记录
            // 3. 删除 sys_perm 中 perm_type='menu' and r_id = menu_id 记录,即第1步中获取的 perm_id， 一一对应
            // 4. 删除 sys_user_menu 中 该角色id的记录
            // 5. 删除 sys_menu 中 id = menu_id 的记录

            $perm_id =  $this->Medoodb->get('sys_perm', 'id', ['perm_type' => 'menu', 'r_id' => $id]);
            $this->Medoodb->delete('sys_menu_perm', ['perm_id' => $perm_id]);
            $this->Medoodb->delete('sys_perm', ['id' => $perm_id]);
            $this->Medoodb->delete('sys_user_menu', ['menu_id' => $id]);
            $result = $this->Medoodb->delete('sys_menu', ['id' => $id]);

            if ($result->rowCount() > 0) {
                $response = [
                    "code" => 20000,
                    "type" => 'success',
                    "message" => '角色删除成功'
                ];
                return $this->respondDeleted($response);
            } else {
                $response = [
                    "code" => 20000,
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
