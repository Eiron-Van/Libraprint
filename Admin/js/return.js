function processReturn(barcode) {
    fetch("api/return_book.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "barcode=" + encodeURIComponent(barcode),
    })
        .then((res) => res.json())
        .then((data) => {
            hideMessages();

            if (data.success && data.overdue) {
                // ✅ Show overdue fine alert
                Swal.fire({
                    title: "Overdue Book!",
                    html: `
                        <p><b>${data.book_title}</b></p>
                        <p>This book is overdue by <b>${data.days_overdue} day(s)</b>.</p>
                        <p>Fine Amount: <b>₱${data.fine}</b></p>
                    `,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Print Bill",
                    cancelButtonText: "Dismiss",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Open printable bill page
                        window.open("print_bill.php?borrow_id=" + data.borrow_id, "_blank");
                    }
                });

                // ✅ Clear barcode input
                const barcodeInput = document.getElementById("returnBarcode");
                barcodeInput.value = "";
                barcodeInput.focus();
            } 
            else if (data.success) {
                // ✅ Normal return
                Swal.fire("Returned!", "Book successfully returned.", "success");
                const barcodeInput = document.getElementById("returnBarcode");
                barcodeInput.value = "";
                barcodeInput.focus();
            } 
            else {
                Swal.fire("Error", data.error || "Unknown error occurred", "error");
            }
        })
        .catch(() => {
            Swal.fire("Error", "Server error while processing return.", "error");
        });
}
