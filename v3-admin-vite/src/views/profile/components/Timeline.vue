<script lang="ts" setup>
const timeline = [
  {
    timestamp: "2024/4/20",
    title: "Update Github template",
    content: "committed 2024/4/20 20:46"
  },
  {
    timestamp: "2024/4/21",
    title: "Update Github template",
    content: "committed 2024/4/21 20:46"
  },
  {
    timestamp: "2024/4/22",
    title: "Build Template",
    content: "committed 2024/4/22 20:46"
  },
  {
    timestamp: "2024/4/23",
    title: "Release New Version",
    content: "committed 2024/4/23 20:46"
  }
]
</script>

<template>
  <div class="block">
    <el-timeline>
      <el-timeline-item v-for="(item, index) of timeline" :key="index" :timestamp="item.timestamp" placement="top">
        <el-card>
          <h4>{{ item.title }}</h4>
          <p>{{ item.content }}</p>
        </el-card>
      </el-timeline-item>
    </el-timeline>
  </div>
</template>

<style lang="scss" scoped>
.user-activity {
  .user-block {
    .username,
    .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }

    .username {
      font-size: 16px;
      color: #000;
    }

    :after {
      clear: both;
    }

    .img-circle {
      border-radius: 50%;
      width: 40px;
      height: 40px;
      float: left;
    }

    span {
      font-weight: 500;
      font-size: 12px;
    }
  }

  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;

    .image {
      width: 100%;
      height: 100%;
    }

    .user-images {
      padding-top: 20px;
    }
  }

  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;

    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }

    .link-black {
      &:hover,
      &:focus {
        color: #999;
      }
    }
  }
}

.box-center {
  margin: 0 auto;
  display: table;
}

.text-muted {
  color: #777;
}
</style>
