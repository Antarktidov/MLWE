<script>
    let { wikiName, userId, userName, userModerateDiscussions, wikiId } = $props();
    let userAvatarBg = $state(null);
    let isEditorOpen = $state(false);
    let postType = $state('post');
    let triviaId = $state(null);
    let pollId = $state(null);
    let title = $state('');
    let content = $state('');
    let categories = $state([]);
    let newPostCategory = $state(0);
    $inspect('newPostCategory', newPostCategory);

    const postTypes = [
        { value: 'post', label: 'Post' },
        { value: 'trivia', label: 'Trivia Quiz' },
        { value: 'poll', label: 'Poll' }
    ];

    async function fetchAvatar() {
        const res = await fetch(`/api/avatar/${userId}`);
        const json = await res.json();
        userAvatarBg = json[0];
    }

    async function fetchCategories() {
        const res = await fetch(`/api/wiki/${wikiName}/discussions/categories`);
        const json = await res.json();
        categories = json;
        newPostCategory = categories[0].id;
    }

    if (userId !== 0) {
        fetchAvatar();
    }

    fetchCategories();

    function openEditor() {
        console.log('Открываем редактор!');
        isEditorOpen = true;
    }

    function openEditorWrapper() {
        if (event.key === 'Enter') {
            openEditor();
        }
    }
</script>
<div>
    {#if !isEditorOpen}
    <div tabindex="0" onkeydown={() => openEditorWrapper()} role="button" onclick={() => openEditor()} class="editor-placeholder">
        <div class="avatar-wrapper">
            {#if userAvatarBg != null}
                <div class="avatar" style="background: {userAvatarBg};"></div>
            {:else}
                <div class="avatar" style="background: gray;"></div>
            {/if}
        </div>
        <div class="whats-on-your-mind text-muted">Чем хочешь поделиться, {userName != null ? userName : 'Анон'}?</div>
    </div>
    {:else}
    <form action="#" method="post">
        {#if postType === 'post'}
        <input class="form-control mb-2" id="title" name="title" type="text"
        placeholder="Введите заголовок" bind:value={title}>
        <textarea class="form-control mb-3" name="content" id="content" placeholder="Введите текст"
        bind:value={content}></textarea>
        {:else if postType === 'trivia'}
        <input class="form-control mb-3" type="number" min="1" step="1" name="trivia_id" id="trivia_id"
        placeholder="Введите id trivia" bind:value={triviaId}>
        {:else if postType === 'poll'}
        <input class="form-control mb-3" type="number" min="1" step="1" name="poll_id" id="poll_id"
        placeholder="Введите id опроса" bind:value={pollId}>
        {/if}
        <button class="btn btn-success">Save</button>
        <select bind:value={postType} id="post-type" name="post-type" class="btn btn-info" aria-label="Тип записи">
            {#each postTypes as option}
                <option value={option.value}>
                    {option.label}
                </option>
            {/each}
        </select>
        <select bind:value={newPostCategory} id="category-id" name="category-id" class="btn btn-primary" aria-label="Категория поста">
            {#each categories as option}
                <option value={option.id}>
                    {option.name}
                </option>
            {/each}
        </select>
    </form>
    {/if}
</div>
<style>
.editor-placeholder {
    border: 1px solid;
    border-radius: 10px;
    height: 100px;
    background-color: var(--bs-tertiary-bg);
    display: flex;
    align-items: center;

    & .avatar {
    width: 40px;
    height: 40px;
    margin-left: 20px;
    }

    & .whats-on-your-mind {
        margin-left: 20px;
    }
}
.avatar {
    border-radius: 90px;
    background-size: cover !important;
}
#title {
    height: 100px;
}
</style>