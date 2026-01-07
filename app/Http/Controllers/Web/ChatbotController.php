<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PythonExpertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected $pythonService;

    public function __construct(PythonExpertService $pythonService)
    {
        $this->pythonService = $pythonService;
    }

    public function start(Request $request)
    {

        
        $request->validate(['question' => 'required']);

        session(['original_question' => $request->question]);

        $response = $this->pythonService->startChatbot($request->question);

        return response()->json([
            'success' => true,
            'bot_message' => $response['response'] ?? 'How can I help you?',
            'session_id' => $response['session_id'] ?? null,
            'escalate' => $response['escalate'] ?? false,
        ]);
    }

    public function message(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'message' => 'required'
        ]);

        $response = $this->pythonService->sendChatMessage(
            $request->session_id,
            $request->message
        );

        $escalate = $response['escalate'] ?? false;
        $botMessage = $response['response'] ?? 'Let me think...';

        // Ab escalate hone pe hi category detect karenge
        if ($escalate) {
            $originalQuestion = session('original_question', $request->message);
            try {
                $recommend = $this->pythonService->recommendExperts($originalQuestion);
                if (!empty($recommend['recommendations'])) {
                    $first = collect($recommend['recommendations'])->first();
                    $category = \App\Models\ExpertCategory::find($first['category_id']);
                    $categoryName = $category?->name ?? 'Expert';
                } else {
                    $categoryName = 'Expert';
                }
            } catch (\Exception $e) {
                Log::error('Category detection failed during escalate: ' . $e->getMessage());
                $categoryName = 'Expert';
            }

            $botMessage = "OK. Got it. I'm sending you to a secure page to join askExpert. While you're filling out that form, I'll tell the <strong>{$categoryName} Technician</strong> about your situation and then connect you two. <strong>Continue >></strong>";
        }

        return response()->json([
            'success' => true,
            'bot_message' => $botMessage,
            'session_id' => $response['session_id'] ?? $request->session_id,
            'escalate' => $escalate,
        ]);
    }
}