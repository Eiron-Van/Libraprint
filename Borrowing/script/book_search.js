document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsContainer = document.getElementById("results");

  function fetchResults(query) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "search.php?search=" + encodeURIComponent(query), true);
    xhr.onload = function () {
      if (xhr.status === 200) {
        resultsContainer.innerHTML = xhr.responseText;
      }
    };
    resultsContainer.innerHTML = "<div>Loading...</div>";
    xhr.send();
  }

  xhr.onerror = function () {
   resultsContainer.innerHTML = "<div class='text-red-500'>Failed to load results.</div>";
  };

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