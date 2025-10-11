document.addEventListener("DOMContentLoaded", () => {
    const menuButton = document.getElementById('menu');
    const sideMenu = document.getElementById('side-menu');

    menuButton.addEventListener('click', (e) => {
    e.stopPropagation();
    sideMenu.classList.toggle('hidden');
  });

});