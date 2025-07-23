<?php

namespace Database\Seeders;

use App\Models\VitaGlyphUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VitaGlyphUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 random users
        VitaGlyphUser::factory()->count(5)->create();
        
        // Create specific test cases using your factory states
        VitaGlyphUser::factory()
            ->withAllSensors()
            ->count(5)
            ->create();
            
        VitaGlyphUser::factory()
            ->filipinoSpeaker()
            ->count(5)
            ->create();
            
        VitaGlyphUser::factory()
            ->privacyConscious()
            ->count(5)
            ->create();
    }
}