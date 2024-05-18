<script lang="ts" setup>
import { reactive, ref, watch, onMounted } from "vue"
import {
  createRoleDataApi,
  deleteRoleDataApi,
  getRoleDataApi,
  updateRoleDataApi,
  getAllMenusApi,
  getAllRolesApi,
  getAllDeptsApi,
  getRoleMenusApi,
  getRoleRolesApi,
  getRoleDeptsApi,
  saveRolePermsApi
} from "@/api/role"
import {
  type CreateOrUpdateRoleRequestData,
  type GetRoleData,
  type GetAllMenusData,
  type GetAllRolesData,
  type GetAllDeptsData
} from "@/api/role/types/role"
import { type FormInstance, type FormRules, ElMessage, ElMessageBox, ElTable, ElTree } from "element-plus"
import type Node from "element-plus/es/components/tree/src/model/node"
import { Search, Refresh, CirclePlus, Delete, Download, RefreshRight } from "@element-plus/icons-vue"
import { usePagination } from "@/hooks/usePagination"
import { cloneDeep } from "lodash-es"

defineOptions({
  // 命名当前组件
  name: "SysRole"
})

const loading = ref<boolean>(false)
const { paginationData, handleCurrentChange, handleSizeChange } = usePagination()

//#region 增
const DEFAULT_FORM_DATA: CreateOrUpdateRoleRequestData = {
  id: undefined,
  name: "",
  remark: "",
  listorder: 99,
  status: 1
}
const dialogVisible = ref<boolean>(false)
const formRef = ref<FormInstance | null>(null)
const formData = ref<CreateOrUpdateRoleRequestData>(cloneDeep(DEFAULT_FORM_DATA))
const formRules: FormRules<CreateOrUpdateRoleRequestData> = {
  name: [{ required: true, trigger: "blur", message: "请输入角色名" }]
}
const options = [
  {
    value: "1",
    label: "启用"
  },
  {
    value: "0",
    label: "禁用"
  }
]
const handleCreateOrUpdate = () => {
  formRef.value?.validate((valid: boolean, fields) => {
    if (!valid) return console.error("表单校验不通过", fields)
    loading.value = true
    const api = formData.value.id === undefined ? createRoleDataApi : updateRoleDataApi
    api(formData.value)
      .then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        dialogVisible.value = false
        getRoleData()
      })
      .finally(() => {
        loading.value = false
      })
      .catch((err) => {
        console.log(err)
      })
  })
}
const resetForm = () => {
  formRef.value?.clearValidate()
  formData.value = cloneDeep(DEFAULT_FORM_DATA)
}
//#endregion

//#region 删
const handleDelete = (row: GetRoleData) => {
  ElMessageBox.confirm(`正在删除用户：${row.name}，确认删除？`, "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      deleteRoleDataApi(row.id).then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        getRoleData()
      })
    })
    .catch((err) => {
      console.log(err)
    })
}
//#endregion

//#region 改
const handleUpdate = (row: GetRoleData) => {
  dialogVisible.value = true
  formData.value = cloneDeep(row)
}
//#endregion

//#region 查
const roleData = ref<GetRoleData[]>([])
const searchFormRef = ref<FormInstance | null>(null)
const searchData = reactive({
  name: "",
  status: ""
})
const getRoleData = () => {
  loading.value = true
  getRoleDataApi({
    currentPage: paginationData.currentPage,
    size: paginationData.pageSize,
    name: searchData.name || undefined,
    status: searchData.status || undefined,
    fields: "id,name,remark,scope,status,listorder", // 与后端一致 前端指定获取的字段
    query: "~name,status", // 前端指定模糊查询的字段为name,精确查询字段为status
    sort: "+listorder" // 前面指定按listorder升序排列
  })
    .then(({ data }) => {
      paginationData.total = data.total
      roleData.value = data.list
    })
    .catch(() => {
      roleData.value = []
    })
    .finally(() => {
      loading.value = false
    })
}
const handleSearch = () => {
  paginationData.currentPage === 1 ? getRoleData() : (paginationData.currentPage = 1)
}
const resetSearch = () => {
  searchFormRef.value?.resetFields()
  handleSearch()
}
//#endregion

// 根据icon值返回对应的组件名称
const getIconComponent = (icon: string) => {
  return icon
}

/** 监听分页参数的变化 */
watch([() => paginationData.currentPage, () => paginationData.pageSize], getRoleData, { immediate: true })

//#region 角色授权
const activeName = ref("menu")
const btnsize = ref<"large" | "default" | "small">("small")

// 定义初始值
const initialSelectRoleValue: GetRoleData = {
  id: "",
  name: "",
  remark: "",
  scope: "0",
  status: 0,
  listorder: 0
}

// 创建 ref
const selectRole = ref<GetRoleData>(initialSelectRoleValue)

const menuLoading = ref<boolean>(false)
const roleLoading = ref<boolean>(false)
const authLoading = ref<boolean>(false)
const deptLoading = ref<boolean>(false)

const checkAll = ref<boolean>(false)
const checkJianlian = ref<boolean>(false)

const menuData = ref<GetAllMenusData[]>([])
const roleDataX = ref<GetAllRolesData[]>([])
const deptData = ref<GetAllDeptsData[]>([])
const currentRoleMenus = ref<GetAllMenusData[]>([]) // 服务端获取当前角色的菜单类权限
const currentRoleRoles = ref<GetAllRolesData[]>([]) // 服务端获取当前角色的角色类权限
const currentRoleDepts = ref<GetAllDeptsData[]>([]) // 服务端获取当前角色的角色类权限

const menuTree = ref<InstanceType<typeof ElTree>>() // 确保menuTree被正确引用
const roleTable = ref<InstanceType<typeof ElTable>>() // 确保roleTable被正确引用
const deptTree = ref<InstanceType<typeof ElTree>>() // 确保deptTree被正确引用

const dataPermScope = ref<string>("")
const dataPermOption = [
  {
    value: "0",
    label: "全部数据权限"
  },
  {
    value: "1",
    label: "部门数据权限"
  },
  {
    value: "2",
    label: "部门及以下数据权限"
  },
  {
    value: "3",
    label: "仅本人数据权限"
  },
  {
    value: "4",
    label: "自定数据权限"
  }
]
const scopeLabels = dataPermOption.reduce(
  (labels, option) => {
    labels[option.value] = option.label
    return labels
  },
  {} as Record<string, string>
)
// 生成以下对象
// scopeLabels: {
//       '0': '全部数据权限',
//       '1': '部门数据权限',
//       '2': '部门及以下数据权限',
//       '3': '仅本人数据权限',
//       '4': '自定数据权限'
//     }

const handleRoleSelectChange = (val: GetRoleData | any) => {
  if (val === null) {
    selectRole.value = { ...initialSelectRoleValue }
    dataPermScope.value = ""
    checkJianlian.value = false // 保存权限提交后重置此值
    return
  }

  selectRole.value = val
  dataPermScope.value = val.scope
  // checkJianlian.value = false // 保存权限提交后重置此值
  // getRoleMenusData({ id: selectRole.value.id })
  // getRoleRolesData({ id: selectRole.value.id })
  // getRoleDeptsData({ id: selectRole.value.id })
  resetSelection()
}

// 树节点选择监听
const handleMenuCheckChange = (data: any, check: any) => {
  if (check) {
    // 节点选中时同步选中父节点
    const parentId = data.pid
    menuTree.value!.setChecked(parentId, true, false)
  } else {
    // 节点取消选中时同步取消选中子节点
    if (data.children != null) {
      data.children.forEach((element: { id: any }) => {
        menuTree.value!.setChecked(element.id, false, false)
      })
    }
  }
}
const handleCheckAll = () => {
  if (checkAll.value) {
    const allMenus: any = []
    checkAllMenu(menuData.value, allMenus)
    menuTree.value!.setCheckedNodes(allMenus)
  } else {
    menuTree.value!.setCheckedNodes([])
  }
}
const checkAllMenu = (menuData: any[], allMenus: any[]) => {
  menuData.forEach((menu) => {
    allMenus.push(menu)
    if (menu.children) {
      checkAllMenu(menu.children, allMenus)
    }
  })
}

const submitAuthForm = () => {
  const roleId = selectRole.value.id

  const rolePerms: any[] = []
  // authLoading.value = true // 设置加载状态为 true

  // 1.获取选中的菜单类权限
  const checkedNodes = menuTree.value!.getCheckedNodes(false, true)
  checkedNodes.forEach((node) => {
    rolePerms.push(node.perm_id)
  })

  // 2.获取选中的角色类权限
  // 调用实例的 getSelectionRows 方法获取选中的项
  const roleSelections = roleTable.value!.getSelectionRows()
  roleSelections.forEach((selection: { perm_id: any }) => {
    rolePerms.push(selection.perm_id)
  })

  const roleScope = dataPermScope.value
  // 获取选中的部门数据权限
  if (dataPermScope.value === "4") {
    const checkedDeptNodes = deptTree.value!.getCheckedNodes(false, true)
    checkedDeptNodes.forEach((node) => {
      rolePerms.push(node.perm_id)
    })
  }

  // console.log("rolePerms选中的项 含menu,role,dept:", rolePerms)
  // [6, 11, 12, 1, 38]

  saveRolePermsApi(roleId, rolePerms, roleScope)
    .then((res: any) => {
      // console.log('saveRolePerms...', res)
      ElMessage({ message: res.message, type: res.type })
      clearAllSelection()
      getRoleData()
    })
    .catch((err) => {
      console.log(err)
    })
    .finally(() => {
      authLoading.value = false
    })
}
// 清空所有选择
const clearAllSelection = () => {
  menuTree.value!.setCheckedNodes([])
  roleTable.value!.clearSelection()
  deptTree.value!.setCheckedNodes([])
}

// 重置默认值
const resetSelection = () => {
  checkJianlian.value = false
  dataPermScope.value = selectRole.value.scope
  getRoleMenusData({ id: selectRole.value.id })
  getRoleRolesData({ id: selectRole.value.id })
  getRoleDeptsData({ id: selectRole.value.id })
}

const getAllDeptsData = () => {
  deptLoading.value = true
  getAllDeptsApi()
    .then(({ data }) => {
      deptData.value = data.list
      // console.log(cloneDeep(deptData.value))
    })
    .catch(() => {
      deptData.value = []
    })
    .finally(() => {
      deptLoading.value = false
    })
}
const getRoleDeptsData = (params: { id: string }) => {
  deptLoading.value = true
  getRoleDeptsApi(params)
    .then(({ data }) => {
      currentRoleDepts.value = data.list
      if (deptTree.value) {
        // 确保menuTree已被赋值 然后设置勾选节点
        deptTree.value.setCheckedNodes(currentRoleDepts.value as unknown as Node[]) // 使用setCheckedNodes设置选中项
      }
    })
    .catch(() => {
      currentRoleDepts.value = []
    })
    .finally(() => {
      deptLoading.value = false
    })
}
const getRoleRolesData = (params: { id: string }) => {
  roleLoading.value = true
  getRoleRolesApi(params)
    .then(({ data }) => {
      currentRoleRoles.value = data.list
      // 清空当前选择
      roleTable.value!.clearSelection()
      // 找到匹配的行，则输出索引并查找对应的roleDataX元素
      currentRoleRoles.value.forEach((role) => {
        const index = roleDataX.value.findIndex((row) => row.perm_id === role.perm_id)
        if (index !== -1) {
          roleTable.value!.toggleRowSelection(roleDataX.value[index], true)
        }
      })
    })
    .catch(() => {
      currentRoleRoles.value = []
    })
    .finally(() => {
      roleLoading.value = false
    })
}
const getAllRolesData = () => {
  roleLoading.value = true
  getAllRolesApi()
    .then(({ data }) => {
      roleDataX.value = data.list
    })
    .catch(() => {
      roleDataX.value = []
    })
    .finally(() => {
      roleLoading.value = false
    })
}

const getRoleMenusData = (params: { id: string }) => {
  menuLoading.value = true
  getRoleMenusApi(params)
    .then(({ data }) => {
      currentRoleMenus.value = data.list
      // console.log("getRoleMenusApi...", data, cloneDeep(currentRoleMenus.value))
      // console.log(cloneDeep(menuTree.value))
      if (menuTree.value) {
        // 确保menuTree已被赋值 然后设置勾选节点
        menuTree.value.setCheckedNodes(currentRoleMenus.value as unknown as Node[]) // 使用setCheckedNodes设置选中项
      }
    })
    .catch(() => {
      currentRoleMenus.value = []
    })
    .finally(() => {
      menuLoading.value = false
    })
}
const getAllMenusData = () => {
  menuLoading.value = true
  getAllMenusApi()
    .then(({ data }) => {
      // console.log("getAllMenusApi...", data)
      menuData.value = data.list
    })
    .catch(() => {
      menuData.value = []
    })
    .finally(() => {
      menuLoading.value = false
    })
}
//#endregion

// 在组件实例创建时立即获取数据
onMounted(() => {
  getAllMenusData()
  getAllRolesData()
  getAllDeptsData()
})
</script>

<template>
  <div class="app-container">
    <el-card shadow="never" class="search-wrapper" v-perm="['/sys/role/get']">
      <el-form ref="searchFormRef" :inline="true" :model="searchData">
        <el-form-item prop="name" label="角色名">
          <el-input v-model="searchData.name" clearable placeholder="请输入角色名称" />
        </el-form-item>
        <el-form-item prop="status" label="状态">
          <el-select v-model="searchData.status" clearable placeholder="请选择">
            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :icon="Search" @click="handleSearch">查询</el-button>
          <el-button :icon="Refresh" @click="resetSearch">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card v-loading="loading" shadow="never">
      <div class="toolbar-wrapper">
        <div>
          <el-button v-perm="['/sys/role/post']" type="primary" :icon="CirclePlus" @click="dialogVisible = true"
            >新增角色</el-button
          >
          <!-- <el-button type="danger" :icon="Delete">批量删除</el-button> -->
        </div>
        <div>
          <!-- <el-tooltip content="下载">
            <el-button type="primary" :icon="Download" circle />
          </el-tooltip> -->
          <el-tooltip content="刷新当前页">
            <el-button type="primary" :icon="RefreshRight" circle @click="getRoleData" />
          </el-tooltip>
        </div>
      </div>
      <div class="table-wrapper">
        <el-table :data="roleData" highlight-current-row @current-change="handleRoleSelectChange">
          <!-- <el-table-column type="selection" width="50" align="center" /> -->
          <el-table-column prop="id" label="ID" align="center" />
          <el-table-column prop="name" label="角色名称" align="center" />
          <el-table-column prop="remark" label="说明" align="center" />
          <el-table-column prop="scope" label="权限范围" align="center">
            <template #default="scope">
              <span>{{ scopeLabels[scope.row.scope] }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="listorder" label="排序" align="center" />
          <el-table-column prop="status" label="状态" align="center">
            <template #default="scope">
              <el-tag v-if="scope.row.status" type="success" effect="plain">启用</el-tag>
              <el-tag v-else type="danger" effect="plain">禁用</el-tag>
            </template>
          </el-table-column>
          <el-table-column fixed="right" label="操作" width="150" align="center">
            <template #default="scope">
              <el-button v-perm="['/sys/role/put']" type="primary" text bg size="small" @click="handleUpdate(scope.row)"
                >修改</el-button
              >
              <el-button
                v-perm="['/sys/role/delete']"
                type="danger"
                text
                bg
                size="small"
                @click="handleDelete(scope.row)"
                >删除</el-button
              >
            </template>
          </el-table-column>
        </el-table>
      </div>
      <div class="pager-wrapper">
        <el-pagination
          background
          :layout="paginationData.layout"
          :page-sizes="paginationData.pageSizes"
          :total="paginationData.total"
          :page-size="paginationData.pageSize"
          :currentPage="paginationData.currentPage"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
    <el-card class="auth-wrapper" shadow="never">
      <div class="table-wrapper">
        <div class="menu-header">
          <span>
            <h2>
              角色授权
              <span v-if="selectRole.id !== ''" class="menu-role">: {{ selectRole.name }}</span>
            </h2>
          </span>
        </div>
        <el-tabs v-model="activeName" type="card" class="demo-tabs">
          <el-tab-pane label="菜单类" name="menu">
            <el-tree
              ref="menuTree"
              v-loading="menuLoading"
              element-loading-text="拼命加载中"
              :data="menuData"
              :check-strictly="true"
              show-checkbox
              node-key="id"
              style="width: 100%; padding-top: 20px"
              @check-change="handleMenuCheckChange"
            >
              <template #default="{ data }">
                <span class="custom-tree-node">
                  <span>
                    <!-- <el-icon v-if="data.icon"><component :is="getIconComponent(data.icon)" /></el-icon> -->
                    <SvgIcon v-if="data.icon" :name="data.icon" />
                    {{ data.title }}({{ data.perm_id }})
                  </span>
                  <span>
                    <el-tag :type="data.type === 0 ? 'primary' : data.type === 1 ? 'success' : 'warning'">
                      {{ data.type === 0 ? "目录" : data.type === 1 ? "菜单" : "操作" }}
                    </el-tag>
                  </span>
                  <span>
                    <el-tag>{{ data.path }}</el-tag>
                  </span>
                </span>
              </template>
            </el-tree>
          </el-tab-pane>
          <el-tab-pane label="角色类" name="role">
            <el-table ref="roleTable" :data="roleDataX" :loading="roleLoading">
              <el-table-column type="selection" width="50" align="center" />
              <el-table-column prop="id" label="ID" align="center" />
              <el-table-column prop="perm_id" label="perm_id" align="center" />
              <el-table-column prop="name" label="角色名称" align="center" />
              <el-table-column prop="remark" label="说明" align="center" />
              <el-table-column prop="listorder" label="排序" align="center" />
              <el-table-column prop="status" label="状态" align="center">
                <template #default="scope">
                  <el-tag v-if="scope.row.status" type="success" effect="plain">启用</el-tag>
                  <el-tag v-else type="danger" effect="plain">禁用</el-tag>
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>
          <el-tab-pane label="数据权限" name="dept">
            <el-row :gutter="0">
              <el-form size="default" label-width="100px">
                <el-col :span="24">
                  <el-form-item :label="'权限范围(' + dataPermScope + ')'" prop="dataPermScope">
                    <el-select v-model="dataPermScope" style="width: 180px">
                      <el-option
                        v-for="item in dataPermOption"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value"
                      />
                    </el-select>
                  </el-form-item>
                </el-col>
                <el-col v-show="dataPermScope == '4'" :span="24">
                  <el-form-item label="部门数据" prop="dept">
                    <!-- :props="defaultDeptProps" -->
                    <el-tree
                      ref="deptTree"
                      v-loading="deptLoading"
                      element-loading-text="拼命加载中"
                      :data="deptData"
                      :check-strictly="!checkJianlian"
                      show-checkbox
                      node-key="id"
                      style="width: 100%; padding-top: 20px"
                    >
                      <!-- @check-change="handleDeptCheckChange" -->
                      <template #default="{ data }">
                        <span class="custom-tree-node">
                          <span> {{ data.name }}({{ data.perm_id }}) </span>
                        </span>
                      </template>
                    </el-tree>
                  </el-form-item>
                </el-col>
                <el-col v-show="dataPermScope == '4'" :span="16">
                  <el-form-item label="勾选级联" prop="jilian">
                    <el-checkbox v-model="checkJianlian" :disabled="selectRole.id == null" toggle />
                  </el-form-item>
                </el-col>
              </el-form>
            </el-row>
          </el-tab-pane>
          <el-tab-pane label="文件类" name="file">文件类</el-tab-pane>
          <div style="float: left; padding-left: 24px; padding-top: 12px; padding-bottom: 4px">
            <el-checkbox
              v-if="activeName === 'menu'"
              v-model="checkAll"
              :disabled="selectRole.id === ''"
              @change="handleCheckAll"
            >
              <b>全选</b>
            </el-checkbox>
          </div>
          <div style="float: right; padding-right: 15px; padding-top: 15px; padding-bottom: 4px">
            <el-button
              v-perm="['/sys/role/saveroleperm/post']"
              :disabled="selectRole.id === ''"
              :size="btnsize"
              type="primary"
              @click="resetSelection"
              >重置</el-button
            >
            <el-button
              v-perm="['/sys/role/saveroleperm/post']"
              :loading="authLoading"
              :disabled="selectRole.id == ''"
              :size="btnsize"
              type="primary"
              @click="submitAuthForm"
              >提交</el-button
            >
          </div>
        </el-tabs>
      </div>
    </el-card>
    <!-- 新增/修改 -->
    <el-dialog
      v-model="dialogVisible"
      :title="formData.id === undefined ? '新增角色' : '修改角色'"
      @closed="resetForm"
      width="30%"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px" label-position="right">
        <el-form-item prop="name" label="角色名">
          <el-input v-model.trim="formData.name" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="remark" label="角色说明">
          <el-input v-model="formData.remark" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="listorder" label="排序">
          <!-- <el-input v-model="formData.listorder" placeholder="请输入" /> -->
          <el-input-number v-model="formData.listorder" :min="99" controls-position="right" size="large" />
        </el-form-item>
        <el-form-item prop="status" label="状态">
          <!-- <el-input v-model="formData.remark" placeholder="请输入" /> -->
          <el-switch v-model="formData.status" :active-value="1" :inactive-value="0">
            <template #active>启用</template>
            <template #inactive>禁用</template>
          </el-switch>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleCreateOrUpdate" :loading="loading">确认</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style lang="scss" scoped>
.search-wrapper {
  margin-bottom: 20px;
  :deep(.el-card__body) {
    padding-bottom: 2px;
  }
}

.auth-wrapper {
  margin-top: 20px;
  :deep(.el-card__body) {
    padding-top: 2px;
  }
}

.toolbar-wrapper {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.table-wrapper {
  margin-bottom: 20px;
}

.el-select {
  width: auto;
  min-width: 100px; /* Adjust as needed */
}

.pager-wrapper {
  display: flex;
  justify-content: flex-end;
}

.demo-tabs > .el-tabs__content {
  padding: 32px;
  color: #6b778c;
  font-size: 32px;
  font-weight: 600;
}

.menu-role {
  color: rgb(211, 66, 22);
}

.custom-tree-node {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;
}
</style>
