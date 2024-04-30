import { type Directive } from "vue"
import { useUserStoreHook } from "@/store/modules/user"

/** 权限指令，和权限判断函数 checkPermission 功能类似 */
export const perm: Directive = {
  mounted(el, binding) {
    const { value: permissionRoles } = binding // permissionRoles 为 v-perm= "['/sys/menu/get']" 传过来的绑定值 permissionStr = ["/sys/menu/get"]
    const { ctrlperm } = useUserStoreHook() // ctrlperm为该user拥有的所有操作权限数组
    // console.log(binding, permissionRoles, ctrlperm)

    if (Array.isArray(permissionRoles) && permissionRoles.length > 0) {
      const hasPermission = ctrlperm.some((item) => permissionRoles.includes(item.path))
      // console.log(hasPermission)
      // hasPermission || (el.style.display = "none") // 隐藏
      hasPermission || el.parentNode?.removeChild(el) // 销毁
    } else {
      throw new Error(`need perm! Like v-perm="['/sys/menu/get']"`)
    }
  }
}
