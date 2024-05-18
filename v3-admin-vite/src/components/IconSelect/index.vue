<script setup lang="ts">
import { ref, watchEffect, defineEmits } from "vue"
import { Search } from "@element-plus/icons-vue"
import icons from "./requireIcons"

defineOptions({
  name: "IconSelect"
})

// 定义事件
const emit = defineEmits(["selected"])

// 数据和状态
const name = ref("")
const iconList = ref([...icons])

// 过滤图标的方法
const filterIcons = () => {
  if (name.value) {
    iconList.value = icons.filter((item: string) => item.includes(name.value))
  } else {
    iconList.value = [...icons]
  }
}

// 选择图标的方法
const selectedIcon = (iconName: string) => {
  // 触发父组件的 selected 事件
  emit("selected", iconName)
  // 模拟鼠标单击页面其他部分，用来关闭 el-popup
  document.body.click()
}

// 监控输入框的值，自动过滤图标
watchEffect(() => {
  filterIcons()
})

// 重置方法
const reset = () => {
  name.value = ""
  iconList.value = [...icons]
}
</script>

<template>
  <div class="icon-body">
    <el-input
      v-model="name"
      style="position: relative"
      clearable
      :suffix-icon="Search"
      placeholder="请输入图标名称"
      @clear="filterIcons"
      @input="filterIcons"
    />
    <div class="icon-list">
      <div v-for="(item, index) in iconList" :key="index" @click="selectedIcon(item)">
        <SvgIcon :name="item" style="height: 30px; width: 16px" />
        <span>{{ item }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.icon-body {
  width: 100%;
  padding: 10px;
  .icon-list {
    height: 200px;
    overflow-y: scroll;
    div {
      height: 30px;
      line-height: 30px;
      margin-bottom: -5px;
      cursor: pointer;
      width: 33%;
      float: left;
    }
    span {
      display: inline-block;
      vertical-align: -0.15em;
      fill: currentColor;
      overflow: hidden;
    }
  }
}
</style>
