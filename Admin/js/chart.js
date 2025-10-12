document.addEventListener("DOMContentLoaded", () => {
    console.log("Fetching analytics data...");

    fetch("api/visitor_data.php")
        .then(res => res.json())
        .then(data => {
            console.log("Data Recieved")
            // Update KPI Card
            const totalVisitorsEl = document.querySelector("#totalVisitors");
            if (totalVisitorsEl) totalVisitorsEl.textContent = data.totalVisitors;

            // Daily Attendance Line Chart
            new Chart(document.getElementById("attendanceChart"), {
                type: "line",
                data: {
                labels: data.daily.dates,
                datasets: [{
                    label: "Visitors per Day",
                    data: data.daily.counts,
                    borderColor: "#00FFFF",
                    fill: false,
                    tension: 0.3
                }]
                }
            });

            // Purpose Distribution Donut Chart
            new Chart(document.getElementById("purposeChart"), {
                type: "doughnut",
                data: {
                labels: data.purpose.labels,
                datasets: [{
                    data: data.purpose.counts,
                    backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56"]
                }]
                }
            });
        })
    .catch(err => console.error("Analytics data error:", err));
});
