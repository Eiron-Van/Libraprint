document.addEventListener("DOMContentLoaded", () => {
    const results = document.getElementById("results");
    const searchInput = document.getElementById("search");

    function fetchLoginLogs(query = "") {
        results.innerHTML = "<div class='text-center text-gray-400 mt-10'>Loading...</div>";
        fetch(`fetch_login_logs.php?search=${encodeURIComponent(query)}`)
            .then(res => res.text())
            .then(data => results.innerHTML = data)
            .catch(err => results.innerHTML = `<div class='text-red-400'>Error: ${err}</div>`);
    }

    // Search as you type
    searchInput.addEventListener("input", () => {
        const q = searchInput.value.trim();
        fetchLoginLogs(q);
    });

    // Initial load
    fetchLoginLogs();
});
