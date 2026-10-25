/**
 * Progressive enhancement for the post like button.
 *
 * The button works without JavaScript as a normal POST form that toggles the
 * like and reloads the page. When JavaScript is available the toggle happens
 * in place through a small fetch request.
 */
(function () {
    'use strict';

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form.classList || !form.classList.contains('like-form')) {
            return;
        }

        event.preventDefault();

        var button = form.querySelector('.like-button');
        var icon = form.querySelector('.like-icon');
        var count = form.querySelector('[data-like-count]');

        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed');
                }

                return response.json();
            })
            .then(function (data) {
                if (button) {
                    button.classList.toggle('is-liked', data.liked);
                    button.setAttribute(
                        'title',
                        data.liked
                            ? button.getAttribute('data-unlike-title')
                            : button.getAttribute('data-like-title')
                    );
                }

                if (icon) {
                    icon.textContent = data.liked ? '\uD83E\uDDE1' : '\uD83E\uDD0D';
                }

                if (count) {
                    count.textContent = data.count;
                }
            })
            .catch(function () {
                form.submit();
            });
    });
})();
