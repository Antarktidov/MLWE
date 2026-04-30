<script>
    let { wikiName, userId, userName, userModerateDiscussions, wikiId } = $props();
    let userAvatarBg = $state(null);

    async function fetchAvatar() {
        const res = await fetch(`/api/avatar/${userId}`);
        const json = await res.json();
        userAvatarBg = json[0];
        console.log(json);
    }
    fetchAvatar();
</script>
<div>
    <div class="editor-placeholder">
        <div class="avatar-wrapper">
            {#if userId !== 0}
                <div class="avatar" style="background: {userAvatarBg};"></div>
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

    & .avatar {
    width: 40px;
    height: 40px;
    margin-top: 30px;
    margin-left: 20px;
    }
}
.avatar {
    border-radius: 90px;
    background-size: cover !important;
}
</style>