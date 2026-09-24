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
    title: 'nav.user_management',
    to: { name: 'admin-users' },
    icon: { icon: 'tabler-users' },
  },
  {
    title: 'nav.audit_log',
    to: { name: 'admin-audit-logs' },
    icon: { icon: 'tabler-history' },
  },
]
