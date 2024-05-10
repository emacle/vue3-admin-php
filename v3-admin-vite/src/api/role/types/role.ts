export interface CreateOrUpdateRoleRequestData {
  id?: string
  name: string
  remark?: string
  scope?: number
  listorder: number
  status: number
}

export interface GetRoleRequestData {
  /** 当前页码 */
  currentPage: number
  /** 查询条数 */
  size: number
  /** 查询参数：角色名 */
  name?: string
  /** 查询参数：角色说明 */
  remark?: string
  status?: string
  listorder?: number
  fields?: string
  query?: string
  sort?: string
}

export interface GetRoleData {
  id: string
  name: string
  remark: string
  scope: number
  status: number
  listorder: number
}

export type GetRoleResponseData = ApiResponseData<{
  list: GetRoleData[]
  total: number
}>

export interface GetAllMenusData {
  perm_id: number
  id: number
  pid: number
  type: number
  title: string
  icon: string
  path: string
  children: GetAllMenusData[]
}
export type GetAllMenusResponseData = ApiResponseData<{
  list: GetAllMenusData[]
}>
