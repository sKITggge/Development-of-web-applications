<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::connection('mongodb')->getMongoDB()
        ->selectCollection('posts')
        ->createIndex(['guid' => 1], ['unique' => true, 'background' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::connection('mongodb')->getMongoDB()
        ->selectCollection('posts')
        ->dropIndex('guid_1');
    }
};
