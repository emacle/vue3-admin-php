import { request } from "@/utils/service"
import type * as Menu from "./types/menu"

/** 增 */
export function createMenuDataApi(data: Menu.CreateOrUpdateMenuRequestData) {
  return request({
    url: "sys/menu",
    method: "post",
    data
  })
}

/** 删 */
export function deleteMenuDataApi(id: string) {
  return request({
    url: `sys/menu/${id}`,
    method: "delete"
  })
}

/** 改 */
export function updateMenuDataApi(data: Menu.CreateOrUpdateMenuRequestData) {
  return request({
    url: "sys/menu/" + data.id,
    method: "put",
    data
  })
}

/** 查 */
export function getMenuDataApi(params: Menu.GetMenuRequestData) {
  return request<Menu.GetMenuResponseData>({
    url: "sys/menu",
    method: "get",
    params
  })
}
