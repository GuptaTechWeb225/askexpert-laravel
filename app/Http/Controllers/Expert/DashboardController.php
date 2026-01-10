<?php

namespace App\Http\Controllers\Expert;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Enums\ViewPaths\Expert\Dashboard;
use App\Http\Controllers\BaseController;
use App\Models\ChatMessage;
use App\Services\DashboardService;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\ChatSession;

class DashboardController extends BaseController
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepo,
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }

    /**
     * @return View
     */
    public function getView(): View
    {
        $expert = auth('expert')->user(); // authenticated expert

        $expertId = $expert->id;
        $assignedChat = null;

        if ($expert && $expert->current_chat_id) {
            $assignedChat = ChatSession::with(['messages', 'customer'])
                ->where('id', $expert->current_chat_id)
                ->first();
        }

        $assignedQuestions = ChatMessage::whereHas('session', function ($q) use ($expertId) {
            $q->where('expert_id', $expertId);
        })
            ->with('session.customer')
            ->get();

        $unreadMessages = ChatMessage::whereHas('session', function ($q) use ($expertId) {
            $q->where('expert_id', $expertId);
        })
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->get();

        $oldChats = ChatSession::where('expert_id', $expertId)
            ->with(['customer'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $averageRating = $expert->average_rating;
        $totalEarning = $expert->total_earned; // accessor se fetch

        return view(Dashboard::INDEX[VIEW], compact(
            'expert',
            'assignedChat',
            'oldChats',
            'assignedQuestions',
            'unreadMessages',
            'averageRating',
            'totalEarning',
        ));
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $expert = auth('expert')->user();

        $newStatus = $expert->is_online ? 0 : 1;

        $expert->is_online = $newStatus;
        $expert->last_active_at = now();
        $expert->save();

        return response()->json([
            'success' => true,
            'new_status' => $newStatus,
            'message' => $newStatus ? 'You are now Online' : 'You are now Offline',
        ]);
    }
}
