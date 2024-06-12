import { EpPropMergeType } from "element-plus/es/utils/index.mjs"

export interface CreateOrUpdateLeaveRequestData {
  form_id?: string
  employee_id?: string
  form_type: string
  start_time: string
  end_time: string
  reason: string
  create_time?: string
  state?: string
}

export interface GetLeaveRequestData {
  /** 当前页码 */
  currentPage: number
  /** 查询条数 */
  size: number
  form_type?: string
  create_time?: string
  fields?: string
  query?: string
  sort?: string
}

export interface GetLeaveData {
  form_id: string
  employee_id?: string
  form_type: string
  start_time: string
  end_time: string
  reason: string
  create_time?: string
  state: string
}

export type GetLeaveResponseData = ApiResponseData<{
  list: GetLeaveData[]
  total: number
}>

export interface CreateOrUpdateLeaveAuditRequestData {
  process_id?: string
  form_id?: string
  result?: string
  reason?: string
  form_type?: string
  start_time?: string
  end_time?: string
  apply_reason?: string
  employee_name?: string
}

export interface GetLeaveAuditRequestData {
  /** 当前页码 */
  currentPage: number
  /** 查询条数 */
  size: number
  result?: string
  reason?: string
  state?: string
  create_time?: string
  fields?: string
  query?: string
  sort?: string
}

export interface GetLeaveAuditData {
  form_id: string
  employee_id?: string
  form_type: string
  start_time: string
  end_time: string
  reason: string
  create_time?: string
  state: string
}

export type GetLeaveAuditResponseData = ApiResponseData<{
  list: GetLeaveAuditData[]
  total: number
}>

export interface GetLeaveFlowData {
  process_id: string
  form_id: string
  operator_id: string
  operator_name: string
  action: string
  result: string
  reason: string
  create_time: string
  audit_time: string
  order_no: number
  state: string
  is_last: number
}

export type GetLeaveFlowResponseData = ApiResponseData<{
  list: GetLeaveFlowData[]
}>
