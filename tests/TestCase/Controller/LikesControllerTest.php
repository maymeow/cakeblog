<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\LikesController Test Case
 */
class LikesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Likes',
        'app.Posts',
        'app.Blogs',
    ];

    /**
     * The post view renders the like button with the current count.
     *
     * @return void
     */
    public function testPostViewRendersLikeButton(): void
    {
        $this->get('/posts/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('like-button');
        $this->assertResponseContains('like-count');
        $this->assertResponseContains('js/likes.js');
    }

    /**
     * The index page shows the like count next to each post.
     *
     * @return void
     */
    public function testIndexRendersLikeCount(): void
    {
        $blogs = $this->getTableLocator()->get('Blogs');
        $blog = $blogs->get(1);
        $blog->is_public = true;
        $blogs->saveOrFail($blog);

        $this->get('/posts');

        $this->assertResponseOk();
        $this->assertResponseContains('like-count');
    }

    /**
     * Only POST is allowed.
     *
     * @return void
     */
    public function testToggleAllowsOnlyPost(): void
    {
        $this->get('/likes/toggle/1');

        $this->assertResponseCode(405);
    }

    /**
     * An AJAX request returns the new state and count.
     *
     * @return void
     */
    public function testToggleAjax(): void
    {
        $this->enableCsrfToken();
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);

        $this->post('/likes/toggle/1');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $data = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($data['liked']);
        $this->assertSame(2, $data['count']);

        // Posting again removes the like. Headers are consumed per request.
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);
        $this->post('/likes/toggle/1');

        $data = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse($data['liked']);
        $this->assertSame(1, $data['count']);
    }
}
