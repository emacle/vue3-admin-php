export interface CreateOrUpdateDeptRequestData {
  id?: string
  pid: string
  name: string
  aliasname: string
  listorder: number
  status: number
}

export interface GetDeptRequestData {
  // /** 当前页码 */
  // offset: number
  // /** 查询条数 */
  // limit: number
  /** 查询参数：用户名 */
  name?: string
  /** 查询参数：手机号 */
  status?: string
  fields?: string
  query?: string
  sort?: string
}

export interface GetDeptData {
  id: string
  pid: string
  name: string
  listorder: number
  status: number
  children: GetDeptData[]
}

export interface GetDeptDataOptions {
  value: string
  label: string
  children: GetDeptDataOptions[]
}

export type GetDeptResponseData = ApiResponseData<{
  list: GetDeptData[]
  total: number
}>
