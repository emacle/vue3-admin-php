import { App } from "vue"

const getIconComponent = (icon: string) => {
  return icon
}

export default {
  install(app: App) {
    app.config.globalProperties.$getIconComponent = getIconComponent
  }
}
