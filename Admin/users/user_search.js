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

  //Delete Button
  function confirmDelete(userId) {
    if (confirm("Are you sure you want to delete this user? This action cannot be undone.")) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'user_id=' + encodeURIComponent(userId)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload(); // Refresh table after deletion
            }
        })
        .catch(error => {
            alert("Error deleting user: " + error);
        });
    }
}
});