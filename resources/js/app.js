import './bootstrap';


import Comments from './components/Comments.svelte'
import Friends from './components/Friends.svelte'
import Discussions from './components/Discussions.svelte'

{
    const el = document.getElementById('comments')
    if (el) {
        new Comments({
            target: el,
            props: {
                wikiName: el.dataset.wikiName,
                articleName: el.dataset.articleName,
                userId: el.dataset.userId,
                userName: el.dataset.userName,
                userCanDeleteComments: el.dataset.userCanDeleteComments === 'true',
                userCanApproveComments: el.dataset.userCanApproveComments === 'true',
            }
        })
    }
}

{
    const el = document.getElementById('user-friends-svelte')
    if (el) {
        new Friends({
            target: el,
            props: {
                userId: el.dataset.userId,
            }
        })
    }
}

{
    const el = document.getElementById('discussions-index-root')
    if (el) {
        new Discussions({
            target: el,
            props: {
                wikiName: el.dataset.wikiName,
                userId: el.dataset.userId,
                userName: el.dataset.userName,
                userCanModerateDiscussions: el.dataset.userCanModerateDiscussions === 'true',
            }
        })
    }
}