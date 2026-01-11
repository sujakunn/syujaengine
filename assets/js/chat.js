/**
 * SYUJA ENGINE - Advanced Chat Logic
 * Versi Upgrade: Mendukung Markdown, Auto-scroll, dan State Management
 */

// Gunakan Marked.js untuk merender teks AI agar mendukung Bold, List, dan Code Block
// Pastikan script marked.min.js sudah dipanggil di index.php
if (typeof marked !== 'undefined') {
    marked.setOptions({
        breaks: true,
        gfm: true
    });
}

/**
 * Fungsi Utama handleChat
 * Menggantikan sendQuestion, askKB, dan askRAG menjadi satu alur terpadu
 */
async function handleChat() {
    const input = document.getElementById("user-input") || document.getElementById("question");
    const container = document.getElementById("chat-display") || document.getElementById("chat-container");
    const btn = document.getElementById("btn-send");
    
    const message = input.value.trim();
    if (!message) return;

    // 1. UI: Tambahkan Pesan User ke Layar
    appendMessage('user', message);
    
    // Reset Input
    input.value = "";
    input.style.height = "auto";
    
    // 2. UI: Tambahkan Bubble Loading (Placeholder untuk jawaban AI)
    const loadingId = "ai-load-" + Date.now();
    appendMessage('bot', `
        <div class="flex items-center gap-2" id="${loadingId}-inner">
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0.2s"></div>
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0.4s"></div>
        </div>
    `, loadingId);

    // Disable tombol saat proses
    if(btn) btn.disabled = true;

    try {
        // 3. API Call: Mengarah ke rag_chat.php (Sistem paling lengkap)
        const response = await fetch("api/rag_chat.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ question: message })
        });

        if (!response.ok) throw new Error("Server Error");

        const data = await response.json();
        const aiBubble = document.getElementById(loadingId);
        
        // 4. Render Jawaban AI
        // Jika ada Marked.js, gunakan marked.parse(), jika tidak gunakan innerText
        const finalContent = typeof marked !== 'undefined' 
            ? marked.parse(data.response || data.content || "Maaf, saya tidak menemukan jawaban.") 
            : (data.response || "Maaf, terjadi kesalahan.");

        aiBubble.innerHTML = `<div class="prose max-w-none">${finalContent}</div>`;
        
    } catch (error) {
        console.error("Chat Error:", error);
        const aiBubble = document.getElementById(loadingId);
        aiBubble.innerHTML = `<span class="text-red-500 italic">Gagal mendapatkan respon dari server.</span>`;
    } finally {
        if(btn) btn.disabled = false;
        // Scroll otomatis ke bawah
        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    }
}

/**
 * Helper: Menambahkan bubble chat ke container secara dinamis
 */
function appendMessage(sender, content, id = null) {
    const container = document.getElementById("chat-display") || document.getElementById("chat-container");
    const wrapper = document.createElement("div");
    
    // Styling berdasarkan pengirim
    wrapper.className = sender === 'user' ? 'flex justify-end mb-4' : 'flex justify-start mb-4';
    
    const bubbleClass = sender === 'user'
        ? 'bg-blue-600 text-white px-4 py-3 rounded-2xl rounded-tr-none shadow-sm max-w-[85%]'
        : 'bg-white border border-slate-200 text-slate-800 px-4 py-3 rounded-2xl rounded-tl-none shadow-sm max-w-[85%]';

    wrapper.innerHTML = `
        <div ${id ? `id="${id}"` : ""} class="${bubbleClass}">
            ${content}
        </div>
    `;

    container.appendChild(wrapper);
    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
}

/**
 * Event Listener: Kirim pesan saat tekan Enter (tanpa Shift)
 */
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById("user-input") || document.getElementById("question");
    if (input) {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleChat();
            }
        });
    }
});