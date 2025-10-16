document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsContainer = document.getElementById("results");

  // --- Fetch search results ---
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

  // --- ✅ Event Delegation for Delete Buttons ---
  resultsContainer.addEventListener("click", function (e) {
    if (e.target.classList.contains("delete-btn")) {
      const userId = e.target.getAttribute("data-user-id");
      confirmDelete(userId);
    }
  });

  // --- SweetAlert2 Delete Confirmation ---
  function confirmDelete(userId) {
    Swal.fire({
      title: 'Are you sure?',
      text: "This action cannot be undone. The user will be permanently deleted.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete user',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('delete_user.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'user_id=' + encodeURIComponent(userId)
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
              }).then(() => {
                fetchResults(searchInput.value); // refresh results without reloading the whole page
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
              });
            }
          })
          .catch(error => {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'An error occurred: ' + error
            });
          });
      }
    });
  }
});
