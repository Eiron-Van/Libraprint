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
    const clickedInsideSearch = mobileSearch.contains(e.target) || toggleBtn.contains(e.target);
    const clickedInsideMenu = sideMenu.contains(e.target) || menuButton.contains(e.target);

    if (!clickedInsideSearch && !mobileSearch.classList.contains('hidden')) {
      mobileSearch.classList.add('hidden');
      brand.classList.remove('hidden');
      toggleBtn.classList.remove('hidden');
    }

    if (!clickedInsideMenu && !sideMenu.classList.contains('hidden')) {
      sideMenu.classList.add('hidden');
    }
  });

  // Get the current year
  document.getElementById("year").textContent = new Date().getFullYear();
});
