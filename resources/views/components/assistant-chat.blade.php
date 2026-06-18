<button type="button" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4 shadow-lg d-flex align-items-center justify-content-center" id="assistantBtn" style="width: 60px; height: 60px; z-index: 1050;">
    <i class="bi bi-chat-dots-fill fs-4"></i> <span class="fs-4"></span>
</button>

<div class="offcanvas offcanvas-end" tabindex="-1" id="assistantOffcanvas" aria-labelledby="assistantOffcanvasLabel" style="z-index: 1060;">
    <div class="offcanvas-header bg-wechsel text-white d-flex justify-content-between align-items-center">
        <h5 class="offcanvas-title" id="assistantOffcanvasLabel">✨ Virtueller Assistent</h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-danger border-0 text-white opacity-75 hover-opacity-100" id="clearChatBtn" title="Chat löschen">
                <span class="d-none d-sm-inline">Chat löschen</span>
            </button>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body d-flex flex-column justify-content-between">
        
        <div id="chatHistory" class="overflow-y-auto mb-3 grow">
            <div class="p-2 mb-3 bg-light rounded text-secondary">
                Hallo! Ich bin Ihr virtueller Assistent. Klicken Sie auf eine der untenstehenden häufigen Fragen oder schreiben Sie mir direkt eine Nachricht!
            </div>
        </div>

        <div class="input-group">
            <input type="text" id="chatInput" class="form-control" placeholder="Frage eingeben..." aria-label="Frage eingeben">
            <button class="btn btn-primary" type="button" id="sendBtn">Senden</button>
        </div>

    </div>
</div>

<style>
    .bg-wechsel {
        background-color: #0a192f;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const offcanvasEl = document.getElementById('assistantOffcanvas');
    const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
    const chatHistory = document.getElementById('chatHistory');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const clearChatBtn = document.getElementById('clearChatBtn');
    const welcomeMessage = document.getElementById('welcomeMessage');

    // Load existing history from sessionStorage, or initialize empty array
    let historyStack = JSON.parse(sessionStorage.getItem('assistant_chat_history')) || [];

    // Initialize UI on page load
    renderSavedHistory();
    
    // Toggle UI open
    document.getElementById('assistantBtn').addEventListener('click', () => bsOffcanvas.show());

    // Trigger on static local FAQ clicks
    document.querySelectorAll('.faq-btn').forEach(button => {
        button.addEventListener('click', function() {
            const assistantAnswer = this.getAttribute('data-answer');
            appendMessage(this.innerText, 'human');
            saveToHistory('human', this.innerText);

            setTimeout(() => {
                appendMessage(assistantAnswer, 'ai');
                saveToHistory('ai', assistantAnswer);
            }, 200);
        });
    });

    // Send on click or when Enter key is pressed
    sendBtn.addEventListener('click', sendUserQuery);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendUserQuery();
    });

    // Clear Chat Action
    clearChatBtn.addEventListener('click', function() {
        if (confirm("Möchten Sie den Chatverlauf wirklich löschen?")) {
            sessionStorage.removeItem('assistant_chat_history');
            historyStack = [];
            
            // Reset Chat History UI to just show the welcome message
            chatHistory.innerHTML = '';
            if (welcomeMessage) {
                chatHistory.appendChild(welcomeMessage);
            } else {
                chatHistory.innerHTML = `
                    <div id="welcomeMessage" class="p-2 mb-3 bg-light rounded text-secondary">
                        Hallo! Ich bin Ihr virtueller Assistent. Klicken Sie auf eine der untenstehenden häufigen Fragen oder schreiben Sie mir direkt eine Nachricht!
                    </div>
                `;
            }
        }
    });

    // Main JS API Calling function
    function sendUserQuery() {
        const queryText = chatInput.value.trim();
        if (!queryText) return;

        // Display user's question locally
        appendMessage(queryText, 'human');
        chatInput.value = '';
        // historyStack.push({ role: 'human', content: queryText });
        // Temporary tracking ID for a loading visual spinner
        const loadingId = 'loading-' + Date.now();
        chatHistory.innerHTML += `
            <div class="text-start mb-2" id="${loadingId}">
                <div class="p-2 bg-light rounded text-muted d-inline-block">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>ZimaTec Assistant antwortet...
                </div>
            </div>
        `;
        chatHistory.scrollTop = chatHistory.scrollHeight;

        // JavaScript AJAX Route Call
        fetch("{{ route('assistant.recommendations') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ message: queryText, history: historyStack })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById(loadingId).remove();
            if (data.success) {
                appendMessage(data.reply, 'ai');

                // Save both chunks to persistent state
                saveToHistory('human', queryText);
                saveToHistory('ai', data.reply);
            } else {
                appendMessage("❌ Fehler: " + data.message, 'ai');
            }
        })
        .catch(err => {
            if (document.getElementById(loadingId)) {
                document.getElementById(loadingId).remove();
            }
            appendMessage("❌ Verbindung zum Server fehlgeschlagen.", 'ai');
        });
    }

    // Central logic to add to array state and dump to sessionStorage
    function saveToHistory(role, content) {
        historyStack.push({ role: role, content: content });
        sessionStorage.setItem('assistant_chat_history', JSON.stringify(historyStack));
    }

    // Build the structural visual log if elements are already saved
    function renderSavedHistory() {
        if (historyStack.length > 0) {
            historyStack.forEach(msg => {
                appendMessage(msg.content, msg.role);
            });
        }
    }

    function appendMessage(text, sender) {
        if (sender === 'human') {
            chatHistory.innerHTML += `
                <div class="text-end mb-2">
                    <span class="badge bg-primary text-wrap text-start p-2" style="max-width: 85%; font-weight: normal; font-size:0.9rem;">
                        ${text}
                    </span>
                </div>
            `;
        } else {
            chatHistory.innerHTML += `
                <div class="text-start mb-2">
                    <div class="p-2 bg-light rounded text-dark d-inline-block" style="max-width: 85%; font-size: 0.9rem; white-space: pre-line;">
                        ${text}
                    </div>
                </div>
            `;
        }
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
});
</script>