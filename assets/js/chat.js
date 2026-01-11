function sendQuestion() {
    let prompt = document.getElementById("question").value;

    fetch("api/ollama.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ prompt: prompt })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("answer").innerText = data.response;
    });
}

function askKB() {
    let q = document.getElementById("question").value;

    fetch("api/kb_chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ question: q })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("answer").innerText = data.response;
    });
}
