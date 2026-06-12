<?php

namespace App\Services;

use app\Models\PrinterProblem;
use App\Models\PrinterProblemEmail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AiAssistantService
{
    private string $backendUrl;

    public function __construct()
    {
        $this->backendUrl = rtrim(config('services.ai_backend.url', env('AI_BACKEND_URL', 'http://127.0.0.1:8001')), '/');
    }

    public function generateProblemRecommendations(
        PrinterProblem $problem
    ): array {

        $context = [
            'problem_uid' => $problem->problem_uid,

            'order_number' => $problem->order_number,
            'designation' => $problem->designation,
            'version_number' => $problem->version_number,

            'design_nozzle_diameter' => $problem->design_nozzle_diameter,
            'tool_nozzle_diameter' => $problem->tool_nozzle_diameter,

            'material' => $problem->material,

            'print_temperature' => $problem->print_temperature,
            'bed_temperature' => $problem->bed_temperature,

            'nozzle_height' => $problem->nozzle_height,

            'offset_x' => $problem->offset_x,
            'offset_y' => $problem->offset_y,
            'offset_z' => $problem->offset_z,

            'maintenance_completed' => $problem->maintenance_completed,

            'machine_error_id' => $problem->machine_error_id,

            'short_description' => $problem->short_description,
            'operator_explanation' => $problem->operator_explanation,
        ];

        $payload = [
            'system_prompt' => <<<'PROMPT'
You are an industrial 3D printing troubleshooting assistant.

Your job:
- analyze the machine issue
- classify the issue type
- suggest realistic troubleshooting
- recommend next actions
- identify probable causes

Rules:
- do not hallucinate unsupported hardware details
- do not invent manufacturer procedures
- provide concise industrial troubleshooting advice
- return structured JSON only
PROMPT,

            'context' => json_encode(
                $context,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ),

            'message' => 'Analyze this 3D printer issue and provide recommendations.',

            'history' => [],

            'output_structure' => [
                'issue_type' => 'string',
                'possible_causes' => ['string'],
                'troubleshooting_steps' => ['string'],
                'next_steps' => ['string'],
                'manufacturer_contact_recommended' => 'boolean',
            ],
        ];

        $response = Http::timeout(120)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post(
                "{$this->backendUrl}/ai/assist",
                $payload
            )
            ->throw();

        $data = $response->json();

        return [
            'result' => $data['result'] ?? [],
            'raw_text' => $data['raw_text'] ?? null,
        ];
    }

    // -------------------------------------------------------------------------
    // NEW: generate first manufacturer email draft
    // -------------------------------------------------------------------------

    /**
     * Generates an initial outgoing email draft to the manufacturer.
     * Passes the full problem context so the AI can write a precise report.
     */
    public function generateEmailDraft(PrinterProblem $problem): array
    {
        $context = $this->buildProblemContext($problem);

        $payload = [
            'system_prompt' => <<<'PROMPT'
You are a technical writer for an industrial manufacturing company.

Your job is to write a professional email to the machine manufacturer
reporting a problem with their 3D printing equipment.

Rules:
- Write in formal, professional German
- Be precise and technical
- Include all relevant machine settings and error details
- Do not invent solutions — only report the issue clearly
- Return structured JSON only
PROMPT,
            'context' => json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'message' => 'Write a professional email to the manufacturer reporting this 3D printer issue.',
            'history' => [],
            'output_structure' => [
                'subject' => 'string',
                'body' => 'string',
            ],
        ];

        $response = Http::timeout(120)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->backendUrl}/ai/assist", $payload)
            ->throw();

        $data = $response->json();

        return [
            'result' => $data['result'] ?? [],
            'raw_text' => $data['raw_text'] ?? null,
        ];
    }

    // -------------------------------------------------------------------------
    // NEW: rewrite email based on user remarks + full conversation history
    // -------------------------------------------------------------------------

    /**
     * Rewrites an email draft based on user remarks.
     * Passes the full email conversation as history so the AI has full context.
     *
     * @param  Collection<PrinterProblemEmail>  $emails
     */
    public function rewriteEmailDraft(
        PrinterProblem $problem,
        Collection $emails,
        string $remarks
    ): array {
        $context = $this->buildProblemContext($problem);

        // Build LangChain-compatible history from the email conversation
        $history = $this->buildEmailHistory($emails);

        $payload = [
            'system_prompt' => <<<'PROMPT'
You are a technical writer for an industrial manufacturing company.

You previously wrote a manufacturer email about a 3D printer issue.
The user has reviewed the draft and provided remarks for improvement.

Your job:
- Rewrite the email based on the user's remarks
- Keep a formal, professional tone in German
- Maintain technical accuracy
- Do not invent new details not present in the context or history
- Return structured JSON only
PROMPT,
            'context' => json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'history' => $history,
            'message' => "Please rewrite the email based on these remarks: {$remarks}",
            'output_structure' => [
                'subject' => 'string',
                'body' => 'string',
            ],
        ];

        $response = Http::timeout(120)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->backendUrl}/ai/assist", $payload)
            ->throw();

        $data = $response->json();

        return [
            'result' => $data['result'] ?? [],
            'raw_text' => $data['raw_text'] ?? null,
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildProblemContext(PrinterProblem $problem): array
    {
        return [
            'problem_uid' => $problem->problem_uid,
            'order_number' => $problem->order_number,
            'designation' => $problem->designation,
            'version_number' => $problem->version_number,
            'nozzle_design' => $problem->nozzle_design,
            'nozzle_tool' => $problem->nozzle_tool,
            'material' => $problem->material,
            'print_temperature' => $problem->print_temperature,
            'bed_temperature' => $problem->bed_temperature,
            'nozzle_height' => $problem->nozzle_height,
            'path_offset_x' => $problem->path_offset_x,
            'path_offset_y' => $problem->path_offset_y,
            'path_offset_z' => $problem->path_offset_z,
            'maintenance_done' => $problem->maintenance_done,
            'error_id' => $problem->error_id,
            'short_description' => $problem->short_description,
            'operator_explanation' => $problem->operator_explanation,
            'issue_type' => $problem->issue_type,
            'ai_troubleshooting' => $problem->ai_troubleshooting,
        ];
    }

    /**
     * Convert email records into LangChain history format.
     * Outgoing emails = 'ai' role (what we sent), incoming = 'human' role (manufacturer reply).
     */
    private function buildEmailHistory(Collection $emails): array
    {
        return $emails->map(function (PrinterProblemEmail $email) {
            $role = $email->direction === 'outgoing' ? 'ai' : 'human';

            $content = $email->subject
                ? "Subject: {$email->subject}\n\n{$email->body}"
                : $email->body;

            return ['role' => $role, 'content' => $content];
        })->values()->toArray();
    }
}
