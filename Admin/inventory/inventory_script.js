document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const table = document.querySelector("table tbody");

    searchInput.addEventListener("keyup", function () {
        const filter = searchInput.value.toLowerCase();
        const rows = table.getElementsByTagName("tr");

        Array.from(rows).forEach(row => {
            const text = row.innerText.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});

function confirmDelete() {
    return confirm("Are you sure you want to delete this book? This action cannot be undone.");
}
