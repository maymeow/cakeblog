<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Post> $posts
 */
use App\Model\Enum\HomepageType;
$homepageType = $this->Blog->getHomepageType();
?>
<div class="posts index content">
    <?php if ($homepageType === HomepageType::HomeLatest): ?>
        <div>
            <?= $this->cell('Homepage') ?>
        </div>
    <?php endif; ?>

    <!-- Guest View: No Actions -->

    <?php if (!$posts->isEmpty()): ?>
        <?php if ($homepageType === HomepageType::HomeLatest): ?>
            <h3>🐰 <?= __('Latest posts') ?></h3>
            <ul>
                <?php foreach ($posts as $post): ?>
                    <li>
                        <?= $this->Html->link($post->title, ['action' => 'view', $post->id]) ?>
                        <?php if (isset($post->comment_count) && $post->comment_count > 0): ?>
                            <span class="comment-count" title="<?= __('Comments') ?>">💬 <?= (int)$post->comment_count ?></span>
                        <?php endif; ?>
                        <span class="like-count" title="<?= __('Likes') ?>">🧡 <?= (int)($post->like_count ?? 0) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif ($homepageType === HomepageType::PostsList): ?>
            <ul class="posts-list" style="list-style: none; padding: 0;">
                <?php foreach ($posts as $post): ?>
                    <li style="margin-bottom: 0.5rem;">
                        <span class="date"
                            style="margin-right: 1rem; color: var(--text-color); opacity: 0.7;"><?= h($post->created->format('Y-m-d')) ?></span>
                        <?= $this->Html->link($post->title, ['action' => 'view', $post->id]) ?>
                        <?php if (isset($post->comment_count) && $post->comment_count > 0): ?>
                            <span class="comment-count" title="<?= __('Comments') ?>">💬 <?= (int)$post->comment_count ?></span>
                        <?php endif; ?>
                        <span class="like-count" title="<?= __('Likes') ?>">🧡 <?= (int)($post->like_count ?? 0) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif ($homepageType === HomepageType::PostsExcerpt): ?>
            <div class="posts-excerpt">
                <?php foreach ($posts as $post): ?>
                    <article style="margin-bottom: 2rem;">
                        <h3>
                            <?= $this->Html->link($post->title, ['action' => 'view', $post->id]) ?>
                            <?php if (isset($post->comment_count) && $post->comment_count > 0): ?>
                                <span class="comment-count" title="<?= __('Comments') ?>">💬 <?= (int)$post->comment_count ?></span>
                            <?php endif; ?>
                            <span class="like-count" title="<?= __('Likes') ?>">🧡 <?= (int)($post->like_count ?? 0) ?></span>
                        </h3>
                        <div class="excerpt" style="margin-top: 0.5rem;">
                            <?= \Cake\Utility\Text::truncate(strip_tags($post->body_html), 200, ['exact' => false, 'html' => false]) ?>
                        </div>
                    </article>
                    <hr>
                <?php endforeach; ?>
            </div>
        <?php elseif ($homepageType === HomepageType::PostsFull): ?>
            <div class="posts-full">
                <?php foreach ($posts as $post): ?>
                    <article style="margin-bottom: 3rem;">
                        <h2>
                            <?= $this->Html->link($post->title, ['action' => 'view', $post->id]) ?>
                            <?php if (isset($post->comment_count) && $post->comment_count > 0): ?>
                                <span class="comment-count" title="<?= __('Comments') ?>">💬 <?= (int)$post->comment_count ?></span>
                            <?php endif; ?>
                            <span class="like-count" title="<?= __('Likes') ?>">🧡 <?= (int)($post->like_count ?? 0) ?></span>
                        </h2>
                        <div class="body" style="margin-top: 1rem;">
                            <?= $post->body_html ?>
                        </div>
                    </article>
                    <hr>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="paginator">
            <ul class="pagination">
                <?= $this->Paginator->prev('⬅️' . __('previous')) ?>
                <?= $this->Paginator->next('➡️' . __('next')) ?>
            </ul>
        </div>
    <?php endif; ?>
</div>