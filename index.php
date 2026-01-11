<?php include 'templates/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-7xl mx-auto mt-4 lg:mt-8 p-2 lg:p-4 font-sans selection:bg-blue-100 selection:text-blue-700">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-120px)]">
        
        <aside class="lg:col-span-3 space-y-4 flex flex-col overflow-y-auto custom-scrollbar pr-1">
            
            <div class="bg-white rounded-[2rem] p-6 shadow-xl shadow-slate-200/50 border border-slate-100 transition-all hover:shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Management</h3>
                    <span class="flex h-2 w-2 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.6)]"></span>
                </div>
                
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 mb-6 group transition-all hover:bg-white hover:border-blue-200">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white rounded-xl shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <i class="fas fa-server text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Backend Engine</p>
                            <p class="text-sm font-bold text-slate-700">Ollama llama3:8b</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <p class="text-xs font-bold text-slate-500 ml-1 mb-2">Knowledge Base Ingestion</p>
                    <div id="drop-zone" class="relative group">
                        <input id="file-upload" name="pdf_file" type="file" class="hidden" accept=".pdf" onchange="uploadDocument()" />
                        <label for="file-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-200 border-dashed rounded-3xl cursor-pointer bg-slate-50 hover:bg-blue-50 hover:border-blue-400 transition-all duration-300">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-file-pdf text-xl text-slate-400 group-hover:text-blue-500 mb-2"></i>
                                <p class="text-[10px] text-slate-600 font-bold tracking-tight">Klik / Drop PDF</p>
                            </div>
                        </label>
                    </div>

                    <button onclick="clearChat()" class="w-full py-3 bg-white hover:bg-red-50 text-slate-500 hover:text-red-600 rounded-2xl text-xs font-black transition-all border border-slate-100 hover:border-red-100 flex items-center justify-center gap-3">
                        <i class="fas fa-trash-alt text-[10px]"></i> RESET SESSION
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-slate-100 flex-1 flex flex-col min-h-[300px]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Database Documents</h3>
                    <button onclick="loadFileList()" class="text-slate-400 hover:text-blue-500 transition-all">
                        <i class="fas fa-sync-alt text-[10px]"></i>
                    </button>
                </div>
                <div class="overflow-hidden flex-1">
                    <div id="file-list" class="space-y-3 max-h-full overflow-y-auto custom-scrollbar pr-1">
                        <p class="text-xs text-slate-400 italic text-center py-4">Memuat daftar dokumen...</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gradient-to-br from-slate-800 to-slate-900 rounded-[2rem] text-white shadow-xl shadow-slate-900/20">
                <p class="text-[10px] font-black tracking-widest text-slate-400 mb-4 uppercase">System Info</p>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 italic">Version</span>
                    <span class="font-mono text-[10px]">v2.0.4-LTS</span>
                </div>
            </div>
        </aside>

        <main class="lg:col-span-9 flex flex-col bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/60 border border-slate-100 overflow-hidden relative">
            
            <header class="px-8 py-5 border-b border-slate-50 flex justify-between items-center bg-white/70 backdrop-blur-xl z-30 sticky top-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-slate-800 leading-none">SYUJA <span class="text-blue-600">ENGINE</span></h2>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="flex h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Neural RAG Pipeline Active</p>
                        </div>
                    </div>
                </div>
                <div class="hidden md:flex gap-3 text-[10px] font-bold text-slate-600">
                    <div class="px-4 py-2 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2">
                        <i class="fas fa-microchip text-slate-400"></i>
                        <span>CUDA: Enabled</span>
                    </div>
                </div>
            </header>

            <div id="chat-display" class="flex-1 overflow-y-auto px-8 py-10 space-y-8 bg-slate-50/30 custom-scrollbar scroll-smooth font-inter">
                <div class="flex items-start gap-5 message-animate">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-white shadow-md shrink-0 border border-slate-700">
                        <i class="fas fa-robot text-xs"></i>
                    </div>
                    <div class="bg-white border border-slate-200 text-slate-800 p-6 rounded-3xl rounded-tl-none shadow-sm max-w-[85%] leading-relaxed ring-1 ring-slate-100 prose">
                        <p class="font-black text-blue-600 text-[10px] uppercase tracking-widest mb-3 flex items-center gap-2 not-prose">
                            <i class="fas fa-check-circle"></i> System Ready
                        </p>
                        Halo! Saya <strong>Syuja Engine</strong>. Saya siap menganalisis dokumen Anda. Silakan unggah PDF atau tanyakan sesuatu.
                    </div>
                </div>
            </div>

            <footer class="p-6 bg-white border-t border-slate-50">
                <div class="max-w-4xl mx-auto flex items-end gap-3 bg-slate-100/80 p-3 rounded-3xl focus-within:ring-4 focus-within:ring-blue-100 focus-within:bg-white transition-all shadow-inner relative border border-transparent focus-within:border-blue-200">
                    <textarea 
                        id="user-input" 
                        rows="1"
                        class="flex-1 bg-transparent border-none focus:ring-0 text-slate-700 placeholder-slate-400 py-3 px-4 resize-none max-h-48 custom-scrollbar font-medium"
                        placeholder="Ketik pesan atau pertanyaan..."
                        oninput="this.style.height = '';this.style.height = this.scrollHeight + 'px'"
                    ></textarea>

                    <div class="flex items-center gap-2 p-1">
                        <button id="voice-btn" onclick="startVoice()" class="w-11 h-11 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all"><i class="fas fa-microphone"></i></button>
                        <button onclick="handleChat()" id="btn-send" class="bg-blue-600 hover:bg-blue-700 text-white w-12 h-12 rounded-2xl flex items-center justify-center transition-all shadow-xl shadow-blue-200 active:scale-95 disabled:bg-slate-300"><i class="fas fa-paper-plane text-sm"></i></button>
                    </div>
                </div>
                <p class="text-[9px] text-center text-slate-400 mt-4 font-black uppercase tracking-[0.3em] opacity-60">SyujaEngine Neural Architecture • 2026</p>
            </footer>
        </main>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 100px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #3b82f6; }
    .prose { font-size: 0.95rem; color: #334155; max-width: none; }
    .prose code { background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 0.4rem; font-size: 0.85em; color: #2563eb; }
    @keyframes messageFadeIn { from { opacity: 0; transform: translateY(15px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
    .message-animate { animation: messageFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    #drop-zone.dragover { border-color: #3b82f6; background-color: #eff6ff; transform: scale(1.02); }
</style>

<script src="assets/js/chat.js"></script>

<script>
/**
 * UI & KNOWLEDGE MANAGEMENT LOGIC
 */
marked.setOptions({ breaks: true, gfm: true });

// Event: Keyboard Send
document.getElementById('user-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleChat();
    }
});

// Logic: Reset Session & Conversational Memory
async function clearChat() {
    const result = await Swal.fire({
        title: 'Reset Session?',
        text: "Riwayat chat dan memori AI akan dihapus.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Ya, Reset',
        customClass: { popup: 'rounded-[2rem]' }
    });

    if (result.isConfirmed) {
        try {
            await fetch('api/manage_knowledge.php?action=clear_memory');
            document.getElementById('chat-display').innerHTML = '';
            location.reload();
        } catch (e) {
            console.error("Gagal reset memori");
        }
    }
}

// Logic: Knowledge Dashboard
async function loadFileList() {
    const listContainer = document.getElementById('file-list');
    try {
        const response = await fetch('api/manage_knowledge.php?action=list');
        const result = await response.json();
        
        if (result.status === 'success' && result.data.length > 0) {
            listContainer.innerHTML = result.data.map(file => `
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center group hover:border-blue-200 transition-all">
                    <div class="overflow-hidden">
                        <p class="text-[10px] font-bold text-slate-700 truncate" title="${file.source_file}">${file.source_file}</p>
                        <p class="text-[9px] text-slate-400 font-medium">${file.total_chunks} data vectors</p>
                    </div>
                    <button onclick="deleteFile('${file.source_file}')" class="text-slate-300 hover:text-red-500 p-2 transition-colors">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
            `).join('');
        } else {
            listContainer.innerHTML = '<p class="text-[10px] text-slate-400 text-center py-4 italic">Belum ada dokumen.</p>';
        }
    } catch (e) {
        listContainer.innerHTML = '<p class="text-[10px] text-red-400 text-center py-4">Gagal memuat data.</p>';
    }
}

async function deleteFile(fileName) {
    const confirm = await Swal.fire({
        title: 'Hapus Dokumen?',
        text: `Seluruh data dari "${fileName}" akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        confirmButtonColor: '#ef4444',
        customClass: { popup: 'rounded-[2rem]' }
    });

    if (confirm.isConfirmed) {
        try {
            const response = await fetch('api/manage_knowledge.php?action=delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ file_name: fileName })
            });
            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire({ title: 'Dihapus!', icon: 'success', customClass: { popup: 'rounded-[2rem]' } });
                loadFileList();
            }
        } catch (e) {
            Swal.fire('Error', 'Gagal menghapus.', 'error');
        }
    }
}

// Logic: Drag & Drop Upload
const dropZone = document.getElementById('drop-zone');
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => {
    dropZone.addEventListener(e, (ev) => { ev.preventDefault(); ev.stopPropagation(); });
});
dropZone.addEventListener('dragover', () => dropZone.classList.add('dragover'));
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', (e) => {
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        document.getElementById('file-upload').files = e.dataTransfer.files;
        uploadDocument();
    }
});

async function uploadDocument() {
    const fileInput = document.getElementById('file-upload');
    if (!fileInput.files[0]) return;

    const formData = new FormData();
    formData.append('pdf_file', fileInput.files[0]);

    Swal.fire({
        title: 'Neural Ingestion...',
        html: 'Mempelajari teks dan menyimpan koordinat vektor...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); },
        customClass: { popup: 'rounded-[2rem]' }
    });

    try {
        const response = await fetch('scripts/process_pdf.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.status === 'success') {
            Swal.fire({ title: 'Success!', text: data.message, icon: 'success', customClass: { popup: 'rounded-[2rem]' } });
            loadFileList(); 
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        Swal.fire({ title: 'Error', text: error.message, icon: 'error', customClass: { popup: 'rounded-[2rem]' } });
    }
}

// Init Load
document.addEventListener('DOMContentLoaded', loadFileList);
</script>

<?php include 'templates/footer.php'; ?>