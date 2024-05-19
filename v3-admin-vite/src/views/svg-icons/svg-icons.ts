// 动态导入 @/icons/svg 目录下的所有 .svg 文件
const modules = import.meta.glob("@/icons/svg/*.svg", { query: "?raw" })
const svgIcons: string[] = []

for (const path in modules) {
  const match = path.match(/([^/]+)\.svg$/)
  const fileName = match ? match[1] : ""
  svgIcons.push(fileName)
}
// console.log(svgIcons) // 输出 ["404", "bug", "build"]

export default svgIcons
