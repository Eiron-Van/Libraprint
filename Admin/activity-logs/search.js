document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsContainer = document.getElementById("results");

  // Track which log type is active
  let currentFile = "fetch_login_logs.php"; // default

  // Generic function to fetch results
  function fetchResults(query = "", page = 1) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `${currentFile}?search=${encodeURIComponent(query)}&page=${page}`, true);

    xhr.onload = function () {
      if (xhr.status === 200) {
        resultsContainer.innerHTML = xhr.responseText;
      } else {
        resultsContainer.innerHTML = `<div class='text-red-400 text-center py-4'>Error loading data.</div>`;
      }
    };

    resultsContainer.innerHTML =
      "<div class='text-center py-4 text-gray-100'>Loading...</div>";
    xhr.send();
  }

  // Initial load
  fetchResults();

  // Debounced live search
  let timer;
  searchInput.addEventListener("input", function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      fetchResults(this.value);
    }, 300);
  });

  // Listen for dropdown menu clicks
  document.querySelectorAll(".dropdown-log").forEach((btn) => {
    btn.addEventListener("click", function () {
      const type = this.textContent.trim();

      // Determine which PHP file to fetch
      switch (type) {
        case "Login Logs":
          currentFile = "fetch_login_logs.php";
          break;
        case "Read Logs":
          currentFile = "fetch_read_logs.php";
          break;
        case "Reservation Logs":
          currentFile = "fetch_reservation_logs.php";
          break;
        case "Claim Logs":
          currentFile = "fetch_claim_logs.php";
          break;
        case "Borrow Logs":
          currentFile = "fetch_borrow_logs.php";
          break;
        case "Overdue Logs":
          currentFile = "fetch_overdue_logs.php";
          break;
      }

      // Reset search and load new type
      searchInput.value = "";
      fetchResults();

      // Highlight selected option
      document.querySelectorAll(".dropdown-log").forEach((el) => {
        el.classList.remove("bg-gray-600");
      });
      this.classList.add("bg-gray-600");
    });
  });

  // Handle pagination clicks
  document.addEventListener("click", function (e) {
    const pageLink = e.target.closest(".page-link");
    if (pageLink) {
      e.preventDefault();
      const page = pageLink.dataset.page;
      const searchValue = searchInput.value;
      fetchResults(searchValue, page);
    }
  });
});
