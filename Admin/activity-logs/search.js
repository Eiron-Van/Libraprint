document.addEventListener("DOMContentLoaded", () => {
  const resultsContainer = document.getElementById("results");
  const searchInput = document.getElementById("search");
  const logButtons = document.querySelectorAll(".dropdown-log");
  let currentType = "login";
  let currentPage = 1;

  function fetchLogs(search = "", page = 1) {
    resultsContainer.innerHTML = "<div class='text-center py-4 text-gray-100'>Loading...</div>";
    fetch(`fetch_logs.php?type=${currentType}&search=${encodeURIComponent(search)}&page=${page}`)
      .then(res => res.text())
      .then(html => {
        resultsContainer.innerHTML = html;
        document.querySelectorAll(".page-btn").forEach(btn => {
          btn.addEventListener("click", () => {
            currentPage = parseInt(btn.dataset.page);
            fetchLogs(searchInput.value, currentPage);
          });
        });
      })
      .catch(() => {
        resultsContainer.innerHTML = "<div class='text-center text-red-500'>Failed to load data.</div>";
      });
  }

  logButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      currentType = btn.textContent.trim().split(" ")[0].toLowerCase(); // “Login Logs” → “login”
      currentPage = 1;
      fetchLogs(searchInput.value, currentPage);
    });
  });

  searchInput.addEventListener("input", () => {
    clearTimeout(window._searchTimer);
    window._searchTimer = setTimeout(() => {
      fetchLogs(searchInput.value, 1);
    }, 300);
  });

  // Load default logs
  fetchLogs();
});
