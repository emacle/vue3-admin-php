<script lang="ts" setup>
import { reactive, ref, watch } from "vue"
import { createMenuDataApi, deleteMenuDataApi, getMenuDataApi, updateMenuDataApi } from "@/api/menu"
import { type CreateOrUpdateMenuRequestData, type GetMenuData, type GetMenuDataOptions } from "@/api/menu/types/menu"
import { type FormInstance, type FormRules, ElMessage, ElMessageBox } from "element-plus"
import { Search, Refresh, CirclePlus, Delete, Download, RefreshRight } from "@element-plus/icons-vue"
import { usePagination } from "@/hooks/usePagination"
// cloneDeep 将ref变量转换成human的格式
import { cloneDeep } from "lodash-es"

defineOptions({
  // 命名当前组件
  name: "SysMenu"
})

const loading = ref<boolean>(false)
const { paginationData, handleCurrentChange, handleSizeChange } = usePagination()

//#region 增
const DEFAULT_FORM_DATA: CreateOrUpdateMenuRequestData = {
  id: undefined,
  pid: "",
  name: "",
  path: "",
  component: "",
  type: 0,
  title: "",
  icon: "",
  redirect: "",
  hidden: 0,
  status: 1,
  condition: "",
  listorder: 99
}
const dialogVisible = ref<boolean>(false)
const formRef = ref<FormInstance | null>(null)
const formData = ref<CreateOrUpdateMenuRequestData>(cloneDeep(DEFAULT_FORM_DATA))
const formRules: FormRules<CreateOrUpdateMenuRequestData> = {
  title: [{ required: true, trigger: "blur", message: "请输入目录/菜单/操作名称" }],
  pid: [{ required: true, trigger: "blur", message: "请选择菜单目录" }],
  path: [{ required: true, trigger: "blur", message: "请输入路由或操作路径" }],
  name: [{ required: true, trigger: "blur", message: "请输入路由组件名" }],
  component: [{ required: true, trigger: "blur", message: "请输入组件路径" }]
}
const handleCreateOrUpdate = () => {
  formRef.value?.validate((valid: boolean, fields) => {
    if (!valid) return console.error("表单校验不通过", fields)
    loading.value = true
    const api = formData.value.id === undefined ? createMenuDataApi : updateMenuDataApi
    api(formData.value)
      .then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        dialogVisible.value = false
        getMenuData()
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
const handleDelete = (row: GetMenuData) => {
  ElMessageBox.confirm(`正在删除菜单：${row.title}，确认删除？`, "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      deleteMenuDataApi(row.id).then((res: any) => {
        ElMessage({ message: res.message, type: res.type })
        getMenuData()
      })
    })
    .catch((err) => {
      console.log(err)
    })
}
//#endregion

//#region 改
const handleUpdate = (row: GetMenuData) => {
  dialogVisible.value = true
  formData.value = cloneDeep(row)
}
//#endregion

//#region 查
const menuData = ref<GetMenuData[]>([])
const menuDataOptions = ref<GetMenuDataOptions[]>([])
// const searchFormRef = ref<FormInstance | null>(null)
const searchData = reactive({
  title: ""
})
const menuTypeList = reactive(["目录", "菜单", "操作"])

const convertMenuData = (menuData: GetMenuData[]): any[] => {
  // 过滤掉 type 为 2 的项
  const filteredMenuData = menuData.filter((item) => item.type !== 2)

  // 转换剩余的菜单项 递归函数
  return filteredMenuData.map(({ id, title, children }) => ({
    value: id,
    label: title,
    children: children ? convertMenuData(children) : undefined
  }))
}
const topLevelMenu = (convertedData: GetMenuData[]): any[] => {
  console.log("topLevelMenu", [
    {
      value: "0",
      label: "顶级菜单",
      children: convertedData
    }
  ])
  return [
    {
      value: 0,
      label: "顶级菜单",
      children: convertedData
    }
  ]
}
const getMenuData = () => {
  loading.value = true
  getMenuDataApi({
    title: searchData.title || undefined
  })
    .then(({ data }) => {
      menuData.value = data.list
      menuDataOptions.value = topLevelMenu(convertMenuData(cloneDeep(menuData.value)))
    })
    .catch(() => {
      menuData.value = []
    })
    .finally(() => {
      loading.value = false
    })
}
// const handleSearch = () => {
//   getMenuData()
// }
// const resetSearch = () => {
//   searchFormRef.value?.resetFields()
//   handleSearch()
// }
//#endregion
// 根据icon值返回对应的组件名称
const getIconComponent = (icon: any) => {
  return icon
}

// 在组件实例创建时立即获取数据
getMenuData()
</script>

<template>
  <div class="app-container">
    <!-- <el-card shadow="never" class="search-wrapper">
      <el-form ref="searchFormRef" :inline="true" :model="searchData">
        <el-form-item prop="title" label="菜单名">
          <el-input v-model="searchData.title" clearable placeholder="请输入菜单名称" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :icon="Search" @click="handleSearch">查询</el-button>
          <el-button :icon="Refresh" @click="resetSearch">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card> -->
    <el-card v-loading="loading" shadow="never">
      <div class="toolbar-wrapper">
        <div>
          <el-button v-perm="['/sys/menu/post']" type="primary" :icon="CirclePlus" @click="dialogVisible = true"
            >新增菜单</el-button
          >
        </div>
        <div>
          <el-tooltip content="刷新当前页">
            <el-button type="primary" :icon="RefreshRight" circle @click="getMenuData" />
          </el-tooltip>
        </div>
      </div>
      <div class="table-wrapper">
        <el-table :data="menuData" row-key="title" border>
          <el-table-column prop="title" label="菜单名称" align="center" />
          <el-table-column prop="name" label="路由别名" align="center" />
          <el-table-column prop="path" label="路由" align="center">
            <template #default="scope">
              <span v-if="scope.row.type !== 2">{{ scope.row.path }}</span>
              <span v-else-if="scope.row.type === 2 && scope.row.path.match(/\/post$/g)">{{
                scope.row.path.replace(/\/post$/, "")
              }}</span>
              <span v-else-if="scope.row.type === 2 && scope.row.path.match(/\/get$/g)">{{
                scope.row.path.replace(/\/get$/, "")
              }}</span>
              <span v-else-if="scope.row.type === 2 && scope.row.path.match(/\/put$/g)">{{
                scope.row.path.replace(/\/put$/, "")
              }}</span>
              <span v-else-if="scope.row.type === 2 && scope.row.path.match(/\/delete$/g)">{{
                scope.row.path.replace(/\/delete$/, "")
              }}</span>
              <span v-else-if="scope.row.type === 2">{{ scope.row.path }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="icon" label="图标" align="center">
            <template #default="scope">
              <el-icon v-if="scope.row.type !== 2" color="#409efc" :size="20" class="no-inherit">
                <component :is="getIconComponent(scope.row.icon)" />
              </el-icon>
              <span v-else-if="scope.row.type === 2 && scope.row.path.match(/\/post$/g)">
                <el-tag size="small" type="success" effect="dark">POST</el-tag>
              </span>
              <span v-else-if="scope.row.type === 2 && scope.row.path.match(/\/get$/g)">
                <el-tag size="small" type="primary" effect="dark">GET</el-tag>
              </span>
              <span v-else-if="scope.row.type === 2 && scope.row.path.match(/\/put$/g)">
                <el-tag size="small" type="warning" effect="dark">PUT</el-tag>
              </span>
              <span v-else-if="scope.row.type === 2 && scope.row.path.match(/\/delete$/g)">
                <el-tag size="small" type="danger" effect="dark">DEL</el-tag>
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="type" label="类型" align="center">
            <template #default="scope">
              <!-- :type="!scope.row.type ? '' : scope.row.type === 1 ? 'success' : 'warning'" -->
              <el-tag :type="!scope.row.type ? 'primary' : scope.row.type === 1 ? 'success' : 'warning'" size="small">{{
                menuTypeList[scope.row.type]
              }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="component" label="组件" align="center" />
          <el-table-column prop="redirect" label="重定向" align="center" />
          <el-table-column prop="listorder" label="排序" align="center" />
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
      :title="formData.id === undefined ? '新增菜单' : '修改菜单'"
      @closed="resetForm"
      width="30%"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px" label-position="right">
        {{ formData.id }}
        <el-form-item prop="type" label="菜单类型">
          <el-radio-group v-model="formData.type">
            <el-radio-button v-for="(type, index) in menuTypeList" :key="index" :value="index">{{
              type
            }}</el-radio-button>
          </el-radio-group>
          {{ formData.type }}
        </el-form-item>
        <el-form-item prop="title" :label="menuTypeList[formData.type] + '名称'">
          <el-input v-model.trim="formData.title" placeholder="请输入" />
        </el-form-item>
        <el-form-item prop="pid" label="上级菜单">
          <el-tree-select
            v-model="formData.pid"
            :data="menuDataOptions"
            check-strictly
            clearable
            accordion
            :render-after-expand="false"
            style="width: 240px"
          />
          {{ formData.pid }}
        </el-form-item>

        <el-form-item prop="path" :label="formData.type !== 2 ? '路由' : '操作'">
          <el-tooltip class="box-item" effect="dark" content="" placement="right-end">
            <template #content>
              目录或菜单：/sys, /sys/role <br />操作：/sys/user/post, <br />以小写 get,post,put,delete 结尾
            </template>
            <el-input
              v-model.trim="formData.path"
              :placeholder="menuTypeList[formData.type] + ', 如 /sys, /sys/menu/menus/get'"
            />
          </el-tooltip>
        </el-form-item>
        <el-form-item prop="name" label="路由别名" v-if="formData.type !== 2">
          <el-input v-model="formData.name" placeholder="@view component name 必须与该别名一致" />
        </el-form-item>
        <el-form-item prop="component" label="组件" v-if="formData.type == 1">
          <el-input v-model="formData.component" placeholder="对应 @/views 目录, 例 sys/menu/index" />
        </el-form-item>
        <el-form-item prop="redirect" label="重定向URL" v-if="formData.type !== 2">
          <el-input v-model="formData.redirect" placeholder="面包屑组件重定向,例 /sys/menu, 可留空" />
        </el-form-item>
        <el-form-item prop="icon" label="图标" v-if="formData.type !== 2">
          <el-input v-model="formData.icon" placeholder="请输入">
            <template #suffix>
              <el-icon class="el-input__icon" v-if="formData.icon"
                ><component :is="getIconComponent(formData.icon)"
              /></el-icon>
            </template>
          </el-input>
        </el-form-item>
        <el-form-item prop="listorder" label="排序">
          <el-input-number v-model="formData.listorder" :min="99" controls-position="right" size="large" />
        </el-form-item>
        <!-- <el-form-item prop="status" label="状态">
          <el-switch v-model="formData.status" :active-value="1" :inactive-value="0">
            <template #active>启用</template>
            <template #inactive>禁用</template>
          </el-switch>
        </el-form-item> -->
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
