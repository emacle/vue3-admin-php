<?php

namespace App\Controllers\Apix\V2\Sys;

use CodeIgniter\RESTful\ResourceController;

// 标准流程
// 1. 配置数据库连接, app/Config/Database.php
// 2. 创建 Model, app/Models/EmployeeModel.php
// 3. 创建 REST Controller, app/Controllers/Employee.php
// 4. 创建 REST API Route, app/Config/Routes.php

class Employee extends ResourceController
{
    private $Medoodb;
    public function __construct()
    {
        $this->Medoodb = \Config\Services::medoo();
    }

    // index() – Get’s all the records from the database.
    // create() – It propels an employee record into the database table.
    // show() – It gets a single employee record from the database.
    // update() – It updates the user record into the database.
    // delete() – It deletes an employee record the database.

    // all users
    public function index()
    {
        // 使用Medoo进行数据库操作
        $data['employees']  = $this->Medoodb->select('employees', '*');
        return $this->respond($data);
    }

    // single user
    public function show($id = null)
    {
        // 处理获取指定用户资源的逻辑
        $data = $this->Medoodb->get('employees', '*', ['id' => $id]);
        if ($data) {
            return $this->respond($data);
        } else {
            return $this->failNotFound('No employee found');
        }
    }

    // create a new user
    public function create()
    {
        // 处理创建用户资源的逻辑
        $data = [
            'name' => $this->request->getVar('name'),
            'email'  => $this->request->getVar('email'),
        ];
        $this->Medoodb->insert('employees', $data);

        $response = [
            'status'   => 201,
            'error'    => null,
            'messages' => [
                'success' => 'Employee created successfully'
            ]
        ];
        return $this->respondCreated($response);
    }

    // update an existing user
    public function update($id = null)
    {
        // 处理更新用户资源的逻辑
        $data = [
            'name' => $this->request->getVar('name'),
            'email'  => $this->request->getVar('email'),
        ];

        $this->Medoodb->update('employees', $data, ['id' => $id]);

        $response = [
            'status'   => 200,
            'error'    => null,
            'messages' => [
                'success' => 'Employee updated successfully'
            ]
        ];
        return $this->respond($response);
    }

    // delete an existing user
    public function delete($id = null)
    {
        // 处理删除用户资源的逻辑
        $data = $this->Medoodb->has('employees', ['id' => $id]);
        if ($data) {
            $this->Medoodb->delete('employees', ['id' => $id]);
            $response = [
                'status'   => 200,
                'error'    => null,
                'messages' => [
                    'success' => 'Employee successfully deleted'
                ]
            ];
            return $this->respondDeleted($response);
        } else {
            return $this->failNotFound('No employee found');
        }
    }
}
