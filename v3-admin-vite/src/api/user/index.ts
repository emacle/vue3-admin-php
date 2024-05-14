import { request } from "@/utils/service"
import type * as User from "./types/user"

/** 增 */
export function createUserDataApi(data: User.CreateOrUpdateUserRequestData) {
  return request({
    url: "sys/user",
    method: "post",
    data
  })
}

/** 删 */
export function deleteUserDataApi(id: string) {
  return request({
    url: `sys/user/${id}`,
    method: "delete"
  })
}

/** 改 */
export function updateUserDataApi(data: User.CreateOrUpdateUserRequestData) {
  return request({
    url: "sys/user/" + data.id,
    method: "put",
    data
  })
}

/** 查 */
export function getUserDataApi(params: User.GetUserRequestData) {
  return request<User.GetUserResponseData>({
    url: "sys/user",
    method: "get",
    params
  })
}

/** 获取该用户拥有的角色权限，返回格式化的 el-select options格式 */
export function getRoleOptionsApi(params: { userId: string }) {
  return request<User.GetRoleOptionsResponseData>({
    url: "sys/user/roleoptions",
    method: "get",
    params
  })
}

/** 获取所有部门做为用户管理中 deptoptions 数据 */
export function getDeptOptionsApi() {
  return request<User.GetDeptOptionsResponseData>({
    url: "sys/user/deptoptions",
    method: "get"

  })
}
