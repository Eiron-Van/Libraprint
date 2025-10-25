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

            // Monthly Attendance Summary (Bar Chart)
            if (data.monthly && data.monthly.labels.length > 0) {
            new Chart(document.getElementById("monthlyAttendanceChart"), {
                type: "bar",
                data: {
                labels: data.monthly.labels,
                datasets: [{
                    label: "Total Visitors",
                    data: data.monthly.counts,
                    backgroundColor: "#36A2EB"
                },
                {
                type: "line",
                label: "Trend",
                data: data.monthly.counts,
                borderColor: "#FF6384",
                borderWidth: 2,
                fill: false
                }]
                },
                options: {
                scales: {
                    y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Visitors"
                    }
                    },
                    x: {
                    title: {
                        display: true,
                        text: "Month"
                    }
                    }
                },
                plugins: {
                    legend: { display: false },
                    title: {
                    display: false
                    }
                }
                }
            });
            } else {
            console.warn("No monthly attendance data found.");
            }

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
            document.getElementById("borrowCount").textContent = data.borrowCount;

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

            // Read vs Borrow Ratio (Donut)
            new Chart(document.getElementById("readBorrowChart"), {
            type: "doughnut",
            data: {
                labels: ["Read", "Borrowed"],
                datasets: [{
                data: [data.readCount, data.borrowCount],
                backgroundColor: ["#36A2EB", "#FFCE56"]
                }]
            },
            options: {
                plugins: { legend: { position: "bottom" } }
            }
            });
        })
    .catch(err => console.error("Reading Trends Error:", err));

    fetch("/Admin/api/apriori_data.php")
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById("aprioriAgeGroups");
        const summaryContainer = document.getElementById("aprioriTableBody"); // reuse this container
        const ctx = document.getElementById("aprioriGraph");
        container.innerHTML = "";
        summaryContainer.innerHTML = "";

        let allRules = [];

        Object.entries(data.age_groups).forEach(([ageGroup, transactions]) => {
        const rules = apriori(transactions, 0.2, 0.5, 3);
        allRules = allRules.concat(
            rules.map(r => ({ ...r, ageGroup }))
        );

        // 🧩 For per age-group insights
        const section = document.createElement("section");
        section.className = "mb-8 bg-white/10 p-4 rounded-lg";
        section.innerHTML = `
            <h3 class="text-xl font-semibold mb-3">${ageGroup}</h3>
            <ul class="list-disc pl-6 space-y-2 text-gray-200">
            ${rules.map(r => `
                <li>
                Out of all <strong>${ageGroup}</strong> visitors, 
                <strong>${(r.support * 100).toFixed(0)}%</strong> read both 
                <strong>${r.rule.replace("→", "</strong> and <strong>")}</strong>; 
                when someone reads 
                <strong>${r.rule.split("→")[0].trim()}</strong>, 
                <strong>${(r.confidence * 100).toFixed(0)}%</strong> of the time 
                they also read <strong>${r.rule.split("→")[1].trim()}</strong>.
                </li>
            `).join("")}
            </ul>`;
        container.appendChild(section);
        });

        // 🔹 Sort all rules by confidence and get top 10
        const topRules = allRules.sort((a, b) => b.confidence - a.confidence).slice(0, 10);

        // 🧩 Create readable summary list for overall Apriori
        summaryContainer.innerHTML = topRules.map(r => `
        <tr>
            <td colspan="3" class="border px-3 py-3">
            Out of all <strong>${r.ageGroup}</strong> visitors, 
            <strong>${(r.support * 100).toFixed(0)}%</strong> read both 
            <strong>${r.rule.replace("→", "</strong> and <strong>")}</strong>; 
            when someone reads <strong>${r.rule.split("→")[0].trim()}</strong>, 
            <strong>${(r.confidence * 100).toFixed(0)}%</strong> of the time they also read 
            <strong>${r.rule.split("→")[1].trim()}</strong>.
            </td>
        </tr>
        `).join("");
    })
    .catch(err => console.error("Apriori Error:", err));


});
