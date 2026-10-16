import { execFileSync } from 'node:child_process'
import { defineConfig } from 'vitepress'

const repo = 'https://github.com/janalis/doctrineviz'
function version(): string {
  try {
    return execFileSync('git', ['describe', '--tags', '--abbrev=0'], { stdio: ['ignore', 'pipe', 'ignore'] }).toString().trim()
  } catch {
    return 'dev'
  }
}

export default defineConfig({
  title: 'doctrineviz',
  description: 'Generate Graphviz diagrams from your Doctrine DBAL database schema.',
  base: '/doctrineviz/',
  lang: 'en-US',
  cleanUrls: true,
  lastUpdated: true,
  sitemap: { hostname: 'https://janalis.github.io/doctrineviz/' },
  head: [['link', { rel: 'icon', type: 'image/svg+xml', href: '/doctrineviz/logo.svg' }]],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Guide', link: '/guide/installation' },
      { text: 'PHP API', link: '/guide/php-api' },
      { text: 'Contributing', link: '/contributing/development' },
      { text: version(), items: [{ text: 'Changelog', link: '/changelog' }, { text: 'Releases', link: repo + '/releases' }] },
    ],
    sidebar: [
      { text: 'Guide', items: [
        { text: 'Installation', link: '/guide/installation' },
        { text: 'Command reference', link: '/guide/usage' },
        { text: 'PHP API', link: '/guide/php-api' },
        { text: 'Migration from 0.3.x', link: '/guide/migration' },
      ] },
      { text: 'Contributing', items: [
        { text: 'Development', link: '/contributing/development' },
        { text: 'Releases', link: '/contributing/releases' },
        { text: 'Changelog', link: '/changelog' },
      ] },
    ],
    search: { provider: 'local' },
    socialLinks: [{ icon: 'github', link: repo }],
    editLink: {
      pattern: ({ filePath }) => filePath === 'changelog.md'
        ? 'https://github.com/janalis/doctrineviz/edit/master/CHANGELOG.md'
        : 'https://github.com/janalis/doctrineviz/edit/master/docs/' + filePath,
    },
    footer: { message: 'Released under the BSD-3-Clause License.' },
  },
})
