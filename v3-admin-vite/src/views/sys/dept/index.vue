<script lang="ts" setup>
import { reactive, ref, watch } from "vue"
import { createDeptDataApi, deleteDeptDataApi, getDeptDataApi, updateDeptDataApi } from "@/api/dept"
import { type CreateOrUpdateDeptRequestData, type GetDeptData, type GetDeptDataOptions } from "@/api/dept/types/dept"
import { type FormInstance, type FormRules, ElMessage, ElMessageBox } from "element-plus"
import { Search, Refresh, CirclePlus, Delete, Download, RefreshRight } from "@element-plus/icons-vue"
// cloneDeep 将ref变量转换成human的格式
import { cloneDeep } from "lodash-es"

defineOptions({
  // 命名当前组件
  name: "SysDept"
})

const loading = ref<boolean>(false)

//#region 增
const DEFAULT_FORM_DATA: CreateOrUpdateDeptRequestData = {
  id: undefined,
  pid: "",
  name: "",
  aliasname: "",
  listorder: 99,
  status: 1
}
const dialogVisible = ref<boolean>(false)
const formRef = ref<FormInstance | null>(null)
const formData = ref<CreateOrUpdateDeptRequestData>(cloneDeep(DEFAULT_FORM_DATA))
const formRules: FormRules<CreateOrUpdateDeptRequestData> = {
  name: [{ required: true, trigger: "blur", message: "请输入部门名" }],
  pid: [{ required: true, trigger: "blur", message: "请选择上级机构" }]
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
    const api = formData.value.id === undefined ? createDeptDataApi : updateDeptDataApi
    api(formData.value)
      .then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        dialogVisible.value = false
        getDeptData()
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
const handleDelete = (row: GetDeptData) => {
  ElMessageBox.confirm(`正在删除部门：${row.name}，确认删除？`, "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      deleteDeptDataApi(row.id).then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        getDeptData()
      })
    })
    .catch((err) => {
      console.log(err)
    })
}
//#endregion

//#region 改
const handleUpdate = (row: CreateOrUpdateDeptRequestData) => {
  dialogVisible.value = true
  // 克隆对象
  const clonedRow = cloneDeep(row)
  // 删除不需要的属性
  if ("children" in clonedRow) {
    delete clonedRow.children
  }
  // 设置formData的值
  formData.value = clonedRow
}
//#endregion

//#region 查
const deptData = ref<GetDeptData[]>([])
const deptDataOptions = ref<GetDeptDataOptions[]>([])
const searchFormRef = ref<FormInstance | null>(null)
const searchData = reactive({
  name: "",
  status: ""
})
const convertDeptData = (deptData: GetDeptData[]): any[] => {
  // 转换剩余的部门项 递归函数
  return deptData.map(({ id, name, children }) => ({
    value: id,
    label: name,
    children: children ? convertDeptData(children) : undefined
  }))
}
const topLevelDept = (convertedData: GetDeptData[]): any[] => {
  // console.log("topLevelDept", [
  //   {
  //     value: "0",
  //     label: "顶级部门",
  //     children: convertedData
  //   }
  // ])
  return [
    {
      value: 0,
      label: "顶级部门",
      children: convertedData
    }
  ]
}
const getDeptData = () => {
  loading.value = true
  getDeptDataApi({
    name: searchData.name || undefined,
    status: searchData.status || undefined
  })
    .then(({ data }) => {
      deptData.value = data.list
      deptDataOptions.value = topLevelDept(convertDeptData(cloneDeep(deptData.value)))
    })
    .catch(() => {
      deptData.value = []
    })
    .finally(() => {
      loading.value = false
    })
}
const handleSearch = () => {
  getDeptData()
}
const resetSearch = () => {
  searchFormRef.value?.resetFields()
  handleSearch()
}
//#endregion

// 在组件实例创建时立即获取数据
getDeptData()
</script>

<template>
  <div class="app-container">
    <el-card shadow="never" class="search-wrapper">
      <el-form ref="searchFormRef" :inline="true" :model="searchData">
        <el-form-item prop="name" label="部门名">
          <el-input v-model="searchData.name" clearable placeholder="请输入部门名称" />
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
          <el-button v-perm="['/sys/dept/post']" type="primary" :icon="CirclePlus" @click="dialogVisible = true"
            >新增部门</el-button
          >
        </div>
        <div>
          <el-tooltip content="刷新当前页">
            <el-button type="primary" :icon="RefreshRight" circle @click="getDeptData" />
          </el-tooltip>
        </div>
      </div>
      <div class="table-wrapper">
        <el-table :data="deptData" row-key="id">
          <el-table-column prop="name" label="部门名称" align="left" />
          <el-table-column prop="id" label="ID" align="center" />
          <el-table-column prop="aliasname" label="别名" align="center" />
          <el-table-column prop="listorder" label="排序" align="center" />
          <el-table-column prop="status" label="状态" align="status">
            <template #default="scope">
              <el-tag v-if="scope.row.status" type="success" effect="plain">启用</el-tag>
              <el-tag v-else type="danger" effect="plain">禁用</el-tag>
            </template>
          </el-table-column>
          <el-table-column fixed="right" label="操作" width="150" align="center">
            <template #default="scope">
              <el-button type="primary" text bg size="small" @click="handleUpdate(scope.row)">修改</el-button>
              <el-button type="danger" text bg size="small" @click="handleDelete(scope.row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </el-card>
    <!-- 新增/修改 -->
    <el-dialog
      v-model="dialogVisible"
      :title="formData.id === undefined ? '新增部门' : '修改部门'"
      @closed="resetForm"
      width="30%"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px" label-position="right">
        <el-form-item prop="name" label="部门名称">
          <el-input v-model.trim="formData.name" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="aliasname" label="部门别名">
          <el-input v-model.trim="formData.aliasname" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="pid" label="上级部门">
          <el-tree-select
            v-model="formData.pid"
            :data="deptDataOptions"
            check-strictly
            clearable
            accordion
            :render-after-expand="false"
            style="width: 240px"
          />
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
