<script setup>
import { themeConfig } from '@themeConfig'

const currentYear = new Date().getFullYear()
</script>

<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <!-- 👉 Footer: left content -->
    <span class="text-medium-emphasis">
      &copy; {{ currentYear }}
      <span class="font-weight-medium text-primary">{{ themeConfig.app.title }}</span>.
      All rights reserved.
    </span>

    <!-- 👉 Footer: right content -->
    <span class="d-md-flex align-center text-medium-emphasis d-none">
      <span class="me-2">本友</span>
      <span>Your Japanese Learning Companion</span>
    </span>
  </div>
</template>
