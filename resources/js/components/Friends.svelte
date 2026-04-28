<script>
    let {userId} = $props();
    let user_friends = $state([]);
    let isFriendsLoaded = $state(false);
    let pagination = $state({});
    let currentPage = $state(1);

    //Код локализации интерфейса
    import ru from '../../../lang/ru.json';
    import en from '../../../lang/en.json';

    const translations = { ru, en };
    const locale = window.locale || 'en';

    function __(key) {
        return translations[locale][key] || key;
    }
    //Конец кода локализации интерфеса

    async function loadFriends(page = 1) {
        isFriendsLoaded = false;
        const res = await fetch(`/api/user/friends/${userId}?page=${page}`);
        const json = await res.json();
        user_friends = json.user_friends;
        pagination = json.pagination;
        currentPage = pagination.page;
        console.log(json);
        isFriendsLoaded = true;
    }

    loadFriends();

    function goToPage(page) {
    if (page >= 1 && page <= pagination.last_page) {
      loadFriends(page);
      }
    }
</script>
<div class="svelte-comments-body">
    {#if user_friends.length > 0}
    <div>
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
    </div>
    <div class="friends-pagination">
        <button class="btn btn-primary" onclick={() => goToPage(currentPage - 1)} disabled={currentPage === 1}>
            ← 
        </button>
        <button class="btn btn-primary" onclick={() => goToPage(currentPage + 1)} disabled={currentPage === pagination.last_page}>
            →
        </button>
    </div>
    {:else if !isFriendsLoaded}
        <p>Друзья загружаются</p>
    {:else if isFriendsLoaded}
        <p>Нет друзей : (</p>
    {/if}
</div>