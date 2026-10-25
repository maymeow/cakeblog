# Add a simple, anonymous like system for posts

> PR text – copy/paste this into the GitHub pull request description.

## Summary

Adds a lightweight like system for blog posts. A visitor can click a heart
below an article to like it, and click it again to remove the like. No admin
management, users, or accounts are involved – only an increase/decrease
function.

## Why

Post reactions are a familiar, low-friction way for readers to engage without
having to register or leave a comment. The goal here is deliberately minimal:
one counter and a toggle.

## How it works

- Each visitor is identified by a **salted hash of their IP address**
  (`LikesTable::hashIp()`, HMAC-SHA256 with the app salt). The raw IP is never
  stored, only the hash.
- A like can be toggled from the post page:
  - If the visitor has no like for the post yet, one is created.
  - If they already liked it, the like is removed.
- A unique index on `(post_id, ipAddressHash)` guarantees that **a single hash
  can add at most one like per post**, even under concurrent requests.
- The total number of likes is shown on the index page right after the comment
  count, and on the post page inside the heart button.

## Changes

### Database

- `config/Migrations/20260518155909_CreateLikes.php`
  - `post_id` is now `integer` (matches `posts.id`, was `string`).
  - `ipAddressHash` is `string(255)` so it can be indexed (a `text` column
    cannot be fully indexed on MySQL).
  - Added a unique index `UNIQUE_LIKES_POST_HASH` on
    `(post_id, ipAddressHash)`.
  - Added a foreign key `post_id -> posts.id` with `ON DELETE CASCADE`.

### Model

- `src/Model/Entity/Like.php` – new entity; `ipAddressHash` is hidden from JSON.
- `src/Model/Table/LikesTable.php` – new table with:
  - `hashIp()` – builds the anonymous visitor hash.
  - `hasLiked()`, `countForPost()`.
  - `toggle()` – creates or removes the like and reports the resulting state.
  - Validation + a unique rule on `(post_id, ipAddressHash)`.
- `src/Model/Table/PostsTable.php` – `hasMany('Likes', ['dependent' => true])`.
- `src/Model/Entity/Post.php` – exposes the `likes` association.

### Controller

- `src/Controller/LikesController.php` – new controller with a single `toggle`
  action:
  - `POST` only.
  - Allowed for unauthenticated visitors.
  - Returns JSON `{ "liked": bool, "count": int }` for AJAX requests, otherwise
    redirects back to the post.

### Views & assets

- `templates/Posts/view.php` – heart button (🤍 / 🧡) with the live count below
  the article body.
- `templates/Posts/index.php` – like count `🧡 N` displayed after the comment
  count in every homepage layout.
- `webroot/js/likes.js` – progressive enhancement: toggles in place via `fetch`.
  Without JavaScript the same button works as a normal form POST.
- `webroot/css/maymeow.css` – styles for the button and count badge.

## Testing

Added tests (all green):

- `tests/TestCase/Model/Table/LikesTableTest.php`
  - hash is deterministic and does not expose the raw IP
  - toggle creates then removes the like and updates the count
  - one hash yields exactly one like per post
- `tests/TestCase/Controller/LikesControllerTest.php`
  - the post page renders the heart button and count
  - the index page renders the like count
  - only `POST` is allowed (GET returns `405`)
  - AJAX toggle returns the correct JSON state on the first and second request
- `tests/Fixture/LikesFixture.php`

Run them with:

```bash
vendor/bin/phpunit tests/TestCase/Model/Table/LikesTableTest.php \
  tests/TestCase/Controller/LikesControllerTest.php
```

## Notes / considerations

- CSRF is handled automatically through `FormHelper` (hidden `_csrfToken`), so
  the feature works with the existing `CsrfProtectionMiddleware`.
- Visitors sharing a NAT/proxy share one IP and therefore one like per post.
  This is an accepted trade-off of a login-free system.
- The app uses `bin/cake`, which currently fails on this machine because of a
  `cakephp/migrations` command type mismatch. The migration was applied through
  `Migrations\Migrations` directly; a `composer install`/update is recommended.

## Checklist

- [x] Migration added/updated
- [x] Model, entity and controller implemented
- [x] Templates and assets updated
- [x] Tests added and passing
- [x] No admin/management UI added (by design)
