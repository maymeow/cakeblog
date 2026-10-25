<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Like Entity
 *
 * @property int $id
 * @property int $post_id
 * @property string $ipAddressHash
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Post $post
 */
class Like extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'post_id' => true,
        'ipAddressHash' => true,
        'created' => true,
        'modified' => true,
        'post' => true,
    ];

    /**
     * Fields excluded from JSON serialization.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'ipAddressHash',
    ];
}
