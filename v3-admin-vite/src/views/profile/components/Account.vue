<script lang="ts" setup>
import { ElMessage } from "element-plus"
import { defineProps, onMounted, ref } from "vue"

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

// 创建默认值对象的函数
const createDefaultObject = <T,>(defaultValue: any): T => {
  return new Proxy(
    {},
    {
      get: () => defaultValue
    }
  ) as T
}
// 直接修改传入的 props 是不推荐的，因为 props 应该是父组件传递给子组件的只读数据。
// 为了避免违反这个规则并解决 ESLint 报错，可以将 props 的数据复制到本地状态，然后在本地状态上进行修改。
const localUser = ref<userInfo>(createDefaultObject<userInfo>(""))

// createDefaultObject 函数 将localUser 创建userInfo类型的属性全部为""空字符串，等价于下面结果
// const localUser = ref<userInfo>({
//   userId: "",
//   username: "",
//   email: "",
//   avatar: "",
//   role: ""
// })

onMounted(() => {
  localUser.value = { ...props.user }
})

const submit = () => {
  ElMessage({ message: "User information has been updated successfully", type: "success" })
}
</script>

<template>
  <el-form label-position="top">
    <el-form-item label="Name">
      <el-input v-model.trim="localUser.username" />
    </el-form-item>
    <el-form-item label="Email">
      <el-input v-model.trim="localUser.email" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="submit">Update</el-button>
    </el-form-item>
  </el-form>
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
