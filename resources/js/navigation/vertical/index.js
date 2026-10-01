// `title` values are vue-i18n keys (rendered through <i18n-t> by the nav link
// component), so the sidebar follows the language switcher.
export default [
  {
    title: 'nav.dashboard',
    to: { name: 'root' },
    icon: { icon: 'tabler-smart-home' },
  },
  {
    title: 'nav.learn',
    to: { name: 'learn' },
    icon: { icon: 'tabler-book' },
  },
  {
    title: 'nav.vocabulary',
    to: { name: 'kosakata' },
    icon: { icon: 'tabler-notebook' },
  },
  {
    title: 'nav.mondaishuu',
    to: { name: 'mondaishuu' },
    icon: { icon: 'tabler-pencil-check' },
  },
  {
    title: 'nav.jlpt_test',
    to: { name: 'jlpt-test' },
    icon: { icon: 'tabler-certificate' },
  },
  {
    title: 'nav.chokai',
    to: { name: 'chokai' },
    icon: { icon: 'tabler-headphones' },
  },
  {
    title: 'nav.kaite_oboeru',
    to: { name: 'kaite-oboeru' },
    icon: { icon: 'tabler-writing-sign' },
  },
  {
    title: 'nav.kana',
    to: { name: 'kana' },
    icon: { icon: 'tabler-language-katakana' },
  },
  {
    title: 'nav.kanji',
    to: { name: 'kanji' },
    icon: { icon: 'tabler-writing' },
  },
  {
    title: 'nav.lampiran',
    to: { name: 'lampiran' },
    icon: { icon: 'tabler-clipboard-list' },
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
