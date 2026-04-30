<script>
    let { wikiName, userId, userName, userModerateDiscussions, wikiId } = $props();

    async function fetchAvatar() {
        const res = await fetch(`/api/avatar/{userId}/{wikiId}`);
        const json = await res.json();
        user_friends = json.user_friends;
        pagination = json.pagination;
        currentPage = pagination.page;
        console.log(json);
    }
    ///api/avatar/{user}/{wiki}
    fetchAvatar();
</script>
<div>
    <div class="editor-placeholder">
        <div class="avatar-wrapper">
            {#if userId !== 0}
                <div></div>
            {:else}
                <div></div>
            {/if}
        </div>
    </div>
</div>
<style>
.editor-placeholder {
    border: 1px solid;
    border-radius: 10px;
    height: 100px;
    background-color: var(--bs-tertiary-bg);
}
</style>