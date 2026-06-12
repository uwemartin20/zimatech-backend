<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    /**
     * The base URL of your FastAPI backend.
     * Set AI_BACKEND_URL in your .env file.
     */
    private string $backendUrl;

    public function __construct()
    {
        $this->backendUrl = rtrim(config('services.ai_backend.url', env('AI_BACKEND_URL', 'http://127.0.0.1:8001')), '/');
    }

    // -------------------------------------------------------------------------
    // GET  /ai-assistant
    // Renders the form. Pre-populates fields from query-string so the page can
    // be deep-linked with a context already filled in, e.g.:
    //   /ai-assistant?system_prompt=You+are+a+SQL+expert&message=Explain+joins
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $prefill = [
            'system_prompt' => $request->query('system_prompt', 'You are a helpful assistant.'),
            'context' => $request->query('context', ''),
            'message' => $request->query('message', ''),
            'history' => [],           // history only comes from session / POST
            'output_structure' => $request->query('output_structure', ''),
        ];

        // Restore last history from session so the conversation survives a page refresh
        $history = session('ai_history', []);

        return view('user.feedback.index', compact('prefill', 'history'));
    }

    // -------------------------------------------------------------------------
    // POST  /ai-assistant
    // Sends the payload to FastAPI and stores the exchange in the session.
    // -------------------------------------------------------------------------
    public function ask(Request $request)
    {
        $validated = $request->validate([
            'system_prompt' => ['nullable', 'string', 'max:4000'],
            'context' => ['nullable', 'string', 'max:8000'],
            'message' => ['required', 'string', 'max:4000'],
            'output_structure' => ['nullable', 'string'],   // JSON string from textarea
            'clear_history' => ['nullable', 'boolean'],
        ]);

        // Allow the user to wipe the conversation
        if ($request->boolean('clear_history')) {
            session()->forget('ai_history');
        }

        $history = session('ai_history', []);

        // Parse output_structure if provided
        $outputStructure = null;
        if (! empty($validated['output_structure'])) {
            $outputStructure = json_decode($validated['output_structure'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withInput()
                    ->withErrors(['output_structure' => 'Output structure must be valid JSON.']);
            }
        }

        // Build payload for FastAPI
        $payload = [
            'system_prompt' => $validated['system_prompt'] ?? 'You are a helpful assistant.',
            'context' => $validated['context'] ?? null,
            'message' => $validated['message'],
            'history' => $history,
            'output_structure' => $outputStructure,
        ];

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->backendUrl}/ai/assist", $payload)
                ->throw();    // throws on 4xx / 5xx

            $data = $response->json();

            $result = $data['result'] ?? null;
            $rawText = $data['raw_text'] ?? '';

            // Append this exchange to the session history
            $history[] = ['role' => 'human', 'content' => $validated['message']];
            $history[] = ['role' => 'ai',    'content' => $rawText];
            session(['ai_history' => $history]);

            // Pretty-print if result is an array (structured JSON output)
            $formattedResult = is_array($result)
                ? json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : $result;

            return back()->with([
                'ai_result' => $formattedResult,
                'ai_success' => true,
            ])->withInput();

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('AI backend request failed', [
                'status' => $e->response?->status(),
                'body' => $e->response?->body(),
                'message' => $e->getMessage(),
            ]);

            $detail = $e->response?->json('detail') ?? 'The AI service returned an error.';

            return back()
                ->withInput()
                ->withErrors(['ai' => "AI service error: {$detail}"]);

        } catch (\Exception $e) {
            Log::error('Unexpected AI assistant error', ['message' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['ai' => 'Could not reach the AI service. Please try again.']);
        }
    }

    // -------------------------------------------------------------------------
    // POST  /ai-assistant/clear
    // Clears the conversation history stored in the session.
    // -------------------------------------------------------------------------
    public function clearHistory()
    {
        session()->forget('ai_history');

        return back()->with('ai_success', false);
    }
}
