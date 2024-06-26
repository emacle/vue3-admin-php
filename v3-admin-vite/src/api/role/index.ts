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

export function getRoleMenusApi(params: { id: string }) {
  return request<Role.GetAllMenusResponseData>({
    url: "sys/role/rolemenus",
    method: "get",
    params
  })
}

export function getAllRolesApi() {
  return request<Role.GetAllRolesResponseData>({
    url: "sys/role/allroles",
    method: "get"
  })
}

export function getRoleRolesApi(params: { id: string }) {
  return request<Role.GetAllRolesResponseData>({
    url: "sys/role/roleroles",
    method: "get",
    params
  })
}

export function getAllDeptsApi() {
  return request<Role.GetAllDeptsResponseData>({
    url: "sys/role/alldepts",
    method: "get"
  })
}

export function getRoleDeptsApi(params: { id: string }) {
  return request<Role.GetAllDeptsResponseData>({
    url: "sys/role/roledepts",
    method: "get",
    params
  })
}

export function saveRolePermsApi(roleId: string, rolePerms: any[], roleScope: string) {
  return request({
    url: "sys/role/saveroleperm",
    method: "post",
    data: { roleId, rolePerms, roleScope }
  })
}
