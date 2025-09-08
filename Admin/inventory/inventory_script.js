document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const tableBody = document.querySelector("table tbody");

    searchInput.addEventListener("keyup", function () {
        const filter = searchInput.value.toLowerCase().trim();
        const rows = tableBody.getElementsByTagName("tr");

        Array.from(rows).forEach(row => {
            const rowText = row.innerText.toLowerCase();
            row.style.display = rowText.includes(filter) ? "" : "none";
        });
    });
});

});

function confirmDelete() {
    return confirm("Are you sure you want to delete this book? This action cannot be undone.");
}
