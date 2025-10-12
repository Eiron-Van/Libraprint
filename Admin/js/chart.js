document.addEventListener("DOMContentLoaded", () => {
    console.log("Fetching analytics data...");
    Chart.defaults.color = 'white';

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

    fetch("api/book_usage.php")
        .then(res => res.json())
        .then(data => {
            console.log("Data Recieved")
            // Update KPI Cards
            document.getElementById("totalBooks").textContent = data.totalBooks;
            document.getElementById("readBooks").textContent = data.readMonth;
            document.getElementById("usageRate").textContent = data.usageRate + "%";

            // Most Read Books (Bar Chart)
            new Chart(document.getElementById("topBooksChart"), {
            type: "bar",
            data: {
                labels: data.topBooks.labels,
                datasets: [{
                label: "Read Count",
                data: data.topBooks.counts,
                backgroundColor: "#36A2EB"
                }]
            },
            options: {
                indexAxis: "y", // horizontal bars
                plugins: { legend: { display: false } }
            }
            });

            // Most Borrowed Genres (Horizontal Bar)
            new Chart(document.getElementById("genreChart"), {
            type: "bar",
            data: {
                labels: data.genre.labels,
                datasets: [{
                label: "Read Count",
                data: data.genre.counts,
                backgroundColor: "#FFCE56"
                }]
            },
            options: {
                indexAxis: "y",
                plugins: { legend: { display: false } }
            }
            });
        })
    .catch(err => console.error("Book Usage Analytics Error:", err));
});
