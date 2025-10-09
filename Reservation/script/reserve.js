document.addEventListener("DOMContentLoaded", function () {
    const overlay = document.getElementById("overlay");
    const closeOverlayBtn = document.getElementById("closeOverlayBtn");
    const readBookBtn = document.getElementById("read-btn");
    const borrowBookBtn = document.getElementById("borrow-btn");
    const successMsg = document.getElementById("successMsg");

    let selectedItemId = null; // store the clicked book’s ID

    // Delegate clicks from dynamically injected results container
    const resultsContainer = document.getElementById("results");
    if (resultsContainer) {
        resultsContainer.addEventListener("click", function (e) {
            const target = e.target;
            if (target && target.classList && target.classList.contains("reserve-btn")) {
                e.preventDefault();
                selectedItemId = target.getAttribute("data-item-id");
                if (!selectedItemId) return;
                overlay.classList.remove("hidden");
                overlay.classList.add("flex");
            }
        });
    }

    function reserveBook(purpose) {
        if (!selectedItemId) return;

        fetch("reserve_book.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                item_id: selectedItemId,
                purpose: purpose
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    successMsg.classList.remove("hidden");
                    setTimeout(() => {
                        overlay.classList.add("hidden");
                        successMsg.classList.add("hidden");
                        location.reload();
                    }, 1500);
                } else {
                    alert(data.message);
                }
            })
            .catch(err => console.error(err));
    }

    if (readBookBtn) readBookBtn.addEventListener("click", () => reserveBook("read"));
    if (borrowBookBtn) borrowBookBtn.addEventListener("click", () => reserveBook("borrow"));

    if (closeOverlayBtn) {
        closeOverlayBtn.addEventListener("click", () => {
            overlay.classList.add("hidden");
        });
    }
});
