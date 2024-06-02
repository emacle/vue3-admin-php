export interface CreateOrUpdateUserRequestData {
  id?: string
  username: string
  password?: string
  email?: string
  tel?: string
  role?: number[]
  dept_id?: number
  listorder: number
  status: number
}

export interface GetUserRequestData {
  /** 当前页码 */
  currentPage: number
  /** 查询条数 */
  size: number
  /** 查询参数：用户名 */
  username?: string
  /** 查询参数：手机号 */
  tel?: string
  listorder?: number
  //** TODO: 查询时status使用 string 与searchData保持一致 */
  status?: string
  fields?: string
  query?: string
  sort?: string
}

export interface GetUserData {
  id: string
  username: string
  email: string
  tel: string
  role: number[]
  dept_id: number
  listorder: number
  status: number
}

export type GetUserResponseData = ApiResponseData<{
  list: GetUserData[]
  total: number
}>

export interface GetRoleOptionsData {
  value: string
  label: string
}

export type GetRoleOptionsResponseData = ApiResponseData<{
  list: GetRoleOptionsData[]
}>

export interface GetDeptOptionsData {
  value: string
  label: string
  children: GetDeptOptionsData[]
}

export type GetDeptOptionsResponseData = ApiResponseData<{
  list: GetDeptOptionsData[]
}>

export interface UpdateUserPasswordRequestData {
  passwordOrig: string
  password: string
  rePassword: string
}
