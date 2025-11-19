document.addEventListener("DOMContentLoaded", function () {
    const overlay = document.getElementById("overlay");
    const closeOverlayBtn = document.getElementById("closeOverlayBtn");
    const readBookBtn = document.getElementById("read-btn");
    const borrowBookBtn = document.getElementById("borrow-btn");
    const successMsg = document.getElementById("successMsg");
    const readOnlyNotice = document.getElementById("readOnlyNotice");

    let selectedTitle = null; // store the clicked book’s Title
    let selectedReadOnly = false;

    // Delegate clicks from dynamically injected results container
    const resultsContainer = document.getElementById("results");
    if (resultsContainer) {
        resultsContainer.addEventListener("click", function (e) {
            const target = e.target;
            if (target && target.classList && target.classList.contains("reserve-btn")) {
                e.preventDefault();
                selectedTitle = target.getAttribute("data-title");  // Get the title
                selectedReadOnly = target.getAttribute("data-read-only") === "true";
                if (!selectedTitle) return;
                overlay.classList.remove("hidden");
                overlay.classList.add("flex");
                updateBorrowButtonState();
            }
        });
    }

    function updateBorrowButtonState() {
        if (!borrowBookBtn) return;
        if (selectedReadOnly) {
            borrowBookBtn.disabled = true;
            borrowBookBtn.classList.add("opacity-50", "cursor-not-allowed");
            if (readOnlyNotice) readOnlyNotice.classList.remove("hidden");
        } else {
            borrowBookBtn.disabled = false;
            borrowBookBtn.classList.remove("opacity-50", "cursor-not-allowed");
            if (readOnlyNotice) readOnlyNotice.classList.add("hidden");
        }
    }

    function reserveBook(purpose) {
        if (!selectedTitle) return;
        if (purpose === "borrow" && selectedReadOnly) {
            alert("This title is for in-library use only and cannot be borrowed.");
            return;
        }

        fetch("reserve_book.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                title: selectedTitle,
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
                    }, 2000);
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
            selectedTitle = null;
            selectedReadOnly = false;
            updateBorrowButtonState();
        });
    }
});
