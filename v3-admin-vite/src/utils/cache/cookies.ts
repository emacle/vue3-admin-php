/** 统一处理 Cookie */

import CacheKey from "@/constants/cache-key"
import Cookies from "js-cookie"

export const getToken = () => {
  return Cookies.get(CacheKey.TOKEN)
}
export const setToken = (token: string) => {
  Cookies.set(CacheKey.TOKEN, token)
}
export const removeToken = () => {
  Cookies.remove(CacheKey.TOKEN)
}
export const getRefreshToken = () => {
  return Cookies.get(CacheKey.REFRESH_TOKEN)
}
export const setRefreshToken = (refresh_token: string) => {
  Cookies.set(CacheKey.REFRESH_TOKEN, refresh_token)
}
export const removeRefreshToken = () => {
  Cookies.remove(CacheKey.REFRESH_TOKEN)
}
