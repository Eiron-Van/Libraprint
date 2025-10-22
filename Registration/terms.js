document.addEventListener("DOMContentLoaded", () => {
    
    // Get elements
    const termsCheckboxButton = document.getElementById('termsCheckboxBtn');
    const termsCheckbox = document.getElementById('termsCheckbox');
    const submitBtn = document.getElementById('submitBtn');
    const termsOverlay = document.getElementById('termsOverlay');
    const termsButton = document.getElementById('termsBtn');
    const closeTerms = document.getElementById('closeTerms');

    termsCheckbox.addEventListener('click', () => {
        if (termsCheckbox.checked) {
            termsCheckbox.checked = false;
        }
    });

    termsCheckbox.addEventListener('click', () => {
        if (!termsCheckbox.checked) {
            termsOverlay.classList.remove('hidden');
            termsOverlay.classList.add('flex');
        }
    });

    termsCheckboxButton.addEventListener('click', () => {
        if (!termsCheckbox.checked) {
            termsOverlay.classList.remove('hidden');
            termsOverlay.classList.add('flex');
        }else{
            termsCheckbox.checked = false;
        }
    });

    termsButton.addEventListener('click', function () {
        termsCheckbox.checked = true; // check the box
        termsOverlay.classList.add('hidden');
        termsOverlay.classList.remove('flex');
    });


    // Enable/disable submit button based on checkbox
    termsCheckbox.addEventListener('change', () => {
        if (termsCheckbox.checked) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-400', 'opacity-70', 'cursor-not-allowed');
            submitBtn.classList.add('bg-[#5364a2]', 'hover:bg-[#7a88bb]', 'active:bg-[#6b78ac]', 'cursor-pointer');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('bg-gray-400', 'opacity-70', 'cursor-not-allowed');
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
            .then(res => res.text())
            .then(data => {
                if (data.trim() === "OK") {
                    document.getElementById("main-content").classList.add("blur-sm", "pointer-events-none");
                    document.getElementById("overlay").classList.remove("hidden");
                    document.getElementById("fingerprint-step").classList.remove("hidden");
                } else {
                    alert("Error: " + data);
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