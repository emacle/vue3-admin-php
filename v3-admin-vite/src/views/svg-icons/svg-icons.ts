// 动态导入 ../../icons/svg 目录下的所有 .svg 文件
// const modulesSvgs = import.meta.glob("@/icons/svg/*.svg", { eager: true })
// // modulesSvgs 结果如下
// // {
// //   "/src/icons/svg/validCode.svg": {
// //       "default": "/src/icons/svg/validCode.svg"
// //   },
// //   "/src/icons/svg/wechat.svg": {
// //       "default": "/src/icons/svg/wechat.svg"
// //   },
// //   "/src/icons/svg/yidong.svg": {
// //       "default": "/src/icons/svg/yidong.svg"
// //   },
// //   "/src/icons/svg/zip.svg": {
// //       "default": "/src/icons/svg/zip.svg"
// //   }
// // }

// const getSvgIcons = (): string[] => {
//   const re = /.*\/(.*)\.svg/
//   return Object.keys(modulesSvgs).map((file: string) => {
//     const match = file.match(re)
//     return match ? match[1] : ""
//   })
// }

// TODO: 动态获取svg文件名
const svgIcons: string[] = [
  "zip",
  "yidong",
  "wechat",
  "validCode",
  "user",
  "upload",
  "tree",
  "tree-table",
  "tool",
  "time",
  "time-range",
  "theme",
  "textarea",
  "table",
  "tab",
  "systemManage",
  "system",
  "sysseting",
  "sysset2",
  "sysset",
  "switch",
  "swagger",
  "star",
  "slider",
  "skill",
  "size",
  "shopping",
  "server",
  "select",
  "row",
  "role",
  "rate",
  "radio",
  "question",
  "qq",
  "post",
  "plane",
  "phone",
  "peoples",
  "people",
  "pdf",
  "password",
  "online",
  "number",
  "nested",
  "monitor",
  "money",
  "message",
  "menu2",
  "menu1",
  "logininfor",
  "log",
  "list",
  "language",
  "job",
  "international",
  "input",
  "icon",
  "guide",
  "guide 2",
  "github",
  "gitee",
  "gitee-white",
  "form",
  "eye",
  "eye-open",
  "exit-fullscreen",
  "excel",
  "example",
  "email",
  "education",
  "edit",
  "druid",
  "drag",
  "download",
  "documentation",
  "dict",
  "dept3",
  "dept2",
  "dept",
  "date",
  "date-range",
  "color",
  "code",
  "clipboard",
  "checkbox",
  "chart",
  "cascader",
  "build",
  "unocss",
  "search",
  "menu",
  "lock",
  "link",
  "keyboard-up",
  "keyboard-esc",
  "keyboard-enter",
  "keyboard-down",
  "fullscreen",
  "fullscreen-exit",
  "dashboard",
  "component",
  "bug",
  "404"
]

export default svgIcons
