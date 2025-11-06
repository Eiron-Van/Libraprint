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
        window.open("libraprint-v://session");
    } catch (ex) {
        console.error("Scan Again", ex);
    }
};

// start listening as soon as page loads
window.onload = startCapture;
