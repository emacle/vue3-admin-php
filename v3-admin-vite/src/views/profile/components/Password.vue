<script lang="ts" setup>
import { type FormInstance, type FormRules, ElMessage } from "element-plus"
import { Lock } from "@element-plus/icons-vue"
import { defineProps, onMounted, ref } from "vue"
import { type UpdateUserPasswordRequestData } from "@/api/user/types/user"
import { updatePasswordApi } from "@/api/user"

interface userInfo {
  userId: string
  username: string
}

const props = defineProps<{
  user: userInfo
}>()

const updateLoading = ref<boolean>(false)
const formRef = ref<FormInstance | null>(null)
const formData = ref<UpdateUserPasswordRequestData>({
  passwordOrig: "",
  password: "",
  rePassword: ""
})

// #region
const validatePassword = (rule: any, value: string, callback: any) => {
  if (!value) {
    return callback(new Error("请输入密码"))
  } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[^]{8,}$/.test(value)) {
    return callback(new Error("密码至少包含大写字母，小写字母和数字，且不少于8位"))
  } else {
    return callback()
  }
}

const validateRePassword = (rule: any, value: string, callback: any) => {
  if (!value) {
    return callback(new Error("请再次输入密码"))
  } else if (value !== formData.value.password) {
    return callback(new Error("两次密码输入不一致"))
  } else {
    return callback()
  }
}
const formRules: FormRules<UpdateUserPasswordRequestData> = {
  passwordOrig: [{ required: true, trigger: "blur", message: "请输入原密码" }],
  password: [{ required: true, trigger: "blur", validator: validatePassword }],
  rePassword: [{ required: true, trigger: "blur", validator: validateRePassword }]
}
// #endregion

const onSubmit = () => {
  formRef.value?.validate((valid: boolean) => {
    if (valid) {
      updateLoading.value = true
      updatePasswordApi(formData.value)
        .then((res: any) => {
          ElMessage({ message: res.message, type: res.type })
        })
        .catch((err) => {
          console.log(err)
        })
        .finally(() => {
          formData.value = {
            passwordOrig: "",
            password: "",
            rePassword: ""
          }
          updateLoading.value = false
        })
    } else {
      ElMessage({ message: "表单校验失败", type: "error" })
    }
  })
}

// onMounted(() => {
//   console.log("onMounted......", formData.value)
// })
</script>

<template>
  <el-form
    ref="formRef"
    :model="formData"
    :rules="formRules"
    label-width="100px"
    label-position="right"
    style="width: 60%; margin-left: 5px"
  >
    <el-form-item prop="passwordOrig" label="原密码">
      <el-input v-model.trim="formData.passwordOrig" placeholder="请输入" :prefix-icon="Lock" show-password />
    </el-form-item>
    <el-form-item prop="password" label="密码">
      <el-input v-model="formData.password" placeholder="请输入" :prefix-icon="Lock" show-password />
    </el-form-item>
    <el-form-item prop="rePassword" label="密码">
      <el-input v-model="formData.rePassword" placeholder="请输入" :prefix-icon="Lock" show-password />
    </el-form-item>
    <el-form-item>
      <el-button :loading="updateLoading" type="primary" @click="onSubmit">修改密码</el-button>
    </el-form-item>
  </el-form>
</template>

<style lang="scss" scoped></style>
