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
        fetch("api/return_book.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "barcode=" + encodeURIComponent(barcode),
        })
            .then((res) => res.json())
            .then((data) => {
                hideMessages();
                if (data.success) {
                    const successMsg = document.getElementById("returnSuccess");
                    successMsg.classList.remove("hidden");

                    // ✅ Clear barcode input
                    const barcodeInput = document.getElementById("returnBarcode");
                    barcodeInput.value = "";
                    barcodeInput.focus();

                    // ✅ Hide message after 2 seconds
                    setTimeout(() => {
                        successMsg.classList.add("hidden");
                    }, 2000);
                } else {
                    const errorMsg = document.getElementById("returnError");
                    errorMsg.classList.remove("hidden");

                    // ✅ Clear invalid input
                    const barcodeInput = document.getElementById("returnBarcode");
                    barcodeInput.value = "";
                    barcodeInput.focus();

                    // ✅ Hide message after 2 seconds
                    setTimeout(() => {
                        errorMsg.classList.add("hidden");
                    }, 2000);
                }

            })
            .catch(() => {
                hideMessages();
                document.getElementById("returnError").classList.remove("hidden");
            });
    }
});