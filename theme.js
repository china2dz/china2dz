function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'dark';
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('c2dz_theme', next);
  const btn = document.getElementById('themeBtn');
  if (btn) btn.textContent = next === 'dark' ? '🌙' : '☀️';
}

// تطبيق الثيم عند تحميل أي صفحة
(function() {
  const saved = localStorage.getItem('c2dz_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
})();