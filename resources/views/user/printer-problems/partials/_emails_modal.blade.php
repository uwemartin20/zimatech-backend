{{--
Email modal partial.
Include on show.blade.php with: @include('user.printer-problems.partials._emails_modal')
Requires $problem to be in scope.
--}}

{{-- ── Trigger button (place wherever you want on the show page) ────────── --}}
<button type="button"
    class="btn btn-sm btn-outline-secondary"
    onclick="EmailModal.open({{ $problem->id }})">
<i class="bi bi-envelope me-1"></i>
E-Mails
@if($problem->emails_count ?? $problem->emails->count())
    <span class="badge bg-secondary ms-1">{{ $problem->emails->count() }}</span>
@endif
</button>

{{-- ── Modal ────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

        {{-- Header --}}
        <div class="modal-header border-bottom py-2">
            <h6 class="modal-title fw-semibold mb-0" id="emailModalLabel">
                <i class="bi bi-envelope me-1"></i> Kommunikation — {{ $problem->problem_uid }}
            </h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        {{-- Body --}}
        <div class="modal-body p-0 d-flex" style="min-height: 520px;">

            {{-- Left: thread --}}
            <div class="d-flex flex-column border-end" style="width: 42%; min-width: 260px;">
                <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold text-muted">Verlauf</span>
                    <button class="btn btn-xs btn-outline-primary btn-sm py-0 px-2"
                            onclick="EmailModal.addReply()"
                            title="Herstellerantwort eintragen">
                        <i class="bi bi-plus-lg"></i> Antwort
                    </button>
                </div>

                {{-- Thread list --}}
                <div id="email-thread" class="grow overflow-auto p-2">
                    <div class="text-center text-muted small py-5" id="thread-loading">
                        <div class="spinner-border spinner-border-sm me-1"></div> Lädt…
                    </div>
                </div>
            </div>

            {{-- Right: editor --}}
            <div class="d-flex flex-column grow">
                <div class="px-3 py-2 border-bottom bg-light">
                    <span class="small fw-semibold text-muted" id="editor-title">Neuer Entwurf</span>
                </div>

                <div class="p-3 grow d-flex flex-column" id="editor-panel">

                    {{-- No emails state --}}
                    <div id="state-empty" class="text-center my-auto">
                        <i class="bi bi-envelope-plus fs-2 text-muted d-block mb-2"></i>
                        <p class="text-muted small mb-3">Noch keine E-Mails vorhanden.</p>
                        <button class="btn btn-primary btn-sm" onclick="EmailModal.generate()">
                            <i class="bi bi-stars me-1"></i> E-Mail generieren
                        </button>
                    </div>

                    {{-- Loading spinner (AI working) --}}
                    <div id="state-loading" class="text-center my-auto d-none">
                        <div class="spinner-border text-primary mb-2"></div>
                        <p class="text-muted small" id="loading-label">KI arbeitet…</p>
                    </div>

                    {{-- Draft editor --}}
                    <div id="state-editor" class="d-none d-flex flex-column grow gap-3">

                        <div>
                            <label class="form-label small fw-medium mb-1">Betreff</label>
                            <input type="text" id="draft-subject"
                                   class="form-control form-control-sm"
                                   placeholder="Betreff">
                        </div>

                        <div class="grow d-flex flex-column">
                            <label class="form-label small fw-medium mb-1">Nachricht</label>
                            <textarea id="draft-body"
                                      class="form-control form-control-sm grow"
                                      style="resize: none; min-height: 200px;"></textarea>
                        </div>

                        {{-- Rewrite with remarks --}}
                        <div class="border rounded p-2 bg-light">
                            <label class="form-label small fw-medium mb-1">
                                <i class="bi bi-pencil-square me-1"></i>Anmerkungen für KI-Umschreibung
                            </label>
                            <div class="d-flex gap-2">
                                <input type="text" id="rewrite-remarks"
                                       class="form-control form-control-sm"
                                       placeholder="z.B. Bitte formeller formulieren, Temperaturwerte betonen…">
                                <button class="btn btn-sm btn-outline-warning text-nowrap"
                                        onclick="EmailModal.rewrite()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Neu schreiben
                                </button>
                            </div>
                        </div>

                        {{-- Draft actions --}}
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm px-4" onclick="EmailModal.saveDraft()">
                                <i class="bi bi-check-lg me-1"></i>Entwurf speichern
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="EmailModal.generate()">
                                <i class="bi bi-stars me-1"></i>Neu generieren
                            </button>
                        </div>

                    </div>

                    {{-- Manufacturer reply form --}}
                    <div id="state-reply" class="d-none d-flex flex-column gap-3">
                        <div>
                            <label class="form-label small fw-medium mb-1">Betreff (optional)</label>
                            <input type="text" id="reply-subject"
                                   class="form-control form-control-sm"
                                   placeholder="Re: …">
                        </div>
                        <div class="grow d-flex flex-column">
                            <label class="form-label small fw-medium mb-1">
                                Herstellerantwort <span class="text-danger">*</span>
                            </label>
                            <textarea id="reply-body"
                                      class="form-control form-control-sm"
                                      rows="8"
                                      style="resize:none;"
                                      placeholder="Antwort des Herstellers hier einfügen…"></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm px-4" onclick="EmailModal.saveReply()">
                                <i class="bi bi-save me-1"></i>Antwort speichern
                            </button>
                            <button class="btn btn-outline-secondary btn-sm"
                                    onclick="EmailModal.showState('editor')">
                                Abbrechen
                            </button>
                        </div>
                    </div>

                    {{-- Read-only view (clicking a thread item) --}}
                    <div id="state-readonly" class="d-none grow d-flex flex-column gap-2">
                        <div class="small text-muted">
                            <span id="ro-meta"></span>
                        </div>
                        <div class="fw-medium" id="ro-subject"></div>
                        <pre id="ro-body"
                             class="grow border rounded p-3 small bg-light"
                             style="white-space: pre-wrap; word-break: break-word; overflow-y: auto; max-height: 340px;"></pre>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="EmailModal.backToEditor()">
                                ← Zurück zum Entwurf
                            </button>
                        </div>
                    </div>

                </div>{{-- /editor-panel --}}
            </div>{{-- /right --}}
        </div>{{-- /modal-body --}}

        {{-- Footer --}}
        <div class="modal-footer border-top py-2 d-flex justify-content-between align-items-center">
            <span id="modal-status" class="small text-muted"></span>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                Schließen
            </button>
        </div>

    </div>
</div>
</div>

@push('scripts')
<script>
    const EmailModal = (() => {
        const ROUTES = {
            thread:   (id) => `/printer-problems/${id}/emails`,
            generate: (id) => `/printer-problems/${id}/emails/generate`,
            rewrite:  (id) => `/printer-problems/${id}/emails/rewrite`,
            save:     (id) => `/printer-problems/${id}/emails/save`,
            reply:    (id) => `/printer-problems/${id}/emails/reply`,
        };
     
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
     
        let problemId    = null;
        let bsModal      = null;
        let currentState = 'empty';
     
        // ── State machine ────────────────────────────────────────────────────────
        // Each state id maps to whether it needs d-flex restored when shown.
        // d-none removes the element; for flex containers we must re-add d-flex.
        const STATES = {
            'state-empty':    false,
            'state-loading':  false,
            'state-editor':   true,   // needs d-flex
            'state-reply':    true,   // needs d-flex
            'state-readonly': true,   // needs d-flex
        };
     
        function showState(state, loadingLabel = 'KI arbeitet…') {
            currentState = state;
     
            Object.entries(STATES).forEach(([id, needsFlex]) => {
                const node = document.getElementById(id);
                if (!node) return;
                node.classList.add('d-none');
                if (needsFlex) node.classList.remove('d-flex');
            });
     
            const target = document.getElementById(`state-${state}`);
            if (!target) return;
            target.classList.remove('d-none');
            if (STATES[`state-${state}`]) target.classList.add('d-flex');
     
            if (state === 'loading') {
                const lbl = document.getElementById('loading-label');
                if (lbl) lbl.textContent = loadingLabel;
            }
            if (state === 'editor') {
                const title = document.getElementById('editor-title');
                if (title) title.textContent = 'Entwurf bearbeiten';
            }
        }
     
        // ── Public ───────────────────────────────────────────────────────────────
     
        function open(id) {
            problemId = id;
            bsModal   = bsModal ?? new bootstrap.Modal(document.getElementById('emailModal'));
            bsModal.show();
            // Reset to loading while thread fetches
            showState('loading', 'Lädt…');
            loadThread();
        }
     
        function generate() {
            showState('loading', 'E-Mail wird generiert…');
     
            post(ROUTES.generate(problemId), {})
                .then(data => {
                    if (data.success) {
                        fillEditor(data.email.subject, data.email.body);
                        showState('editor');
                        setStatus('Entwurf bereit – bitte prüfen und speichern.');
                    } else {
                        setStatus(data.message ?? 'Generierung fehlgeschlagen.', true);
                        showState('empty');
                    }
                })
                .catch(err => {
                    console.error('generate error', err);
                    setStatus('Fehler bei der Generierung.', true);
                    showState('empty');
                });
        }
     
        function rewrite() {
            const remarks = el('rewrite-remarks').value.trim();
            if (!remarks) {
                setStatus('Bitte Anmerkungen eingeben.', true);
                return;
            }
     
            showState('loading', 'E-Mail wird neu geschrieben…');
     
            post(ROUTES.rewrite(problemId), { remarks })
                .then(data => {
                    if (data.success) {
                        fillEditor(data.email.subject, data.email.body);
                        el('rewrite-remarks').value = '';
                        showState('editor');
                        setStatus('Entwurf aktualisiert – bitte prüfen und speichern.');
                    } else {
                        setStatus(data.message ?? 'Umschreiben fehlgeschlagen.', true);
                        showState('editor');
                    }
                })
                .catch(err => {
                    console.error('rewrite error', err);
                    setStatus('Fehler beim Umschreiben.', true);
                    showState('editor');
                });
        }
     
        function saveDraft() {
            const subject = el('draft-subject').value.trim();
            const body    = el('draft-body').value.trim();
     
            if (!body) {
                setStatus('Nachricht darf nicht leer sein.', true);
                return;
            }
     
            post(ROUTES.save(problemId), { subject, body })
                .then(data => {
                    if (data.success) {
                        loadThread();
                        setStatus(`Entwurf gespeichert ✓  (${data.created_at})`);
                    } else {
                        setStatus('Speichern fehlgeschlagen.', true);
                    }
                })
                .catch(() => setStatus('Fehler beim Speichern.', true));
        }
     
        function addReply() {
            el('reply-subject').value = '';
            el('reply-body').value    = '';
            const title = el('editor-title');
            if (title) title.textContent = 'Herstellerantwort eintragen';
            showState('reply');
        }
     
        function saveReply() {
            const subject = el('reply-subject').value.trim();
            const body    = el('reply-body').value.trim();
     
            if (!body) {
                setStatus('Antwort darf nicht leer sein.', true);
                return;
            }
     
            post(ROUTES.reply(problemId), { subject, body })
                .then(data => {
                    if (data.success) {
                        loadThread();
                        showState('editor');
                        const title = el('editor-title');
                        if (title) title.textContent = 'Entwurf bearbeiten';
                        setStatus(`Herstellerantwort gespeichert ✓  (${data.created_at})`);
                    } else {
                        setStatus('Speichern fehlgeschlagen.', true);
                    }
                })
                .catch(() => setStatus('Fehler beim Speichern.', true));
        }
     
        function backToEditor() {
            showState('editor');
        }
     
        // ── Private ──────────────────────────────────────────────────────────────
     
        function loadThread() {
            const threadEl = el('email-thread');
            // Keep existing items visible, just show spinner alongside them
            const spinner = el('thread-loading');
            if (spinner) spinner.classList.remove('d-none');
     
            fetch(ROUTES.thread(problemId))
                .then(r => r.json())
                .then(data => {
                    if (spinner) spinner.classList.add('d-none');
     
                    const emails = data.emails ?? [];
     
                    if (emails.length === 0) {
                        showState('empty');
                        return;
                    }
     
                    // On first load (came from loading state): prefill editor with latest saved entry
                    if (currentState === 'loading' || currentState === 'empty') {
                        const last = emails[emails.length - 1];
                        fillEditor(last.subject, last.body);
                        showState('editor');
                    }
     
                    renderThread(emails);
                })
                .catch(err => {
                    console.error('loadThread error', err);
                    if (spinner) spinner.classList.add('d-none');
                    setStatus('Thread konnte nicht geladen werden.', true);
                    if (currentState === 'loading') showState('empty');
                });
        }
     
        function renderThread(emails) {
            const container = el('email-thread');
            // Remove old items, keep the spinner node
            container.querySelectorAll('.email-item').forEach(e => e.remove());
     
            emails.forEach(email => {
                const isOut = email.direction === 'outgoing';
                const div   = document.createElement('div');
                div.className = 'email-item border rounded p-2 mb-2 small';
                div.style.cursor     = 'pointer';
                div.style.borderLeft = `3px solid ${isOut ? '#0d6efd' : '#198754'}`;
     
                const typeLabel = {
                    ai_draft:           'KI-Entwurf',
                    user_edited:        'Gespeichert',
                    manufacturer_reply: 'Herstellerantwort',
                }[email.email_type] ?? email.email_type;
     
                const preview = (email.body ?? '').substring(0, 80);
     
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="d-flex gap-1 flex-wrap">
                            <span class="badge ${isOut ? 'bg-primary' : 'bg-success'}">${isOut ? 'Ausgehend' : 'Eingehend'}</span>
                            <span class="badge bg-secondary">${typeLabel}</span>
                        </div>
                        <span class="text-muted" style="font-size:0.7rem;">${email.created_at}</span>
                    </div>
                    <div class="fw-medium text-truncate">${email.subject ?? '(kein Betreff)'}</div>
                    <div class="text-muted text-truncate">${preview}${preview.length >= 80 ? '…' : ''}</div>
                `;
     
                div.addEventListener('click', () => viewEmail(email));
                container.appendChild(div);
            });
        }
     
        function viewEmail(email) {
            const roMeta    = el('ro-meta');
            const roSubject = el('ro-subject');
            const roBody    = el('ro-body');
     
            if (roMeta)    roMeta.textContent    = `${email.direction === 'outgoing' ? 'Ausgehend' : 'Eingehend'} · ${email.created_at} · ${email.created_by}`;
            if (roSubject) roSubject.textContent = email.subject ?? '(kein Betreff)';
            if (roBody)    roBody.textContent    = email.body ?? '';
     
            showState('readonly');
        }
     
        function fillEditor(subject, body) {
            const s = el('draft-subject');
            const b = el('draft-body');
            if (s) s.value = subject ?? '';
            if (b) b.value = body    ?? '';
        }
     
        function setStatus(msg, isError = false) {
            const s = el('modal-status');
            if (!s) return;
            s.textContent = msg;
            s.className   = `small ${isError ? 'text-danger' : 'text-success'}`;
            // Keep success messages visible longer since they confirm an action
            setTimeout(() => {
                s.textContent = '';
                s.className   = 'small text-muted';
            }, isError ? 5000 : 8000);
        }
     
        function post(url, data) {
            return fetch(url, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify(data),
            }).then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            });
        }
     
        function el(id) { return document.getElementById(id); }
     
        return { open, generate, rewrite, saveDraft, addReply, saveReply, showState, backToEditor };
    })();
</script>
@endpush