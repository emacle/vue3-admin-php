<script lang="ts" setup>
import { ref } from "vue"
import { useUserStore } from "@/store/modules/user"
import UserCard from "./components/UserCard.vue"
import Activity from "./components/Activity.vue"
import Timeline from "./components/Timeline.vue"
import Account from "./components/Account.vue"
import Password from "./components/Password.vue"

defineOptions({
  // 命名当前组件
  name: "Profile"
})

const userStore = useUserStore()
const loading = ref<boolean>(false)
const activeTab = ref<string>("activity")
// const isAdmin = userStore.roles.includes("admin")

console.log("dashboard userStore.userId", userStore.userId, userStore.username, userStore.email)
console.log("Type of userStore.userId", typeof userStore.userId)

const user = {
  userId: userStore.userId,
  username: userStore.username,
  email: userStore.email,
  avatar: userStore.avatar,
  role: userStore.roles[0].name
}
</script>

<template>
  <div class="app-container">
    <div v-if="true">
      <el-row :gutter="20">
        <el-col :span="6" :xs="24">
          <UserCard :user="user" />
        </el-col>

        <el-col :span="18" :xs="24">
          <el-card>
            <el-tabs v-model="activeTab">
              <el-tab-pane label="Activity" name="activity">
                <Activity />
              </el-tab-pane>
              <el-tab-pane label="Timeline" name="timeline">
                <Timeline />
              </el-tab-pane>
              <el-tab-pane label="Account" name="account">
                <Account :user="user" />
              </el-tab-pane>
              <el-tab-pane label="Password" name="password">
                <Password :user="user" />
              </el-tab-pane>
            </el-tabs>
          </el-card>
        </el-col>
      </el-row>
    </div>
  </div>
</template>
