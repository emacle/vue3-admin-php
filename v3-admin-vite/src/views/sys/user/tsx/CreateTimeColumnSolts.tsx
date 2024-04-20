import { type VxeColumnPropTypes } from "vxe-table/types/column"

const solts: VxeColumnPropTypes.Slots = {
  default: ({ row, column }) => {
    const cellValue = row[column.field]
    // 将时间戳转换为 Date 对象
    const date = new Date(cellValue * 1000) // 注意要乘以1000转换为毫秒级时间戳
    // 格式化日期时间
    const formattedDate = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`
    return formattedDate
  }
}

export default solts
