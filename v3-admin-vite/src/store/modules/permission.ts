import { ref } from "vue"
import store from "@/store"
import { defineStore } from "pinia"
import { type RouteRecordRaw } from "vue-router"
import { constantRoutes } from "@/router"
import { flatMultiLevelRoutes } from "@/router/helper"
import routeSettings from "@/config/route"
import { useUserStoreHook } from "@/store/modules/user"

const Layouts = () => import("@/layouts/index.vue")

// const hasPermission = (roles: string[], route: RouteRecordRaw) => {
//   const routeRoles = route.meta?.roles
//   return routeRoles ? roles.some((role) => routeRoles.includes(role)) : true
// }

// const filterDynamicRoutes = (routes: RouteRecordRaw[], roles: string[]) => {
//   const res: RouteRecordRaw[] = []
//   console.log("...", roles, routes)
//   routes.forEach((route) => {
//     if (hasPermission(roles, tempRoute)) {
//       if (tempRoute.children) {
//         tempRoute.children = filterDynamicRoutes(tempRoute.children, roles)
//       }
//       res.push(tempRoute)
//     }
//   })
//   return res
// }

// 导入组件的匹配模式
const views = import.meta.glob("@/views/**/*.vue")
// 定义一个函数来解析组件路径
const loadView = (view: any) => {
  // 返回动态导入组件的 promise
  // console.log(views)
  return views[`/src/views/${view}.vue`]
}

// 遍历后台传来的路由字符串，转换为组件对象
function filterAsyncRouter(asyncRouterMap: any[]) {
  return asyncRouterMap.filter((route: any) => {
    if (route.component) {
      // Layout组件特殊处理
      if (route.component === "Layout") {
        route.component = Layouts
      } else {
        route.component = loadView(route.component)
      }
    }
    // 面包屑上 点击 redirect 的 url  首页/系统管理/菜单管理, 可点击系统管理
    route.redirect = route.redirect ? route.redirect : route.component === "Layout" ? "noRedirect" : ""
    // route.alwaysShow = route.children.length === 1

    if (route.children != null && route.children && route.children.length) {
      route.children = filterAsyncRouter(route.children)
    }
    return true
  })
}

let dynamicRoutes: RouteRecordRaw[] = []

export const usePermissionStore = defineStore("permission", () => {
  /** 可访问的路由 */
  const routes = ref<RouteRecordRaw[]>([])
  /** 有访问权限的动态路由 */
  const addRoutes = ref<RouteRecordRaw[]>([])
  // 后端传递过来的字符串转换形成 dynamicRoutes
  // const dynamicRoutes = ref<RouteRecordRaw[]>([])

  /** 根据角色生成可访问的 Routes（可访问的路由 = 常驻路由 + 有访问权限的动态路由） */
  // const setRoutes = (roles: string[]) => {
  //   console.log("usePermissionStore.setRoutes", dynamicRoutes)
  //   const accessedRoutes = filterDynamicRoutes(dynamicRoutes, roles)
  //   _set(accessedRoutes)
  // }
  const setRoutes = () => {
    const { asyncRouterMap } = useUserStoreHook()
    // console.log("usePermissionStore.setRoutes", JSON.stringify(asyncRouterMap, null, 2))
    // [
    //   {
    //     "perm_id": 2,
    //     "pid": 0,
    //     "name": "Sys",
    //     "path": "/sys",
    //     "component": "Layout",
    //     "type": 0,
    //     "redirect": "/sys/menu",
    //     "hidden": 0,
    //     "status": 1,
    //     "condition": "",
    //     "listorder": 99,
    //     "create_time": null,
    //     "update_time": null,
    //     "id": 1,
    //     "meta": {
    //       "title": "系统管理",
    //       "elIcon": "sysset2"
    //     },
    //
    dynamicRoutes = filterAsyncRouter(asyncRouterMap)
    const accessedRoutes = dynamicRoutes
    _set(accessedRoutes)
  }
  /** 所有路由 = 所有常驻路由 + 所有动态路由 */
  const setAllRoutes = () => {
    _set(dynamicRoutes)
  }

  const _set = (accessedRoutes: RouteRecordRaw[]) => {
    routes.value = constantRoutes.concat(accessedRoutes)
    addRoutes.value = routeSettings.thirdLevelRouteCache ? flatMultiLevelRoutes(accessedRoutes) : accessedRoutes
  }

  return { routes, addRoutes, setRoutes, setAllRoutes }
})

/** 在 setup 外使用 */
export function usePermissionStoreHook() {
  return usePermissionStore(store)
}
