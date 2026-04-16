<script>
  import MarkdownIt from 'markdown-it';
  let { wikiName, articleName, userId, userName, userCanDeleteComments, userCanApproveComments } = $props();
//data-user-can-approve-comments
  let comments = $state([]);
  let new_comment = $state('');
  let edited_comment = $state('');
  let edited_comment_id = 0;
  let currentPage = $state(1);
  let meta = $state({});

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


  console.log('Пропсы:', wikiName, articleName, userId, userName, userCanDeleteComments, userCanApproveComments);

  async function loadComments(page = 1) {
    const res = await fetch(`/api/wiki/${wikiName}/article/${articleName}/comments?page=${page}`);
    const json = await res.json();
    comments = json.data;
    meta = json.meta;
    currentPage = meta.current_page;
    console.log(comments);
    console.log('userCanApproveComments', userCanApproveComments);
  }
  loadComments();

  async function postComment() {
    let comment = {
      'content': new_comment
    };

    let response = await fetch(`/api/wiki/${wikiName}/article/${articleName}/comments/store`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json;charset=utf-8',
      'X-CSRF-TOKEN': csrf_token,
    },
      body: JSON.stringify(comment)
    });
    const resJson = await response.json();
    const commentId = resJson.id;
    
    comments.unshift({
      'id': commentId,
      'user_id': userId,
      'user_name': userName,
      'content': md.render(new_comment),
      'markdown_content': new_comment,
      'created_at': __('Just now'),
    });
    console.log('Обновлённые комменты: ', comments);
    new_comment = '';
  }

  async function deleteComment(commentId) {
    console.log('Delete btn pressed');
    let response = await fetch(`/api/wiki/${wikiName}/article/${articleName}/comments/${commentId}/delete`, {
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
    let response = await fetch(`/api/wiki/${wikiName}/article/${articleName}/comments/${commentId}/approve`, {
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
    let comment = comments.find(comment => comment.id === commentId);
    console.log('Комент, выбранный для редактирования:', comment);
    comment.is_editor_open = true;
    edited_comment = comment.markdown_content;
  }

  async function saveEditedComment(commentId) {
    let toSaveEditedComment = {
      'content': edited_comment,
    }

    try {
      let response = await fetch(`/api/wiki/${wikiName}/article/${articleName}/comments/${commentId}/update`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8',
          'X-CSRF-TOKEN': csrf_token,
        },
        body: JSON.stringify(toSaveEditedComment)
      });

      const result = await response.json();
      
      if (response.ok) {
        let comment = comments.find(comment => comment.id === commentId);
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
    let comment = comments.find(comment => comment.id === commentId);
    comment.is_editor_open = false;
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
  <div class="d-flex">
    <textarea bind:value={new_comment} class="form-control" placeholder={__('Enter new comment')}></textarea>
    <button onclick={() => postComment()} class="btn btn-primary ms-4">{__('Send')}</button>
  </div>
  {#if comments.length > 0}
    <div>
      {#each comments as comment, index (comment.id + '-' + index)}
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