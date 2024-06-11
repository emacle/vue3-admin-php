<?php

namespace App\Controllers\Api\V2\Flow;

use CodeIgniter\RESTful\ResourceController;
use Config\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nette\Utils\Strings;
use Exception;
use PDO;

class Leaveaudit extends ResourceController
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
        // 指定条件模糊或搜索查询,author like %zhangsan%, status=1
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

        $userId =  getUserIdByToken($this->request->getHeaderLine('Authorization'));
        $where["operator_id"] = $userId; // 只能查询本人审批的任务
        $where["action"] = 'audit'; // 查询动作为审批的任务
        $where["state"] = ['complete', 'process']; // 查询状态为正在进行中或完成的审批任务

        // 执行查询
        $LeaveProcessArr = $this->Medoodb->select("adm_process_flow", $columns, $where);
        $sqlCmd = $this->Medoodb->log()[0];

        // 捕获错误信息
        if ($this->Medoodb->error) { // 如果出错 否则为NULL
            $response = ["code" => 20400, "sql" => $sqlCmd, "message" => $this->Medoodb->error];
            return $this->respond($response, 400);
        }

        // 获取记录总数
        $wherecnt = array_diff_key($where, array_flip(["LIMIT", "ORDER"])); // 查询total去除排序字段，提高查询效率
        $total = $this->Medoodb->count("adm_process_flow", $wherecnt);

        // 遍历查询结果，并关联 adm_leave_form 表信息
        foreach ($LeaveProcessArr as $k => $v) {
            $leaveFormArr = [];
            if (isset($v['form_id'])) {
                $leaveFormArr = $this->Medoodb->get(
                    'adm_leave_form',
                    ["employee_id", "form_type [String]", "start_time", "end_time", "reason (apply_reason)", "create_time (apply_time)"],
                    ["form_id" => $v['form_id']]
                );
                $LeaveProcessArr[$k] = array_merge($v, $leaveFormArr);
            }

            // 关联审批人信息
            $operator_name = "";
            if (isset($v['operator_id'])) {
                $operator_name = $this->Medoodb->get('sys_user', 'username', ["id" => $v['operator_id']]);
            };
            $LeaveProcessArr[$k]['operator_name'] =   $operator_name;

            // 关联申请人信息
            $applyUserName = "";
            if (isset($LeaveProcessArr[$k]['employee_id'])) {
                $applyUserName = $this->Medoodb->get('sys_user', 'username', ["id" => $LeaveProcessArr[$k]['employee_id']]);
            };
            $LeaveProcessArr[$k]['employee_name'] =   $applyUserName;
        }

        $response = [
            "code" => 20000,
            "data" => [
                'list' => $LeaveProcessArr,
                'total' => $total,
                "sql" => $sqlCmd
            ]
        ];
        return $this->respond($response);
    }
    #endregion

    #region 审批接口 /flow/leaveaudit/put
    public function update($id = null)
    {
        $parms = get_object_vars($this->request->getVar()); // 获取表单参数，类型为数组
        $process_id = $parms['process_id']; // $id 从payload中获取，不从/sys/role/3 uri里取，效果一样
        $form_id = $parms['form_id'];
        unset($parms['process_id']);
        unset($parms['form_id']);
        $appConfig = config(App::class); // 获取app/Config/App.php文件夹里变量
        $userId =  getUserIdByToken($this->request->getHeaderLine('Authorization'));
        $currentUser = $this->Medoodb->get('sys_user', '*', ['id' => $userId]);

        // 参数检验/数据预处理
        $hasRecord = $this->Medoodb->has('adm_process_flow', ['process_id' => $process_id]);
        if (!$hasRecord) {
            $response = ["code" => 20404, "type" => 'error', 'message' => 'process_id(' . $process_id . '）不存在'];
            return $this->respond($response, 404);
        }

        $parms['audit_time'] = date('Y-m-d H:i:s');
        $parms['state'] = 'complete';
        $result = $this->Medoodb->update('adm_process_flow', $parms, ['process_id' => $process_id]);

        // process_id
        // form_id
        // operator_id
        // action
        // result
        // reason
        // create_time
        // audit_time
        // order_no
        // state
        // is_last
        if ($result->rowCount() > 0) {
            $currentProcessNode = $this->Medoodb->get('adm_process_flow', '*', ['process_id' => $process_id]);
            // [
            //     'form_id' => $form_id,
            //     'action' => 'audit', // 审批人
            //     'operator_id' => $userId
            // ]); // TODO: 使用 $process_id 过滤即可？

            if ($currentProcessNode['result'] === 'approved') { // 当前节点审批通过
                // 不是最后一个节点，则下一个节点state由初始化ready置为process,
                if (!$currentProcessNode['is_last']) {
                    $this->Medoodb->update('adm_process_flow', ['state' => 'process'], ["form_id" => $form_id, "order_no" => $currentProcessNode['order_no'] + 1]);
                }
                // 且是最后一个节点，整个流程审批完结
                if ($currentProcessNode['is_last']) {
                    // adm_leave_form 中 state = 'approved'
                    $this->Medoodb->update('adm_leave_form', ['state' => 'approved'], ['form_id' => $form_id]);
                }
            } else if ($currentProcessNode['result'] === 'refused') { // 当前节点(包括最后节点)审批驳回
                // adm_leave_form 中 state = 'refused'
                $this->Medoodb->update('adm_leave_form', ['state' => 'refused'], ['form_id' => $form_id]);
                // 中间节点驳回时 adm_process_flow 后续节点state = 'cancel'
                $this->Medoodb->update('adm_process_flow', ['state' => 'cancel'], ['form_id' => $form_id, 'order_no[>]' => $currentProcessNode['order_no']]);
            }

            #region adm_notice 表，记录通知信息
            // 查找adm_process_flow 表的 form_id 中 apply 申请人 查找对应的 operator_id 插入 adm_notice
            // 1. 无论审批结果通过与否，都通知申请人 "action" => "apply"
            // 当前申请人id
            $applyUserid = $this->Medoodb->get('adm_process_flow', 'operator_id', ["form_id" => $form_id, 'action' => 'apply']);
            // 当前审批人信息
            $auditUser = $this->Medoodb->get(
                'adm_process_flow',
                ['[>]sys_user' => ['operator_id' => 'id']],
                ['id', 'username', 'tel', 'dept_id', 'position_code'],
                ["adm_process_flow.process_id" => $process_id]
            );

            if ($applyUserid) {
                $this->Medoodb->insert("adm_notice", [
                    "receiver_id" => $applyUserid,
                    "content" => "您的请假申请已经被" . $auditUser['username'] . "审批。审批结果为" . getLabelByKey($currentProcessNode['result'],  $appConfig->auditResultOptions)
                ]);
            }
            // 2.判断是否是审批动作且是否是最终节点，不是最后节点，需要下一步处理人order_no-1 发消息，最后节点只需要向申请人发送
            if (!$currentProcessNode['is_last'] && $currentProcessNode['result'] === 'approved') {
                $next_operator_id = $this->Medoodb->get(
                    'adm_process_flow',
                    'operator_id',
                    [
                        "form_id" => $form_id,
                        "order_no" => $currentProcessNode['order_no'] + 1
                    ]
                );
                if ($next_operator_id) {
                    $apply_username =  $this->Medoodb->get('sys_user', 'username', ["id" => $applyUserid]);
                    $this->Medoodb->insert("adm_notice", [
                        "receiver_id" => $next_operator_id,
                        "content" => $apply_username . "员工已发起请假申请，请您审批。"
                    ]);
                }
            }
            #endregion

            $response = [
                "code" => 20000,
                "type" => 'success',
                "message" => '审批已完成'
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                "code" => 20204,
                "type" => 'info',
                "message" => '审批流程出错'
            ];
            return $this->respond($response);
        }
    }
    #endregion

}
