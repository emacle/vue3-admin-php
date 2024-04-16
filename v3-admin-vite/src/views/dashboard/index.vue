<script lang="ts" setup>
import { useUserStore } from "@/store/modules/user"
import Admin from "./components/Admin.vue"
import Editor from "./components/Editor.vue"

const userStore = useUserStore()

// const isAdmin = userStore.roles.includes("admin")
const isAdmin = userStore.roles.some((role) => role.id === 1 && role.name === "超级管理员")
// console.log("dashboard", isAdmin)
</script>

<template>
  <component :is="isAdmin ? Admin : Editor" />
</template>
