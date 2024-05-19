<script lang="ts" setup>
import { onMounted, reactive, ref, watch, computed } from "vue"
// import { ElMessage } from "element-plus"
import svgIcons from "./svg-icons"
import elementIcons from "./element-icons"
import clipboard from "@/utils/clipboard"
import { ElMessage, ElTooltip, ElPagination, TabsPaneContext } from "element-plus"
import { Search } from "@element-plus/icons-vue"

// const svgIconsRef = ref(svgIcons)
// const elementIconsRef = ref(elementIcons)

const generateIconCode = (symbol: string) => {
  return `<SvgIcon name="${symbol}" />`
}

const generateElementIconCode = (symbol: string) => {
  return `<el-icon><component :is="getIconComponent('${symbol}')" /></el-icon>`
}

const handleClipboard = (text: string, event: Event) => {
  // clipboard(text, event)
  // 创建一个临时的 textarea 元素
  const textarea = document.createElement("textarea")
  // 设置 textarea 的值为 text
  textarea.value = text
  // 将 textarea 添加到 DOM 中
  document.body.appendChild(textarea)
  // 选中 textarea 的文本
  textarea.select()
  // 执行复制命令
  document.execCommand("copy")
  // 移除 textarea 元素
  document.body.removeChild(textarea)
  ElMessage({ message: "已复制", type: "success" })
}
// 根据icon值返回对应的组件名称
const getIconComponent = (icon: any) => {
  return icon
}

// #region 分页
const activeName = ref("svgIcons")

const searchQuery = ref("")
const currentPage = ref(1)
const pageSize = ref(60) // Number of items per page

const filteredSvgIcons = computed(() => {
  if (searchQuery.value) {
    return svgIcons.filter((icon) => icon.toLowerCase().includes(searchQuery.value.toLowerCase()))
  }
  return svgIcons
})

const paginatedSvgIcons = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredSvgIcons.value.slice(start, end)
})

// elementIcons page
const searchQueryel = ref("")

const filteredElIcons = computed(() => {
  if (searchQueryel.value) {
    return elementIcons.filter((icon) => icon.toLowerCase().includes(searchQueryel.value.toLowerCase()))
  }
  return elementIcons
})

const paginatedElIcons = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredElIcons.value.slice(start, end)
})
const handleClick = (tab: TabsPaneContext, event: Event) => {
  currentPage.value = 1
  pageSize.value = 60
  searchQuery.value = ""
  searchQueryel.value = ""
  // if (tab.paneName == "elementIcons") {
  //   currentPage.value = 1
  //   pageSize.value = 30
  //   searchQueryel.value = ""
  // } else {
  //   currentPage.value = 1
  //   pageSize.value = 40
  //   searchQuery.value = ""
  // }
}
// #endregion

onMounted(() => {})
</script>

<template>
  <div class="icons-container">
    <el-card class="search-wrapper">
      svgIcons图标
      <el-link type="success" href="https://juejin.cn/post/7089377403717287972" target="_blank">Add and use </el-link>
      从 <el-link type="primary" href="https://www.iconfont.cn/" target="_blank">iconfont</el-link> 直接下载svg 图标
      放入 @icons/svg 目录下（todo:组件动态获取文件名）如plane 单击图标 复制样式即可使用。菜单里的图标使用svg图标。
      <br />
      Element-UI Icons 需要在页面定义一个函数（getIconComponent全局函数？），复制引用代码
    </el-card>
    <el-card>
      <el-tabs v-model="activeName" @tab-click="handleClick">
        <el-tab-pane label="svgIcons" name="svgIcons">
          <el-input
            v-model="searchQuery"
            clearable
            :prefix-icon="Search"
            placeholder="Search icons..."
            style="width: 240px"
          />
          <div class="grid">
            <div v-for="item of paginatedSvgIcons" :key="item" @click="handleClipboard(generateIconCode(item), $event)">
              <el-tooltip placement="top">
                <template #content>
                  {{ generateIconCode(item) }}
                </template>
                <div class="icon-item">
                  <SvgIcon :name="item" />
                  <span>{{ item }}</span>
                </div>
              </el-tooltip>
            </div>
          </div>
          <el-pagination
            v-model:current-page="currentPage"
            :page-size="pageSize"
            :total="filteredSvgIcons.length"
            layout="prev, pager, next"
          />
        </el-tab-pane>
        <el-tab-pane label="Element-UI Icons" name="elementIcons">
          <el-input
            v-model="searchQueryel"
            clearable
            :prefix-icon="Search"
            placeholder="Search icons..."
            style="width: 240px"
          />
          <div class="grid">
            <div
              v-for="item of paginatedElIcons"
              :key="item"
              @click="handleClipboard(generateElementIconCode(item), $event)"
            >
              <el-tooltip placement="top">
                <template #content>
                  {{ generateElementIconCode(item) }}
                </template>
                <div class="icon-item">
                  <el-icon> <component :is="getIconComponent(item)" /> </el-icon>
                  <span>{{ item }}</span>
                </div>
              </el-tooltip>
            </div>
          </div>
          <el-pagination
            v-model:current-page="currentPage"
            :page-size="pageSize"
            :total="filteredElIcons.length"
            layout="prev, pager, next"
          />
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<style lang="scss" scoped>
.icons-container {
  margin: 10px 20px 0;

  .grid {
    position: relative;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }

  .icon-item {
    margin: 20px;
    text-align: center;
    width: 100px;
    float: left;
    font-size: 30px;
    color: #24292e;
    cursor: pointer;
  }

  span {
    display: block;
    font-size: 16px;
    margin-top: 10px;
  }

  .disabled {
    pointer-events: none;
  }
}

.search-wrapper {
  margin-bottom: 5px;
  :deep(.el-card__body) {
    padding-bottom: 2px;
  }
}
</style>
