// `title` values are vue-i18n keys (rendered through <i18n-t> by the nav link
// component), so the sidebar follows the language switcher.
//
// Urutan item MENGIKUTI ROADMAP BELAJAR, dari dasar sampai ujian:
//   Dasbor → Kana → Tata Bahasa → Referensi Tata Bahasa → Kosakata → Kanji
//   → Latihan Soal → Latihan Menulis → Latihan Menyimak → Percakapan → Tes JLPT
// Menu admin selalu di paling bawah.
export default [
  {
    title: 'nav.dashboard',
    to: { name: 'root' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    title: 'nav.kana',
    to: { name: 'kana' },
    icon: { icon: 'tabler-language-katakana' },
  },
  {
    title: 'nav.learn',
    to: { name: 'learn' },
    icon: { icon: 'tabler-book' },
  },
  {
    title: 'nav.lampiran',
    to: { name: 'lampiran' },
    icon: { icon: 'tabler-clipboard-list' },
  },
  {
    title: 'nav.vocabulary',
    to: { name: 'kosakata' },
    icon: { icon: 'tabler-notebook' },
  },
  {
    title: 'nav.kanji',
    to: { name: 'kanji' },
    icon: { icon: 'tabler-writing' },
  },
  {
    title: 'nav.mondaishuu',
    to: { name: 'mondaishuu' },
    icon: { icon: 'tabler-pencil-check' },
  },
  {
    title: 'nav.kaite_oboeru',
    to: { name: 'kaite-oboeru' },
    icon: { icon: 'tabler-writing-sign' },
  },
  {
    title: 'nav.chokai',
    to: { name: 'chokai' },
    icon: { icon: 'tabler-headphones' },
  },
  {
    title: 'nav.kaiwa',
    to: { name: 'kaiwa' },
    icon: { icon: 'tabler-messages' },
  },
  {
    title: 'nav.jlpt_test',
    to: { name: 'jlpt-test' },
    icon: { icon: 'tabler-certificate' },
  },
  {
    title: 'nav.user_management',
    to: { name: 'admin-users' },
    icon: { icon: 'tabler-users' },
    adminOnly: true,
  },
  {
    title: 'nav.audit_log',
    to: { name: 'admin-audit-logs' },
    icon: { icon: 'tabler-history' },
    adminOnly: true,
  },
]
