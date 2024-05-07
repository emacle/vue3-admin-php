import { request } from "@/utils/service"
import type * as Role from "./types/role"

/** 增 */
export function createRoleDataApi(data: Role.CreateOrUpdateRoleRequestData) {
  return request({
    url: "sys/role",
    method: "post",
    data
  })
}

/** 删 */
export function deleteRoleDataApi(id: string) {
  return request({
    url: `sys/role/${id}`,
    method: "delete"
  })
}

/** 改 */
export function updateRoleDataApi(data: Role.CreateOrUpdateRoleRequestData) {
  return request({
    url: "sys/role/" + data.id,
    method: "put",
    data
  })
}

/** 查 */
export function getRoleDataApi(params: Role.GetRoleRequestData) {
  return request<Role.GetRoleResponseData>({
    url: "sys/role",
    method: "get",
    params
  })
}

export function getAllMenusApi() {
  return request<Role.GetAllMenusResponseData>({
    url: "sys/role/allmenus",
    method: "get"
  })
}
export function getAllRolesApi() {
  return request<Role.GetRoleResponseData>({
    url: "sys/role/allroles",
    method: "get"
  })
}
export function getAllDeptsApi() {
  return request<Role.GetRoleResponseData>({
    url: "sys/role/alldepts",
    method: "get"
  })
}
