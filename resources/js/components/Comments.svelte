<script>
  import MarkdownIt from 'markdown-it';
  let { wikiName, articleName, pageType = 'article', userId, userName, userCanDeleteComments, userCanApproveComments } = $props();

  const pageSegment = pageType;
//data-user-can-approve-comments
  let comments = $state([]);
  let new_comment = $state('');
  let edited_comment = $state('');
  let edited_comment_id = 0;
  let currentPage = $state(1);
  let meta = $state({});

  let avatar = $state("");

  //Код локализации интерфейса
  import ru from '../../../lang/ru.json';
  import en from '../../../lang/en.json';

  const translations = { ru, en };
  const locale = window.locale || 'en';

  function __(key) {
    return translations[locale][key] || key;
  }
  //Конец кода локализации интерфеса

  const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const md = new MarkdownIt();

  function findCommentById(items, commentId) {
    for (const item of items || []) {
      if (item.id === commentId) {
        return item;
      }

      if (Array.isArray(item.children) && item.children.length > 0) {
        const nestedComment = findCommentById(item.children, commentId);
        if (nestedComment) {
          return nestedComment;
        }
      }
    }

    return null;
  }

  console.log('Пропсы:', wikiName, articleName, userId, userName, userCanDeleteComments, userCanApproveComments);

  async function loadComments(page = 1) {
    const res = await fetch(`/api/wiki/${wikiName}/${pageSegment}/${articleName}/comments?page=${page}`);
    const json = await res.json();
    comments = json.data;
    comments.new_reply = '';
    meta = json.meta;
    currentPage = meta.current_page;
    console.log(comments);
    console.log('userCanApproveComments', userCanApproveComments);
  }
  loadComments();

  async function fetch_avatar() {
    const res = await fetch(`/api/avatar/${userId}`);
    const json = await res.json();
    avatar = json.avatar;
    console.log(avatar);
  }
  fetch_avatar();

  async function postComment(parent_id = null) {
    if (parent_id === null) {
      var comment = {
      'content': new_comment,
      };
    } else {
      //comments = comments.filter(comment => comment.id !== commentId);
      var main_comment = comments.find(comment => comment.id === parent_id);
      var comment = {
      'content': main_comment.new_reply,
      'parent_id': parent_id,
      };
    }

    console.log('Новый коммент или ответ:', comment);

    let response = await fetch(`/api/wiki/${wikiName}/${pageSegment}/${articleName}/comments/store`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json;charset=utf-8',
      'X-CSRF-TOKEN': csrf_token,
    },
      body: JSON.stringify(comment)
    });
    const resJson = await response.json();
    const commentId = resJson.id;

    if (parent_id === null) {
        comments.unshift({
        'id': commentId,
        'user_id': userId,
        'user_name': userName,
        'content': md.render(new_comment),
        'markdown_content': new_comment,
        'created_at': __('Just now'),
        'avatar': avatar,
        'children': [],
        'new_reply': '',
        
      });
      new_comment = '';
    } else {
      main_comment.children.push({
        'id': commentId,
        'user_id': userId,
        'user_name': userName,
        'content': md.render(main_comment.new_reply),
        'markdown_content': main_comment.new_reply,
        'created_at': __('Just now'),
        'avatar': avatar,
    });
    main_comment.new_reply = '';
    }
    
    console.log('Обновлённые комменты: ', comments);
    new_comment = '';
  }

  async function deleteComment(commentId) {
    console.log('Delete btn pressed');
    let response = await fetch(`/api/wiki/${wikiName}/${pageSegment}/${articleName}/comments/${commentId}/delete`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json;charset=utf-8',
      'X-CSRF-TOKEN': csrf_token,
    },
    });
    comments = comments.filter(comment => comment.id !== commentId);
    console.log(response);
  }

  async function approveComment(commentId) {
    console.log('Approve btn pressed');
    let response = await fetch(`/api/wiki/${wikiName}/${pageSegment}/${articleName}/comments/${commentId}/approve`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json;charset=utf-8',
      'X-CSRF-TOKEN': csrf_token,
      },
    });
    console.log(response);
  }

  function openCommentEditor(commentId) {
    closeEditedComment(edited_comment_id)
    edited_comment_id = commentId;
    console.log('Edit btn pressed');
    let comment = findCommentById(comments, commentId);
    console.log('Комент, выбранный для редактирования:', comment);

    if (comment) {
      comment.is_editor_open = true;
      edited_comment = comment.markdown_content ?? '';
    }
  }

  async function saveEditedComment(commentId) {
    let toSaveEditedComment = {
      'content': edited_comment,
    }

    try {
      let response = await fetch(`/api/wiki/${wikiName}/${pageSegment}/${articleName}/comments/${commentId}/update`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8',
          'X-CSRF-TOKEN': csrf_token,
        },
        body: JSON.stringify(toSaveEditedComment)
      });

      const result = await response.json();
      
      if (response.ok) {
        let comment = findCommentById(comments, commentId);
        if (comment) {
          comment.content = md.render(edited_comment);
          comment.markdown_content = edited_comment;
        }
        
        closeEditedComment(edited_comment_id);
        edited_comment_id = 0;
        console.log('Save btn pressed - success');
      } else {
        console.error('Ошибка при сохранении комментария:', result.error);
        alert('Ошибка при сохранении комментария: ' + result.error);
      }
    } catch (error) {
      console.error('Ошибка сети при сохранении комментария:', error);
      console.error('Ошибка сети при сохранении комментария');
    }
  }

  function closeEditedComment(commentId) {
    if (commentId === 0) {
      return;
    }

    console.log('Close btn pressed');
    let comment = findCommentById(comments, commentId);

    if (comment) {
      comment.is_editor_open = false;
    }

    edited_comment = '';

  }

  function goToPage(page) {
    if (page >= 1 && page <= meta.last_page) {
      loadComments(page);
      }
    }

</script>

<div class="comments">
  <h2>{__('Comments')}</h2>
  <h3>{__('New comment')}</h3>
  <div class="comment-and-avatar">
  {#if avatar != null}
             <div class="comment-avatar" style="background: {avatar}">
             </div>
          {:else}
              <div class="comment-avatar" style="background: gray;"> ?
              </div>
          {/if}
  <div class="d-flex">
    <textarea bind:value={new_comment} class="form-control" placeholder={__('Enter new comment')}></textarea>
    <button onclick={() => postComment()} class="btn btn-primary ms-4">{__('Send')}</button>
  </div>
  </div>
  {#if comments.length > 0}
    <div class="mt-4">
      {#each comments as comment, index (comment.id)}
      <div class="mb-4">
      <div class="comment-and-avatar">
        {#if comment.avatar != null}
             <div title="{comment.user_name}" class="comment-avatar mt-4" style="background: {comment.avatar}">
             </div>
          {:else}
              <div title="{comment.user_name}" class="comment-avatar mt-4" style="background: gray;"> ?
              </div>
          {/if}
        <div class="card mt-4 p-2">
          <div class="d-flex">
            <span class="fw-bold">{comment.user_name}</span><span class="ms-auto fst-italic text-secondary">{comment.created_at}</span>
          </div>
          <div class="p2 mt-2">
            {#if comment.is_editor_open == undefined || comment.is_editor_open == null || comment.is_editor_open === false}
              {@html comment.content}
            {/if}
          </div>
          {#if comment.is_editor_open == undefined || comment.is_editor_open == null || comment.is_editor_open === false}
          <div class="ms-auto">
            {#if userId !== 0 && comment.user_id !== 0 && +userId === +comment.user_id}
              <span>
                <button onclick={() => openCommentEditor(comment.id)} class="btn btn-primary">{__('Edit')}</button>
              </span>  
            {/if}
            <span>
              {#if userCanDeleteComments}
               <span>
                <button onclick={() => deleteComment(comment.id)} class="btn btn-danger">{__('Delete')}</button>
              </span>
              {/if}
            </span>
            <span>
              {#if userCanApproveComments && !comment.is_approved}
               <span>
                <button onclick={() => approveComment(comment.id)} class="btn btn-success">{__('Approve')}</button>
              </span>
              {/if}
            </span>
          </div>
          {:else}
          <div class="d-flex">
            <textarea bind:value={edited_comment} class="form-control" ></textarea>
            <button onclick={() => closeEditedComment(comment.id)} class="btn btn-danger ms-2">{__('Close')}</button>
            <button onclick={() => saveEditedComment(comment.id)} class="btn btn-primary ms-2">{__('Save')}</button>
          </div>
          {/if}
          
        </div>
        </div>
    <div class="replies">
      {#if comment.children.length > 0}
        {#each comment.children as rep, index (rep.id)}
        <div class="comment-and-avatar">
        {#if rep.avatar != null}
             <div title="{rep.user_name}" class="comment-avatar mt-4" style="background: {rep.avatar}">
             </div>
          {:else}
              <div title="{rep.user_name}" class="comment-avatar mt-4" style="background: gray;"> ?
              </div>
          {/if}
          <div class="card mt-4 p-2">
            <div class="d-flex">
              <span class="fw-bold">{rep.user_name}</span><span class="ms-auto fst-italic text-secondary">{rep.created_at}</span>
            </div>
            <div class="p2 mt-2">
              {#if rep.is_editor_open == undefined || rep.is_editor_open == null || rep.is_editor_open === false}
                {@html rep.content}
              {/if}
            </div>
            {#if rep.is_editor_open == undefined || rep.is_editor_open == null || rep.is_editor_open === false}
              <div class="ms-auto">
                {#if userId !== 0 && rep.user_id !== 0 && +userId === +rep.user_id}
                  <span>
                    <button onclick={() => openCommentEditor(rep.id)} class="btn btn-primary">{__('Edit')}</button>
                  </span>
                {/if}
                <span>
                  {#if userCanDeleteComments}
                    <span>
                      <button onclick={() => deleteComment(rep.id)} class="btn btn-danger">{__('Delete')}</button>
                    </span>
                  {/if}
                </span>
                <span>
                  {#if userCanApproveComments && !rep.is_approved}
                    <span>
                      <button onclick={() => approveComment(rep.id)} class="btn btn-success">{__('Approve')}</button>
                    </span>
                  {/if}
                </span>
              </div>
            {:else}
              <div class="d-flex">
                <textarea bind:value={edited_comment} class="form-control"></textarea>
                <button onclick={() => closeEditedComment(rep.id)} class="btn btn-danger ms-2">{__('Close')}</button>
                <button onclick={() => saveEditedComment(rep.id)} class="btn btn-primary ms-2">{__('Save')}</button>
              </div>
            {/if}
          </div>
          </div>
        {/each}
      {/if}

      <div class="reply-form-wrapper mt-4">
        <div class="comment-and-avatar">
          {#if avatar != null}
            <div class="comment-avatar" style="background: {avatar}"></div>
          {:else}
            <div class="comment-avatar" style="background: gray;">?</div>
          {/if}

          <div class="d-flex">
            <textarea bind:value={comment.new_reply} class="form-control" placeholder={__('Enter new reply')}></textarea>
            <button onclick={() => postComment(comment.id)} class="btn btn-primary ms-4">{__('Send')}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  {/each}
</div>
  <div class="pagination mt-4">
    <button class="btn btn-primary" onclick={() => goToPage(currentPage - 1)} disabled={currentPage === 1}>
      ← {__('Back')}
    </button>
    <span class="m-auto">{__('Page')} {currentPage} {__('(Page) of')} {meta.last_page}</span>
    <button class="btn btn-primary" onclick={() => goToPage(currentPage + 1)} disabled={currentPage === meta.last_page}>
      {__('Forward')} →
    </button>
  </div>
  {:else}
    <p>{__('No comments yet.')}</p>
  {/if}
</div>
<style>
  .comment-and-avatar {
    display: grid;
    grid-template-columns: 40px 1fr;
    gap: 10px;
}
.comment-avatar {
    height: 40px;
    width: 40px;
    border-radius: 12px;
    
    display: flex;
    align-items: center;
    justify-content: center;
    background-size: cover !important;
}
.replies {
    margin-left: 50px;
}
</style>