<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use League\CommonMark\GithubFlavoredMarkdownConverter;

/**
 * Post Entity
 *
 * @property int $id
 * @property string $title
 * @property string|null $body
 * @property string $slug
 * @property bool $published
 * @property int $blog_id
 * @property bool $pinned
 * @property bool $comments_enabled
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Blog $blog
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\Like[] $likes
 */
class Post extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'title' => true,
        'body' => true,
        'slug' => true,
        'published' => true,
        'blog_id' => true,
        'created' => true,
        'modified' => true,
        'blog' => true,
        'pinned' => true,
        'comments_enabled' => true,
        'comments' => true,
        'likes' => true,
    ];

    protected array $_virtual = ['body_html'];

    protected function _getBodyHtml(): string
    {
        if (empty($this->body)) {
            return '';
        }

        $converter = new GithubFlavoredMarkdownConverter();
        return $converter->convert($this->body)->getContent();
    }
}
