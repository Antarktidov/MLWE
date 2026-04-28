<script>
    let {userId} = $props();
    let user_friends = $state([]);

    async function loadFriends(page = 1) {
        const res = await fetch(`/api/user/friends/${userId}?page=${page}`);
        const json = await res.json();
        user_friends = json.user_friends;
        //meta = json.meta;
        //currentPage = meta.current_page;
        console.log(json);
    }

    loadFriends();
</script>
<div class="svelte-comments-body">
    {#if user_friends.length > 0}
        <div class="friends-avatars">
            {#each user_friends as friend}
                <div class="friend">
                    {#if friend.avatar != null}
                        <div class="friend-avatar" style="background: {friend.avatar}">
                        </div>
                    {:else}
                        <div class="friend-avatar" style="background: gray;"> ?
                        </div>
                    {/if}
                </div>
            {/each}
        </div>
    {/if}
</div>