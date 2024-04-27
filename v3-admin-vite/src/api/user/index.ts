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
