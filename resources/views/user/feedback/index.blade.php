@extends('user.layouts.index')
 
@section('title', 'AI Assistant')
 
@section('content')
    <div class="container py-4" style="max-width: 860px;">
    
        <h4 class="mb-1 fw-semibold">AI Assistant</h4>
        <p class="text-muted small mb-4">Ask anything — configure a system prompt, add context, and optionally define a structured output format.</p>
    
        {{-- ── Global errors ───────────────────────────────────────────────── --}}
        @if ($errors->has('ai'))
            <div class="alert alert-danger py-2">{{ $errors->first('ai') }}</div>
        @endif
    
        {{-- ── Conversation history ─────────────────────────────────────────── --}}
        @if (!empty($history))
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom py-2">
                    <span class="fw-medium small">Conversation history</span>
                    <form method="POST" action="{{ route('feedback.clear') }}" class="m-0">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2">Clear</button>
                    </form>
                </div>
                <div class="card-body p-3" style="max-height:300px; overflow-y:auto;">
                    @foreach ($history as $turn)
                        <div class="mb-2">
                            @if ($turn['role'] === 'human')
                                <span class="badge bg-primary-subtle text-primary me-1">You</span>
                            @else
                                <span class="badge bg-success-subtle text-success me-1">AI</span>
                            @endif
                            <span class="small">{{ $turn['content'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    
        {{-- ── Result ───────────────────────────────────────────────────────── --}}
        @if (session('ai_success') && session('ai_result'))
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-success-subtle border-bottom py-2">
                    <span class="fw-medium small text-success">AI Response</span>
                </div>
                <div class="card-body p-0">
                    <pre class="m-0 p-3 small" style="white-space:pre-wrap;word-break:break-word;">{{ session('ai_result') }}</pre>
                </div>
            </div>
        @endif
    
        {{-- ── Main form ────────────────────────────────────────────────────── --}}
        <form method="POST" action="{{ route('feedback.ask') }}">
            @csrf
    
            {{-- System prompt --}}
            <div class="mb-3">
                <label for="system_prompt" class="form-label fw-medium small">System Prompt</label>
                <textarea
                    id="system_prompt"
                    name="system_prompt"
                    rows="2"
                    class="form-control form-control-sm @error('system_prompt') is-invalid @enderror"
                    placeholder="e.g. You are a SQL expert."
                >{{ old('system_prompt', $prefill['system_prompt']) }}</textarea>
                @error('system_prompt')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
    
            {{-- Context --}}
            <div class="mb-3">
                <label for="context" class="form-label fw-medium small">
                    Context
                    <span class="text-muted fw-normal">(optional)</span>
                </label>
                <textarea
                    id="context"
                    name="context"
                    rows="3"
                    class="form-control form-control-sm @error('context') is-invalid @enderror"
                    placeholder="Background information the model should know about…"
                >{{ old('context', $prefill['context']) }}</textarea>
                @error('context')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
    
            {{-- Message --}}
            <div class="mb-3">
                <label for="message" class="form-label fw-medium small">Your Message <span class="text-danger">*</span></label>
                <textarea
                    id="message"
                    name="message"
                    rows="3"
                    class="form-control form-control-sm @error('message') is-invalid @enderror"
                    placeholder="Ask anything…"
                    required
                >{{ old('message', $prefill['message']) }}</textarea>
                @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
    
            {{-- Output structure --}}
            <div class="mb-3">
                <label for="output_structure" class="form-label fw-medium small">
                    Output Structure
                    <span class="text-muted fw-normal">(optional — paste a JSON schema)</span>
                </label>
                <textarea
                    id="output_structure"
                    name="output_structure"
                    rows="4"
                    class="form-control form-control-sm font-monospace @error('output_structure') is-invalid @enderror"
                    placeholder='{ "company": "string", "founder": "string" }'
                >{{ old('output_structure', $prefill['output_structure']) }}</textarea>
                @error('output_structure')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
    
            {{-- Actions --}}
            <div class="d-flex gap-2 align-items-center">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    Send
                </button>
                @if (!empty($history))
                    <label class="form-check-label small d-flex align-items-center gap-1 text-muted">
                        <input type="checkbox" name="clear_history" value="1" class="form-check-input m-0">
                        Clear history before sending
                    </label>
                @endif
            </div>
    
        </form>
    </div>
@endsection