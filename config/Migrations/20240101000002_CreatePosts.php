<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreatePosts extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('posts');
        $table->addColumn('title', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('body', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('slug', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('published', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('blog_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex([
            'blog_id',
            'slug',
        ], [
            'name' => 'UNIQUE_BLOG_SLUG',
            'unique' => true,
        ]);
        $table->addForeignKey('blog_id', 'blogs', 'id', [
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ]);
        $table->create();
    }
}
