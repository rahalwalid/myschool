(() => {
  const box = document.getElementById('msgs'); if (!box) return;
  const form = document.getElementById('send'), input = form.querySelector('input[name=body]');
  const fileIn = document.getElementById('file'), fname = document.getElementById('fname');
  const w = box.dataset.with, me = box.dataset.me, t = box.dataset.t;
  let last = 0, busy = false, stick = true;
  const link = (url, cls) => { const a = document.createElement('a'); a.href = url; a.target = '_blank'; if (cls) a.className = cls; return a; };
  const add = m => {
    const d = document.createElement('div'); d.className = 'msg ' + (m.sender_id == me ? 'me' : 'them');
    if (m.body) { const p = document.createElement('p'); p.textContent = m.body; d.append(p); }
    if (m.kind) {
      const url = 'chat_file.php?id=' + m.id;
      if (m.kind === 'image') {
        const a = link(url), img = document.createElement('img');
        img.src = url; img.alt = m.file_name || ''; img.addEventListener('load', () => { if (stick) box.scrollTop = box.scrollHeight; });
        a.append(img); d.append(a);
      } else { const a = link(url, 'file'); a.textContent = '📄 ' + (m.file_name || 'ملف PDF'); d.append(a); }
    }
    const s = document.createElement('small'); s.textContent = m.created_at.slice(11, 16); d.append(s);
    box.append(d); last = Math.max(last, +m.id);
  };
  async function poll() {
    if (busy || document.hidden) return; busy = true;
    try {
      const j = await (await fetch(`chat_api.php?a=fetch&with=${w}&after=${last}`)).json();
      if (j.messages && j.messages.length) {
        stick = last === 0 || box.scrollHeight - box.scrollTop - box.clientHeight < 80;
        j.messages.forEach(add); if (stick) box.scrollTop = box.scrollHeight;
      }
    } catch (e) {}
    busy = false;
  }
  fileIn.addEventListener('change', () => {
    const f = fileIn.files[0]; if (!f) { fname.textContent = ''; return; }
    const pdf = /\.pdf$/i.test(f.name), max = (pdf ? 10 : 5) * 1048576;
    if (!pdf && !f.type.startsWith('image/')) { alert('الصور و PDF فقط'); fileIn.value = ''; fname.textContent = ''; return; }
    if (f.size > max) { alert(`الحجم الأقصى ${pdf ? 10 : 5}MB`); fileIn.value = ''; fname.textContent = ''; return; }
    fname.textContent = '📎 ' + f.name.slice(0, 25);
  });
  form.addEventListener('submit', async e => {
    e.preventDefault(); const v = input.value.trim(), f = fileIn.files[0]; if (!v && !f) return;
    const fd = new FormData(); fd.append('with', w); fd.append('body', v); fd.append('t', t); if (f) fd.append('file', f);
    input.value = ''; fileIn.value = ''; fname.textContent = '';
    const r = await fetch('chat_api.php', { method: 'POST', body: fd });
    if (!r.ok) alert('تعذر الإرسال. تأكد من نوع الملف وحجمه (صور حتى 5MB، PDF حتى 10MB).');
    stick = true; poll();
  });
  poll(); setInterval(poll, 3000);
})();
