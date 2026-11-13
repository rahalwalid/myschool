// تبديل التبويبات (الدخول / التسجيل / لوحة المدير)
document.querySelectorAll('.tabs').forEach(tabs => {
  tabs.querySelectorAll('.tab').forEach(btn => btn.addEventListener('click', () => {
    tabs.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.pane').forEach(p => p.hidden = (p.id !== btn.dataset.t));
  }));
});
