<script lang="ts" setup>
import { onMounted, reactive, ref, watch } from "vue"

// import { ElMessage } from "element-plus"
import svgIcons from "./svg-icons"
import elementIcons from "./element-icons"
import clipboard from "@/utils/clipboard"
import { ElMessage } from "element-plus"

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
onMounted(() => {})
</script>

<template>
  <div class="icons-container">
    <el-card class="search-wrapper">
      svgIcons图标
      <el-link type="success" href="https://juejin.cn/post/7089377403717287972" target="_blank">Add and use </el-link>
      从 <el-link type="primary" href="https://www.iconfont.cn/" target="_blank">iconfont</el-link> 直接下载svg 图标
      放入 @icons/svg 目录下, 如plane 单击图标 复制样式即可使用。
      <br />
      Element-UI Icons 需要在页面定义一个函数（全局函数？），复制引用代码
    </el-card>
    <el-card>
      <el-tabs type="border-card">
        <el-tab-pane label="Icons">
          <div class="grid">
            <div v-for="item of svgIcons" :key="item" @click="handleClipboard(generateIconCode(item), $event)">
              <!-- <div v-for="item of svgIcons" :key="item"> -->
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
        </el-tab-pane>
        <el-tab-pane label="Element-UI Icons">
          <div class="grid">
            <!-- <div v-for="item of elementIcons" :key="item" @click="handleClipboard(generateElementIconCode(item), $event)"> -->
            <div v-for="item of elementIcons" :key="item">
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
  margin-bottom: 20px;
  :deep(.el-card__body) {
    padding-bottom: 2px;
  }
}
</style>
