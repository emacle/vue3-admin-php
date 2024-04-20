export interface CreateOrUpdateUserRequestData {
  id?: string
  username: string
  password?: string
}

export interface GetUserRequestData {
  /** 当前页码 */
  offset: number
  /** 查询条数 */
  limit: number
  /** 查询参数：用户名 */
  username?: string
  /** 查询参数：手机号 */
  phone?: string
}

export interface GetUserData {
  createTime: string
  email: string
  id: number
  phone: string
  role: number[]
  dept: number[]
  status: number
  username: string
}

export type GetUserResponseData = ApiResponseData<{
  list: GetUserData[]
  total: number
}>
