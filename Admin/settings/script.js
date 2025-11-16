document.addEventListener("DOMContentLoaded", () => {
    const bookDueDateInput = document.getElementById('book-due-date');
    const bookDueDateLabel = document.getElementById('book-due-date-label');
    const feedbackMessage = document.getElementById('feedback-message');

    let lastLoadedDueDate = null;

    // ===== UPDATE LABEL =====
    // When value is 1, show "day" instead of "days"
    function updateLabel() {
        if (bookDueDateInput.value === '1') {
            bookDueDateLabel.textContent = 'day';
        } else {
            bookDueDateLabel.textContent = 'days';
        }
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

    // ===== SAVE SETTING =====
    // Send the new value to the server
    async function saveSetting() {
        const value = bookDueDateInput.value.trim();
        
        // Check if empty
        if (value === '' || value === null) {
            bookDueDateInput.value = 7;
            showFeedback('Value was empty. Reset to 7 days.', true);
            updateLabel();
            return;
        }
        
        // Convert to number
        const numValue = parseInt(value);

        // If the value hasn't changed, do nothing (on blur etc)
        if (numValue === lastLoadedDueDate) return;
        
        // Check if it's a valid number
        if (isNaN(numValue)) {
            showFeedback('Error: Please enter a valid number', false);
            bookDueDateInput.value = 7;
            updateLabel();
            return;
        }
        
        // Check if it's at least 1
        if (numValue < 1) {
            showFeedback('Error: Due date must be at least 1 day', false);
            bookDueDateInput.value = 7;
            updateLabel();
            return;
        }
        
        // Check if it's not more than 365
        if (numValue > 365) {
            showFeedback('Error: Due date cannot exceed 365 days', false);
            bookDueDateInput.value = 365;
            updateLabel();
            return;
        }
        
        // All validation passed, now send to server
        try {
            const response = await fetch('php/update_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    book_due_date: numValue
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showFeedback(`✓ Successfully changed to ${numValue} day(s)`, true);
                updateLabel();
            } else {
                showFeedback(`Error: ${data.error}`, false);
                bookDueDateInput.value = 7;
                updateLabel();
            }
        } catch (error) {
            showFeedback(`Error: ${error.message}`, false);
            bookDueDateInput.value = 7;
            updateLabel();
        }
    }

    // ===== LOAD SETTING =====
    // When page loads, get the current setting from database
    async function loadSetting() {
        try {
            const response = await fetch('php/get_settings.php');
            const data = await response.json();
            
            if (data.success) {
                bookDueDateInput.value = data.due_date_days;
                lastLoadedDueDate = data.due_date_days;
                updateLabel();
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

    // Save when pressing Enter
    bookDueDateInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            saveSetting();
        }
    });

    // Save when clicking outside (blur)
    bookDueDateInput.addEventListener('blur', saveSetting);

    // Load settings when page loads
    window.addEventListener('DOMContentLoaded', loadSetting);

});