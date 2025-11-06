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


    // AFK Overlay

    const form = document.getElementById("registrationForm");
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(form);

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
                } else if (data.error) {
                    alert(data.error);
                } else if (data.text === "OK") {
                    const mainContent = document.getElementById("main-content");
                    if (mainContent) {
                        mainContent.classList.add("blur-sm", "pointer-events-none");
                    }
                    const overlay = document.getElementById("overlay");
                    const fingerprintStep = document.getElementById("fingerprint-step");
                    if (overlay) overlay.classList.remove("hidden");
                    if (fingerprintStep) fingerprintStep.classList.remove("hidden");
                } else {
                    alert("Error: " + (data.text || "Unexpected response from server."));
                }
            })
            .catch(err => alert("Request failed: " + err));
    });

    const overlay = document.getElementById("inactivity-overlay");
    let inactivityTimer;

    function showOverlay() {
        overlay.classList.remove("hidden");
        overlay.classList.add("flex");
    }

    function resetTimer() {
        // Hide overlay if user becomes active again
        overlay.classList.add("hidden");
        overlay.classList.remove("flex");

        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(showOverlay, 2 * 60 * 1000); // 2 minutes
    }

    // Reset timer on any user activity
    ["mousemove", "keydown", "click", "touchstart"].forEach(event => {
        document.addEventListener(event, resetTimer);
    });

    // Start the first timer
    resetTimer();

});