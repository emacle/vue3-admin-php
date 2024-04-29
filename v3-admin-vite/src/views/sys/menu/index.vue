<script lang="ts" setup>
import { reactive, ref, watch } from "vue"
import { createMenuDataApi, deleteMenuDataApi, getMenuDataApi, updateMenuDataApi } from "@/api/menu"
import { type CreateOrUpdateMenuRequestData, type GetMenuData } from "@/api/menu/types/menu"
import { type FormInstance, type FormRules, ElMessage, ElMessageBox } from "element-plus"
import { Search, Refresh, CirclePlus, Delete, Download, RefreshRight } from "@element-plus/icons-vue"
import { usePagination } from "@/hooks/usePagination"
import { cloneDeep } from "lodash-es"

defineOptions({
  // 命名当前组件
  name: "SysMenu"
})

const loading = ref<boolean>(false)
const { paginationData, handleCurrentChange, handleSizeChange } = usePagination()

//#region 增
// const DEFAULT_FORM_DATA: CreateOrUpdateMenuRequestData = {
//   id: undefined,
//   pid: ""
//   name: ""
//   path: ""
//   component: ""
//   type: ""
//   title: ""
//   icon: ""
//   redirect: ""
//   hidden: ""
//   status: ""
//   condition: ""
//   listorder: 99
// }
// const dialogVisible = ref<boolean>(false)
// const formRef = ref<FormInstance | null>(null)
// const formData = ref<CreateOrUpdateMenuRequestData>(cloneDeep(DEFAULT_FORM_DATA))
// const formRules: FormRules<CreateOrUpdateMenuRequestData> = {
//   name: [{ required: true, trigger: "blur", message: "请输入菜单名" }]
// }
// const handleCreateOrUpdate = () => {
//   formRef.value?.validate((valid: boolean, fields) => {
//     if (!valid) return console.error("表单校验不通过", fields)
//     loading.value = true
//     const api = formData.value.id === undefined ? createMenuDataApi : updateMenuDataApi
//     api(formData.value)
//       .then((res: any) => {
//         ElMessage({ message: res.message, type: res.type })
//         dialogVisible.value = false
//         getMenuData()
//       })
//       .finally(() => {
//         loading.value = false
//       })
//       .catch((err) => {
//         console.log(err)
//       })
//   })
// }
// const resetForm = () => {
//   formRef.value?.clearValidate()
//   formData.value = cloneDeep(DEFAULT_FORM_DATA)
// }
//#endregion

//#region 删
// const handleDelete = (row: GetMenuData) => {
//   ElMessageBox.confirm(`正在删除用户：${row.name}，确认删除？`, "提示", {
//     confirmButtonText: "确定",
//     cancelButtonText: "取消",
//     type: "warning"
//   })
//     .then(() => {
//       deleteMenuDataApi(row.id).then((res: any) => {
//         ElMessage({ message: res.message, type: res.type })
//         getMenuData()
//       })
//     })
//     .catch((err) => {
//       console.log(err)
//     })
// }
//#endregion

//#region 改
// const handleUpdate = (row: GetMenuData) => {
//   dialogVisible.value = true
//   formData.value = cloneDeep(row)
// }
//#endregion

//#region 查
const menuData = ref<GetMenuData[]>([])
const searchFormRef = ref<FormInstance | null>(null)
const searchData = reactive({
  title: ""
})
const menuTypeList = reactive(["目录", "菜单", "操作"])

const getMenuData = () => {
  loading.value = true
  getMenuDataApi({
    title: searchData.title || undefined
  })
    .then(({ data }) => {
      menuData.value = data.list
    })
    .catch(() => {
      menuData.value = []
    })
    .finally(() => {
      loading.value = false
    })
}
const handleSearch = () => {
  getMenuData()
}
const resetSearch = () => {
  searchFormRef.value?.resetFields()
  handleSearch()
}
//#endregion

/** 监听分页参数的变化 */
watch([() => paginationData.currentPage, () => paginationData.pageSize], getMenuData, { immediate: true })
</script>

<template>
  <div class="app-container">
    <el-card shadow="never" class="search-wrapper">
      <el-form ref="searchFormRef" :inline="true" :model="searchData">
        <el-form-item prop="title" label="菜单名">
          <el-input v-model="searchData.title" clearable placeholder="请输入菜单名称" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :icon="Search" @click="handleSearch">查询</el-button>
          <el-button :icon="Refresh" @click="resetSearch">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card v-loading="loading" shadow="never">
      <div class="toolbar-wrapper">
        <!-- <div>
          <el-button type="primary" :icon="CirclePlus" @click="dialogVisible = true">新增菜单</el-button>
        </div> -->
        <div>
          <el-tooltip content="刷新当前页">
            <el-button type="primary" :icon="RefreshRight" circle @click="getMenuData" />
          </el-tooltip>
        </div>
      </div>
      <div class="table-wrapper">
        <el-table :data="menuData" row-key="title" border >
          <el-table-column prop="title" label="菜单名称" align="center" />
          <el-table-column prop="name" label="路由别名" align="center" />
          <el-table-column prop="path" label="路由" align="center" />
          <el-table-column prop="icon" label="图标" align="center" />
          <el-table-column prop="type" label="类型" align="center">
            <template #default="scope">
              <!-- :type="!scope.row.type ? '' : scope.row.type === 1 ? 'success' : 'warning'" -->
              <el-tag size="small">{{ menuTypeList[scope.row.type] }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="component" label="组件" align="center" />
          <el-table-column prop="redirect" label="重定向" align="center" />
          <el-table-column prop="listorder" label="排序" align="center" />
          <el-table-column fixed="right" label="操作" width="150" align="center">
            <!-- <template #default="scope">
              <el-button type="primary" text bg size="small" @click="handleUpdate(scope.row)">修改</el-button>
              <el-button type="danger" text bg size="small" @click="handleDelete(scope.row)">删除</el-button>
            </template> -->
          </el-table-column>
        </el-table>
      </div>
    </el-card>
    <!-- 新增/修改 -->
    <!-- <el-dialog
      v-model="dialogVisible"
      :title="formData.id === undefined ? '新增菜单' : '修改菜单'"
      @closed="resetForm"
      width="30%"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px" label-position="left">
        <el-form-item prop="name" label="菜单名">
          <el-input v-model.trim="formData.name" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="remark" label="菜单说明">
          <el-input v-model="formData.remark" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="listorder" label="排序">
          <el-input-number v-model="formData.listorder" :min="99" controls-position="right" size="large" />
        </el-form-item>
        <el-form-item prop="status" label="状态">
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
    </el-dialog> -->
  </div>
</template>

<style lang="scss" scoped>
.search-wrapper {
  margin-bottom: 20px;
  :deep(.el-card__body) {
    padding-bottom: 2px;
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
</style>
