document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsDiv = document.getElementById("results");

  // Function to initialize tooltips
  function initTooltips() {
    const conditionDots = document.querySelectorAll('.condition-dot');
    conditionDots.forEach(dot => {
      const container = dot.closest('.condition-dot-container');
      const tooltip = container.querySelector('.condition-tooltip');

      if (container && tooltip) {
        container.addEventListener('mouseenter', function () {
          tooltip.classList.remove('opacity-0');
          tooltip.classList.add('opacity-100');
        });

        container.addEventListener('mouseleave', function () {
          tooltip.classList.remove('opacity-100');
          tooltip.classList.add('opacity-0');
        });
      }
    });
  }

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
        if (table) {
          document.getElementById("results").innerHTML = table.innerHTML;
          // Initialize tooltips after content is loaded
          initTooltips();
        }

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

  // Initialize tooltips on initial load
  initTooltips();
});

function confirmDelete() {
  return confirm("Are you sure you want to delete this book? This action cannot be undone.");
}
