<script setup>
import { avatarText } from '@core/utils/formatters'
import { useAuthStore } from '@/stores/auth'

// HonTomo's own avatar: the user's photo when they have one, otherwise a
// circle with their initials on the brand gradient — no stock template photos.
const props = defineProps({
  size: { type: [Number, String], default: 40 },
})

const authStore = useAuthStore()

const initials = computed(() => avatarText(authStore.user?.name) || '?')
</script>

<template>
  <VAvatar
    :size="size"
    class="user-avatar"
  >
    <VImg
      v-if="authStore.user?.avatar_url"
      :src="authStore.user.avatar_url"
      :alt="authStore.user?.name"
    />
    <span
      v-else
      class="user-avatar-initials"
    >{{ initials }}</span>

    <slot />
  </VAvatar>
</template>

<style lang="scss" scoped>
.user-avatar-initials {
  display: flex;
  inline-size: 100%;
  block-size: 100%;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 600;
  background: linear-gradient(135deg, #0a84f0 0%, #22c8ec 100%);
}
</style>
