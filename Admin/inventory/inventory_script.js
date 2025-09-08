document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const tableBody = document.querySelector("table tbody");

    searchInput.addEventListener("keyup", function () {
        const filter = searchInput.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll("tr");

        rows.forEach(row => {
            // Get plain text of row, strip extra spaces
            const rowText = row.textContent.toLowerCase().replace(/\s+/g, " ");
            if (rowText.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});


});

function confirmDelete() {
    return confirm("Are you sure you want to delete this book? This action cannot be undone.");
}
