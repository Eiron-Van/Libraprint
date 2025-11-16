document.addEventListener("DOMContentLoaded", () => {
    const bookDueDateInput = document.getElementById('book-due-date');
    const bookDueDateLabel = document.getElementById('book-due-date-label');
    const feedbackMessage = document.getElementById('feedback-message');
    const overdueEmailIntervalInput = document.getElementById('overdue-email-interval');
    const overdueEmailIntervalLabel = document.getElementById('overdue-email-interval-label');
    const intervalFeedback = document.getElementById('interval-feedback-message');

    let lastLoadedDueDate = null;
    let lastLoadedInterval = null;

    // ===== UPDATE LABEL =====
    // When value is 1, show "day" instead of "days"
    function updateLabel() {
        if (bookDueDateInput.value === '1') {
            bookDueDateLabel.textContent = 'day';
        } else {
            bookDueDateLabel.textContent = 'days';
        }
    }

    // ===== UPDATE INTERVAL LABEL =====
    // When value is 1, show "day" instead of "days"
    function updateIntervalLabel() {
        if (overdueEmailIntervalInput.value === '1') {
            overdueEmailIntervalLabel.textContent = 'day';
        } else {
            overdueEmailIntervalLabel.textContent = 'days';
        }
    }

    // ===== SHOW INTERVAL FEEDBACK =====
    // Display success or error messages
    function showIntervalFeedback(msg, isSuccess=true) {
        intervalFeedback.textContent = msg;
        intervalFeedback.className = `text-sm ${isSuccess ? 'text-green-400' : 'text-red-400'}`;
        intervalFeedback.classList.remove('hidden');
        setTimeout(() => {
            intervalFeedback.classList.add('hidden');
        }, 3000);
    }

    // ===== SHOW FEEDBACK =====
    // Display success or error messages
    function showFeedback(message, isSuccess = true) {
        feedbackMessage.textContent = message;
        feedbackMessage.className = `text-sm ${isSuccess ? 'text-green-400' : 'text-red-400'}`;
        feedbackMessage.classList.remove('hidden');
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            feedbackMessage.classList.add('hidden');
        }, 3000);
    }

    // ===== SAVE DUE DATE SETTING =====
    // Send the new value to the server
    async function saveDueDateSetting() {
        const numValue = Number(bookDueDateInput.value);
        if (numValue === Number(lastLoadedDueDate)) return;
        if (isNaN(numValue) || numValue < 1 || numValue > 365) {
            showFeedback('Due date must be between 1 and 365.', false);
            bookDueDateInput.value = lastLoadedDueDate || 7;
            updateLabel();
            return;
        }
        try {
            const response = await fetch('php/update_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ book_due_date: numValue })
            });
            const data = await response.json();
            if (data.success) {
                showFeedback(`✓ Successfully changed to ${numValue} day(s)`, true);
                lastLoadedDueDate = numValue;
            } else {
                showFeedback(data.error, false);
            }
            updateLabel();
        } catch (error) {
            showFeedback(`Network error: ${error.message}`, false);
            bookDueDateInput.value = lastLoadedDueDate || 7;
        }
    }
    
    // ===== SAVE INTERVAL SETTING =====
    // Send the new value to the server
    async function saveIntervalSetting() {
        const numValue = Number(overdueEmailIntervalInput.value);
        if (numValue === Number(lastLoadedInterval)) return;
        if (isNaN(numValue) || numValue < 1 || numValue > 30) {
            showIntervalFeedback('Interval must be between 1 and 30.', false);
            overdueEmailIntervalInput.value = lastLoadedInterval || 3;
            updateIntervalLabel();
            return;
        }
        try {
            const response = await fetch('php/update_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ overdue_email_interval: numValue })
            });
            const data = await response.json();
            if (data.success) {
                showIntervalFeedback(`✓ Interval set to ${numValue} day(s)`, true);
                lastLoadedInterval = numValue;
            } else {
                showIntervalFeedback(data.error, false);
            }
            updateIntervalLabel();
        } catch (error) {
            showIntervalFeedback(`Network error: ${error.message}`, false);
            overdueEmailIntervalInput.value = lastLoadedInterval || 3;
        }
    }

    // ===== LOAD SETTING =====
    // When page loads, get the current setting from database
    async function loadSettings() {
        try {
            const response = await fetch('php/get_settings.php');
            const data = await response.json();
            if (data.success) {
                bookDueDateInput.value = data.due_date_days;
                lastLoadedDueDate = Number(data.due_date_days);
                updateLabel();
                overdueEmailIntervalInput.value = data.overdue_email_interval_days;
                lastLoadedInterval = Number(data.overdue_email_interval_days);
                updateIntervalLabel();
            } else {
                console.error('Error loading settings:', data.error);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    // ===== EVENT LISTENERS =====

    // Update label when typing
    bookDueDateInput.addEventListener('input', updateLabel);
    overdueEmailIntervalInput.addEventListener('input', updateIntervalLabel);

    // Save setting when input loses focus or user presses enter
    bookDueDateInput.addEventListener('blur', saveDueDateSetting);
    bookDueDateInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') saveDueDateSetting();
    });

    overdueEmailIntervalInput.addEventListener('blur', saveIntervalSetting);
    overdueEmailIntervalInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') saveIntervalSetting();
    });

    // Load settings
    loadSettings();

});