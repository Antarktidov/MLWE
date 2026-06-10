const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function removeCat(cat_id) {
    let response = await fetch(`/api/remove_category_from_article/${cat_id}/${article_id}`, {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json;charset=utf-8',
            'X-CSRF-TOKEN': csrf_token,
            },
        });
        if (response.ok === true) {
            var cat_selector = document.querySelector(`[data-cat-id="${cat_id}"]`);
            cat_selector.remove();
        }
}