<?php
declare(strict_types=1);

namespace App\Controller;

use App\View\RssView;
use Cake\View\JsonView;

/**
 * Posts Controller
 *
 * @property \App\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    public function viewClasses(): array
    {
        return [JsonView::class, RssView::class];
    }


    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index', 'view', 'addComment']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $currentBlog = $this->request->getAttribute('currentBlog');

        $query = $this->Posts->find('published')
            ->contain(['Blogs']);

        if ($currentBlog) {
            $query->where(['Posts.blog_id' => $currentBlog->id]);
            $query->where(['Posts.pinned' => false]);
            $query->where(['Posts.slug NOT IN' => ['home', $currentBlog->slug]]);
        } else {
            $query->innerJoinWith('Blogs', function ($q) {
                return $q->where(['Blogs.is_public' => true]);
            });
        }

        // Add approved comment count and total like count
        $query->select($this->Posts)
            ->select(['comment_count' => $this->Posts->Comments->find()
                ->where(['Comments.post_id = Posts.id', 'Comments.is_approved' => true])
                ->select($this->Posts->Comments->find()->func()->count('Comments.id')),
            ])
            ->select(['like_count' => $this->Posts->Likes->find()
                ->where(['Likes.post_id = Posts.id'])
                ->select($this->Posts->Likes->find()->func()->count('Likes.id')),
            ]);

        $posts = $this->paginate($query);

        $this->set(compact('posts'));
        $this->viewBuilder()->setOption('serialize', ['posts']);
    }

    /**
     * View method
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $post = $this->Posts->get($id, contain: [
            'Blogs',
            'Comments' => function ($q) {
                return $q->find('approved');
            },
        ]);

        $comment = $this->Posts->Comments->newEmptyEntity();

        $likeHash = $this->Posts->Likes->hashIp($this->request->clientIp());
        $likeCount = $this->Posts->Likes->countForPost((int)$post->id);
        $liked = $this->Posts->Likes->hasLiked((int)$post->id, $likeHash);

        $this->set(compact('post', 'comment', 'likeCount', 'liked'));
    }

    /**
     * Add a comment to a post.
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null Redirects back to the post.
     */
    public function addComment($id = null)
    {
        $this->request->allowMethod(['post']);

        $post = $this->Posts->get($id, contain: ['Blogs']);

        if (!$post->comments_enabled) {
            $this->Flash->error(__('Comments are not allowed on this post.'));

            return $this->redirect(['action' => 'view', $post->id]);
        }

        $comment = $this->Posts->Comments->newEmptyEntity();
        $comment = $this->Posts->Comments->patchEntity($comment, $this->request->getData());
        $comment->post_id = (int)$post->id;
        $comment->is_approved = false;

        if ($this->Posts->Comments->save($comment)) {
            $this->Flash->success(__('Your comment has been submitted and is awaiting approval.'));
        } else {
            $this->Flash->error(__('Your comment could not be saved. Please check for errors and try again.'));
        }

        return $this->redirect(['action' => 'view', $post->id]);
    }
}
