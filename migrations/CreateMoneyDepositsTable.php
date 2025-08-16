<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if (!$schema->hasTable('money_deposits')) {
            $schema->create('money_deposits', function (Blueprint $table) {
                $table->id();
                $table->string('tx_hash')->unique();
                $table->integer('user_id');
                $table->decimal('token_amount', 20, 10);
                $table->integer('awarded_points');
                $table->timestamps();
            });
        }

        if (!$schema->hasColumn('users', 'money_deposit_id')) {
            $schema->table('users', function (Blueprint $table) {
                $table->string('money_deposit_id')->nullable()->unique();
            });
        }
    },
    'down' => function (Builder $schema) {
        $schema->dropIfExists('money_deposits');
        $schema->table('users', function ($table) {
            $table->dropColumn('money_deposit_id');
        });
    }
];
