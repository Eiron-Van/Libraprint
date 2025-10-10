document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsDiv = document.getElementById("results");

  function fetchResults(query) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "search.php?search=" + encodeURIComponent(query), true);
    xhr.onload = function () {
      if (xhr.status === 200) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(xhr.responseText, "text/html");

        // Extract results count and table
        const count = doc.getElementById("results-count");
        const table = doc.querySelector("div.overflow-auto");

        if (count) document.getElementById("results-count").innerHTML = count.innerHTML;
        if (table) document.getElementById("results").innerHTML = table.innerHTML;

      }
    };
    xhr.send();
  }

  // fetch on page load (with current value)
  fetchResults(searchInput.value);

  // live search with delay
  let timer;
  searchInput.addEventListener("input", function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      fetchResults(this.value);
    }, 300); // wait 300ms after typing stops
  });
});

function confirmDelete() {
    return confirm("Are you sure you want to delete this book? This action cannot be undone.");
}
