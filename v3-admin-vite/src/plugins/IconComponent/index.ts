import { type App } from "vue"
import getIconComponent from "./getIconComponent"

export function loadIconComponent(app: App) {
  app.use(getIconComponent)
}
