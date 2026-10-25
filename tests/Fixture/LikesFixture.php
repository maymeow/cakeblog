<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LikesFixture
 */
class LikesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'post_id' => 1,
                'ipAddressHash' => 'fixture-hash-one',
                'created' => '2026-05-18 15:59:09',
                'modified' => '2026-05-18 15:59:09',
            ],
        ];
        parent::init();
    }
}
