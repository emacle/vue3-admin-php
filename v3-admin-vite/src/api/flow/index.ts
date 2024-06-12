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

/** 请假审批接口 */
/** 查 */
export function getLeaveAuditDataApi(params: Leave.GetLeaveAuditRequestData) {
  return request<Leave.GetLeaveResponseData>({
    url: "flow/leaveaudit",
    method: "get",
    params
  })
}

/** 改 */
export function updateLeaveAuditDataApi(data: Leave.CreateOrUpdateLeaveAuditRequestData) {
  return request({
    url: "flow/leaveaudit/" + data.process_id,
    method: "put",
    data
  })
}

/** 查流程数据 */
export function getLeaveFlowDataApi(form_id: string) {
  return request<Leave.GetLeaveFlowResponseData>({
    url: "flow/leave/process/" + form_id,
    method: "get"
  })
}
