document.addEventListener("DOMContentLoaded", () => {
  const menuButton = document.getElementById('menu');
  const sideMenu = document.getElementById('side-menu');

  // Side menu toggle
  menuButton.addEventListener('click', (e) => {
    e.stopPropagation();
    sideMenu.classList.toggle('hidden');
  });

  // Unified click-outside handler
  document.addEventListener('click', (e) => {
    const clickedInsideMenu = sideMenu.contains(e.target) || menuButton.contains(e.target);

    if (!clickedInsideMenu && !sideMenu.classList.contains('hidden')) {
      sideMenu.classList.add('hidden');
    }
  });

  // Get the current year
  document.getElementById("year").textContent = new Date().getFullYear();
});
