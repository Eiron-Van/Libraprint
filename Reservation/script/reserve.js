document.addEventListener("DOMContentLoaded", function () {
    const overlay = document.getElementById("overlay");
    const closeOverlayBtn = document.getElementById("closeOverlayBtn");
    const reserveBookBtn = document.getElementById("reserve-btn");
    const readBookBtn = document.getElementById("read-btn");
    const borrowBookBtn = document.getElementById("borrow-btn");
    const successMsg = document.getElementById("successMsg");

    let selectedItemId = null; // store the clicked book’s ID

        // ✅ Open overlay for index.php
        reserveBookBtn.addEventListener("click", (e) => {
            e.preventDefault();
            overlay.classList.remove("hidden");
            overlay.classList.add("flex");
            barcodeInput.focus();
        });

    // ✅ 2. Function to handle reservation (Borrow or Read)
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
                    location.reload(); // refresh to show update
                }, 1500);
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    }

    // ✅ 3. Bind click events for Borrow/Read buttons
    readBookBtn.addEventListener("click", () => reserveBook("read"));
    borrowBookBtn.addEventListener("click", () => reserveBook("borrow"));

    // ✅ 4. Close overlay
    closeOverlayBtn.addEventListener("click", () => {
        overlay.classList.add("hidden");
    });
});
