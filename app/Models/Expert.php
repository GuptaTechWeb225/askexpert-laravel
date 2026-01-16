<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StorageTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Expert extends Authenticatable
{
    use HasFactory, SoftDeletes, StorageTrait, Notifiable;
    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'phone',
        'image',
        'category_id',
        'primary_specialty',
        'secondary_specialty',
        'experience',
        'certification',
        'education_degree',
        'resume',
        'country',
        'state',
        'prefer_to_answer',
        'available_to_start',
        'availability',
        'is_active',
        'status',
        'reject_reason',
        'password',
        'remember_token',
        'is_online',
        'is_busy',
        'last_active_at',
        'current_chat_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'available_to_start' => 'datetime',
        'is_online'  => 'boolean',
        'is_busy' => 'boolean',
        'last_active_at' => 'datetime',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function category()
    {
        return $this->belongsTo(ExpertCategory::class, 'category_id');
    }

    public function chats()
    {
        return $this->hasMany(AdminExpertChat::class, 'expert_id');
    }

    // Expert ke messages through chats
    public function messages()
    {
        return $this->hasManyThrough(
            AdminExpertMessage::class,
            AdminExpertChat::class,
            'expert_id', // Foreign key on AdminExpertChat table
            'admin_expert_chat_id', // Foreign key on AdminExpertMessage table
            'id', // Local key on Expert table
            'id'  // Local key on AdminExpertChat table
        );
    }

    // Expert.php mein yeh relations add kar do

    public function availability()
    {
        return $this->hasMany(ExpertAvailability::class);
    }

    public function communicationModes()
    {
        return $this->hasMany(ExpertCommunicationMode::class);
    }

    public function notificationPreferences()
    {
        return $this->hasMany(ExpertNotificationPreference::class);
    }

    public function getImageFullUrlAttribute(): array
    {
        $value = $this->image;
        if (count($this->storage) > 0) {
            $storage = $this->storage->where('key', 'image')->first();
        }
        return $this->storageLink('expert/profile', $value, $storage['value'] ?? 'public');
    }
    protected $appends = [
        'image_full_url',
        'average_rating',
        'total_reviews',
        'rating_breakdown',
    ];

    // Expert.php

    public function reviews()
    {
        return $this->hasMany(ExpertReview::class, 'expert_id');
    }
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }


    public function getTotalReviewsAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getRatingBreakdownAttribute(): array
    {
        $data = $this->reviews()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $breakdown = [];

        for ($i = 1; $i <= 5; $i++) {
            $breakdown[$i] = $data[$i] ?? 0;
        }

        return $breakdown;
    }

    // Expert.php mein
    public function earnings()
    {
        return $this->hasMany(ExpertEarning::class);
    }

    public function isModeAvailable(string $mode): bool
    {
        $modeRow = $this->communicationModes()
            ->where('mode', $mode)
            ->first();

        if (!$modeRow) {
            return true;
        }

        return $modeRow->available
            && !$modeRow->on_break
            && !$modeRow->vacation_mode;
    }
    public function blockedUsers()
    {
        return $this->hasMany(ExpertBlockedUser::class);
    }


    public function hasBlockedUser(int $userId): bool
    {
        return $this->blockedUsers()->where('user_id', $userId)->exists();
    }
    public function blockUser(int $userId)
    {
        return ExpertBlockedUser::updateOrCreate(
            ['expert_id' => $this->id, 'user_id' => $userId],
        );  
    }
    public function isAvailableNow(): bool
    {
        $today = strtolower(now()->format('l')); // 'thursday'
        $availability = $this->availability()
            ->where('day', $today)
            ->where('is_active', true)
            ->first();

        if (!$availability) {
            return true;
        }

        $now = now();

        // Convert start and end time to today ke datetime
        $start = Carbon::parse($availability->start_time);
        $end   = Carbon::parse($availability->end_time);

        return $now->between($start, $end);
    }

    public function getTotalEarnedAttribute()
    {
        return $this->earnings()->where('status', 'paid')->sum('total_amount');
    }

    public function getPendingPayoutAttribute()
    {
        return $this->earnings()->where('status', 'pending')->sum('total_amount');
    }
}
