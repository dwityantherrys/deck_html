<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chat_header_id');
            $table->smallInteger('sender')->comment('0=employee, 1=customer'); 
            $table->smallInteger('sender_id')->nullable()->comment('kalau sender=0 isi dengan employee_id yang mengirim pesan'); 
            $table->text('message')->nullable();
            $table->string('file')->nullable();
            $table->boolean('is_read')->default(false);
            $table->datetime('read_at')->nullable();
            $table->timestamps();

            $table->foreign('chat_header_id')->references('id')->on('chat_headers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}
