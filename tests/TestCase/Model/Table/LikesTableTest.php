<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LikesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\LikesTable Test Case
 */
class LikesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\LikesTable
     */
    protected $Likes;

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
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Likes') ? [] : ['className' => LikesTable::class];
        $this->Likes = $this->getTableLocator()->get('Likes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Likes);

        parent::tearDown();
    }

    /**
     * The hash is deterministic and does not expose the raw IP.
     *
     * @return void
     */
    public function testHashIp(): void
    {
        $first = $this->Likes->hashIp('203.0.113.10');
        $second = $this->Likes->hashIp('203.0.113.10');

        $this->assertSame($first, $second);
        $this->assertNotSame('203.0.113.10', $first);
        $this->assertNotSame($this->Likes->hashIp('203.0.113.11'), $first);
    }

    /**
     * Toggling creates a like the first time and removes it the second time.
     *
     * @return void
     */
    public function testToggle(): void
    {
        $hash = 'toggle-hash';

        $this->assertFalse($this->Likes->hasLiked(1, $hash));
        $this->assertSame(1, $this->Likes->countForPost(1));

        $this->assertTrue($this->Likes->toggle(1, $hash));
        $this->assertTrue($this->Likes->hasLiked(1, $hash));
        $this->assertSame(2, $this->Likes->countForPost(1));

        $this->assertFalse($this->Likes->toggle(1, $hash));
        $this->assertFalse($this->Likes->hasLiked(1, $hash));
        $this->assertSame(1, $this->Likes->countForPost(1));
    }

    /**
     * A single hash only ever adds one like per post.
     *
     * @return void
     */
    public function testToggleIsIdempotentPerHash(): void
    {
        $this->Likes->toggle(1, 'unique-hash');

        $likes = $this->Likes->find()
            ->where(['post_id' => 1, 'ipAddressHash' => 'unique-hash'])
            ->count();

        $this->assertSame(1, $likes);
    }
}
