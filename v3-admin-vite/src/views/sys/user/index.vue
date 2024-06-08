<script lang="ts" setup>
import { onMounted, reactive, ref, watch } from "vue"
import {
  createUserDataApi,
  deleteUserDataApi,
  getUserDataApi,
  updateUserDataApi,
  getRoleOptionsApi,
  getDeptOptionsApi
} from "@/api/user"
import {
  type CreateOrUpdateUserRequestData,
  type GetUserData,
  type GetRoleOptionsData,
  type GetDeptOptionsData
} from "@/api/user/types/user"
import { type FormInstance, type FormRules, ElMessage, ElMessageBox, ElTree } from "element-plus"
import { Search, Refresh, CirclePlus, Delete, Download, RefreshRight } from "@element-plus/icons-vue"
import { usePagination } from "@/hooks/usePagination"
import { cloneDeep, isNull } from "lodash-es"
import { useUserStore } from "@/store/modules/user"

defineOptions({
  // 命名当前组件
  name: "SysUser"
})

const userStore = useUserStore()
const loading = ref<boolean>(false)
const { paginationData, handleCurrentChange, handleSizeChange } = usePagination()

//#region 增
const DEFAULT_FORM_DATA: CreateOrUpdateUserRequestData = {
  id: undefined,
  username: "",
  password: "",
  email: "",
  role: [],
  dept_id: undefined,
  position_code: "",
  listorder: 1000,
  status: 1
}
const dialogVisible = ref<boolean>(false)
const formRef = ref<FormInstance | null>(null)
const formData = ref<CreateOrUpdateUserRequestData>(cloneDeep(DEFAULT_FORM_DATA))
const formRules: FormRules<CreateOrUpdateUserRequestData> = {
  username: [{ required: true, trigger: "blur", message: "请输入用户名" }],
  password: [{ required: true, trigger: "blur", message: "请输入密码" }],
  role: [{ required: true, trigger: "blur", message: "请选择角色" }]
}
const options = ref([
  { value: "1", label: "启用" },
  { value: "0", label: "禁用" }
])
const positionOptions = ref([
  { value: "GM", label: "总经理" },
  { value: "DGM", label: "副总经理" },
  { value: "DM", label: "部门经理" },
  { value: "STAFF", label: "普通员工" }
])
const formatPosition = (row: any, column: any, cellValue: string, index: any) => {
  const position = positionOptions.value.find((option) => option.value === cellValue)
  return position ? position.label : cellValue
}

const handleCreateOrUpdate = () => {
  formRef.value?.validate((valid: boolean, fields) => {
    if (!valid) return console.error("表单校验不通过", fields)
    loading.value = true
    const api = formData.value.id === undefined ? createUserDataApi : updateUserDataApi
    api(formData.value)
      .then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        dialogVisible.value = false
        getUserData()
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
const handleDelete = (row: GetUserData) => {
  ElMessageBox.confirm(`正在删除用户：${row.username}，确认删除？`, "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      deleteUserDataApi(row.id).then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        getUserData()
      })
    })
    .catch(() => {})
}
//#endregion

//#region 改
const handleUpdate = (row: GetUserData) => {
  dialogVisible.value = true
  formData.value = cloneDeep(row)
}
//#endregion

//#region 查
interface Tree {
  [key: string]: any
}

const deptName = ref("")
const treeRef = ref<InstanceType<typeof ElTree>>()

const defaultProps = {
  children: "children",
  label: "label"
}

watch(deptName, (val) => {
  treeRef.value!.filter(val)
})

const filterNode = (value: string, data: Tree) => {
  if (!value) return true
  return data.label.includes(value)
}

const handleNodeClick = (data: Tree) => {
  searchData.dept_id = data.id
  handleSearch()
}

const userData = ref<GetUserData[]>([])
const searchFormRef = ref<FormInstance | null>(null)
const searchData = reactive({
  username: "",
  tel: "",
  dept_id: undefined,
  status: ""
})
// 监听排序
const sortParm = ref<string>("+listorder")
const handleSort = (column: any) => {
  if (!column.order) {
    sortParm.value = "+listorder"
  } else {
    sortParm.value = (column.order === "ascending" ? "+" : "-") + column.prop
  }
  console.log("sortParm.value", sortParm.value)
  getUserData()
}
const getUserData = () => {
  loading.value = true
  getUserDataApi({
    currentPage: paginationData.currentPage,
    size: paginationData.pageSize,
    username: searchData.username || undefined,
    tel: searchData.tel || undefined,
    dept_id: searchData.dept_id || undefined,
    status: searchData.status || undefined,
    fields: "id,username,email,tel,dept_id,position_code,status,listorder", // 与后端一致 前端指定获取的字段
    query: "~username,~tel,dept_id,status", // 前端指定模糊查询的字段为name,精确查询字段为status
    sort: sortParm.value // 前面指定按listorder升序排列
  })
    .then(({ data }) => {
      paginationData.total = data.total
      userData.value = data.list
    })
    .catch(() => {
      userData.value = []
    })
    .finally(() => {
      loading.value = false
    })
}
const handleSearch = () => {
  paginationData.currentPage === 1 ? getUserData() : (paginationData.currentPage = 1)
}
const resetSearch = () => {
  searchFormRef.value?.resetFields()
  searchData.dept_id = undefined
  treeRef.value!.setCurrentKey(undefined) // 清除选中
  handleSearch()
}
//#endregion

/** 监听分页参数的变化 */
watch([() => paginationData.currentPage, () => paginationData.pageSize], getUserData, { immediate: true })

//#region role选择
const roleOptions = ref<GetRoleOptionsData[]>([])
const getRoleOptionsData = () => {
  getRoleOptionsApi()
    .then(({ data }) => {
      roleOptions.value = data.list
    })
    .catch(() => {
      roleOptions.value = []
    })
    .finally(() => {})
}
//#endregion

//#region dept选择
const deptOptions = ref<GetDeptOptionsData[]>([])
const getDeptOptionsData = () => {
  getDeptOptionsApi()
    .then(({ data }) => {
      deptOptions.value = data.list
      console.log("getDeptOptionsApi", cloneDeep(deptOptions.value))
    })
    .catch(() => {
      deptOptions.value = []
    })
    .finally(() => {})
}
//#endregion
onMounted(() => {
  getRoleOptionsData()
  getDeptOptionsData()
})
</script>

<template>
  <div class="app-container">
    <el-row :gutter="20">
      <!--部门数据-->
      <el-col :span="4" :xs="24">
        <div class="left-container">
          <el-input
            v-model="deptName"
            placeholder="请输入部门名称"
            clearable
            :prefix-icon="Search"
            style="margin-bottom: 10px"
          />
          <el-tree
            ref="treeRef"
            :data="deptOptions"
            :props="defaultProps"
            :expand-on-click-node="false"
            :filter-node-method="filterNode"
            node-key="id"
            default-expand-all
            highlight-current
            @node-click="handleNodeClick"
          />
        </div>
      </el-col>
      <el-col :span="20" :xs="24">
        <el-card shadow="never" class="search-wrapper" v-perm="['/sys/user/get']">
          <el-form ref="searchFormRef" :inline="true" :model="searchData">
            <el-form-item prop="username" label="用户名">
              <el-input v-model="searchData.username" clearable placeholder="请输入用户名称" />
            </el-form-item>
            <el-form-item prop="tel" label="手机号">
              <el-input v-model="searchData.tel" clearable placeholder="请输入手机号" />
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
              <el-button v-perm="['/sys/user/post']" type="primary" :icon="CirclePlus" @click="dialogVisible = true"
                >新增用户</el-button
              >
              <!-- <el-button type="danger" :icon="Delete">批量删除</el-button> -->
            </div>
            <div>
              <!-- <el-tooltip content="下载">
            <el-button type="primary" :icon="Download" circle />
          </el-tooltip> -->
              <el-tooltip content="刷新当前页">
                <el-button type="primary" :icon="RefreshRight" circle @click="getUserData" />
              </el-tooltip>
            </div>
          </div>
          <div class="table-wrapper">
            <el-table :data="userData" @sort-change="handleSort">
              <el-table-column type="selection" width="50" align="center" />
              <el-table-column prop="id" label="ID" sortable="custom" align="center" />
              <el-table-column prop="username" label="用户名" align="center" />
              <el-table-column prop="tel" label="电话" align="center" />
              <el-table-column prop="email" label="邮箱" align="center" />
              <el-table-column prop="dept.name" label="部门" align="center" />
              <el-table-column prop="position_code" label="职务" align="center" :formatter="formatPosition" />
              <el-table-column prop="listorder" label="排序" align="center" />
              <el-table-column prop="status" label="状态" align="center">
                <template #default="scope">
                  <el-tag v-if="scope.row.status" type="success" effect="plain">启用</el-tag>
                  <el-tag v-else type="danger" effect="plain">禁用</el-tag>
                </template>
              </el-table-column>
              <el-table-column fixed="right" label="操作" width="150" align="center">
                <template #default="scope">
                  <el-button
                    v-perm="['/sys/user/put']"
                    type="primary"
                    text
                    bg
                    size="small"
                    @click="handleUpdate(scope.row)"
                    >修改</el-button
                  >
                  <el-button
                    v-perm="['/sys/user/delete']"
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
      </el-col>
    </el-row>
    <!-- 新增/修改 -->
    <el-dialog
      v-model="dialogVisible"
      :title="formData.id === undefined ? '新增用户' : '修改用户'"
      @closed="resetForm"
      width="30%"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px" label-position="right">
        <el-form-item prop="username" label="用户名">
          <el-input v-model.trim="formData.username" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="password" label="密码" v-if="formData.id === undefined">
          <el-input v-model="formData.password" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="email" label="邮箱">
          <el-input v-model="formData.email" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="tel" label="手机号">
          <el-input v-model="formData.tel" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="role" label="角色">
          <el-select v-model="formData.role" multiple placeholder="请选择" style="width: 240px">
            <el-option v-for="item in roleOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item prop="dept_id" label="部门">
          <el-tree-select
            v-model="formData.dept_id"
            :data="deptOptions"
            :render-after-expand="false"
            check-strictly
            style="width: 240px"
          />
        </el-form-item>
        <el-form-item prop="positon_code" label="职务">
          <el-select v-model="formData.position_code" placeholder="请选择" style="width: 240px">
            <el-option v-for="item in positionOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
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
: any: any: string: any
