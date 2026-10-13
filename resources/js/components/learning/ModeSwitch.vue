<script setup>
// Selektor pil umum (gaya sama dengan LevelSwitch). Dipakai Kaiwa untuk
// memilih mode: Situasi | N5 | N4. `options` = [{ value, label }].
defineProps({
  modelValue: { type: String, default: null },
  options: { type: Array, required: true },
  ariaLabel: { type: String, default: '' },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div
    class="mode-switch"
    role="tablist"
    :aria-label="ariaLabel"
  >
    <button
      v-for="o in options"
      :key="o.value"
      type="button"
      role="tab"
      class="mode-switch__btn"
      :class="{ 'mode-switch__btn--active': modelValue === o.value }"
      :aria-selected="modelValue === o.value"
      @click="$emit('update:modelValue', o.value)"
    >
      {{ o.label }}
    </button>
  </div>
</template>

<style scoped>
.mode-switch {
  display: inline-flex;
  padding: 4px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  background: rgb(var(--v-theme-surface));
  gap: 4px;
}

.mode-switch__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-inline-size: 52px;
  padding-block: 8px;
  padding-inline: 16px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: rgba(var(--v-theme-on-surface), 0.72);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  transition: background 0.15s ease, color 0.15s ease;
}

.mode-switch__btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.mode-switch__btn--active,
.mode-switch__btn--active:hover {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.mode-switch__btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}
</style>
