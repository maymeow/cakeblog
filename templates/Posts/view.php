<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Post $post
 * @var \App\Model\Entity\Comment $comment
 * @var int $likeCount
 * @var bool $liked
 */
?>
<?= $this->Html->script('likes', ['block' => 'script', 'defer' => true]) ?>
<div class="row">
    <div class="column column-80">
        <div class="posts view content">
            <?= $post->body_html ?>
        </div>

        <div class="post-likes">
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Likes', 'action' => 'toggle', $post->id],
                'class' => 'like-form',
            ]) ?>
            <button type="submit" class="like-button<?= $liked ? ' is-liked' : '' ?>"
                data-like-title="<?= h(__('Like this post')) ?>"
                data-unlike-title="<?= h(__('Unlike this post')) ?>"
                title="<?= $liked ? h(__('Unlike this post')) : h(__('Like this post')) ?>">
                <span class="like-icon"><?= $liked ? '🧡' : '🤍' ?></span>
                <span class="like-count" data-like-count><?= (int)$likeCount ?></span>
            </button>
            <?= $this->Form->end() ?>
        </div>

        <?php if (!empty($post->comments)): ?>
            <section class="comments-section">
                <h3 class="comments-heading">💬 <?= __('Comments') ?> (<?= count($post->comments) ?>)</h3>
                <?php foreach ($post->comments as $c): ?>
                    <div class="comment">
                        <div class="comment-meta">
                            <strong class="comment-author">
                                <?php if (!empty($c->author_website)): ?>
                                    <a href="<?= h($c->author_website) ?>" rel="nofollow noopener" target="_blank"><?= h($c->author_name) ?></a>
                                <?php else: ?>
                                    <?= h($c->author_name) ?>
                                <?php endif; ?>
                            </strong>
                            <time class="comment-date"><?= h($c->created->format('M j, Y \a\t H:i')) ?></time>
                        </div>
                        <div class="comment-body">
                            <?= nl2br(h($c->body)) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if ($post->comments_enabled): ?>
            <section class="comment-form-section">
                <h3 class="comments-heading">✏️ <?= __('Leave a Comment') ?></h3>
                <p class="comment-note"><?= __('Your comment will be visible after approval.') ?></p>
                <?= $this->Form->create($comment, ['url' => ['action' => 'addComment', $post->id]]) ?>
                <div class="comment-form-fields">
                    <?= $this->Form->control('author_name', [
                        'label' => __('Name'),
                        'required' => true,
                        'id' => 'comment-author-name',
                    ]) ?>
                    <?= $this->Form->control('author_email', [
                        'type' => 'email',
                        'label' => __('Email'),
                        'required' => false,
                        'id' => 'comment-author-email',
                    ]) ?>
                    <?= $this->Form->control('author_website', [
                        'type' => 'url',
                        'label' => __('Website'),
                        'required' => false,
                        'id' => 'comment-author-website',
                    ]) ?>
                    <?= $this->Form->control('body', [
                        'type' => 'textarea',
                        'label' => __('Comment'),
                        'required' => true,
                        'rows' => 5,
                        'id' => 'comment-body',
                    ]) ?>
                </div>
                <?= $this->Form->button(__('Submit Comment'), ['id' => 'comment-submit']) ?>
                <?= $this->Form->end() ?>
            </section>
        <?php else: ?>
            <?php if (!empty($post->comments)): ?>
                <p class="comments-closed"><?= __('Comments are closed for this post.') ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>