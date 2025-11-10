<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateRssSourcesIndex extends Migration
{
    public function up()
    {
        DB::connection('mongodb')->getMongoDB()
          ->selectCollection('sources')
          ->createIndex(['url' => 1], ['unique' => true, 'background' => true]);
    }

    public function down()
    {
        DB::connection('mongodb')->getMongoDB()
          ->selectCollection('sources')
          ->dropIndex('url_1');
    }
}
