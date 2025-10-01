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
        // Fetch the PHP data
        let response = await fetch("fingerprint_verify.php");
        let result = await response.json();

        if (result.success) {
            // Encode data so it can be passed in URL
            let encoded = encodeURIComponent(JSON.stringify(result.data));

            // Send it to your custom protocol handler
            window.location.href = "libraprint-e://session?data=" + encoded;
        } else {
            console.error("No fingerprint data found");
        }
    } catch (ex) {
        console.error("Scan Again", ex);
    }
};

// start listening as soon as page loads
window.onload = startCapture;
