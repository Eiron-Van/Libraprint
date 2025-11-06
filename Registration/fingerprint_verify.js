let sdk = new Fingerprint.WebApi();

function startCapture() {
    sdk.startAcquisition(Fingerprint.SampleFormat.Intermediate)
        .then(() => {
            console.log("Scanner started, waiting for fingerprint...");
        })
        .catch(err => console.error("Acquisition error:", err));
}

sdk.onSamplesAcquired = async function (s) {
    try {
        // try to open the app without navigating away from this page
        window.location.href = "libraprint-v://session";

        // reload after a short delay so the page refreshes even if the custom scheme returns quickly
        setTimeout(() => {
        // normal reload
        window.location.reload();
        // or force reload from server:
        // window.location.reload(true);
        }, 3000); // 500-1200 ms is a common choice; adjust as needed

    } catch (ex) {
        console.error("Scan Again", ex);
    }
};

// start listening as soon as page loads
window.onload = startCapture;
