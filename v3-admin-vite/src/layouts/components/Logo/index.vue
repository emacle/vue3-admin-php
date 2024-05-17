<script lang="ts" setup>
import { useLayoutMode } from "@/hooks/useLayoutMode"
import logo from "@/assets/layouts/logo.png?url"
import logoText1 from "@/assets/layouts/logo-text-1.png?url"
import logoText2 from "@/assets/layouts/logo-text-2.png?url"

interface Props {
  collapse?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  collapse: true
})

const { isLeft, isTop } = useLayoutMode()
</script>

<template>
  <div class="layout-logo-container" :class="{ collapse: props.collapse, 'layout-mode-top': isTop }">
    <transition name="layout-logo-fade">
      <router-link v-if="props.collapse" key="collapse" to="/">
        <img :src="logo" class="layout-logo" />
      </router-link>
      <router-link v-else key="expand" to="/">
        <img :src="!isLeft ? logoText2 : logoText1" class="layout-logo-text" />
        <h1 class="sidebar-title">Emacs Org</h1>
      </router-link>
    </transition>
  </div>
</template>

<style lang="scss" scoped>
.layout-logo-container {
  position: relative;
  width: 100%;
  height: var(--v3-header-height);
  line-height: var(--v3-header-height);
  text-align: center;
  overflow: hidden;
  .layout-logo {
    display: none;
  }
  .layout-logo-text {
    height: 50%;
    vertical-align: middle;
    margin-right: 16px;
  }
}

.layout-mode-top {
  height: var(--v3-navigationbar-height);
  line-height: var(--v3-navigationbar-height);
}

.collapse {
  .layout-logo {
    // width: 48px;
    // height: 48px;
    height: 50%;
    vertical-align: middle;
    display: inline-block;
  }
  .layout-logo-text {
    display: none;
  }
}

.sidebar-title {
  display: inline-block;
  margin: 0;
  color: #fff;
  font-weight: 600;
  line-height: 50px;
  font-size: 20px;
  font-family:
    Avenir,
    Helvetica Neue,
    Arial,
    Helvetica,
    sans-serif;
  vertical-align: middle;
}
</style>
