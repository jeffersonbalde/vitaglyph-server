<?php

namespace Database\Factories;

use App\Models\VitaGlyphUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VitaGlyphUserFactory extends Factory
{
    protected $model = VitaGlyphUser::class;

    public function definition(): array
    {
        $filipinoCities = [
            'Pagadian City', 'Cebu City', 'Davao City',
            'Manila', 'Quezon City', 'Zamboanga City'
        ];

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('Password123!'), // Matches your frontend validation rules
            'age' => $this->faker->numberBetween(13, 35), // Focus on Filipino youth demographic
            'gender' => $this->faker->randomElement([
                'male', 'female', 'other', 'prefer_not_to_say'
            ]),

            'email_verified_at' => $this->faker->boolean(80) ? now() : null, // 80% verified
            'email_verification_token' => $this->faker->boolean(20) ? Str::random(60) : null,

            'location' => $this->faker->randomElement($filipinoCities),
            'language_preference' => $this->faker->randomElement(['en', 'tl', 'ceb']),

            // System preferences (matches your React toggle defaults)
            'enable_facial_analysis' => $this->faker->boolean(80), // 80% enable
            'enable_physiological_analysis' => $this->faker->boolean(60),
            'store_emotional_data' => $this->faker->boolean(70),
            'store_physiological_data' => $this->faker->boolean(70),
            'data_sharing_consent' => true, // Required per your GDPR compliance

            // Device context
            'device_id' => Str::random(10),
            'camera_type' => $this->faker->randomElement(['built-in', 'external', 'none']),
            'ppg_sensor_type' => $this->faker->randomElement(['Fitbit', 'Xiaomi', 'none']),

            // Adaptive learning
            'personalization_score' => $this->faker->randomFloat(2, 0, 1),

            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory for specific test cases
     */
    public function configure()
    {
        return $this->afterCreating(function (VitaGlyphUser $user) {
            // Force camera disabled if no camera type
            if ($user->camera_type === 'none') {
                $user->update(['enable_facial_analysis' => false]);
            }

            // Force PPG disabled if no sensor
            if ($user->ppg_sensor_type === 'none') {
                $user->update(['enable_physiological_analysis' => false]);
            }
        });
    }

    /**
     * State for users with all sensors enabled
     */
    public function withAllSensors()
    {
        return $this->state([
            'enable_facial_analysis' => true,
            'enable_physiological_analysis' => true,
            'camera_type' => 'built-in',
            'ppg_sensor_type' => 'Fitbit',
        ]);
    }

    /**
     * State for Filipino language preferences
     */
    public function filipinoSpeaker()
    {
        return $this->state([
            'language_preference' => 'tl',
            'location' => $this->faker->randomElement(['Manila', 'Quezon City', 'Cebu City']),
        ]);
    }

    /**
     * State for users who declined data storage
     */
    public function privacyConscious()
    {
        return $this->state([
            'store_emotional_data' => false,
            'store_physiological_data' => false,
        ]);
    }
}
