import { request } from "@/utils/service"
import type * as Leave from "./types/flow"

/** 增 */
export function createLeaveDataApi(data: Leave.CreateOrUpdateLeaveRequestData) {
  return request({
    url: "flow/leave",
    method: "post",
    data
  })
}

/** 删 */
export function deleteLeaveDataApi(id: string) {
  return request({
    url: `flow/leave/${id}`,
    method: "delete"
  })
}

/** 改 */
export function updateLeaveDataApi(data: Leave.CreateOrUpdateLeaveRequestData) {
  return request({
    url: "flow/leave/" + data.form_id,
    method: "put",
    data
  })
}

/** 查 */
export function getLeaveDataApi(params: Leave.GetLeaveRequestData) {
  return request<Leave.GetLeaveResponseData>({
    url: "flow/leave",
    method: "get",
    params
  })
}
