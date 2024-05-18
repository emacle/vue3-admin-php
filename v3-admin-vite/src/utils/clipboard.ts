import { ElMessage } from "element-plus"
import Clipboard from "clipboard"

function clipboardSuccess() {
  ElMessage({
    message: "Copy successfully",
    type: "success",
    duration: 1500
  })
}

function clipboardError() {
  ElMessage({
    message: "Copy failed",
    type: "error"
  })
}

export default function handleClipboard(text: string, event: Event) {
  const clipboard = new Clipboard(event.target as Element, {
    text: () => text
  })
  clipboard.on("success", () => {
    clipboardSuccess()
    clipboard.destroy()
  })
  clipboard.on("error", () => {
    clipboardError()
    clipboard.destroy()
  })
  clipboard.onClick(event)
}
