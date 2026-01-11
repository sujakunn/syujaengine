<?php include 'templates/header.php'; ?>

<div class="chat-box">
    <h2>SYUJA ENGINE</h2>
    <textarea id="question" placeholder="Tulis pertanyaan..."></textarea>
    <button onclick="sendQuestion()">Kirim</button>

    <div id="answer"></div>
</div>

<script src="assets/js/chat.js"></script>

<?php include 'templates/footer.php'; ?>

<textarea id="q"></textarea>
<button onclick="ask()">Tanya</button>
<div id="a"></div>

<script>
function ask() {
  fetch('api/rag_chat.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({question: q.value})
  })
  .then(r => r.json())
  .then(d => a.innerText = d.response);
}
</script>
