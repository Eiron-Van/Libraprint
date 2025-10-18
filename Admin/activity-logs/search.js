document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsContainer = document.getElementById("results");

  // Track which log type is active
  let currentFile = "fetch_login_logs.php"; // default view

  // Generic function to fetch results
  function fetchResults(query = "") {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `${currentFile}?search=${encodeURIComponent(query)}`, true);

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
  fetchResults("");

  // Debounced live search
  let timer;
  searchInput.addEventListener("input", function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      fetchResults(this.value);
    }, 300);
  });

  // Listen for dropdown button clicks
  document.querySelectorAll(".dropdown-log").forEach((btn) => {
    btn.addEventListener("click", function () {
      const type = this.textContent.trim();

      // Determine which file to fetch
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
      }

      // Reset search field and fetch data
      searchInput.value = "";
      fetchResults("");

      // Active style (highlight the selected log)
      document.querySelectorAll(".dropdown-log").forEach((el) => {
        el.classList.remove("bg-gray-600");
      });
      this.classList.add("bg-gray-600");
    });
  });
});
