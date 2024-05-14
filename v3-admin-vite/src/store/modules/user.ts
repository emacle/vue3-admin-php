import { ref } from "vue"
import store from "@/store"
import { defineStore } from "pinia"
import { useTagsViewStore } from "./tags-view"
import { useSettingsStore } from "./settings"
import {
  getToken,
  removeToken,
  setToken,
  getRefreshToken,
  removeRefreshToken,
  setRefreshToken
} from "@/utils/cache/cookies"
import { resetRouter } from "@/router"
import { loginApi, getUserInfoApi, refreshTokenApi } from "@/api/login"
import { type LoginRequestData } from "@/api/login/types/login"
// import routeSettings from "@/config/route"

export const useUserStore = defineStore("user", () => {
  const token = ref<string>(getToken() || "")
  const refresh_token = ref<string>(getRefreshToken() || "")

  // const roles = ref<string[]>([])
  // 声明ref响应式数据变量
  const roles = ref<{ id: number; name: string }[]>([])

  const userId = ref<string>("")
  const username = ref<string>("")
  const avatar = ref<string>("")
  const asyncRouterMap = ref<unknown[]>([])
  const ctrlperm = ref<{ path: string }[]>([])

  const tagsViewStore = useTagsViewStore()
  const settingsStore = useSettingsStore()

  /** 登录 */
  const login = async ({ username, password, code }: LoginRequestData) => {
    const { data } = await loginApi({ username, password, code })
    // console.log("useUserStore.login return..........", data)
    // loginApi return 如下 const { data }  直接对应其中的 data
    //   {
    //     "code": 20000,
    //     "message": "Login successful",
    //     "data": {
    //         "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MTMwNzAyMzcsIm5iZiI6MTcxMzA3MDIzNywidXNlcl9pZCI6MSwic2NvcGVzIjoicm9sZV9hY2Nlc3MiLCJleHAiOjE3MTMwNzc0Mzd9.nFlGlpUQK3t5IKnxKtRAsWqQI4Iu1ZD7TBoqzfTAdAU",
    //         "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MTMwNzAyMzcsIm5iZiI6MTcxMzA3MDIzNywidXNlcl9pZCI6MSwic2NvcGVzIjoicm9sZV9yZWZyZXNoIiwiZXhwIjoxNzEzNjc1MDM3LCJjb3VudCI6MH0.9UM8PnMdT4cri4pcNW56F_ad9CXo2FGzSQZYWIVg3r0"
    //     }
    // }
    setToken(data.token)
    token.value = data.token
    setRefreshToken(data.refresh_token)
    refresh_token.value = data.refresh_token
  }
  /** 获取用户详情 */
  const getInfo = async () => {
    const { data } = await getUserInfoApi()
    console.log("useUserStore.getInfo", data)
    userId.value = data.id
    username.value = data.username
    avatar.value = data.avatar
    asyncRouterMap.value = data.asyncRouterMap
    // 验证返回的 roles 是否为一个非空数组，否则塞入一个没有任何作用的默认角色，防止路由守卫逻辑进入无限循环
    // roles.value = data.roles?.length > 0 ? data.roles : routeSettings.defaultRoles
    roles.value = data.roles.map((role: any) => ({ id: Number(role.id), name: role.name }))
    ctrlperm.value = data.ctrlperm
  }
  /** 模拟角色变化 */
  // const changeRoles = async (role: string) => {
  const changeRoles = async (role: any) => {
    const newToken = "token-" + role
    token.value = newToken
    setToken(newToken)
    // 用刷新页面代替重新登录
    window.location.reload()
  }
  /** 登出 */
  const logout = () => {
    removeToken()
    token.value = ""
    removeRefreshToken()
    refresh_token.value = ""
    roles.value = []
    ctrlperm.value = []
    resetRouter()
    _resetTagsView()
  }
  /** 重置 Token, refresh_Token */
  const resetToken = () => {
    removeToken()
    token.value = ""
    removeRefreshToken()
    refresh_token.value = ""
    roles.value = []
    ctrlperm.value = []
  }

  const handleRefreshToken = async () => {
    const { data } = await refreshTokenApi()
    console.log("useUserStore.handleRefreshToken", data)
    setToken(data.token)
    token.value = data.token
    console.log("handleRefreshToken...setToken 完成")
    setRefreshToken(data.refresh_token)
    refresh_token.value = data.refresh_token
  }
  /** 重置 Visited Views 和 Cached Views */
  const _resetTagsView = () => {
    if (!settingsStore.cacheTagsView) {
      tagsViewStore.delAllVisitedViews()
      tagsViewStore.delAllCachedViews()
    }
  }

  return {
    token,
    roles,
    userId,
    username,
    avatar,
    asyncRouterMap,
    ctrlperm,
    login,
    getInfo,
    changeRoles,
    logout,
    resetToken,
    handleRefreshToken
  }
})

/** 在 setup 外使用 */
export function useUserStoreHook() {
  return useUserStore(store)
}
