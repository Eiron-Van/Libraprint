document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById('search-toggle');
  const mobileSearch = document.getElementById('mobile-search');
  const brand = document.getElementById('brand');
  const searchInput = document.getElementById('search-input');
  const menuButton = document.getElementById('menu');
  const sideMenu = document.getElementById('side-menu');

  // searchbar on mobile
  toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    mobileSearch.classList.toggle('hidden');
    brand.classList.toggle('hidden');
    toggleBtn.classList.toggle('hidden');
    if (!mobileSearch.classList.contains('hidden')) {
      searchInput.focus();
    }
  });

  // close searchbar when clicked outside
  document.addEventListener('click', (e) => {
    const clickedInside =
      mobileSearch.contains(e.target) ||
      toggleBtn.contains(e.target);
    if (!clickedInside && !mobileSearch.classList.contains('hidden')) {
      mobileSearch.classList.add('hidden');
      brand.classList.remove('hidden');
      toggleBtn.classList.remove('hidden');
    }
  });

  mobileSearch.addEventListener('click', (e) => e.stopPropagation());

  // side menu button unhide/hide
  menuButton.addEventListener('click', () => {
    const menu = document.getElementById('menu');
    sideMenu.classList.toggle('hidden');
  });

  // close side menu when clicked outside
  document.addEventListener('click', (e) => {
    const clickedInsideMenu = sideMenu.contains(e.target);
    const clickedToggle = menuButton.contains(e.target);
  
    if (!clickedInsideMenu && !clickedToggle && !sideMenu.classList.contains('hidden')) {
      sideMenu.classList.add('hidden');
    }
  });

});