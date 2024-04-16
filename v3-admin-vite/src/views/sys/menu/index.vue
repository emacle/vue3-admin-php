<script lang="ts" setup>
import { ref } from "vue"

defineOptions({
  name: "SysMenu"
})

const text = ref("helloworld")
</script>

<template>
  <div class="app-container">
    <el-card header="菜单管理">
      <el-input v-model="text" />
    </el-card>
  </div>
</template>
