import { request } from "@/utils/service"
import type * as Dept from "./types/dept"

/** 增 */
export function createDeptDataApi(data: Dept.CreateOrUpdateDeptRequestData) {
  return request({
    url: "sys/dept",
    method: "post",
    data
  })
}

/** 删 */
export function deleteDeptDataApi(id: string) {
  return request({
    url: `sys/dept/${id}`,
    method: "delete"
  })
}

/** 改 */
export function updateDeptDataApi(data: Dept.CreateOrUpdateDeptRequestData) {
  return request({
    url: "sys/dept/" + data.id,
    method: "put",
    data
  })
}

/** 查 */
export function getDeptDataApi(params: Dept.GetDeptRequestData) {
  return request<Dept.GetDeptResponseData>({
    url: "sys/dept",
    method: "get",
    params
  })
}
