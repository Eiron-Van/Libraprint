document.addEventListener("DOMContentLoaded", () => {
    const results = document.getElementById("results");
    const dropdownButtons = document.querySelectorAll("el-menu ul li button");
    const searchInput = document.getElementById("search");

    let currentType = "Login_record"; // Default

    // Function to fetch logs
    function fetchLogs(query = "") {
        results.innerHTML = "Loading...";
        fetch(`fetch_logs.php?type=${currentType}&search=${encodeURIComponent(query)}`)
            .then(res => res.text())
            .then(data => results.innerHTML = data)
            .catch(err => results.innerHTML = `<p class='text-red-400'>Error: ${err}</p>`);
    }

    // Dropdown click event
    dropdownButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            currentType = btn.textContent.trim().replace(" Logs", "_record");
            if (currentType === "Read_record") currentType = "Book_record";
            if (currentType === "Borrow_record") currentType = "Borrow_log";
            if (currentType === "Claim_record") currentType = "Claim_log";
            if (currentType === "Reservation_record") currentType = "Reservation";

            fetchLogs();
        });
    });

    // Search input event
    searchInput.addEventListener("input", () => {
        const query = searchInput.value.trim();
        fetchLogs(query);
    });

    // Initial load
    fetchLogs();
});