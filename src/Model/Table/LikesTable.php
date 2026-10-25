<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Security;
use Cake\Validation\Validator;

/**
 * Likes Model
 *
 * @property \App\Model\Table\PostsTable&\Cake\ORM\Association\BelongsTo $Posts
 * @method \App\Model\Entity\Like newEmptyEntity()
 * @method \App\Model\Entity\Like newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Like> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Like get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Like findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Like patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Like> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Like|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Like saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LikesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('likes');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Posts', [
            'foreignKey' => 'post_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('post_id')
            ->notEmptyString('post_id');

        $validator
            ->scalar('ipAddressHash')
            ->maxLength('ipAddressHash', 255)
            ->requirePresence('ipAddressHash', 'create')
            ->notEmptyString('ipAddressHash');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['post_id'], 'Posts'), ['errorField' => 'post_id']);
        $rules->add($rules->isUnique(['post_id', 'ipAddressHash']), ['errorField' => 'ipAddressHash']);

        return $rules;
    }

    /**
     * Build a stable, salted hash for a visitor's IP address.
     *
     * The raw IP is never stored so likes stay anonymous.
     *
     * @param string $ip The client IP address.
     * @return string
     */
    public function hashIp(string $ip): string
    {
        return hash_hmac('sha256', $ip, Security::getSalt());
    }

    /**
     * Check whether the visitor matching the given hash already liked a post.
     *
     * @param int $postId Post id.
     * @param string $hash Visitor hash.
     * @return bool
     */
    public function hasLiked(int $postId, string $hash): bool
    {
        return $this->exists(['post_id' => $postId, 'ipAddressHash' => $hash]);
    }

    /**
     * Total number of likes for a post.
     *
     * @param int $postId Post id.
     * @return int
     */
    public function countForPost(int $postId): int
    {
        return $this->find()
            ->where(['Likes.post_id' => $postId])
            ->count();
    }

    /**
     * Toggle a like for the given post and visitor.
     *
     * Creates the like when it does not exist yet, removes it otherwise.
     * Each hash can only add a single like per post.
     *
     * @param int $postId Post id.
     * @param string $hash Visitor hash.
     * @return bool True when the post ends up liked, false when unliked.
     */
    public function toggle(int $postId, string $hash): bool
    {
        $like = $this->find()
            ->where(['Likes.post_id' => $postId, 'Likes.ipAddressHash' => $hash])
            ->first();

        if ($like !== null) {
            $this->delete($like);

            return false;
        }

        $like = $this->newEmptyEntity();
        $like->post_id = $postId;
        $like->ipAddressHash = $hash;
        $this->save($like);

        return true;
    }
}
