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
