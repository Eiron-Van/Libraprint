document.addEventListener("DOMContentLoaded", () => {
  const borrowBtn = document.querySelectorAll("button span");
  const termsOverlay = document.getElementById("termsOverlay");
  const closeTerms = document.getElementById("closeTerms");
  const cancelTerms = document.getElementById("cancelTerms");
  const continueBtn = document.getElementById("continueBtn");
  const agreeCheckbox = document.getElementById("agreeCheckbox");
  const termsContent = document.getElementById("termsContent");
  const agreementLabel = document.getElementById("agreementLabel");

  // Find the "Borrow a Book" button
  document.querySelectorAll("button").forEach(btn => {
    if (btn.innerText.includes("Borrow a Book")) {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        termsOverlay.classList.remove("hidden");
        termsOverlay.classList.add("flex");
      });
    }
  });

  // Close overlay
  [closeTerms, cancelTerms].forEach(el => {
    el.addEventListener("click", () => {
      termsOverlay.classList.add("hidden");
      termsOverlay.classList.remove("flex");
      agreeCheckbox.checked = false;
      agreeCheckbox.disabled = true;
      continueBtn.disabled = true;
      continueBtn.classList.add("bg-blue-400", "cursor-not-allowed");
      continueBtn.classList.remove("bg-blue-600");
    });
  });

  // Enable checkbox when scrolled to bottom
  termsContent.addEventListener("scroll", () => {
    const { scrollTop, scrollHeight, clientHeight } = termsContent;
    if (scrollTop + clientHeight >= scrollHeight - 10) {
      agreeCheckbox.disabled = false;
      agreementLabel.classList.remove("text-gray-400");
      agreementLabel.classList.add("text-gray-700");
    }
  });

  // Enable "Continue" button only when checkbox is checked
  agreeCheckbox.addEventListener("change", () => {
    if (agreeCheckbox.checked) {
      continueBtn.disabled = false;
      continueBtn.classList.remove("bg-blue-400", "cursor-not-allowed");
      continueBtn.classList.add("bg-blue-600", "hover:bg-blue-700");
    } else {
      continueBtn.disabled = true;
      continueBtn.classList.add("bg-blue-400", "cursor-not-allowed");
      continueBtn.classList.remove("bg-blue-600", "hover:bg-blue-700");
    }
  });

  // Proceed to another page (redirect)
  continueBtn.addEventListener("click", () => {
    window.location.href = "borrow_book.php"; // change this to your next page
  });
});
