<?php

namespace App\Http\Controllers\Expert\Auth;

use App\Http\Controllers\BaseController;
use App\Traits\EmailTemplateTrait;
use App\Enums\ViewPaths\Expert\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Models\Expert;
use App\Models\Admin;
use App\Models\ExpertCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Brian2694\Toastr\Facades\Toastr;
use App\Utils\ImageManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Services\PythonExpertService;


class RegisterController extends BaseController
{
    use EmailTemplateTrait;
    public function __construct(
        private readonly AdminNotificationRepositoryInterface   $notificationRepo,
    ) {}


    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }
    public function getView(): View|RedirectResponse
    {
        $countries = COUNTRIES;
        $categories = ExpertCategory::all();
        return view(Auth::EXPERT_REGISTRATION[VIEW], compact('categories', 'countries'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:experts,email',
            'phone' => 'required|string|max:20',
            'category_id' => 'required|exists:expert_categories,id',
            'primary_specialty' => 'required|string|max:255',
            'secondary_specialty' => 'nullable|string|max:255',
            'experience' => 'nullable|integer',
            'certification' => 'nullable|file|mimes:pdf,jpg,png,jpeg',
            'degree' => 'nullable|file|mimes:pdf,jpg,png,jpeg',
            'resume' => 'nullable|file|mimes:pdf,doc,docx',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'preference' => 'required|in:phone,chat,video',
            'start_date' => 'required|date',
            'password' => 'required|string|min:8|confirmed', // ✅ password validation
        ]);



        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all()
            ]);
        }

        $certificationName = $request->file('certification')
            ? $request->file('certification')->store('expert/certification', 'public')
            : null;

        $degreeName = $request->file('degree')
            ? $request->file('degree')->store('expert/degree', 'public')
            : null;

        $resumeName = $request->file('resume')
            ? $request->file('resume')->store('expert/resume', 'public')
            : null;

        $availability = 'mon-fri'; // default
        if ($request->has('availability_weekdays') && $request->has('availability_weekend')) {
            $availability = 'both';
        } elseif ($request->has('availability_weekend')) {
            $availability = 'sat-sun';
        }

        $expert = Expert::create([
            'f_name' => $request->first_name,
            'l_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'category_id' => $request->category_id,
            'primary_specialty' => $request->primary_specialty,
            'secondary_specialty' => $request->secondary_specialty,
            'experience' => $request->experience,
            'certification' => $certificationName,
            'education_degree' => $degreeName,
            'resume' => $resumeName,
            'country' => $request->country,
            'state' => $request->state,
            'prefer_to_answer' => $request->preference,
            'available_to_start' => $request->start_date,
            'availability' => $availability, // mapped from checkboxes
            'password' => Hash::make($request->password),
            'is_active' => false,
            'status' => 'pending',
        ]);


        $title = 'New Expert Registration';
        $message = "Expert {$expert->f_name} {$expert->l_name} has registered and is pending approval.";

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


        logActivity(
            "Expert {$expert->f_name} {$expert->l_name} registered and awaiting approval",
            $expert,
            [
                'email' => $expert->email,
                'category' => optional($expert->category)->name,
                'status' => 'pending',
            ]
        );

        try {
            $pythonService = app(PythonExpertService::class);
            $trained = $pythonService->trainModel();

            if ($trained) {
                Log::info('Expert model training triggered successfully after new registration', [
                    'expert_id' => $expert->id,
                    'email' => $expert->email
                ]);
                Toastr::success('Expert registered successfully! Model training started in background.');
            } else {
                Log::warning('Expert model training failed after registration', [
                    'expert_id' => $expert->id
                ]);
                Toastr::warning('Expert registered, but model training failed. Contact support.');
            }
        } catch (\Exception $e) {
            Log::error('Exception while triggering model training after expert registration', [
                'error' => $e->getMessage(),
                'expert_id' => $expert->id ?? null
            ]);
            Toastr::error('Expert registered successfully, but training process failed.');
        }

        return response()->json([
            'status' => 1,
            'message' => 'Expert registration submitted successfully!',
            'redirect_url' => route('home') // or wherever
        ]);
    }
}
