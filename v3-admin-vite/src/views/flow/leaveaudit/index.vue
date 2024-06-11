<script lang="ts" setup>
import { onMounted, reactive, ref, watch } from "vue"
import { getLeaveAuditDataApi, updateLeaveAuditDataApi } from "@/api/flow"
import { type CreateOrUpdateLeaveAuditRequestData, type GetLeaveAuditData } from "@/api/flow/types/flow"
import { type FormInstance, type FormRules, ElMessage, ElMessageBox, ElTree } from "element-plus"
import { Search, Refresh, CirclePlus, Delete, Download, RefreshRight } from "@element-plus/icons-vue"
import { usePagination } from "@/hooks/usePagination"
import { cloneDeep, isNull } from "lodash-es"
import { useUserStore } from "@/store/modules/user"

defineOptions({
  // 命名当前组件
  name: "FLowLeaveAudit"
})

const userStore = useUserStore()
const loading = ref<boolean>(false)
const { paginationData, handleCurrentChange, handleSizeChange } = usePagination()

//#region 增
const DEFAULT_FORM_DATA: CreateOrUpdateLeaveAuditRequestData = {
  process_id: undefined,
  result: "",
  reason: ""
}

const dialogVisible = ref<boolean>(false)
const formRef = ref<FormInstance | null>(null)
const formData = ref<CreateOrUpdateLeaveAuditRequestData>(cloneDeep(DEFAULT_FORM_DATA))
const formRules: FormRules<CreateOrUpdateLeaveAuditRequestData> = {
  result: [{ required: true, trigger: "blur", message: "请选择审批结果" }]
  // reason: [{ required: true, trigger: "blur", message: "请输入审批意见" }]
}

const typeOptions = ref([
  { value: "1", label: "年假" },
  { value: "2", label: "病假" },
  { value: "3", label: "婚假" },
  { value: "4", label: "产假" },
  { value: "5", label: "事假" }
])
const formatType = (row: any, column: any, cellValue: string, index: any) => {
  const type = typeOptions.value.find((option) => option.value === cellValue.toString())
  return type ? type.label : cellValue
}

type TagType = "warning" | "success" | "info" | "primary" | "danger"

interface StateOption {
  value: string
  label: string
  tagType: TagType
}
const stateOptions = ref<StateOption[]>([
  { value: "process", label: "处理中", tagType: "warning" },
  { value: "complete", label: "已处理", tagType: "success" }
])
const getStateProperty = (
  value: string,
  options: StateOption[],
  property: "label" | "tagType"
): string | TagType | undefined => {
  const option = options.find((option) => option.value === value)
  return option ? option[property] : undefined
}

const resultOptions = ref<StateOption[]>([
  { value: "approved", label: "同意", tagType: "success" },
  { value: "refused", label: "驳回", tagType: "danger" }
])

const handleCreateOrUpdate = () => {
  formRef.value?.validate((valid: boolean, fields) => {
    if (!valid) return console.error("表单校验不通过", fields)
    loading.value = true
    const api = formData.value.process_id === undefined ? createLeaveAuditDataApi : updateLeaveAuditDataApi
    // 提取并保留 result, reason 和 process_id 三项
    const newFormData = {
      result: formData.value.result,
      reason: formData.value.reason,
      process_id: formData.value.process_id,
      form_id: formData.value.form_id
    }
    api(newFormData)
      .then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        dialogVisible.value = false
        getLeaveAuditData()
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

//#region 改
const handleUpdate = (row: GetLeaveAuditData) => {
  dialogVisible.value = true
  formData.value = cloneDeep(row)
}
//#endregion

//#region 查
const leaveAuditData = ref<GetLeaveAuditData[]>([])
const searchFormRef = ref<FormInstance | null>(null)
const searchData = reactive({
  form_type: "",
  create_time: "",
  state: ""
})

const getLeaveAuditData = () => {
  loading.value = true
  getLeaveAuditDataApi({
    currentPage: paginationData.currentPage,
    size: paginationData.pageSize,
    state: searchData.state || undefined,
    create_time: searchData.create_time || undefined,
    fields: "process_id,order_no,form_id,operator_id,result,reason,state,audit_time,create_time", // 与后端一致 前端指定获取的字段
    query: "form_type,create_time,state", // 前端指定模糊查询的字段为name,精确查询字段为status
    sort: "-create_time"
  })
    .then(({ data }) => {
      paginationData.total = data.total
      leaveAuditData.value = data.list
    })
    .catch(() => {
      leaveAuditData.value = []
    })
    .finally(() => {
      loading.value = false
    })
}
const handleSearch = () => {
  paginationData.currentPage === 1 ? getLeaveAuditData() : (paginationData.currentPage = 1)
}
const resetSearch = () => {
  searchFormRef.value?.resetFields()
  handleSearch()
}
//#endregion

/** 监听分页参数的变化 */
watch([() => paginationData.currentPage, () => paginationData.pageSize], getLeaveAuditData, { immediate: true })

onMounted(() => {})
</script>

<template>
  <div class="app-container">
    <el-card shadow="never" class="search-wrapper" v-perm="['/flow/leave/get']">
      <el-form ref="searchFormRef" :inline="true" :model="searchData">
        <el-form-item prop="state" label="审批状态">
          <el-select v-model="searchData.state" clearable placeholder="请选择">
            <el-option v-for="item in stateOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <!-- <el-form-item prop="form_type" label="类型">
          <el-select v-model="searchData.form_type" clearable placeholder="请选择">
            <el-option v-for="item in typeOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item prop="create_time" label="日期">
          <el-input v-model="searchData.create_time" clearable placeholder="请选择申请日期" />
        </el-form-item> -->
        <el-form-item>
          <el-button type="primary" :icon="Search" @click="handleSearch">查询</el-button>
          <el-button :icon="Refresh" @click="resetSearch">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card v-loading="loading" shadow="never">
      <div class="toolbar-wrapper">
        <div>
          <el-tooltip content="刷新当前页">
            <el-button type="primary" :icon="RefreshRight" circle @click="getLeaveAuditData" />
          </el-tooltip>
        </div>
      </div>
      <div class="table-wrapper">
        <el-table :data="leaveAuditData">
          <el-table-column prop="process_id" label="process_id" align="center" />
          <el-table-column prop="form_id" label="form_id" align="center" />
          <el-table-column prop="order_no" label="步骤" align="center" />
          <el-table-column prop="employee_name" label="申请人" align="center" />
          <el-table-column prop="form_type" label="类型" align="center" :formatter="formatType" />
          <el-table-column prop="start_time" label="开始日期" align="center" />
          <el-table-column prop="end_time" label="结束日期" align="center" />
          <el-table-column prop="apply_reason" label="申请原因" align="center" />
          <el-table-column prop="apply_time" label="申请时间" align="center" />
          <el-table-column prop="operator_name" label="处理人" align="center" />
          <el-table-column prop="result" label="审批结果" align="center">
            <template #default="{ row }">
              <el-tag v-if="row.result" :type="getStateProperty(row.result, resultOptions, 'tagType') as TagType">{{
                getStateProperty(row.result, resultOptions, "label")
              }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="reason" label="审批意见" align="center" />
          <el-table-column prop="audit_time" label="审批时间" align="center" />
          <el-table-column prop="state" label="状态" align="center">
            <template #default="{ row }">
              <el-tag :type="getStateProperty(row.state, stateOptions, 'tagType') as TagType">{{
                getStateProperty(row.state, stateOptions, "label")
              }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column fixed="right" label="操作" width="150" align="center">
            <template #default="scope">
              <el-button
                v-if="scope.row.state == 'process'"
                v-perm="['/flow/leaveaudit/put']"
                type="primary"
                text
                bg
                size="small"
                @click="handleUpdate(scope.row)"
                >审批</el-button
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
      :title="formData.process_id === undefined ? '新增' : '审批'"
      @closed="resetForm"
      :close-on-click-modal="false"
      width="30%"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px" label-position="right">
        <el-form-item prop="employee_name" label="申请人">
          <el-input v-model="formData.employee_name" placeholder="请输入" style="width: 240px" readonly />
        </el-form-item>
        <el-form-item prop="form_type" label="类型">
          <el-select v-model="formData.form_type" placeholder="请选择类型" style="width: 240px" disabled>
            <el-option v-for="item in typeOptions" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item prop="apply_reason" label="申请原因">
          <el-input
            v-model="formData.apply_reason"
            placeholder="请输入"
            style="width: 240px"
            autosize
            type="textarea"
            readonly
          />
        </el-form-item>
        <el-form-item prop="start_time" label="开始时间">
          <el-date-picker
            v-model="formData.start_time"
            type="date"
            placeholder="选择开始日期"
            style="width: 240px"
            value-format="YYYY-MM-DD"
            disabled
          />
        </el-form-item>
        <el-form-item prop="end_time" label="结束时间">
          <el-date-picker
            v-model="formData.end_time"
            type="date"
            placeholder="选择结束日期"
            style="width: 240px"
            value-format="YYYY-MM-DD"
            disabled
          />
        </el-form-item>
        <el-form-item prop="result" label="审批结果">
          <el-radio-group v-model="formData.result">
            <el-radio-button v-for="(item, index) in resultOptions" :key="index" :value="item.value">{{
              item.label
            }}</el-radio-button>
          </el-radio-group>
        </el-form-item>
        <el-form-item prop="reason" label="审批意见">
          <el-input v-model="formData.reason" placeholder="请输入" style="width: 240px" autosize type="textarea" />
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
