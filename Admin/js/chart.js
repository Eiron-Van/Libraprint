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

            // Age Group Distribution (Bar)
            new Chart(document.getElementById("ageGroupChart"), {
            type: "bar",
            data: {
                labels: data.age.labels,
                datasets: [{
                label: "Visitors",
                data: data.age.counts,
                backgroundColor: [
                    "#FF6384",
                    "#36A2EB",
                    "#FFCE56",
                    "#4BC0C0",
                    "#9966FF"
                ]
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
            });

            // Gender Distribution (Pie Chart)
            if (data.gender.labels.length > 0) {
            new Chart(document.getElementById("genderChart"), {
                type: "pie",
                data: {
                labels: data.gender.labels,
                datasets: [{
                    data: data.gender.counts,
                    backgroundColor: [
                    "#36A2EB", // Male
                    "#FF6384", // Female
                    "#FF9F40", // Lesbian
                    "#9966FF", // Gay
                    "#4BC0C0", // Bisexual
                    "#C9CBCF", // Transgender
                    "#FFCD56", // Queer/Questioning
                    "#9AD0F5", // Other
                    "#666666"  // Unknown
                    ]
                }]
                },
                options: {
                plugins: {
                    legend: {
                    position: "bottom",
                    labels: {
                        color: "white",
                        boxWidth: 15
                    }
                    },
                    tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${ctx.formattedValue}`
                    }
                    }
                }
                }
            });
            } else {
            console.warn("No gender data found this month.");
            }
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

    
    fetch("api/reading_trends.php")
        .then(res => res.json())
        .then(data => {
            console.log("Data Recieved")
            // KPI Cards
            document.getElementById("avgMonthlyReads").textContent = data.avgMonthlyReads;
            document.getElementById("readCount").textContent = data.readCount;
            document.getElementById("reserveCount").textContent = data.reserveCount;

            // Monthly Reading Trend (Line)
            new Chart(document.getElementById("monthlyTrendChart"), {
            type: "line",
            data: {
                labels: data.monthly.labels,
                datasets: [{
                label: "Books Read",
                data: data.monthly.counts,
                borderColor: "#36A2EB",
                fill: false,
                tension: 0.3
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
            });

            // Quarterly Comparison (Bar)
            new Chart(document.getElementById("quarterlyChart"), {
            type: "bar",
            data: {
                labels: data.quarterly.labels,
                datasets: [{
                label: "Reads",
                data: data.quarterly.counts,
                backgroundColor: "#4BC0C0"
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
            });

            // Read vs Reservation Ratio (Donut)
            new Chart(document.getElementById("readReserveChart"), {
            type: "doughnut",
            data: {
                labels: ["Read", "Reserved"],
                datasets: [{
                data: [data.readCount, data.reserveCount],
                backgroundColor: ["#36A2EB", "#FFCE56"]
                }]
            },
            options: {
                plugins: { legend: { position: "bottom" } }
            }
            });
        })
    .catch(err => console.error("Reading Trends Error:", err));
});
