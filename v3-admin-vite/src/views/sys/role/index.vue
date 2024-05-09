<script lang="ts" setup>
import { reactive, ref, watch, onMounted } from "vue"
import { createRoleDataApi, deleteRoleDataApi, getRoleDataApi, updateRoleDataApi, getAllMenusApi } from "@/api/role"
import { type CreateOrUpdateRoleRequestData, type GetRoleData, type GetAllMenusData } from "@/api/role/types/role"
import { type FormInstance, type FormRules, ElMessage, ElMessageBox } from "element-plus"
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

/** 监听分页参数的变化 */
watch([() => paginationData.currentPage, () => paginationData.pageSize], getRoleData, { immediate: true })

// 角色授权
import type { TabsPaneContext } from "element-plus"

const activeName = ref("menu")

const handleClick = (tab: TabsPaneContext, event: Event) => {
  console.log(tab, event)
}
const selectRole = ref<GetRoleData>({
  id: "",
  name: "",
  pid: "",
  remark: "",
  scope: 0,
  status: 0,
  listorder: 0
})
const menuData = ref<GetAllMenusData[]>([])
const menuLoading = ref<boolean>(false)

// 根据icon值返回对应的组件名称
const getIconComponent = (icon: string) => {
  return icon
}

const handleRoleSelectChange = (val: GetRoleData | any) => {
  if (val === null) {
    selectRole.value = {
      id: "",
      name: "",
      pid: "",
      remark: "",
      scope: 0,
      status: 0,
      listorder: 0
    }
    // this.dataPermScope = ""
    // this.checkJianlian = false // 保存权限提交后重置此值
    return
  }
  selectRole.value = val
  console.log("selectRole.value", selectRole.value, selectRole.value.name)
}

const getAllMenusData = () => {
  menuLoading.value = true
  getAllMenusApi()
    .then(({ data }) => {
      console.log("getAllMenusApi...", data)
      menuData.value = data.list
    })
    .catch(() => {
      // menuData.value = []
    })
    .finally(() => {
      menuLoading.value = false
    })
}

// 在组件实例创建时立即获取数据
onMounted(() => {
  getAllMenusData()
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
              <span v-if="selectRole.id != null" class="menu-role">: {{ selectRole.name }}</span>
            </h2>
          </span>
        </div>
        <el-tabs v-model="activeName" type="card" class="demo-tabs" @tab-click="handleClick">
          <el-tab-pane label="菜单类" name="menu">
            <!-- <el-tree
              ref="menuTree"
              v-loading="menuLoading"
              :data="menuData"
              :props="defaultProps"
              :render-content="renderContent"
              :check-strictly="true"
              show-checkbox
              node-key="id"
              size="mini"
              style="width: 100%; pading-top: 20px"
              element-loading-text="拼命加载中"
              @check-change="handleMenuCheckChange"
            /> -->
            <!-- <el-tree
              ref="menuTree"
              v-loading="menuLoading"
              element-loading-text="拼命加载中"
              :data="menuData"
              show-checkbox
              node-key="id"
              style="width: 100%; pading-top: 20px"
              :expand-on-click-node="false"
            >
              <template #default="{ data }">
                <span class="custom-tree-node">
                  <span>
                    <el-icon v-if="data.icon"><component :is="getIconComponent(data.icon)" /></el-icon>
                    {{ data.title }}
                  </span>
                  <span>
                    <el-tag :type="data.type === 0 ? 'primary' : data.type === 1 ? 'success' : 'warning'">
                      {{ data.type === 0 ? "目录" : data.type === 1 ? "菜单" : "操作" }}
                    </el-tag>
                  </span>
                  <span>{{ data.path }}</span>
                </span>
              </template>
            </el-tree> -->
          </el-tab-pane>
          <el-tab-pane label="角色类" name="role">角色类</el-tab-pane>
          <el-tab-pane label="数据权限" name="dept">数据权限</el-tab-pane>
          <el-tab-pane label="文件类" name="file">文件类</el-tab-pane>
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
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px" label-position="left">
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
