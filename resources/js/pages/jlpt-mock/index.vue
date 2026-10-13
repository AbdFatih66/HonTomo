<script setup>
// Simulasi JLPT sudah digabung ke Tes JLPT. Halaman ini hanya mengalihkan
// tautan lama (bookmark, notifikasi, pintasan) ke halaman baru.
definePage({ meta: { layout: 'blank' } })

const router = useRouter()

router.replace({ name: 'jlpt-test' })
</script>

<template>
  <div />
</template>
