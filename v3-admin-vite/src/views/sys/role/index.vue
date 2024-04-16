<script lang="ts" setup>
import { ref } from "vue"

defineOptions({
  name: "SysRole"
})

const text = ref("helloworld")
</script>

<template>
  <div class="app-container">
    <el-card header="角色管理">
      <el-input v-model="text" />
    </el-card>
  </div>
</template>
