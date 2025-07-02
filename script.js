document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById('search-toggle');
  const mobileSearch = document.getElementById('mobile-search');
  const brand = document.getElementById('brand');
  const searchInput = document.getElementById('search-input');
  const menuButton = document.getElementById('menu');
  const sideMenu = document.getElementById('side-menu');

  // Mobile searchbar toggle
  toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    mobileSearch.classList.toggle('hidden');
    brand.classList.toggle('hidden');
    toggleBtn.classList.toggle('hidden');
    if (!mobileSearch.classList.contains('hidden')) {
      searchInput.focus();
    }
  });

  // Prevent clicks inside searchbar from closing it
  mobileSearch.addEventListener('click', (e) => e.stopPropagation());

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
});
