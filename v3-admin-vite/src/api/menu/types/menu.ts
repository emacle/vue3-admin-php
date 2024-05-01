export interface CreateOrUpdateMenuRequestData {
  id?: string
  pid: string
  name: string
  path: string
  component: string
  type: number
  title: string
  icon: string
  redirect: string
  hidden: number
  status: number
  condition: string
  listorder: number
}

export interface GetMenuRequestData {
  /** 查询参数：菜单名 */
  title?: string
}

export interface GetMenuData {
  id: string
  pid: string
  name: string
  path: string
  component: string
  type: number
  title: string
  icon: string
  redirect: string
  hidden: number
  status: number
  condition: string
  listorder: number
  children: GetMenuData[]
}
export interface GetMenuDataOptions {
  value: string
  label: string
  children: GetMenuDataOptions[]
}

export type GetMenuResponseData = ApiResponseData<{
  list: GetMenuData[]
}>

// {
//   "code": 20000,
//   "data": [
//     {
//       "perm_id": "43",
//       "id": 31,
//       "pid": 0,
//       "name": "asd",
//       "path": "asd",
//       "component": "asdsa",
//       "type": 1,
//       "title": "jyftdrdc",
//       "icon": "cascader",
//       "redirect": "sad",
//       "hidden": 0,
//       "status": "1",
//       "condition": "",
//       "listorder": 99,
//       "create_time": null,
//       "update_time": null
//     },
//     {
//       "perm_id": "44",
//       "id": 32,
//       "pid": 0,
//       "name": "12",
//       "path": "12",
//       "component": "Layout",
//       "type": 0,
//       "title": "ceshi",
//       "icon": "chart",
//       "redirect": "123",
//       "hidden": 0,
//       "status": "1",
//       "condition": "",
//       "listorder": 99,
//       "create_time": null,
//       "update_time": null,
//       "children": [
//         {
//           "perm_id": "45",
//           "id": 33,
//           "pid": 32,
//           "name": "asd",
//           "path": "asd",
//           "component": "ased",
//           "type": 1,
//           "title": "阿萨德",
//           "icon": "",
//           "redirect": "qwe",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 99,
//           "create_time": null,
//           "update_time": null
//         }
//       ]
//     },
//     {
//       "perm_id": "2",
//       "id": 1,
//       "pid": 0,
//       "name": "Sys",
//       "path": "/sys",
//       "component": "Layout",
//       "type": 0,
//       "title": "系统管理",
//       "icon": "color",
//       "redirect": "/sys/menu",
//       "hidden": 0,
//       "status": "1",
//       "condition": "",
//       "listorder": 99,
//       "create_time": null,
//       "update_time": null,
//       "children": [
//         {
//           "perm_id": "3",
//           "id": 2,
//           "pid": 1,
//           "name": "SysMenu",
//           "path": "/sys/menu",
//           "component": "sys/menu/index",
//           "type": 1,
//           "title": "菜单管理",
//           "icon": "menu1",
//           "redirect": "",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 80,
//           "create_time": null,
//           "update_time": null,
//           "children": [
//             {
//               "perm_id": "10",
//               "id": 9,
//               "pid": 2,
//               "name": "",
//               "path": "/sys/menu/menus/get",
//               "component": "",
//               "type": 2,
//               "title": "查看",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 80,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "7",
//               "id": 6,
//               "pid": 2,
//               "name": "",
//               "path": "/sys/menu/menus/post",
//               "component": "",
//               "type": 2,
//               "title": "添加",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 90,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "8",
//               "id": 7,
//               "pid": 2,
//               "name": "",
//               "path": "/sys/menu/menus/put",
//               "component": "",
//               "type": 2,
//               "title": "编辑",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 95,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "9",
//               "id": 8,
//               "pid": 2,
//               "name": "",
//               "path": "/sys/menu/menus/delete",
//               "component": "",
//               "type": 2,
//               "title": "删除",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 99,
//               "create_time": null,
//               "update_time": null
//             }
//           ]
//         },
//         {
//           "perm_id": "29",
//           "id": 23,
//           "pid": 1,
//           "name": "SysDept",
//           "path": "/sys/dept",
//           "component": "sys/dept/index",
//           "type": 1,
//           "title": "部门管理",
//           "icon": "dept",
//           "redirect": "",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 85,
//           "create_time": null,
//           "update_time": null,
//           "children": [
//             {
//               "perm_id": "30",
//               "id": 24,
//               "pid": 23,
//               "name": "",
//               "path": "/sys/dept/depts/get",
//               "component": "",
//               "type": 2,
//               "title": "查看",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 99,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "31",
//               "id": 25,
//               "pid": 23,
//               "name": "",
//               "path": "/sys/dept/depts/post",
//               "component": "",
//               "type": 2,
//               "title": "添加",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 100,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "32",
//               "id": 26,
//               "pid": 23,
//               "name": "",
//               "path": "/sys/dept/depts/put",
//               "component": "",
//               "type": 2,
//               "title": "编辑",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 102,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "33",
//               "id": 27,
//               "pid": 23,
//               "name": "",
//               "path": "/sys/dept/depts/delete",
//               "component": "",
//               "type": 2,
//               "title": "删除",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 104,
//               "create_time": null,
//               "update_time": null
//             }
//           ]
//         },
//         {
//           "perm_id": "4",
//           "id": 3,
//           "pid": 1,
//           "name": "SysRole",
//           "path": "/sys/role",
//           "component": "sys/role/index",
//           "type": 1,
//           "title": "角色管理",
//           "icon": "role",
//           "redirect": "",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 99,
//           "create_time": null,
//           "update_time": null,
//           "children": [
//             {
//               "perm_id": "14",
//               "id": 13,
//               "pid": 3,
//               "name": "",
//               "path": "/sys/role/roles/get",
//               "component": "",
//               "type": 2,
//               "title": "查看",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 90,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "15",
//               "id": 14,
//               "pid": 3,
//               "name": "",
//               "path": "/sys/role/roles/post",
//               "component": "",
//               "type": 2,
//               "title": "添加",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 91,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "16",
//               "id": 15,
//               "pid": 3,
//               "name": "",
//               "path": "/sys/role/roles/put",
//               "component": "",
//               "type": 2,
//               "title": "编辑",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 92,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "17",
//               "id": 16,
//               "pid": 3,
//               "name": "",
//               "path": "/sys/role/roles/delete",
//               "component": "",
//               "type": 2,
//               "title": "删除",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 101,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "27",
//               "id": 21,
//               "pid": 3,
//               "name": "",
//               "path": "/sys/role/saveroleperm/post",
//               "component": "",
//               "type": 2,
//               "title": "角色授权",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 120,
//               "create_time": null,
//               "update_time": null
//             }
//           ]
//         },
//         {
//           "perm_id": "5",
//           "id": 4,
//           "pid": 1,
//           "name": "SysUser",
//           "path": "/sys/user",
//           "component": "sys/user/index",
//           "type": 1,
//           "title": "用户管理",
//           "icon": "user",
//           "redirect": "",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 99,
//           "create_time": null,
//           "update_time": null,
//           "children": [
//             {
//               "perm_id": "18",
//               "id": 17,
//               "pid": 4,
//               "name": "",
//               "path": "/sys/user/users/get",
//               "component": "",
//               "type": 2,
//               "title": "查看",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 96,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "19",
//               "id": 18,
//               "pid": 4,
//               "name": "",
//               "path": "/sys/user/users/post",
//               "component": "",
//               "type": 2,
//               "title": "添加",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 97,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "20",
//               "id": 19,
//               "pid": 4,
//               "name": "",
//               "path": "/sys/user/users/put",
//               "component": "",
//               "type": 2,
//               "title": "编辑",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 99,
//               "create_time": null,
//               "update_time": null
//             },
//             {
//               "perm_id": "21",
//               "id": 20,
//               "pid": 4,
//               "name": "",
//               "path": "/sys/user/users/delete",
//               "component": "",
//               "type": 2,
//               "title": "删除",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 100,
//               "create_time": null,
//               "update_time": null
//             }
//           ]
//         },
//         {
//           "perm_id": "39",
//           "id": 28,
//           "pid": 1,
//           "name": "SysLog",
//           "path": "/sys/log",
//           "component": "sys/log/index",
//           "type": 1,
//           "title": "系统日志",
//           "icon": "log",
//           "redirect": "",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 101,
//           "create_time": null,
//           "update_time": null,
//           "children": [
//             {
//               "perm_id": "40",
//               "id": 29,
//               "pid": 28,
//               "name": "",
//               "path": "/sys/log/logs/get",
//               "component": "",
//               "type": 2,
//               "title": "查看",
//               "icon": "",
//               "redirect": "",
//               "hidden": 0,
//               "status": "1",
//               "condition": "",
//               "listorder": 99,
//               "create_time": null,
//               "update_time": null
//             }
//           ]
//         }
//       ]
//     },
//     {
//       "perm_id": "6",
//       "id": 5,
//       "pid": 0,
//       "name": "Sysx",
//       "path": "/sysx",
//       "component": "Layout",
//       "type": 0,
//       "title": "测试菜单",
//       "icon": "github",
//       "redirect": "/sysx/xiangjun",
//       "hidden": 0,
//       "status": "1",
//       "condition": "",
//       "listorder": 100,
//       "create_time": null,
//       "update_time": null,
//       "children": [
//         {
//           "perm_id": "11",
//           "id": 10,
//           "pid": 5,
//           "name": "SysxXiangjun",
//           "path": "/sysx/xiangjun",
//           "component": "xiangjun/index",
//           "type": 1,
//           "title": "vue课堂测试",
//           "icon": "form",
//           "redirect": "",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 95,
//           "create_time": null,
//           "update_time": null
//         },
//         {
//           "perm_id": "41",
//           "id": 30,
//           "pid": 5,
//           "name": "SysMenuOrder",
//           "path": "/sys/menu/order",
//           "component": "sys/menu/order",
//           "type": 1,
//           "title": "订单管理",
//           "icon": "chart",
//           "redirect": "",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 99,
//           "create_time": null,
//           "update_time": null
//         },
//         {
//           "perm_id": "12",
//           "id": 11,
//           "pid": 5,
//           "name": "SysxUploadimg",
//           "path": "/sysx/uploadimg",
//           "component": "uploadimg/index",
//           "type": 1,
//           "title": "上传证件照",
//           "icon": "bug",
//           "redirect": "",
//           "hidden": 0,
//           "status": "1",
//           "condition": "",
//           "listorder": 100,
//           "create_time": null,
//           "update_time": null
//         }
//       ]
//     }
//   ]
// }
