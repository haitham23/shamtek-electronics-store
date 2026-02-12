let chatOpen = false;
let pollTimer = null;
let lastRendered = ""; // باش ما نعاودوش نفس الرندر

function $(id){ return document.getElementById(id); }

function openChat() {
  chatOpen = true;
  $("chat-panel").classList.add("is-open");
  $("chat-panel").setAttribute("aria-hidden", "false");
  $("chat-toggle").setAttribute("aria-expanded", "true");
  $("chat-badge").style.display = "none";

  loadMessages(true);
  startPolling();
  setTimeout(() => $("msg").focus(), 50);
}

function closeChat() {
  chatOpen = false;
  $("chat-panel").classList.remove("is-open");
  $("chat-panel").setAttribute("aria-hidden", "true");
  $("chat-toggle").setAttribute("aria-expanded", "false");
  stopPolling();
}

function toggleChat() {
  chatOpen ? closeChat() : openChat();
}

function startPolling(){
  stopPolling();
  pollTimer = setInterval(() => loadMessages(false), 2000);
}

function stopPolling(){
  if (pollTimer) clearInterval(pollTimer);
  pollTimer = null;
}

function parseLine(line){
  // expected: Bob dit "message"
  const marker = ' dit "';
  const i = line.indexOf(marker);
  if (i === -1) return { user: "", text: line };

  const user = line.slice(0, i).trim();
  let text = line.slice(i + marker.length);
  if (text.endsWith('"')) text = text.slice(0, -1);
  return { user, text };
}

function renderMessages(data, forceScroll){
  const box = $("messages");

  // نتهربو من إعادة نفس المحتوى
  const joined = data.join("\n");
  if (!forceScroll && joined === lastRendered) return;
  lastRendered = joined;

  box.innerHTML = "";
  data.forEach(line => {
    const { user, text } = parseLine(line);

    const wrap = document.createElement("div");
    wrap.className = "msg-line";

    if (user) {
      const u = document.createElement("div");
      u.className = "msg-user";
      u.textContent = user;
      wrap.appendChild(u);
    }

    const m = document.createElement("div");
    m.className = "msg-text";
    m.textContent = text;
    wrap.appendChild(m);

    box.appendChild(wrap);
  });

  // سكرول لتحت
  if (forceScroll) {
    box.scrollTop = box.scrollHeight;
  }
}

function loadMessages(forceScroll = false) {
  fetch("load_messages.php")
    .then(res => res.json())
    .then(data => {
      // إذا الشات مسدود وبانت رسائل جديدة -> badge
      if (!chatOpen) {
        if (data.join("\n") !== lastRendered) {
          $("chat-badge").style.display = "inline-flex";
        }
        lastRendered = data.join("\n");
        return;
      }

      renderMessages(data, forceScroll);
    })
    .catch(err => console.error("load_messages error:", err));
}

function sendMessage() {
  const msgEl = $("msg");
  const btn = $("chat-send");
  let msg = msgEl.value.trim();

  if (msg.length === 0) {
    alert("Message vide.");
    return;
  }
  if (msg.length > 256) {
    alert("Message trop long (max 256).");
    return;
  }

  btn.disabled = true;

  // 1) check offensive
  fetch("check_message.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "message=" + encodeURIComponent(msg)
  })
    .then(res => res.text())
    .then(result => {
      result = result.trim();

      if (result !== "ok") {
        btn.disabled = false;
        if (result === "offensive") return alert("Message offensant : envoi refusé.");
        if (result === "too_long") return alert("Message trop long (max 256).");
        if (result.startsWith("error:")) return alert("Erreur serveur: " + result);
        return alert("Réponse inattendue (check): " + result);
      }

      // 2) send
      return fetch("send_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "message=" + encodeURIComponent(msg)
      });
    })
    .then(res => {
      if (!res) return null;
      return res.text();
    })
    .then(r => {
      btn.disabled = false;
      if (!r) return;

      r = r.trim();
      if (r === "success") {
        msgEl.value = "";
        loadMessages(true);
        return;
      }

      if (r === "error:offensive") return alert("Message offensant : envoi refusé.");
      if (r === "error:too_long") return alert("Message trop long (max 256).");
      if (r === "error:empty") return alert("Message vide.");
      if (r.startsWith("error:")) return alert("Erreur serveur: " + r);

      alert("Réponse inattendue: " + r);
    })
    .catch(err => {
      btn.disabled = false;
      alert("Erreur réseau: " + err);
    });
}

// أحداث
window.addEventListener("load", () => {
  $("chat-toggle").addEventListener("click", toggleChat);
  $("chat-close").addEventListener("click", closeChat);
  $("chat-send").addEventListener("click", sendMessage);

  $("msg").addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  // ما نحملوش الرسائل تلقائياً الآن: غير ملي تفتح الشات
  // لكن نقدر نعمل check خفيف كل 6 ثواني باش badge يبان:
  setInterval(() => loadMessages(false), 6000);
});
