let sdk = new Fingerprint.WebApi();
let fingerprintDetected = false;
let verifyButton = null;
let isCapturing = false;

function startCapture() {
    if (isCapturing) return; // Already capturing
    
    isCapturing = true;
    sdk.startAcquisition(Fingerprint.SampleFormat.Intermediate)
        .then(() => {
            console.log("Scanner started, waiting for fingerprint...");
        })
        .catch(err => {
            console.error("Acquisition error:", err);
            isCapturing = false;
        });
}

function openVerificationApp() {
    try {
        // Use a user-initiated action to open the app
        window.location.href = "libraprint-v://session";
        // Reset the flag after attempting to open
        fingerprintDetected = false;
        if (verifyButton) {
            verifyButton.style.display = 'none';
        }
        // Restart capture after a short delay
        setTimeout(() => {
            isCapturing = false;
            startCapture();
        }, 1000);
    } catch (ex) {
        console.error("Failed to open verification app:", ex);
        alert("Failed to open fingerprint scanner. Please try again.");
        isCapturing = false;
    }
}

sdk.onSamplesAcquired = async function (s) {
    // Store that fingerprint was detected, but don't open app automatically
    // This requires a user gesture (click) to open custom URL schemes
    fingerprintDetected = true;
    
    // Stop capturing while showing the button
    isCapturing = false;
    
    // Show a button that requires user click
    if (!verifyButton) {
        // Create a button if it doesn't exist
        verifyButton = document.createElement('button');
        verifyButton.textContent = 'Click to Verify Fingerprint';
        verifyButton.className = 'bg-[#5364a2] hover:bg-[#7a88bb] active:bg-[#6b78ac] text-white px-5 py-2 rounded-xl mt-3 cursor-pointer';
        verifyButton.style.display = 'block';
        verifyButton.style.margin = '10px auto';
        verifyButton.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            openVerificationApp();
        };
        
        // Find the fingerprint-step section and add the button
        const fingerprintStep = document.getElementById('fingerprint-step');
        if (fingerprintStep) {
            // Insert before the paragraph
            const paragraph = fingerprintStep.querySelector('p');
            if (paragraph) {
                fingerprintStep.insertBefore(verifyButton, paragraph);
            } else {
                fingerprintStep.appendChild(verifyButton);
            }
        }
    } else {
        verifyButton.style.display = 'block';
    }
};

// start listening as soon as page loads
window.onload = startCapture;
