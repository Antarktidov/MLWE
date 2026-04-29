const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


async function approveRev() {
    event.preventDefault();

    let response = await fetch(`/userprofile-global/${upRevId}/approve`, {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json;charset=utf-8',
            'X-CSRF-TOKEN': csrf_token,
            },
        });
}

async function deleteProfile() {
    event.preventDefault();

    let response = await fetch(`/userprofile-global/${userId}/delete`, {
            method: 'DELETE',
            headers: {
            'Content-Type': 'application/json;charset=utf-8',
            'X-CSRF-TOKEN': csrf_token,
            },
        });
}

async function addFriend() {
    event.preventDefault();

    let response = await fetch(`/api/friends/add_friend/${userId}`, {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json;charset=utf-8',
            'X-CSRF-TOKEN': csrf_token,
            },
        });
}