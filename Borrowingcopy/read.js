document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.getElementById("overlay");
  const readBookBtn = document.querySelector(".fa-book-reader").closest("button");
  const closeOverlayBtn = document.getElementById("closeOverlayBtn");
  const saveBookBtn = document.getElementById("saveBookBtn");
  const barcodeInput = document.getElementById("barcodeInput");
  const bookListItems = document.getElementById("bookListItems");
  let scannedBooks = [];

  // Open overlay when "Read a Book" button is clicked
  readBookBtn.addEventListener("click", (e) => {
    e.preventDefault();
    overlay.classList.remove("hidden");
    overlay.classList.add("flex");
    barcodeInput.focus();
  });

  // Close overlay
  closeOverlayBtn.addEventListener("click", () => {
    overlay.classList.add("hidden");
  });

  // Save book barcode
  saveBookBtn.addEventListener("click", async () => {
    const barcode = barcodeInput.value.trim();
    if (!barcode) return alert("Please scan or type a barcode first.");

    // Add to local list (for display)
    scannedBooks.push(barcode);
    const li = document.createElement("li");
    li.textContent = barcode;
    bookListItems.appendChild(li);


    console.log("Sending barcode:", barcode); //for testing

    // Send to PHP (backend)
    const response = await fetch("save_book.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ barcode }),
    });

    const result = await response.json();
    if (result.success) {
      barcodeInput.value = "";
      barcodeInput.focus();
    } else {
      alert("Error saving book: " + result.message);
    }
  });

  // Auto submit on barcode scan (scanner acts like keyboard)
  barcodeInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") saveBookBtn.click();
  });
});
