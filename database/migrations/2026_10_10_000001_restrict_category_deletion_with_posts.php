<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceDeleteRule('restrict');
    }

    public function down(): void
    {
        $this->replaceDeleteRule('cascade');
    }

    private function replaceDeleteRule(string $rule): void
    {
        $connection = Schema::getConnection();
        $oldName = $rule === 'restrict' ? 'posts_category_id_foreign' : 'posts_category_id_restrict_foreign';
        $newName = $rule === 'restrict' ? 'posts_category_id_restrict_foreign' : 'posts_category_id_foreign';
        if ($connection->getDriverName() === 'mysql') {
            // Replace both clauses in one ALTER so the foreign key is never left absent.
            $grammar = $connection->getSchemaGrammar();
            $table = $grammar->wrapTable('posts');
            $categories = $grammar->wrapTable('categories');
            $oldConstraint = $grammar->wrap($connection->getTablePrefix().$oldName);
            $newConstraint = $grammar->wrap($connection->getTablePrefix().$newName);
            $connection->statement("ALTER TABLE {$table} DROP FOREIGN KEY {$oldConstraint}, ADD CONSTRAINT {$newConstraint} FOREIGN KEY (`category_id`) REFERENCES {$categories} (`id`) ON UPDATE CASCADE ON DELETE {$rule}");
            return;
        }

        Schema::table('posts', function (Blueprint $table) use ($rule, $oldName, $newName, $connection) {
            $table->dropForeign($connection->getTablePrefix().$oldName);
            $table->foreign('category_id', $connection->getTablePrefix().$newName)->references('id')->on('categories')
                ->onUpdate('cascade')->onDelete($rule);
        });
    }
};
