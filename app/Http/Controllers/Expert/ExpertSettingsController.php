<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\ExpertAvailability;
use App\Models\Admin;
use App\Models\ExpertCategory;
use App\Models\ExpertCommunicationMode;
use App\Models\ExpertNotificationPreference;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Utils\ImageManager;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;




class ExpertSettingsController extends Controller
{

    public function __construct(
        private readonly AdminNotificationRepositoryInterface   $notificationRepo,
    )
    {}
    public function availability()
    {
        $expert = auth('expert')->user();
        $availabilities = $expert->availability()->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")->get();

        // Agar koi day missing ho to default bana do
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            if (!$availabilities->contains('day', $day)) {
                $availabilities->push(ExpertAvailability::make([
                    'day' => $day,
                    'start_time' => '09:00',
                    'end_time' => '18:00',
                    'is_active' => true
                ]));
            }
        }

        return view('expert-views.settings.availability', compact('availabilities'));
    }

    public function updateAvailability(Request $request)
    {
        $expertId = auth('expert')->id();

        foreach ($request->days as $day => $data) {
            ExpertAvailability::updateOrCreate(
                ['expert_id' => $expertId, 'day' => $day],
                [
                    'start_time' => $data['start'],
                    'end_time' => $data['end'],
                    'is_active' => isset($data['active'])
                ]
            );
        }

        return redirect()->back()->with('success', 'Availability updated successfully!');
    }

    public function communicationModes()
    {
        $expert = auth('expert')->user();
        $modes = $expert->communicationModes;

        $defaultModes = ['text_chat', 'voice_call', 'video_call'];
        foreach ($defaultModes as $mode) {
            if (!$modes->contains('mode', $mode)) {
                $modes->push(ExpertCommunicationMode::make(['mode' => $mode]));
            }
        }

        return view('expert-views.settings.communication-modes', compact('modes'));
    }

    public function updateCommunicationModes(Request $request)
    {
        $expertId = auth('expert')->id();

        foreach ($request->modes as $mode => $settings) {
            ExpertCommunicationMode::updateOrCreate(
                ['expert_id' => $expertId, 'mode' => $mode],
                [
                    'available' => isset($settings['available']),
                    'on_break' => isset($settings['on_break']),
                    'vacation_mode' => isset($settings['vacation_mode']),
                ]
            );
        }

        return redirect()->back()->with('success', 'Communication modes updated!');
    }

    public function notifications()
    {
        $expert = auth('expert')->user();
        $preferences = $expert->notificationPreferences;

        $types = ['new_question_assigned', 'payout_processed', 'admin_message', 'system_updates'];
        foreach ($types as $type) {
            if (!$preferences->contains('type', $type)) {
                $preferences->push(ExpertNotificationPreference::make(['type' => $type]));
            }
        }

        return view('expert-views.settings.notifications', compact('preferences'));
    }

    public function updateNotifications(Request $request)
    {
        $expertId = auth('expert')->id();

        foreach ($request->notifications as $type => $channels) {
            ExpertNotificationPreference::updateOrCreate(
                ['expert_id' => $expertId, 'type' => $type],
                [
                    'email' => isset($channels['email']),
                    'sms' => isset($channels['sms']),
                    'dashboard' => isset($channels['dashboard']),
                ]
            );
        }

        return redirect()->back()->with('success', 'Notification preferences updated!');
    }


    public function profileEdit()
    {
        $expert = auth('expert')->user();
        return view('expert-views.settings.profile', compact('expert'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'f_name' => 'required|string|max:255',
            'email' => 'required|email|unique:experts,email,' . auth('expert')->id(),
            'phone' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $expert = auth('expert')->user();

        $expert->f_name = $request->f_name;
        $expert->l_name = $request->l_name;
        $expert->email = $request->email;
        $expert->phone = $request->phone;
        $expert->country = $request->country;
        $expert->state = $request->state;
        $expert->primary_specialty = $request->primary_specialty;
        $expert->secondary_specialty = $request->secondary_specialty;
        $expert->experience = $request->experience;

        if ($request->hasFile('image')) {
            $expert->image = ImageManager::update('expert/profile/', $expert->image, 'webp', $request->file('image'));
        }
        $expert->save();

        
        $title = 'Expert Profile Update';
        $message = "Expert {$expert->f_name} {$expert->l_name} has update their profile.";

        $recipients = [
            ['type' => 'admin', 'id' => 1], 
        ];

        $this->notificationRepo->notifyRecipients(
            1,
            Admin::class,
            $title,
            $message,
            $recipients
        );


        toastr()->success(translate('Profile updated successfully!'));
        return redirect()->back();
    }



public function expertEarnings(Request $request)
{
    $expert = auth('expert')->user();

    $totalEarned = $expert->total_earned; 
    $pendingPayout = $expert->pending_payout;
    $thisMonthEarned = $expert->earnings()
        ->where('status', 'paid')
        ->whereMonth('created_at', Carbon::now()->month)
        ->whereYear('created_at', Carbon::now()->year)
        ->sum('total_amount');
    $withdrawn = $totalEarned;

    $query = $expert->earnings()
        ->with(['chat', 'category']) 
        ->latest();

    // Filters
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

   if ($request->filled('search')) {
    $search = $request->search;

    $query->where(function ($q) use ($search) {
        $q->whereHas('chat.firstMessage', function ($sub) use ($search) {
            $sub->where('message', 'like', "%{$search}%")
                ->where('sender_type', 'customer');
        })
        ->orWhereHas('chat.messages', function ($sub) use ($search) {
            $sub->where('message', 'like', "%{$search}%");
        });
    });
}
    $earnings = $query->paginate(10);
    $categories = ExpertCategory::active()->pluck('name', 'id');

    return view('expert-views.earnings.index', compact(
        'totalEarned',
        'thisMonthEarned',
        'pendingPayout',
        'withdrawn',
        'earnings',
        'categories'
    ));
}
}
