<script>
    let {userId} = $props();
    let user_friends = $state([]);
    let isFriendsLoaded = $state(false);

    async function loadFriends(page = 1) {
        isFriendsLoaded = false;
        const res = await fetch(`/api/user/friends/${userId}?page=${page}`);
        const json = await res.json();
        user_friends = json.user_friends;
        console.log(json);
        isFriendsLoaded = true;
    }

    loadFriends();
</script>
<div class="svelte-comments-body">
    {#if user_friends.length > 0}
        <div class="friends-avatars">
            {#each user_friends as friend}
                <div class="friend">
                    <a href="/userprofile-global/{friend.id}" style="color: inherit; text-decoration: none;">
                    {#if friend.avatar != null}
                        <div title="{friend.name}" class="friend-avatar" style="background: {friend.avatar}">
                        </div>
                    {:else}
                        <div title="{friend.name}" class="friend-avatar" style="background: gray;"> ?
                        </div>
                    {/if}
                    </a>
                </div>
            {/each}
        </div>
    {:else if !isFriendsLoaded}
        <p>Друзья загружаются</p>
    {:else if isFriendsLoaded}
        <p>Нет друзей : (</p>
    {/if}
</div>