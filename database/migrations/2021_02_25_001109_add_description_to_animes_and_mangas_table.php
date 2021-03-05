<?php

use App\Models\Anime;
use App\Models\Manga;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToAnimesAndMangasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table((new Anime())->getTable(), function (Blueprint $table) {
            $table->mediumText('description')->nullable();
        });

        Schema::table((new Manga())->getTable(), function (Blueprint $table) {
            $table->mediumText('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new Anime())->getTable(), function (Blueprint $table) {
            //
        });

        Schema::table((new Manga())->getTable(), function (Blueprint $table) {
            //
        });
    }
}
