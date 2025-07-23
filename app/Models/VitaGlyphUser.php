<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitaGlyphUser extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'tbl_VitaGlyphUser';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     * Matches all fields from your registration form.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
            'email_verified_at',
    'email_verification_token',
        'age',
        'gender',
        'location',
        'language_preference',
        'enable_facial_analysis',
        'enable_physiological_analysis',
        'store_emotional_data',
        'store_physiological_data',
        'data_sharing_consent',
        'device_id',
        'camera_type',
        'ppg_sensor_type',
        'personalization_score'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'enable_facial_analysis' => 'boolean',
        'enable_physiological_analysis' => 'boolean',
        'store_emotional_data' => 'boolean',
        'store_physiological_data' => 'boolean',
        'data_sharing_consent' => 'boolean',
        'personalization_score' => 'float',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Custom: Check if user has facial analysis enabled
     */
    public function canUseFacialAnalysis(): bool
    {
        return $this->enable_facial_analysis && $this->camera_type !== 'none';
    }

    /**
     * Custom: Check if user has physiological analysis enabled
     */
    public function canUsePhysiologicalAnalysis(): bool
    {
        return $this->enable_physiological_analysis && $this->ppg_sensor_type !== 'none';
    }

    /**
     * Relationships
     */
    // public function sessions()
    // {
    //     return $this->hasMany(EmotionSession::class, 'user_id');
    // }

    // public function memeFeedbacks()
    // {
    //     return $this->hasManyThrough(
    //         MemeFeedback::class,
    //         EmotionSession::class,
    //         'user_id',
    //         'session_id'
    //     );
    // }

    /**
     * Scope: Users who consented to data sharing
     */
    public function scopeWithDataConsent($query)
    {
        return $query->where('data_sharing_consent', true);
    }

    /**
     * Scope: Users with active sensors
     */
    public function scopeWithActiveSensors($query)
    {
        return $query->where(function ($q) {
            $q->where('enable_facial_analysis', true)
              ->orWhere('enable_physiological_analysis', true);
        });
    }
}
