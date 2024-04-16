<script lang="ts" setup>
import { ref } from "vue"

defineOptions({
  name: "SysUser"
})

const text = ref("helloworld")
</script>

<template>
  <div class="app-container">
    <el-card header="用户管理">
      <el-input v-model="text" />
    </el-card>
  </div>
</template>
