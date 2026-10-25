<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Likes Controller
 *
 * @property \App\Model\Table\PostsTable $Posts
 * @property \App\Model\Table\LikesTable $Likes
 */
class LikesController extends AppController
{
    /**
     * @param \Cake\Event\EventInterface $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['toggle']);
    }

    /**
     * Toggle the current visitor's like on a post.
     *
     * Each visitor (identified by a hash of their IP address) can add at
     * most one like per post. Clicking again removes it.
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null JSON for AJAX requests, redirect otherwise.
     */
    public function toggle(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        $posts = $this->fetchTable('Posts');
        $likes = $this->fetchTable('Likes');

        $post = $posts->get($id);
        $hash = $likes->hashIp($this->request->clientIp());

        $liked = $likes->toggle((int)$post->id, $hash);
        $count = $likes->countForPost((int)$post->id);

        if ($this->request->is('ajax')) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'liked' => $liked,
                    'count' => $count,
                ], JSON_THROW_ON_ERROR));
        }

        return $this->redirect($this->referer(['controller' => 'Posts', 'action' => 'view', $post->id]));
    }
}
