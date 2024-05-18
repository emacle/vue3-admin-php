<script lang="ts" setup>
import { defineProps, defineEmits, onMounted, ref } from "vue"
import PanThumb from "@/components/PanThumb/index.vue"

interface userInfo {
  userId: string
  username: string
  email: string
  avatar: string
  role: string
}

const props = defineProps<{
  user: userInfo
}>()

// 直接修改传入的 props 是不推荐的，因为 props 应该是父组件传递给子组件的只读数据。
// 为了避免违反这个规则并解决 ESLint 报错，可以将 props 的数据复制到本地状态，然后在本地状态上进行修改。
const localUser = ref<userInfo>({
  userId: "",
  username: "",
  email: "",
  avatar: "",
  role: ""
})

onMounted(() => {
  localUser.value = { ...props.user }
})
// const emit = defineEmits<{
//   (e: "custom-event", payload: string): void
// }>()

// const handleClick = () => {
//   emit("custom-event", "Hello from ChildComponent")
// }
</script>

<template>
  <div>
    <!-- <p>{{ user.username }}</p>
    <p>{{ user.userId }}</p>
    <p>{{ user.email }}</p>
    <p>{{ user.avatar }}</p>
    <p>{{ user.role }}</p> -->
    <el-card style="margin-bottom: 20px">
      <template v-slot:header>
        <div class="clearfix">
          <span>About me</span>
        </div>
      </template>

      <div class="user-profile">
        <div class="box-center">
          <PanThumb :image="localUser.avatar" :zIndex="5" width="100px" height="100px">
            Hello
            <p>{{ localUser.role }}</p>
          </PanThumb>
          <p>{{ localUser.username }}</p>
          <p>{{ localUser.email }}</p>
        </div>
      </div>

      <div class="user-bio">
        <div class="user-education user-bio-section">
          <div class="user-bio-section-header">
            <el-icon><Reading /></el-icon><span>Education</span>
          </div>
          <div class="user-bio-section-body">
            <div class="text-muted">JS in Computer Science from the University of Technology</div>
          </div>
        </div>

        <div class="user-skills user-bio-section">
          <div class="user-bio-section-header">
            <el-icon><List /></el-icon><span>Skills</span>
          </div>
          <div class="user-bio-section-body">
            <div class="progress-item">
              <span>Vue</span>
              <el-progress :percentage="70" />
            </div>
            <div class="progress-item">
              <span>JavaScript</span>
              <el-progress :percentage="18" />
            </div>
            <div class="progress-item">
              <span>Css</span>
              <el-progress :percentage="12" />
            </div>
            <div class="progress-item">
              <span>ESLint</span>
              <el-progress :percentage="100" status="success" />
            </div>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<style lang="scss" scoped>
.box-center {
  margin: 0 auto;
  display: table;
}

.text-muted {
  color: #777;
}

.user-profile {
  .user-name {
    font-weight: bold;
  }

  .box-center {
    padding-top: 10px;
  }

  .user-role {
    padding-top: 10px;
    font-weight: 400;
    font-size: 14px;
  }

  .box-social {
    padding-top: 30px;

    .el-table {
      border-top: 1px solid #dfe6ec;
    }
  }

  .user-follow {
    padding-top: 20px;
  }
}

.user-bio {
  margin-top: 20px;
  color: #606266;

  span {
    padding-left: 4px;
  }

  .user-bio-section {
    font-size: 14px;
    padding: 15px 0;

    .user-bio-section-header {
      border-bottom: 1px solid #dfe6ec;
      padding-bottom: 10px;
      margin-bottom: 10px;
      font-weight: bold;
    }
  }
}
</style>
