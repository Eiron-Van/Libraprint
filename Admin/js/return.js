document.addEventListener("DOMContentLoaded", () => {

    // ✅ Show overlay when button is clicked
    document.getElementById("returnBookBtn").addEventListener("click", () => {
        const overlay = document.getElementById("returnOverlay");
        overlay.classList.remove("hidden");
        overlay.classList.add("flex");
        document.getElementById("returnBarcode").focus();
    });

    // ✅ Hide overlay when clicking "X" or outside
    document.getElementById("closeReturnBtn").addEventListener("click", closeOverlay);
    document.getElementById("returnOverlay").addEventListener("click", (e) => {
        if (e.target.id === "returnOverlay") closeOverlay();
    });

    function closeOverlay() {
        const overlay = document.getElementById("returnOverlay");
        overlay.classList.add("hidden");
        overlay.classList.remove("flex");
        document.getElementById("returnBarcode").value = "";
        hideMessages();
    }

    // ✅ Hide messages
    function hideMessages() {
        document.getElementById("returnSuccess").classList.add("hidden");
        document.getElementById("returnError").classList.add("hidden");
    }

    // ✅ Handle barcode scan/enter
    document.getElementById("returnBarcode").addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
            const barcode = e.target.value.trim();
            if (barcode) processReturn(barcode);
        }
    });

    // ✅ Send request to backend
    function processReturn(barcode) {
        // Get selected condition
        const conditionInput = document.querySelector('input[name="condition"]:checked');
        const condition = conditionInput ? conditionInput.value : "Good Condition";

        fetch("api/return_book.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "barcode=" + encodeURIComponent(barcode) + "&condition=" + encodeURIComponent(condition),
        })
            .then((res) => res.json())
            .then((data) => {
                hideMessages();

                // ✅ If successful
                if (data.success) {
                    const barcodeInput = document.getElementById("returnBarcode");
                    barcodeInput.value = "";
                    barcodeInput.focus();

                    if (data.overdue) {
                        // ✅ Show billing overlay receipt
                        showBillingReceipt(data);
                    } else {
                        // ✅ Show normal success message
                        const successMsg = document.getElementById("returnSuccess");
                        successMsg.classList.remove("hidden");
                        setTimeout(() => successMsg.classList.add("hidden"), 2000);
                    }

                } else {
                    // ❌ Show error
                    const errorMsg = document.getElementById("returnError");
                    errorMsg.classList.remove("hidden");

                    const barcodeInput = document.getElementById("returnBarcode");
                    barcodeInput.value = "";
                    barcodeInput.focus();

                    setTimeout(() => errorMsg.classList.add("hidden"), 2000);
                }

            })
            .catch(() => {
                hideMessages();
                document.getElementById("returnError").classList.remove("hidden");
            });
    }

    // ✅ Function to show billing receipt overlay
    function showBillingReceipt(data) {
        const receiptOverlay = document.getElementById("receiptOverlay");
        document.getElementById("receiptBorrower").innerText = data.borrower;
        document.getElementById("receiptBook").innerText = data.book_title;
        document.getElementById("receiptDays").innerText = data.days_overdue;
        document.getElementById("receiptPenalty").innerText = data.penalty.toFixed(2);
        document.getElementById("receiptDate").innerText = new Date().toLocaleString();

        receiptOverlay.classList.remove("hidden");
        receiptOverlay.classList.add("flex");
    }

    // ✅ Hide receipt overlay when clicking close or outside
    document.getElementById("closeReceiptBtn").addEventListener("click", () => {
        const receiptOverlay = document.getElementById("receiptOverlay");
        receiptOverlay.classList.add("hidden");
        receiptOverlay.classList.remove("flex");
    });

    document.getElementById("receiptOverlay").addEventListener("click", (e) => {
        if (e.target.id === "receiptOverlay") {
            const receiptOverlay = document.getElementById("receiptOverlay");
            receiptOverlay.classList.add("hidden");
            receiptOverlay.classList.remove("flex");
        }
    });
});
