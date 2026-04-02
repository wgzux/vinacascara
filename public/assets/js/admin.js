// Admin JS
function toggleSidebar() {
  document.getElementById('adminSidebar')?.classList.toggle('open');
}
document.addEventListener('click', (e) => {
  const sidebar = document.getElementById('adminSidebar');
  if (sidebar && window.innerWidth <= 768 && sidebar.classList.contains('open')) {
    if (!e.target.closest('#adminSidebar') && !e.target.closest('.sidebar-toggle')) {
      sidebar.classList.remove('open');
    }
  }
});
