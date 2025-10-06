document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsDiv = document.getElementById("results");

  function fetchResults(query) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "search.php?search=" + encodeURIComponent(query), true);
    xhr.onload = function () {
      if (xhr.status === 200) {
        resultsDiv.innerHTML = xhr.responseText;
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
