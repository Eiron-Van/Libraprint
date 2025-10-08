document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.getElementById("overlay");
  const borrowBookBtn = document.getElementById("borrowBookBtn");
  const closeOverlayBtn = document.getElementById("closeOverlayBtn");
  const saveBookBtn = document.getElementById("saveBookBtn");
  const barcodeInput = document.getElementById("barcodeInput");
  const bookListItems = document.getElementById("bookListItems");
  const successMsg = document.getElementById("successMsg");

  let scannedBooks = [];

  // ✅ Open overlay for index.php
  borrowBookBtn.addEventListener("click", (e) => {
    e.preventDefault();
    overlay.classList.remove("hidden");
    overlay.classList.add("flex");
    barcodeInput.focus();
  });

  // ✅ Close overlay
  closeOverlayBtn.addEventListener("click", () => {
    overlay.classList.add("hidden");
  });

  // ✅ Save book
  saveBookBtn.addEventListener("click", async () => {
    const barcode = barcodeInput.value.trim();
    if (!barcode) return alert("Please scan or type a barcode first.");

    // Send barcode to backend
    try {
      const response = await fetch("borrow_function.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ barcode }),
      });

      const result = await response.json();
      console.log("Response:", result);

      if (result.success) {
        // ✅ Show success message briefly
        successMsg.classList.remove("hidden");
        setTimeout(() => successMsg.classList.add("hidden"), 1500);

        // ✅ Add scanned book to the list
        scannedBooks.push(barcode);
        const li = document.createElement("li");
        li.textContent = `${result.title ? result.title + " – " : ""}Barcode: ${barcode}`;
        bookListItems.appendChild(li);

        // Clear input for next scan
        barcodeInput.value = "";
        barcodeInput.focus();

        // window.location.href = "borrow_book.php?success=1";

      } else {
        alert("Error: " + result.message);
      }
    } catch (error) {
      console.error("Fetch error:", error);
      alert("Connection error while saving book.");
    }
  });

  // ✅ Auto-submit on Enter key
  barcodeInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") saveBookBtn.click();
  });
});
