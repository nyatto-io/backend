<?php

use App\Models\Anime;
use App\Models\File;
use App\Models\Manga;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToMangasAndAnimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fileTable = (new File())->getTable();
        Schema::table((new Anime())->getTable(), function (Blueprint $table) use ($fileTable) {
            $table->foreignIdFor(new File(), 'image_id')->constrained($fileTable);
        });
        Schema::table((new Manga())->getTable(), function (Blueprint $table) use ($fileTable) {
            $table->foreignIdFor(new File(), 'image_id')->constrained($fileTable);
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
