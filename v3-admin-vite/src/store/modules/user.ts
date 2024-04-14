import { ref } from "vue"
import store from "@/store"
import { defineStore } from "pinia"
import { useTagsViewStore } from "./tags-view"
import { useSettingsStore } from "./settings"
import { getToken, removeToken, setToken } from "@/utils/cache/cookies"
import { resetRouter } from "@/router"
import { loginApi, getUserInfoApi } from "@/api/login"
import { type LoginRequestData } from "@/api/login/types/login"
import routeSettings from "@/config/route"

export const useUserStore = defineStore("user", () => {
  const token = ref<string>(getToken() || "")
  const roles = ref<string[]>([])
  const username = ref<string>("")

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
  }
  /** 获取用户详情 */
  const getInfo = async () => {
    const { data } = await getUserInfoApi()
    username.value = data.username
    // 验证返回的 roles 是否为一个非空数组，否则塞入一个没有任何作用的默认角色，防止路由守卫逻辑进入无限循环
    roles.value = data.roles?.length > 0 ? data.roles : routeSettings.defaultRoles
  }
  /** 模拟角色变化 */
  const changeRoles = async (role: string) => {
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
    roles.value = []
    resetRouter()
    _resetTagsView()
  }
  /** 重置 Token */
  const resetToken = () => {
    removeToken()
    token.value = ""
    roles.value = []
  }
  /** 重置 Visited Views 和 Cached Views */
  const _resetTagsView = () => {
    if (!settingsStore.cacheTagsView) {
      tagsViewStore.delAllVisitedViews()
      tagsViewStore.delAllCachedViews()
    }
  }

  return { token, roles, username, login, getInfo, changeRoles, logout, resetToken }
})

/** 在 setup 外使用 */
export function useUserStoreHook() {
  return useUserStore(store)
}
