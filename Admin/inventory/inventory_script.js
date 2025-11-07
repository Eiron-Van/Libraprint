document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsDiv = document.getElementById("results");

  // Function to initialize tooltips
  function initTooltips() {
    const containers = document.querySelectorAll('.condition-dot-container');
    containers.forEach((container, index) => {
      const tooltip = container.querySelector('.condition-tooltip');
      const dot = container.querySelector('.condition-dot');

      if (container && tooltip && dot) {
        // Remove any existing event listeners by cloning
        const newContainer = container.cloneNode(true);
        container.parentNode.replaceChild(newContainer, container);

        // Get fresh references
        const newTooltip = newContainer.querySelector('.condition-tooltip');
        const newDot = newContainer.querySelector('.condition-dot');

        if (newContainer && newTooltip && newDot) {
          // Set initial state
          newTooltip.style.opacity = '0';
          newTooltip.style.visibility = 'hidden';
          newTooltip.style.display = 'block';

          // Use mouseenter/mouseleave for better control
          newContainer.addEventListener('mouseenter', function (e) {
            e.stopPropagation();
            newTooltip.style.opacity = '1';
            newTooltip.style.visibility = 'visible';
            newTooltip.style.display = 'block';
          });

          newContainer.addEventListener('mouseleave', function (e) {
            e.stopPropagation();
            newTooltip.style.opacity = '0';
            newTooltip.style.visibility = 'hidden';
          });
        }
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
