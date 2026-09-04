import { createHighlighterCore } from 'shiki/core'
import { createJavaScriptRegexEngine } from 'shiki/engine/javascript'
import php from '@shikijs/langs/php'
import ayuDark from '@shikijs/themes/ayu-dark'
import githubLight from '@shikijs/themes/github-light'

const highlighterPromise = createHighlighterCore({
  themes: [githubLight, ayuDark],
  langs: [php],
  engine: createJavaScriptRegexEngine(),
})

function decodeBase64Utf8(value) {
  const bytes = Uint8Array.from(atob(value || ''), character => character.charCodeAt(0))
  return new TextDecoder().decode(bytes)
}

function decorateLines(pre, startingLine, errorLine, errorMessage, removeSyntheticOpeningTag) {
  const code = pre.querySelector('code')
  if (!code) return

  const renderedLines = Array.from(code.querySelectorAll(':scope > .line'))
  if (removeSyntheticOpeningTag && renderedLines.length) {
    renderedLines.shift().remove()
  }

  Array.from(code.querySelectorAll(':scope > .line')).forEach((line, index) => {
    const lineNumber = startingLine + index
    const number = document.createElement('span')
    number.className = 'line-number'
    number.setAttribute('aria-hidden', 'true')
    number.textContent = String(lineNumber)
    line.prepend(number)

    if (lineNumber === errorLine) {
      line.classList.add('is-error')
      line.setAttribute('aria-current', 'true')

      if (errorMessage) {
        const message = document.createElement('span')
        message.className = 'errtk-code-msg'
        message.setAttribute('role', 'note')
        message.textContent = errorMessage
        line.after(message)
      }
    }
  })
}

async function highlightBlock(block) {
  if (block.dataset.shikiReady || !block.dataset.source) return

  const source = decodeBase64Utf8(block.dataset.source)
  const errorMessage = decodeBase64Utf8(block.dataset.errorMessage)
  const startingLine = Number.parseInt(block.dataset.lineStart || '1', 10)
  const errorLine = Number.parseInt(block.dataset.errorLine || '0', 10)
  const needsOpeningTag = !/^\s*<\?(?:php|=)?/i.test(source)
  const highlighter = await highlighterPromise
  const html = highlighter.codeToHtml((needsOpeningTag ? '<?php\n' : '') + source, {
    lang: 'php',
    themes: {
      light: 'github-light',
      dark: 'ayu-dark',
    },
    defaultColor: false,
  })
  const template = document.createElement('template')
  template.innerHTML = html.trim()
  const pre = template.content.firstElementChild

  if (!pre) throw new Error('Shiki did not return a source block.')

  pre.setAttribute('tabindex', '0')
  pre.setAttribute('aria-label', 'PHP source code')
  decorateLines(pre, startingLine, errorLine, errorMessage, needsOpeningTag)
  block.replaceChildren(pre)
  block.dataset.shikiReady = 'true'
}

async function highlight(root = document) {
  const blocks = Array.from(root.querySelectorAll('[data-shiki-php]:not([data-shiki-ready])'))
  await Promise.all(blocks.map(async block => {
    try {
      await highlightBlock(block)
    } catch (error) {
      const status = document.createElement('div')
      status.className = 'errtk-shiki-status is-error'
      status.setAttribute('role', 'status')
      status.textContent = 'Syntax highlighting could not be loaded. Plain source remains available.'
      block.append(status)
      block.dataset.shikiReady = 'fallback'
    }
  }))
}

if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => highlight(), { once: true })
  } else {
    highlight()
  }
}

if (typeof window !== 'undefined') {
  window.PHPFusionShiki = { highlight }
}
