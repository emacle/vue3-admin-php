import axios, { type AxiosInstance, type AxiosRequestConfig } from "axios"
import { useUserStoreHook } from "@/store/modules/user"
import { ElMessage } from "element-plus"
import { get, merge } from "lodash-es"
import { getToken, getRefreshToken } from "./cache/cookies"

/** 退出登录并强制刷新页面（会重定向到登录页） */
function logout() {
  useUserStoreHook().logout()
  location.reload()
}

/** 创建请求实例 */
function createService() {
  // 创建一个 axios 实例命名为 service
  const service = axios.create()
  // 请求拦截
  service.interceptors.request.use(
    (config) => config,
    // 发送失败
    (error) => Promise.reject(error)
  )
  // 响应拦截（可根据具体业务作出相应的调整）
  service.interceptors.response.use(
    (response) => {
      // console.log(response)
      // apiData 是 api 返回的数据
      const apiData = response.data
      // 二进制数据则直接返回
      const responseType = response.request?.responseType
      if (responseType === "blob" || responseType === "arraybuffer") return apiData
      // 这个 code 是和后端约定的业务 code
      const code = apiData.code
      // 如果没有 code, 代表这不是项目后端开发的 api
      if (code === undefined) {
        ElMessage.error("非本系统的接口")
        return Promise.reject(new Error("非本系统的接口"))
      }

      // 本系统采用 code === 20000 来表示没有业务错误
      // if (code !== 20000) {
      if (code === 60204) {
        // 密码错误时，此处中断
        ElMessage({ message: apiData.message, type: apiData.type })
        return Promise.reject(new Error(apiData.message || "Error"))
      } else {
        return apiData
      }
    },
    (error) => {
      // console.log(error)
      // console.log(error.response)
      // console.log(error.response.data.code)
      // code 后端返回的 code 50014
      const code = get(error, "response.data.code") // 50014 CI_ENVIRONMENT 必须要为生产环境，开发环境会带有debug信息导致出错
      // status 是 HTTP 状态码
      const status = get(error, "response.status")
      switch (status) {
        case 400:
          error.message = "请求错误"
          break
        case 401:
          // console.log(status, code)
          if (code === 50014) {
            // token过期
            return againRequest(error) // 此函数先以refresh_token 去获取新access_token, 然后再次以新 access_token 发送原请求
          }
          if (code === 50015) {
            console.log(get(error, "response.data.message"))
            // refresh_token过期 Token 过期时
            logout()
            // // 50008:非法的token; 50012:其他客户端登录了;  50014:Token 过期了;  50015: refresh_token过期
            // if (res.code === 50008 || res.code === 50012 || res.code === 50015) {
            //   // 请自行在引入 MessageBox
            //   // import { Message, MessageBox } from 'element-ui'
            //   console.log(' refresh_token过期 超时......')
            //   MessageBox.confirm('你已被登出，可以取消继续留在该页面，或者重新登录', '确定登出', {
            //     confirmButtonText: '重新登录',
            //     cancelButtonText: '取消',
            //     type: 'warning'
            //   }).then(() => {
            //     store.dispatch('user/FedLogOut').then(() => {
            //       location.reload() // 为了重新实例化vue-router对象 避免bug
            //     })
            //   })
            // }
          }
          break
        case 403:
          error.message = "拒绝访问"
          break
        case 404:
          error.message = "请求地址出错"
          break
        case 408:
          error.message = "请求超时"
          break
        case 500:
          error.message = "服务器内部错误"
          break
        case 501:
          error.message = "服务未实现"
          break
        case 502:
          error.message = "网关错误"
          break
        case 503:
          error.message = "服务不可用"
          break
        case 504:
          error.message = "网关超时"
          break
        case 505:
          error.message = "HTTP 版本不受支持"
          break
        default:
          break
      }
      ElMessage.error(error.message)
      return Promise.reject(error)
    }
  )
  return service
}

async function againRequest(error: { response: { config: any } }) {
  await useUserStoreHook().handleRefreshToken() // 同步以获取刷新 access_token 并且保存在 cookie/localstorage
  const config = error.response.config
  const token = getToken()
  config.headers["Authorization"] = token ? `Bearer ${token}` : undefined // 以新的 access_token
  // console.log("againRequest config ", config)
  const res = await axios.request(config) // 重新进行原请求
  return res.data // 以error.response.config重新请求返回的数据包是在函数内是 被封装在data里面
}

/** 创建请求方法 */
function createRequest(service: AxiosInstance) {
  return function <T>(config: AxiosRequestConfig): Promise<T> {
    const token = getToken()
    // console.log("......", token, import.meta.env.VITE_BASE_API)
    const defaultConfig = {
      headers: {
        // 携带 Token
        Authorization: token ? `Bearer ${token}` : undefined,
        "Content-Type": "application/json"
      },
      timeout: 5000,
      baseURL: import.meta.env.VITE_BASE_API,
      data: {}
    }
    // 将默认配置 defaultConfig 和传入的自定义配置 config 进行合并成为 mergeConfig
    const mergeConfig = merge(defaultConfig, config)

    // 监听 是否 /sys/user/refreshtoken 是则重置token为刷新token
    const url = mergeConfig.url
    // console.log("createRequest", url) // output: createRequest sys/user/refreshtoken
    if (url) {
      if (url.split("/").pop() === "refreshtoken") {
        mergeConfig.headers["Authorization"] = "Bearer " + getRefreshToken()
      }
    } else {
      console.log("Error: URL is undefined or null")
    }

    return service(mergeConfig)
  }
}

/** 用于网络请求的实例 */
const service = createService()
/** 用于网络请求的方法 */
export const request = createRequest(service)
