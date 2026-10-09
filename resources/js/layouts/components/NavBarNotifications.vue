<script setup>
const { t, locale } = useI18n()

// 👉 Notifikasi nyata dari backend (tabel user_notifications) — dibuat oleh
// NotificationService setiap kali pengguna benar-benar mencapai sesuatu
// (pelajaran selesai/sempurna, milestone streak). Tidak ada lagi data demo
// (PayPal, "Tom Holland", dst).
const rawNotifications = ref([])

function relativeTime(dateString) {
  const date = new Date(dateString)
  const diffSeconds = Math.round((date.getTime() - Date.now()) / 1000)
  const rtf = new Intl.RelativeTimeFormat(locale.value === 'id' ? 'id' : 'en', { numeric: 'auto' })

  const ranges = [
    ['year', 60 * 60 * 24 * 365],
    ['month', 60 * 60 * 24 * 30],
    ['day', 60 * 60 * 24],
    ['hour', 60 * 60],
    ['minute', 60],
  ]

  for (const [unit, secondsInUnit] of ranges) {
    if (Math.abs(diffSeconds) >= secondsInUnit)
      return rtf.format(Math.round(diffSeconds / secondsInUnit), unit)
  }

  return rtf.format(diffSeconds, 'second')
}

// 👉 Bentuk yang dipahami komponen generik @core/components/Notifications.vue.
// `title`/`subtitle` disimpan sebagai i18n key di server supaya tetap
// diterjemahkan sesuai bahasa UI saat ini, bukan bahasa waktu kejadiannya.
const notifications = computed(() => rawNotifications.value.map(n => ({
  id: n.id,
  icon: n.icon,
  color: n.color,
  title: t(n.title),
  subtitle: n.subtitle?.key ? t(n.subtitle.key, n.subtitle) : '',
  time: relativeTime(n.created_at),
  isSeen: n.is_read,
})))

async function loadNotifications() {
  try {
    const res = await $api('/notifications')

    rawNotifications.value = res.notifications ?? []
  }
  catch {
    // Gagal memuat (mis. offline) — biarkan lonceng tampil kosong daripada
    // menampilkan data palsu.
  }
}

loadNotifications()

function removeNotification(notificationId) {
  rawNotifications.value = rawNotifications.value.filter(item => item.id !== notificationId)
  $api(`/notifications/${notificationId}`, { method: 'DELETE' }).catch(() => {})
}

function markRead(ids) {
  rawNotifications.value.forEach(item => {
    if (ids.includes(item.id))
      item.is_read = true
  })
  $api('/notifications/read', { method: 'PATCH', body: { ids } }).catch(() => {})
}

function markUnread(ids) {
  rawNotifications.value.forEach(item => {
    if (ids.includes(item.id))
      item.is_read = false
  })
  $api('/notifications/unread', { method: 'PATCH', body: { ids } }).catch(() => {})
}

function handleNotificationClick(notification) {
  if (!notification.isSeen)
    markRead([notification.id])
}
</script>

<template>
  <Notifications
    :notifications="notifications"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnread"
    @click:notification="handleNotificationClick"
  />
</template>
