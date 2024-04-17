export interface LoginRequestData {
  /** admin 或 editor */
  username: "admin" | "editor"
  /** 密码 */
  password: string
  /** 验证码 */
  code: string
}

export type LoginCodeResponseData = ApiResponseData<string>

export type LoginResponseData = ApiResponseData<{ token: string }>

// export type UserInfoResponseData = ApiResponseData<{ username: string; roles: string[] }>

export interface CtrlPermData {
  // 假设每个控制权限对象有以下属性
  path: string
}
export interface metaData {
  icon: string
  title: string
}
export interface asyncRouterMapData {
  // 假设每个控制权限对象有以下属性
  component: string
  hidden: number
  id: number
  listorder: number
  name: string
  path: string
  pid: number
  redirect: string
  type: number
  status: number
  meta: metaData
  children: asyncRouterMapData[]
}

export type UserInfoResponseData = ApiResponseData<{
  avatar: string
  username: string
  roles: string[]
  ctrlperm: CtrlPermData[]
  asyncRouterMap: asyncRouterMapData[]
}>
