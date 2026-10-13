<script setup>
// Tautan lama /jlpt-mock/<paket> → Tes JLPT pada paket yang sama.
definePage({ meta: { layout: 'blank' } })

const route = useRoute()
const router = useRouter()

router.replace({ name: 'jlpt-test', query: { pack: String(route.params.key) } })
</script>

<template>
  <div />
</template>
