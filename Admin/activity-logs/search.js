document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsContainer = document.getElementById("results");

  function fetchResults(query) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_login_logs.php?search=" + encodeURIComponent(query), true);
    xhr.onload = function () {
      if (xhr.status === 200) {
        resultsContainer.innerHTML = xhr.responseText;
      }
    };
    resultsContainer.innerHTML = "<div class='text-center py-4 text-gray-100'>Loading...</div>";
    xhr.send();
  }

  

  // Initial load
  fetchResults("");

  // Live search with debounce
  let timer;
  searchInput.addEventListener("input", function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      fetchResults(this.value);
    }, 300);
  });
});