document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const resultsContainer = document.getElementById("results");

  const configOverlay = document.getElementById("configuration");
  const configContext = document.getElementById("config-context");
  const configForm = document.getElementById("config-form");
  const configDueInput = document.getElementById("config-due-date");
  const configIntervalInput = document.getElementById("config-email-interval");
  const dueHelper = document.getElementById("due-date-helper");
  const intervalHelper = document.getElementById("email-interval-helper");
  const configMessage = document.getElementById("config-message");
  const configCloseButtons = [
    document.getElementById("config-close"),
    document.getElementById("config-cancel"),
  ];
  const configResetButton = document.getElementById("config-reset");

  let activeConfig = null;

  // Track which log type is active
  let currentFile = "fetch_login_logs.php"; // default view

  // Generic function to fetch results
  function fetchResults(query = "") {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `${currentFile}?search=${encodeURIComponent(query)}`, true);

    xhr.onload = function () {
      if (xhr.status === 200) {
        resultsContainer.innerHTML = xhr.responseText;
      } else {
        resultsContainer.innerHTML = `<div class='text-red-400 text-center py-4'>Error loading data.</div>`;
      }
    };

    resultsContainer.innerHTML =
      "<div class='text-center py-4 text-gray-100'>Loading...</div>";
    xhr.send();
  }

  function showConfigMessage(text, isError = false) {
    configMessage.textContent = text;
    configMessage.classList.remove("hidden");
    configMessage.className = `text-sm mt-2 ${isError ? "text-red-600" : "text-green-600"
      }`;
  }

  function clearConfigMessage() {
    configMessage.classList.add("hidden");
    configMessage.textContent = "";
  }

  function openConfigOverlay(button) {
    activeConfig = {
      userId: button.dataset.userId,
      bookId: button.dataset.bookId,
      borrowId: button.dataset.borrowId,
      userName: button.dataset.userName,
      bookTitle: button.dataset.bookTitle,
      defaultDue: parseInt(button.dataset.defaultDue, 10) || 7,
      defaultInterval: parseInt(button.dataset.defaultInterval, 10) || 3,
      customDue: button.dataset.customDue || "",
      customInterval: button.dataset.customInterval || "",
    };

    configContext.textContent = `${activeConfig.userName} • ${activeConfig.bookTitle}`;
    configDueInput.value = activeConfig.customDue;
    configDueInput.placeholder = `${activeConfig.defaultDue}`;
    dueHelper.textContent = `Default: ${activeConfig.defaultDue} day(s)`;

    configIntervalInput.value = activeConfig.customInterval;
    configIntervalInput.placeholder = `${activeConfig.defaultInterval}`;
    intervalHelper.textContent = `Default: ${activeConfig.defaultInterval} day(s)`;

    clearConfigMessage();
    configOverlay.classList.remove("hidden");
  }

  function closeConfigOverlay() {
    activeConfig = null;
    configOverlay.classList.add("hidden");
  }

  async function submitConfiguration(payload) {
    const response = await fetch("update_borrow_config.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: "Unknown error" }));
      throw new Error(error.error || "Request failed");
    }

    return response.json();
  }

  // Initial load
  fetchResults("");

  // Debounced live search
  let timer;
  searchInput.addEventListener("input", function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      fetchResults(this.value);
    }, 300);
  });

  // Listen for dropdown button clicks
  document.querySelectorAll(".dropdown-log").forEach((btn) => {
    btn.addEventListener("click", function () {
      const type = this.textContent.trim();

      // Determine which file to fetch
      switch (type) {
        case "Login Logs":
          currentFile = "fetch_login_logs.php";
          break;
        case "Read Logs":
          currentFile = "fetch_read_logs.php";
          break;
        case "Reservation Logs":
          currentFile = "fetch_reservation_logs.php";
          break;
        case "Claim Logs":
          currentFile = "fetch_claim_logs.php";
          break;
        case "Borrow Logs":
          currentFile = "fetch_borrow_logs.php";
          break;
        case "Overdue Logs":
          currentFile = "fetch_overdue_logs.php";
          break;
        case "Delinquent Logs":
          currentFile = "fetch_delinquent_logs.php";
          break;
      }

      // Reset search field and fetch data
      searchInput.value = "";
      fetchResults("");

      // Active style (highlight the selected log)
      document.querySelectorAll(".dropdown-log").forEach((el) => {
        el.classList.remove("bg-gray-600");
      });
      this.classList.add("bg-gray-600");
    });
  });

  // Delegated handler for configuration buttons
  resultsContainer.addEventListener("click", (event) => {
    const button = event.target.closest(".config-btn");
    if (!button) return;
    openConfigOverlay(button);
  });

  // Delegated handler for ping buttons
  resultsContainer.addEventListener("click", async (event) => {
    const button = event.target.closest(".ping-btn");
    if (!button) return;

    const borrowId = button.dataset.borrowId;
    if (!borrowId) return;

    // Disable button and show loading state
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = "Sending...";
    button.classList.add("opacity-50", "cursor-not-allowed");

    try {
      const formData = new FormData();
      formData.append("borrow_id", borrowId);

      const response = await fetch("ping_overdue_email.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.status === "success") {
        button.textContent = "Sent!";
        button.classList.remove("bg-blue-500", "hover:bg-blue-600");
        button.classList.add("bg-green-500");
        
        // Show success message temporarily
        setTimeout(() => {
          button.textContent = originalText;
          button.classList.remove("bg-green-500", "opacity-50", "cursor-not-allowed");
          button.classList.add("bg-blue-500", "hover:bg-blue-600");
          button.disabled = false;
        }, 2000);
      } else {
        button.textContent = "Error";
        button.classList.remove("bg-blue-500", "hover:bg-blue-600");
        button.classList.add("bg-red-500");
        
        // Show error message
        alert(result.message || "Failed to send email");
        
        setTimeout(() => {
          button.textContent = originalText;
          button.classList.remove("bg-red-500", "opacity-50", "cursor-not-allowed");
          button.classList.add("bg-blue-500", "hover:bg-blue-600");
          button.disabled = false;
        }, 2000);
      }
    } catch (error) {
      button.textContent = "Error";
      button.classList.remove("bg-blue-500", "hover:bg-blue-600");
      button.classList.add("bg-red-500");
      alert("An error occurred: " + error.message);
      
      setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove("bg-red-500", "opacity-50", "cursor-not-allowed");
        button.classList.add("bg-blue-500", "hover:bg-blue-600");
        button.disabled = false;
      }, 2000);
    }
  });

  configForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!activeConfig) return;

    const dueValue = configDueInput.value.trim();
    const intervalValue = configIntervalInput.value.trim();

    const payload = {
      user_id: activeConfig.userId,
      book_id: Number(activeConfig.bookId),
    };

    if (dueValue !== "") {
      const parsedDue = Number(dueValue);
      if (parsedDue < 1 || parsedDue > 365) {
        showConfigMessage("Due date must be between 1 and 365 days.", true);
        return;
      }
      payload.due_date_days = parsedDue;
    } else if (activeConfig.customDue) {
      payload.due_date_days = null;
    }

    if (intervalValue !== "") {
      const parsedInterval = Number(intervalValue);
      if (parsedInterval < 1 || parsedInterval > 30) {
        showConfigMessage("Email interval must be between 1 and 30 days.", true);
        return;
      }
      payload.overdue_email_interval_days = parsedInterval;
    } else if (activeConfig.customInterval) {
      payload.overdue_email_interval_days = null;
    }

    const hasDueChange = Object.prototype.hasOwnProperty.call(
      payload,
      "due_date_days"
    );
    const hasIntervalChange = Object.prototype.hasOwnProperty.call(
      payload,
      "overdue_email_interval_days"
    );

    if (!hasDueChange && !hasIntervalChange) {
      showConfigMessage("Enter at least one custom value or reset to default.", true);
      return;
    }

    showConfigMessage("Saving...", false);

    try {
      await submitConfiguration(payload);
      showConfigMessage("Configuration saved.", false);
      setTimeout(() => {
        closeConfigOverlay();
        fetchResults(searchInput.value);
      }, 600);
    } catch (error) {
      showConfigMessage(error.message, true);
    }
  });

  configResetButton.addEventListener("click", async () => {
    if (!activeConfig) return;
    showConfigMessage("Resetting...", false);
    try {
      await submitConfiguration({
        user_id: activeConfig.userId,
        book_id: Number(activeConfig.bookId),
        clear: true,
      });
      showConfigMessage("Reverted to defaults.", false);
      setTimeout(() => {
        closeConfigOverlay();
        fetchResults(searchInput.value);
      }, 600);
    } catch (error) {
      showConfigMessage(error.message, true);
    }
  });

  configCloseButtons.forEach((btn) =>
    btn.addEventListener("click", () => {
      closeConfigOverlay();
    })
  );

  configOverlay.addEventListener("click", (event) => {
    if (event.target === configOverlay) {
      closeConfigOverlay();
    }
  });
});
