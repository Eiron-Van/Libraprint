document.addEventListener("DOMContentLoaded", () => {

    // Get elements
    const termsCheckboxButton = document.getElementById('termsCheckboxBtn');
    const termsCheckbox = document.getElementById('termsCheckbox');
    const submitBtn = document.getElementById('submitBtn');
    const termsOverlay = document.getElementById('termsOverlay');
    const termsButton = document.getElementById('termsBtn');
    const closeTerms = document.getElementById('closeTerms');

    termsCheckboxButton.addEventListener('click', () => {
        if (!termsCheckbox.checked) {
            termsOverlay.classList.remove('hidden');
            termsOverlay.classList.add('flex');
        } else {
            termsCheckbox.checked = false;
        }
    });

    termsButton.addEventListener('click', function () {
        termsCheckbox.checked = true; // check the box
        termsCheckbox.dispatchEvent(new Event('change'));
        termsOverlay.classList.add('hidden');
        termsOverlay.classList.remove('flex');
    });


    // Enable/disable submit button based on checkbox
    termsCheckbox.addEventListener('change', () => {
        if (termsCheckbox.checked) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-600', 'opacity-70', 'cursor-not-allowed');
            submitBtn.classList.add('bg-[#5364a2]', 'hover:bg-[#7a88bb]', 'active:bg-[#6b78ac]', 'cursor-pointer');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('bg-gray-600', 'opacity-70', 'cursor-not-allowed');
            submitBtn.classList.remove('bg-[#5364a2]', 'hover:bg-[#7a88bb]', 'active:bg-[#6b78ac]', 'cursor-pointer');
        }
    });


    // Close overlay when clicking X button
    closeTerms.addEventListener('click', () => {
        termsOverlay.classList.add('hidden');
        termsOverlay.classList.remove('flex');
    });

    // Close overlay when clicking outside of it
    termsOverlay.addEventListener('click', (e) => {
        if (e.target === termsOverlay) {
            termsOverlay.classList.add('hidden');
            termsOverlay.classList.remove('flex');
        }
    });


    // AFK Overlay Management

    const form = document.getElementById("registrationForm");
    const inactivityOverlay = document.getElementById("inactivity-overlay");
    let inactivityTimer;
    let isRegistrationActive = false; // Track if user is actively registering
    let enrollmentPollInterval = null; // Track polling interval

    function showOverlay() {
        // Don't show inactivity overlay if registration is in progress
        if (isRegistrationActive) return;
        
        if (inactivityOverlay) {
            inactivityOverlay.classList.remove("hidden");
            inactivityOverlay.classList.add("flex");
        }
    }

    function resetTimer() {
        // Don't reset timer if registration is active
        if (isRegistrationActive) return;

        // Hide overlay if user becomes active again
        if (inactivityOverlay) {
            inactivityOverlay.classList.add("hidden");
            inactivityOverlay.classList.remove("flex");
        }

        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(showOverlay, 2 * 60 * 1000); // 2 minutes
    }

    let formActivityTimer = null;
    
    function disableInactivityTimer() {
        isRegistrationActive = true;
        clearTimeout(inactivityTimer);
        if (inactivityOverlay) {
            inactivityOverlay.classList.add("hidden");
            inactivityOverlay.classList.remove("flex");
        }
    }

    function enableInactivityTimer() {
        isRegistrationActive = false;
        resetTimer();
    }

    // Disable inactivity timer when form is being actively interacted with
    // Re-enable after 5 seconds of inactivity (unless in fingerprint enrollment)
    const formInputs = form.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('focus', () => {
            disableInactivityTimer();
            // Clear any pending re-enable timer
            if (formActivityTimer) {
                clearTimeout(formActivityTimer);
            }
        });
        
        input.addEventListener('input', () => {
            disableInactivityTimer();
            // Clear any pending re-enable timer
            if (formActivityTimer) {
                clearTimeout(formActivityTimer);
            }
            // Re-enable after 5 seconds of no input (but only if not in fingerprint enrollment)
            formActivityTimer = setTimeout(() => {
                // Only re-enable if we're not in the fingerprint enrollment phase
                // Check if fingerprint step is visible
                const fingerprintStep = document.getElementById("fingerprint-step");
                if (fingerprintStep && fingerprintStep.classList.contains("hidden")) {
                    enableInactivityTimer();
                }
            }, 5000);
        });
        
        input.addEventListener('blur', () => {
            // Re-enable after 5 seconds if not in fingerprint enrollment
            if (formActivityTimer) {
                clearTimeout(formActivityTimer);
            }
            formActivityTimer = setTimeout(() => {
                const fingerprintStep = document.getElementById("fingerprint-step");
                if (fingerprintStep && fingerprintStep.classList.contains("hidden")) {
                    enableInactivityTimer();
                }
            }, 5000);
        });
    });

    // Check if registration is complete by polling session
    function checkRegistrationStatus() {
        fetch('check_registration_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ check: true })
        })
        .then(res => res.json())
        .then(data => {
            if (data.registrationComplete) {
                // Registration is complete, clean up all timers
                clearInterval(enrollmentPollInterval);
                enrollmentPollInterval = null;
                if (formActivityTimer) {
                    clearTimeout(formActivityTimer);
                    formActivityTimer = null;
                }
                clearTimeout(inactivityTimer);
                
                // Hide fingerprint enrollment overlays
                const overlay = document.getElementById("overlay");
                const fingerprintStep = document.getElementById("fingerprint-step");
                if (overlay) overlay.classList.add("hidden");
                if (fingerprintStep) fingerprintStep.classList.add("hidden");
                
                // Show success message and redirect
                if (data.message) {
                    alert(data.message);
                    // Redirect to login or home page after 2 seconds
                    setTimeout(() => {
                        window.location.href = '/Login/index.php';
                    }, 2000);
                } else {
                    // Just redirect if no message
                    window.location.href = '/Login/index.php';
                }
            }
        })
        .catch(err => {
            console.error('Error checking registration status:', err);
        });
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(form);

        // Disable inactivity timer during registration
        disableInactivityTimer();

        fetch("", { method: "POST", body: formData })
            .then(res => {
                const contentType = res.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return res.json();
                } else {
                    return res.text().then(text => ({ text: text.trim() }));
                }
            })
            .then(data => {
                if (data.success) {
                    const mainContent = document.getElementById("main-content");
                    if (mainContent) {
                        mainContent.classList.add("blur-sm", "pointer-events-none");
                    }
                    const overlay = document.getElementById("overlay");
                    const fingerprintStep = document.getElementById("fingerprint-step");
                    if (overlay) overlay.classList.remove("hidden");
                    if (fingerprintStep) fingerprintStep.classList.remove("hidden");
                    
                    // Start polling for registration completion
                    if (enrollmentPollInterval) {
                        clearInterval(enrollmentPollInterval);
                    }
                    enrollmentPollInterval = setInterval(checkRegistrationStatus, 2000); // Check every 2 seconds
                    
                    // Keep inactivity timer disabled during fingerprint enrollment
                    // It will be re-enabled when registration completes
                } else if (data.error) {
                    alert(data.error);
                    // Re-enable inactivity timer if registration failed
                    enableInactivityTimer();
                } else if (data.text === "OK") {
                    const mainContent = document.getElementById("main-content");
                    if (mainContent) {
                        mainContent.classList.add("blur-sm", "pointer-events-none");
                    }
                    const overlay = document.getElementById("overlay");
                    const fingerprintStep = document.getElementById("fingerprint-step");
                    if (overlay) overlay.classList.remove("hidden");
                    if (fingerprintStep) fingerprintStep.classList.remove("hidden");
                    
                    // Start polling for registration completion
                    if (enrollmentPollInterval) {
                        clearInterval(enrollmentPollInterval);
                    }
                    enrollmentPollInterval = setInterval(checkRegistrationStatus, 2000);
                } else {
                    alert("Error: " + (data.text || "Unexpected response from server."));
                    // Re-enable inactivity timer if registration failed
                    enableInactivityTimer();
                }
            })
            .catch(err => {
                alert("Request failed: " + err);
                // Re-enable inactivity timer if request failed
                enableInactivityTimer();
            });
    });

    // Reset timer on any user activity (only if not in registration)
    ["mousemove", "keydown", "click", "touchstart"].forEach(event => {
        document.addEventListener(event, resetTimer);
    });

    // Start the first timer
    resetTimer();

});