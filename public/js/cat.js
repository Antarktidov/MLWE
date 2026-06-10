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

async function addCat() {
    var new_category_input = document.querySelector('[name="new-category"]');
    var categories_list_selector = document.querySelector('.categories-list');
    var category_name = new_category_input.value;
    var data = {
        'category_name': category_name
    };
    let response = await fetch(`/api/add_category/${article_id}`, {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json;charset=utf-8',
            'X-CSRF-TOKEN': csrf_token,
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        console.log(result);
        var category_id = result[1].category_id;
        var category_id_str = category_id.toString();
        console.log(category_id);
        if (response.ok === true) {
            new_category_input.value = '';
            categories_list_selector.innerHTML += `<span class="border rounded p-1 category-item m-1" data-cat-id="${escapeHTML(category_id_str)}">
                <span class="cat-item-body">${escapeHTML(category_name)}</span>
                <span class="cat-item-remove text-danger" onclick="removeCat(${escapeHTML(category_id_str)})">x</span>
            </span>
            `;
        }
}