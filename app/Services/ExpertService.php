<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use App\Models\Expert;
use App\Models\ExpertEarning;
use App\Models\ChatSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ExpertService
{
    use FileManagerTrait;

    /**
     * @return array[f_name: mixed, l_name: mixed, email: mixed, phone: mixed, country: mixed, city: mixed, zip: mixed, street_address: mixed, password: string]
     */


    public function isLoginSuccessful(string $email, string $password, string|null|bool $rememberToken): bool
    {
        if (auth('expert')->attempt(['email' => $email, 'password' => $password], $rememberToken)) {
            return true;
        }
        return false;
    }
    public function logout(): void
    {
        auth()->guard('expert')->logout();
        session()->invalidate();
    }


    public function findAvailableExpert(
        array $expertIds,
        ?int $categoryId = null,
        string $mode = 'text_chat',
        int $userId
    ) {
        $matchCategory       = getWebConfig(name: 'match_category') == 1;
        $prioritizeRatings   = getWebConfig(name: 'prioritize_ratings') == 1;
        $avoidPendingPayouts = getWebConfig(name: 'avoid_pending_payouts') == 1;

        Log::info("Finding available expert", [
            'expert_ids' => $expertIds,
            'category_id' => $categoryId,
            'mode' => $mode,
            'match_category' => $matchCategory,
            'prioritize_ratings' => $prioritizeRatings,
            'avoid_pending_payouts' => $avoidPendingPayouts,
            'user_id' => $userId
        ]);
        $query = Expert::query()
            ->whereIn('id', $expertIds)
            ->where('is_online', true)
            ->where('is_busy', false)
            ->where('is_active', true);

        if ($matchCategory && $categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($avoidPendingPayouts) {
            $query->whereDoesntHave('earnings', function ($q) {
                $q->where('status', 'pending')
                    ->groupBy('expert_id')
                    ->havingRaw('COUNT(*) > 10');
            });
        }

        if ($prioritizeRatings) {
            $query->withAvg('reviews', 'rating')
                ->orderByDesc('reviews_avg_rating')
                ->orderBy('last_active_at');
        } else {
            $query->orderBy('last_active_at');
        }

        $experts = $query
            ->with(['availability', 'communicationModes', 'blockedUsers'])
            ->get();

        foreach ($experts as $expert) {
            $available = $expert->isAvailableNow();
            $modeAvailable = $expert->isModeAvailable($mode);
            $blockedByExpert = $expert->hasBlockedUser($userId);


            Log::info("Expert check", [
                'expert_id' => $expert->id,
                'name' => $expert->f_name . ' ' . $expert->l_name,
                'available_now' => $available,
                'mode_available' => $modeAvailable,
                'blocked_user' => $blockedByExpert
            ]);
        }

        $selectedExpert = $experts->first(function ($expert) use ($mode, $userId) {
            return $expert->isAvailableNow()
                && $expert->isModeAvailable($mode)
                && !$expert->hasBlockedUser($userId);
        });

        if ($selectedExpert) {
            Log::info("Selected expert", [
                'expert_id' => $selectedExpert->id,
                'name' => $selectedExpert->f_name . ' ' . $selectedExpert->l_name
            ]);
        } else {
            Log::warning("No expert available for selection", [
                'expert_ids' => $expertIds,
                'mode' => $mode,
                'user_id' => $userId
            ]);
        }

        return $selectedExpert;
    }

    //    public function findAvailableExpert(array $expertIds, ?int $categoryId = null)
    // {
    //     $matchCategory        = getWebConfig(name: 'match_category') == 1;
    //     $prioritizeRatings    = getWebConfig(name: 'prioritize_ratings') == 1;
    //     $avoidPendingPayouts  = getWebConfig(name: 'avoid_pending_payouts') == 1;

    //     $query = Expert::query()
    //         ->whereIn('id', $expertIds)
    //         ->where('is_online', true)
    //         ->where('is_busy', false)
    //         ->where('is_active', true);

    //     if ($matchCategory && $categoryId) {
    //         $query->where('category_id', $categoryId);
    //     }  
    //     if ($avoidPendingPayouts) {
    //         $query->whereDoesntHave('earnings', function ($q) {
    //             $q->where('status', 'pending')
    //               ->groupBy('expert_id')
    //               ->havingRaw('COUNT(*) > 10');
    //         });
    //     }
    //     if ($prioritizeRatings) {
    //         $query->withAvg('reviews', 'rating')
    //               ->orderByDesc('reviews_avg_rating')
    //               ->orderBy('last_active_at');
    //     } else {
    //         $query->orderBy('last_active_at');
    //     }

    //     return $query->first();
    // }
    public function getCustomerData(object $request): array
    {
        return [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'country' => $request['country'] ?? null,
            'city' => $request['city'] ?? null,
            'zip' => $request['zip_code'] ?? null,
            'street_address' => $request['address'] ?? null,
            'password' => bcrypt($request['password'] ?? 'password')
        ];
    }

    public function deleteImage(object|null $data): bool
    {
        if ($data && $data['image']) {
            $this->delete('profile/' . $data['image']);
        };
        return true;
    }

    // app/Services/ExpertService.php  (ya jo bhi tera service hai)

    public function createExpertEarning(ChatSession $chat, string $endedBy = 'user', string $note = null)
    {
        // Prevent duplicate earning
        if (ExpertEarning::where('chat_session_id', $chat->id)->exists()) {
            return;
        }

        $category = $chat->category; // ya $chat->expert->category

        $basic = $category->expert_basic ?? 0;
        $premium = 0; // default - sirf high review pe milega

        $defaultNote = match ($endedBy) {
            'expert' => 'Chat ended by expert - Basic payout only',
            'user'   => 'Chat ended by user - Awaiting review for premium',
            default  => 'Initial basic payout'
        };

        ExpertEarning::create([
            'chat_session_id' => $chat->id,
            'expert_id'       => $chat->expert_id,
            'category_id'     => $category->id,
            'basic_amount'    => $basic,
            'premium_amount'  => $premium,
            'total_amount'    => $basic + $premium,
            'status'          => 'pending',
            'note'            => $note ?? $defaultNote,
        ]);
    }

    public function addPremiumIfEligible(ChatSession $chat, int $rating)
    {
        $earning = ExpertEarning::where('chat_session_id', $chat->id)->first();

        if (!$earning || $earning->premium_amount > 0) {
            return; // already processed ya no record
        }

        $category = $chat->category;
        $premium = 0;

        if ($rating >= 4) {
            $premium = $category->expert_premium ?? 0;
            $note = "Premium added: High rating ({$rating} stars)";
        } else {
            $note = "Low rating ({$rating} stars) - No premium";
        }

        $earning->update([
            'premium_amount' => $premium,
            'total_amount'   => $earning->basic_amount + $premium,
            'note'           => $earning->note . ' | ' . $note,
        ]);
    }
}
