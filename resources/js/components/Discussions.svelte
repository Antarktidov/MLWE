<script>
    let { wikiName, userId, userName, userModerateDiscussions } = $props();
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