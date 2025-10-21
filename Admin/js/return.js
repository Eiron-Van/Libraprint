document.addEventListener("DOMContentLoaded", () => {
    // ✅ Send request to backend
function processReturn(barcode) {
    fetch("api/return_book.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "barcode=" + encodeURIComponent(barcode),
    })
        .then((res) => res.json())
        .then((data) => {
            hideMessages();
            const barcodeInput = document.getElementById("returnBookBtn");

            // ✅ If success and overdue
            if (data.success && data.overdue) {
                Swal.fire({
                    title: "Overdue Book Detected!",
                    html: `
                        <p><b>${data.book_title}</b></p>
                        <p>This book is overdue by <b>${data.days_overdue} day(s)</b>.</p>
                        <p>Fine: <b>₱${data.fine}</b></p>
                    `,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Print Bill",
                    cancelButtonText: "Dismiss",
                }).then((result) => {
                    if (result.isConfirmed && data.borrow_id) {
                        window.open("print_bill.php?borrow_id=" + data.borrow_id, "_blank");
                    }
                });

                // ✅ Clear and refocus input
                barcodeInput.value = "";
                barcodeInput.focus();
            }

            // ✅ If success and not overdue
            else if (data.success) {
                const successMsg = document.getElementById("returnSuccess");
                successMsg.classList.remove("hidden");
                barcodeInput.value = "";
                barcodeInput.focus();

                setTimeout(() => {
                    successMsg.classList.add("hidden");
                }, 2000);
            }

            // ❌ If error from backend
            else {
                const errorMsg = document.getElementById("returnError");
                errorMsg.classList.remove("hidden");
                barcodeInput.value = "";
                barcodeInput.focus();

                setTimeout(() => {
                    errorMsg.classList.add("hidden");
                }, 2000);
            }
        })
        .catch(() => {
            hideMessages();
            Swal.fire("Error", "Server error while processing return.", "error");
        });
}

})

