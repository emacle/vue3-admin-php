<script lang="ts" setup>
// 如果有需要在 setup 中添加逻辑，可以在这里编写
import GithubCorner from "@/components/GIthubCorner/index.vue"
</script>

<template>
  <div class="app-container center">
    <el-empty description="欢迎来到 admin 角色专属首页" />
    <GithubCorner />
  </div>
</template>

<style lang="scss" scoped>
.center {
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.app-container {
  position: relative;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.github-corner {
  position: absolute;
  top: 0;
  right: 0;
}
</style>
