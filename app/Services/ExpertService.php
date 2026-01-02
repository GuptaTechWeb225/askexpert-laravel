<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use App\Models\Expert;
use App\Models\ExpertEarning;
use App\Models\ChatSession;

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

    public function findAvailableExpert(array $expertIds)
    {
        return Expert::whereIn('id', $expertIds)
            ->where('is_online', true)
            ->where('is_busy', false)
            ->orderBy('last_active_at')
            ->first();
    }
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
