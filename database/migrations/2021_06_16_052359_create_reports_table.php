<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 50)->unique('name');
            $table->string('title')->nullable();
            $table->string('report_type', 10)->default('vertical');
            $table->string('tag')->nullable();
            $table->string('db_name')->nullable()->default('default');
            $table->text('sql_query');
            $table->longText('formatting')->nullable();
            $table->longText('aggregates')->nullable();
            $table->longText('pdf')->nullable();
            $table->longText('csv')->nullable();
            $table->longText('chart')->nullable();
            $table->longText('parameters')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
