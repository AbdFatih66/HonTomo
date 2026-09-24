<script setup>
import { useInstallPrompt } from '@/composables/useInstallPrompt'

const { canInstall, showIosGuide, install } = useInstallPrompt()
</script>

<template>
  <template v-if="canInstall">
    <IconBtn @click="install">
      <VIcon icon="tabler-device-mobile-down" />
      <VTooltip
        activator="parent"
        location="bottom"
      >
        Install app
      </VTooltip>
    </IconBtn>

    <!-- iPhone / iPad: no install API, so explain "Add to Home Screen" -->
    <VDialog
      v-model="showIosGuide"
      max-width="380"
    >
      <VCard title="Install HonTomo">
        <VCardText>
          <p class="mb-4">
            Add HonTomo to your Home Screen to open it like a regular app:
          </p>
          <ol class="ps-5 d-flex flex-column gap-y-2">
            <li>Tap the <strong>Share</strong> button in Safari.</li>
            <li>Choose <strong>Add to Home Screen</strong>.</li>
            <li>Tap <strong>Add</strong>.</li>
          </ol>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn @click="showIosGuide = false">
            Got it
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </template>
</template>
