document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById('search-toggle');
  const mobileSearch = document.getElementById('mobile-search');
  const brand = document.getElementById('brand');
  const searchInput = document.getElementById('search-input');

  toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    mobileSearch.classList.toggle('hidden');
    brand.classList.toggle('hidden');
    toggleBtn.classList.toggle('hidden');
    if (!mobileSearch.classList.contains('hidden')) {
      searchInput.focus();
    }
  });

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
});

console.log("Hello World");