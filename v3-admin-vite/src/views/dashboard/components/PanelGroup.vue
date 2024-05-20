<script setup lang="ts">
import { defineEmits } from "vue"
import CountUp from "vue-countup-v3"

const emit = defineEmits(["handleSetLineChartData"])

const handleSetLineChartData = (type: string) => {
  emit("handleSetLineChartData", type)
}
</script>

<template>
  <el-row :gutter="40" class="panel-group">
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel on-dark-background" @click="handleSetLineChartData('newVisitis')">
        <div class="card-panel-icon-wrapper icon-people">
          <SvgIcon name="peoples" class="card-panel-icon" />
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">New Visits</div>
          <count-up :start-val="0" :end-val="102400" :duration="2.5" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel on-dark-background" @click="handleSetLineChartData('messages')">
        <div class="card-panel-icon-wrapper icon-message">
          <SvgIcon name="message" class="card-panel-icon" />
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Messages</div>
          <count-up :start-val="0" :end-val="81212" :duration="2.5" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel on-dark-background" @click="handleSetLineChartData('purchases')">
        <div class="card-panel-icon-wrapper icon-money">
          <SvgIcon name="money" class="card-panel-icon" />
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Purchases</div>
          <count-up :start-val="0" :end-val="9280" :duration="3" class="card-panel-num" />
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel on-dark-background" @click="handleSetLineChartData('shoppings')">
        <div class="card-panel-icon-wrapper icon-shopping">
          <SvgIcon name="shopping" class="card-panel-icon" />
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Shoppings</div>
          <count-up :start-val="0" :end-val="13600" :duration="3" class="card-panel-num" />
        </div>
      </div>
    </el-col>
  </el-row>
</template>

<style lang="scss" scoped>
.panel-group {
  margin-top: 18px;

  .card-panel-col {
    margin-bottom: 32px;
  }
  /* 如果需要在特定背景下调整边框颜色，可以继续使用之前提到的类名方法，例如： */
  .card-panel.on-dark-background {
    border-color: rgba(255, 255, 255, 0.1); /* 在深色背景下，边框颜色调整为浅色 */
  }
  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    background-color: transparent; /* 保持透明背景 */
    box-shadow: 4px 4px 40px rgba(0, 0, 0, 0.05); /* 维持阴影效果 */
    border: 1px solid rgba(0, 0, 0, 0.1); /* 添加边框，这里使用半透明黑色作为示例 */

    &:hover {
      .card-panel-icon-wrapper {
        color: #fff;
      }

      .icon-people {
        background: #40c9c6;
      }

      .icon-message {
        background: #36a3f7;
      }

      .icon-money {
        background: #f4516c;
      }

      .icon-shopping {
        background: #34bfa3;
      }
    }

    .icon-people {
      color: #40c9c6;
    }

    .icon-message {
      color: #36a3f7;
    }

    .icon-money {
      color: #f4516c;
    }

    .icon-shopping {
      color: #34bfa3;
    }

    .card-panel-icon-wrapper {
      float: left;
      margin: 14px 0 0 14px;
      padding: 16px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }

    .card-panel-icon {
      float: left;
      font-size: 48px;
    }

    .card-panel-description {
      float: right;
      font-weight: bold;
      margin: 26px;
      margin-left: 0px;

      .card-panel-text {
        line-height: 18px;
        color: rgba(0, 0, 0, 0.45);
        font-size: 16px;
        margin-bottom: 12px;
      }

      .card-panel-num {
        font-size: 20px;
      }
    }
  }
}
// .countup-wrap {
//   font-size: 1.6em;
//   margin: 0;
//   color: #4d63bc;
// }
@media (max-width: 550px) {
  .card-panel-description {
    display: none;
  }

  .card-panel-icon-wrapper {
    float: none !important;
    width: 100%;
    height: 100%;
    margin: 0 !important;

    .svg-icon {
      display: block;
      margin: 14px auto !important;
      float: none !important;
    }
  }
}
</style>
