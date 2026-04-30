<script>
    let { wikiName, userId, userName, userModerateDiscussions, wikiId } = $props();
    let userAvatarBg = $state(null);
    let isEditorOpen = $state(false);

    async function fetchAvatar() {
        const res = await fetch(`/api/avatar/${userId}`);
        const json = await res.json();
        userAvatarBg = json[0];
        console.log(json);
    }
    if (userId !== 0) {
        fetchAvatar();
    }

    function openEditor() {
        console.log('Открываем редактор!');
    }

    function openEditorWrapper() {
        if (event.key === 'Enter') {
            openEditor();
        }
    }
</script>
<div>
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
</style>