const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            async function votePoll(variantIdx) {
                var vote = {
                    'variant_idx': variantIdx
                };
                var response = await fetch(`/api/polls/accept_vote/${pollId}`, {
                    method: 'POST',
                    headers: {
                    'Content-Type': 'application/json;charset=utf-8',
                    'X-CSRF-TOKEN': csrf_token,
                    },
                    body: JSON.stringify(vote)
                    });
                console.log(response);
                if (response.ok === true) {
                    document.querySelectorAll('.variant').forEach((el) => {
                        el.setAttribute('onclick', '');
                    })
                    fetchVotes();
                }
            }

            if (userAlreadyVotedInPull) {
                fetchVotes();
            }

            function fetchVotes() {
            var url = `/api/polls/get_votes/${pollId}`;

            fetch(url, { method: 'GET' })
            .then(Result => Result.json())
            .then(data => {
                console.log("Данные опроса", data);
                var poll_data = data[0];
                var poll_total = data[1][0];
                var my_vote_idx = data[2][0].variant_idx;

                console.error(my_vote_idx);
                poll_data.forEach((pollElem) => {
                    console.log('pollElem', pollElem);
                    var percent = pollElem.count / poll_total.count;
                    var pollHtmlEl = document.querySelector(`[data-poll-answer_idx="${pollElem.variant_idx}"]`);
                    var pollHtmlElChild = document.createElement('div');
                    pollHtmlElChild.style.width = (percent * 110) +  '%';
                    pollHtmlElChild.classList.add('sub-variant');

                    if (my_vote_idx === pollElem.variant_idx) {
                        pollHtmlElChild.classList.add('my-sub-variant');
                    }

                    pollHtmlEl.append(pollHtmlElChild);
                })
            })
            .catch(errorMsg => { console.log(errorMsg); });
            }