import './bootstrap'
import { createInertiaApp } from '@inertiajs/svelte'
import { mount } from 'svelte'

console.info('[MLWE] app.js загружен')

createInertiaApp({
  setup({ el, App, props }) {
    new App({ target: el, props })
  },
})

void (async () => {
    const el = document.getElementById('comments')
    if (!el) {
        return
    }
    const { default: Comments } = await import('./components/Comments.svelte')
    mount(Comments, {
        target: el,
        props: {
            wikiName: el.dataset.wikiName,
            articleName: el.dataset.articleName,
            userId: el.dataset.userId,
            userName: el.dataset.userName,
            userCanDeleteComments: el.dataset.userCanDeleteComments === 'true',
            userCanApproveComments: el.dataset.userCanApproveComments === 'true',
        },
    })
})()

void (async () => {
    const el = document.getElementById('user-friends-svelte')
    if (!el) {
        return
    }
    const { default: Friends } = await import('./components/Friends.svelte')
    mount(Friends, {
        target: el,
        props: {
            userId: el.dataset.userId,
        },
    })
})()
