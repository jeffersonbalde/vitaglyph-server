<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('tbl_VitaGlyphUser', function (Blueprint $table) {
            // Authentication
            $table->id('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            
            // Email verification
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_token')->nullable();

            // Personalization (Step 2)
            $table->integer('age')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->default('prefer_not_to_say');
            $table->string('location', 100)->nullable()->comment('City/Region in Philippines');
            $table->enum('language_preference', ['en', 'tl', 'ceb'])->default('en');

            // System Preferences (Step 3)
            $table->boolean('enable_facial_analysis')->default(false);
            $table->boolean('enable_physiological_analysis')->default(false);
            $table->boolean('store_emotional_data')->default(false);
            $table->boolean('store_physiological_data')->default(false);
            $table->boolean('data_sharing_consent')->default(false);

            // Device Context
            $table->string('device_id')->nullable()->comment('Browser/device fingerprint');
            $table->string('camera_type')->nullable()->comment('e.g., built-in, none');
            $table->string('ppg_sensor_type')->nullable()->comment('e.g., Fitbit, none');

            // Adaptive Learning
            $table->float('personalization_score', 3, 2)->default(0)->comment('0.00-1.00 scale');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index('device_id');
        });

        // Add constraint to enforce GDPR compliance
        DB::statement("ALTER TABLE tbl_VitaGlyphUser ADD CONSTRAINT chk_consent CHECK (data_sharing_consent = 1)");
    }

    public function down()
    {
        Schema::dropIfExists('tbl_VitaGlyphUser');
    }
};