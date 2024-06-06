<script lang="ts" setup>
import { onMounted, reactive, ref, watch } from "vue"
import { createLeaveDataApi, deleteLeaveDataApi, getLeaveDataApi, updateLeaveDataApi } from "@/api/flow"
import { type CreateOrUpdateLeaveRequestData, type GetLeaveData } from "@/api/flow/types/flow"
import { type FormInstance, type FormRules, ElMessage, ElMessageBox, ElTree } from "element-plus"
import { Search, Refresh, CirclePlus, Delete, Download, RefreshRight } from "@element-plus/icons-vue"
import { usePagination } from "@/hooks/usePagination"
import { cloneDeep, isNull } from "lodash-es"
import { useUserStore } from "@/store/modules/user"

defineOptions({
  // 命名当前组件
  name: "FLowLeave"
})

const userStore = useUserStore()
const loading = ref<boolean>(false)
const { paginationData, handleCurrentChange, handleSizeChange } = usePagination()

//#region 增
// 获取当天的日期
const getToday = () => {
  const today = new Date()
  return today.toISOString().split("T")[0]
}
const DEFAULT_FORM_DATA: CreateOrUpdateLeaveRequestData = {
  form_id: undefined,
  form_type: "",
  start_time: getToday(),
  end_time: "",
  reason: ""
}

const dialogVisible = ref<boolean>(false)
const formRef = ref<FormInstance | null>(null)
const formData = ref<CreateOrUpdateLeaveRequestData>(cloneDeep(DEFAULT_FORM_DATA))
const formRules: FormRules<CreateOrUpdateLeaveRequestData> = {
  form_type: [{ required: true, trigger: "blur", message: "请选择类型" }],
  reason: [{ required: true, trigger: "blur", message: "请输入理由" }],
  start_time: [{ required: true, trigger: "blur", message: "请选择开始时间" }],
  end_time: [
    { required: true, trigger: "blur", message: "请选择结束时间" },
    {
      validator: (rule, value, callback) => {
        if (value <= formData.value.start_time) {
          callback(new Error("结束时间必须大于开始时间"))
        } else {
          callback()
        }
      },
      trigger: "blur"
    }
  ]
}

const options = ref([
  { value: "1", label: "年假" },
  { value: "2", label: "病假" },
  { value: "3", label: "婚假" },
  { value: "4", label: "产假" },
  { value: "5", label: "事假" }
])

const handleCreateOrUpdate = () => {
  formRef.value?.validate((valid: boolean, fields) => {
    if (!valid) return console.error("表单校验不通过", fields)
    loading.value = true
    const api = formData.value.form_id === undefined ? createLeaveDataApi : updateLeaveDataApi
    api(formData.value)
      .then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        dialogVisible.value = false
        getLeaveData()
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
const handleDelete = (row: GetLeaveData) => {
  ElMessageBox.confirm(`正在删除用户：${row.username}，确认删除？`, "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      deleteLeaveDataApi(row.id).then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        getLeaveData()
      })
    })
    .catch(() => {})
}
//#endregion

//#region 改
const handleUpdate = (row: GetLeaveData) => {
  dialogVisible.value = true
  formData.value = cloneDeep(row)
}
//#endregion

//#region 查
const userData = ref<GetLeaveData[]>([])
const searchFormRef = ref<FormInstance | null>(null)
const searchData = reactive({
  form_type: "",
  create_time: ""
})

const getLeaveData = () => {
  loading.value = true
  getLeaveDataApi({
    currentPage: paginationData.currentPage,
    size: paginationData.pageSize,
    form_type: searchData.form_type || undefined,
    create_time: searchData.create_time || undefined,
    fields: "form_id,employee_id,form_type,start_time,end_time,reason,create_time,state", // 与后端一致 前端指定获取的字段
    query: "form_type,create_time", // 前端指定模糊查询的字段为name,精确查询字段为status
    sort: "+create_time" // 前面指定按listorder升序排列
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
  paginationData.currentPage === 1 ? getLeaveData() : (paginationData.currentPage = 1)
}
const resetSearch = () => {
  searchFormRef.value?.resetFields()
  handleSearch()
}
//#endregion

/** 监听分页参数的变化 */
watch([() => paginationData.currentPage, () => paginationData.pageSize], getLeaveData, { immediate: true })

onMounted(() => {})
</script>

<template>
  <div class="app-container">
    <el-card shadow="never" class="search-wrapper" v-perm="['/sys/user/get']">
      <el-form ref="searchFormRef" :inline="true" :model="searchData">
        <el-form-item prop="form_type" label="类型">
          <el-select v-model="searchData.form_type" clearable placeholder="请选择">
            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item prop="create_time" label="时间">
          <el-input v-model="searchData.create_time" clearable placeholder="请选择创建时间" />
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
          <el-button v-perm="['/flow/leave/post']" type="primary" :icon="CirclePlus" @click="dialogVisible = true"
            >新增申请</el-button
          >
        </div>
        <div>
          <el-tooltip content="刷新当前页">
            <el-button type="primary" :icon="RefreshRight" circle @click="getLeaveData" />
          </el-tooltip>
        </div>
      </div>
      <div class="table-wrapper">
        <el-table :data="userData">
          <el-table-column prop="form_id" label="ID" align="center" />
          <el-table-column prop="employee_id" label="用户名" align="center" />
          <el-table-column prop="form_type" label="类型" align="center" />
          <el-table-column prop="start_time" label="开始时间" align="center" />
          <el-table-column prop="end_time" label="结束时间" align="center" />
          <el-table-column prop="reason" label="原因" align="center" />
          <el-table-column prop="create_time" label="申请时间" align="center" />
          <el-table-column prop="state" label="状态" align="center" />
          <el-table-column fixed="right" label="操作" width="150" align="center">
            <template #default="scope">
              <el-button
                v-perm="['/flow/leave/put']"
                type="primary"
                text
                bg
                size="small"
                @click="handleUpdate(scope.row)"
                >修改</el-button
              >
              <el-button
                v-perm="['/flow/leave/delete']"
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
    <!-- 新增/修改 -->
    <el-dialog
      v-model="dialogVisible"
      :title="formData.form_id === undefined ? '新增申请' : '修改申请'"
      @closed="resetForm"
      :close-on-click-modal="false"
      width="30%"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px" label-position="right">
        <el-form-item prop="form_type" label="类型">
          <el-select v-model="formData.form_type" placeholder="请选择类型" style="width: 240px">
            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
          {{ formData.form_type }}
        </el-form-item>
        <el-form-item prop="reason" label="原因">
          <el-input v-model="formData.reason" placeholder="请输入" style="width: 240px" autosize type="textarea" />
          {{ formData.reason }}
        </el-form-item>
        <el-form-item prop="start_time" label="开始时间">
          <el-date-picker
            v-model="formData.start_time"
            type="date"
            placeholder="选择开始日期"
            style="width: 240px"
            value-format="YYYY-MM-DD"
          />
        </el-form-item>
        <el-form-item prop="end_time" label="结束时间">
          <el-date-picker
            v-model="formData.end_time"
            type="date"
            placeholder="选择结束日期"
            style="width: 240px"
            value-format="YYYY-MM-DD"
          />
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