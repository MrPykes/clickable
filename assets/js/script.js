jQuery(document).ready(function ($) {
  $("#dateRangePicker")
    .daterangepicker({
      autoUpdateInput: false,
      locale: { format: "DD/MM/YYYY" },
    })
    .on("apply.daterangepicker", function (ev, picker) {
      $(this).val(
        picker.startDate.format("DD/MM/YYYY") +
          " - " +
          picker.endDate.format("DD/MM/YYYY")
      );

      // Extract the start and end year from the selected date range
      const startYear = picker.startDate.year().toString();
      const endYear = picker.endDate.year().toString();

      // Update chart with the selected year range
      updateChart(startYear, endYear);
    })
    .on("cancel.daterangepicker", function (ev, picker) {
      $(this).val("");
    });
  // Slider
  $("#news-slider").owlCarousel({
    items: 3,
    // itemsDesktop: [1440, 3],
    itemsDesktopSmall: [1440, 2],
    itemsMobile: [991, 1],
    navigation: true,
    navigationText: ["", ""],
    pagination: true,
    autoPlay: false,
  });

  // Sample expenses data
  const expensesData = {
    2019: 40000,
    2020: 60000,
    2021: 90000,
    2022: 70000,
    2023: 80000,
    2024: 50000,
    2025: 70000,
  };

  // const ctx = document.getElementById("expensesChart");
  // if (ctx) {
  //   let expensesChart;

  //   function updateChart(startYear, endYear) {
  //     const filteredYears = Object.keys(expensesData).filter(
  //       (year) => year >= startYear && year <= endYear
  //     );
  //     const labels = filteredYears;
  //     const data = labels.map((year) => expensesData[year]);
  //     let totalExpenses = data.reduce((a, b) => a + b, 0);

  //     if (expensesChart) expensesChart.destroy();

  //     expensesChart = new Chart(ctx.getContext("2d"), {
  //       type: "bar",
  //       data: {
  //         labels,
  //         datasets: [
  //           {
  //             label: "Total Expenses",
  //             data,
  //             backgroundColor: "#2194FF",
  //             borderColor: "#2194FF",
  //             borderWidth: 1,
  //           },
  //         ],
  //       },
  //       options: {
  //         responsive: true,
  //         scales: {
  //           y: {
  //             beginAtZero: true,
  //           },
  //         },
  //       },
  //     });

  //     const yearlyExpensesList = document.getElementById("yearlyExpenses");
  //     yearlyExpensesList.innerHTML = "";
  //     labels.forEach((year) => {
  //       let listItem = `<li>${year} <strong>$${expensesData[year]}</strong></li>`;
  //       yearlyExpensesList.innerHTML += listItem;
  //     });

  //     document.getElementById("totalExpenses").innerText = `$${totalExpenses}`;
  //   }

  //   // Initialize chart with full range
  //   updateChart("2019", "2025");
  // }

  //  Single Channel Revenue

  // Dashboard Revenue
  // const revChart = document.getElementById("revChart");
  // if (revChart) {
  //   new Chart(revChart.getContext("2d"), {
  //     type: "bar",
  //     data: {
  //       labels: ["2019", "2020", "2021", "2022", "2023", "2024", "2025"],
  //       datasets: [
  //         {
  //           label: "Revenue",
  //           data: [10000, 15000, 20000, 18500, 14000, 12500, 10000],
  //           backgroundColor: "#2194FF",
  //         },
  //       ],
  //     },
  //     options: {
  //       responsive: true,
  //       scales: {
  //         y: {
  //           beginAtZero: true,
  //         },
  //       },
  //       plugins: {
  //         legend: {
  //           display: false,
  //           labels: {
  //             color: "rgb(255, 99, 132)",
  //           },
  //         },
  //       },
  //     },
  //   });
  // }

  // Dynamic Active Roster Data
  // const rosterData = [
  //     { name: 'X-Cop', subscribers: '312K', videos: '57', description: 'Factual Police body cam & Interrogation documentaries', image: 'avatar1.png' },
  //     { name: 'JarToonYT', subscribers: '370K', videos: '75', description: 'Creating cartoon tv shows, movies and anime recap videos', image: 'avatar2.png' },
  //     { name: 'TVGuy_', subscribers: '72.5K', videos: '13', description: 'Creative ranking videos covering your favorite cartoon characters and episodes', image: 'avatar3.png' },
  //     { name: 'Scribbl', subscribers: '3.83K', videos: '5', description: 'The best cartoons list videos on YouTube!', image: 'avatar4.png' }
  // ];

  // const rosterContainer = document.getElementById('roster-container');
  // rosterData.forEach(user => {
  //     const userCard = document.createElement('div');
  //     userCard.className = 'col-md-3';
  //     userCard.innerHTML = `
  //         <div class="card p-3 text-center">
  //             <img src="/wp-content/uploads/2025/02/x-cop.png" class="rounded-circle mb-3" style="width: 80px; height: 80px;">
  //             <p class="text-muted">${user.subscribers} subscribers • ${user.videos} videos</p>
  //             <h6 class="fw-bold">${user.name}</h6>
  //             <p class="small">${user.description}</p>
  //         </div>
  //     `;
  //     rosterContainer.appendChild(userCard);
  // });

  // Paid Invoices Section
  const invoices = [
    {
      number: "341U-05",
      name: "BruceWayne#8566",
      date: "30/11/2024",
      paid: "26/12/2024",
    },
    {
      number: "185Q-68",
      name: "EnolaHolmes#7585",
      date: "30/11/2024",
      paid: "26/12/2024",
    },
    {
      number: "937K-57",
      name: "SansaStark#8954",
      date: "30/11/2024",
      paid: "26/12/2024",
    },
    {
      number: "739C-28",
      name: "PepperPotts#5353",
      date: "30/11/2024",
      paid: "26/12/2024",
    },
    {
      number: "036E-07",
      name: "AlbusDumbledore#1234",
      date: "30/11/2024",
      paid: "26/12/2024",
    },
  ];

  let currentPage = 1;
  const rowsPerPage = 3;

  function renderTable() {
    const tableBody = document.getElementById("invoice-table-body");
    tableBody.innerHTML = "";
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    invoices.slice(start, end).forEach((invoice) => {
      const row = `<tr>
                    <td>${invoice.number}</td>
                    <td>${invoice.name}</td>
                    <td>${invoice.date}</td>
                    <td>${invoice.paid}</td>
                    <td>📥 🗑️</td>
                </tr>`;
      tableBody.innerHTML += row;
    });
    document.getElementById(
      "pagination-info"
    ).innerText = `Page ${currentPage} of ${Math.ceil(
      invoices.length / rowsPerPage
    )}`;
  }
  // renderTable();
  const prevPage = document.getElementById("prevPage");
  if (prevPage) {
    prevPage.addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        renderTable();
      }
    });
  }
  const nextPage = document.getElementById("nextPage");
  if (nextPage) {
    nextPage.addEventListener("click", () => {
      if (currentPage < Math.ceil(invoices.length / rowsPerPage)) {
        currentPage++;
        renderTable();
      }
    });
  }
  const search = document.getElementById("search");
  if (search) {
    search.addEventListener("input", (e) => {
      const query = e.target.value.toLowerCase();
      const filteredInvoices = invoices.filter(
        (invoice) =>
          invoice.number.toLowerCase().includes(query) ||
          invoice.name.toLowerCase().includes(query) ||
          invoice.date.includes(query) ||
          invoice.paid.includes(query)
      );
      const tableBody = document.getElementById("invoice-table-body");
      if (tableBody) {
        tableBody.innerHTML = "";

        filteredInvoices.forEach((invoice) => {
          const row = `<tr>
                      <td>${invoice.number}</td>
                      <td>${invoice.name}</td>
                      <td>${invoice.date}</td>
                      <td>${invoice.paid}</td>
                      <td>📥 🗑️</td>
                  </tr>`;
          tableBody.innerHTML += row;
        });
      }
    });
  }

  //    Expenses Section

  // Profile Earnings
  // const earningsChart = document.getElementById("earningsChart");
  // const gradient = earningsChart.createLinearGradient(0, 0, 0, height);
  //       gradient.addColorStop(0, 'rgba(250,174,50,1)');
  //       gradient.addColorStop(1, 'rgba(250,174,50,0)');

  // if (earningsChart) {
  //   new Chart(earningsChart.getContext("2d"), {
  //     type: "line",
  //     data: {
  //       labels: [
  //         "Jan",
  //         "Feb",
  //         "Mar",
  //         "Apr",
  //         "May",
  //         "June",
  //         "July",
  //         "Aug",
  //         "Sep",
  //         "Oct",
  //         "Nov",
  //         "Dec",
  //       ],
  //       datasets: [
  //         {
  //           label: "Earnings",
  //           data: [
  //             10000, 80000, 75000, 90000, 95000, 60000, 70000, 85000, 72000,
  //             78000, 74000, 50000,
  //           ],
  //           // borderColor: "#007bff",
  //           // backgroundColor: "rgba(0, 123, 255, 0.3)",
  //           backgroundColor: gradient,
  //           fill: true,
  //           tension: 0.4,
  //         },
  //       ],
  //     },
  //     options: {
  //       responsive: true,
  //       maintainAspectRatio: false,
  //       scales: {
  //         y: {
  //           beginAtZero: true,
  //           ticks: {
  //             stepSize: 20000,
  //           },
  //         },
  //       },
  //       plugins: {
  //         legend: {
  //           display: false,
  //           labels: {
  //             color: "rgb(255, 99, 132)",
  //           },
  //         },
  //       },
  //     },
  //   });
  // }
  const earningsChart = document.getElementById("earningsChart");
  if (earningsChart) {
    const earns = earningsChart.getContext("2d");
    const height = earningsChart.height;
    const gradient = earns.createLinearGradient(0, 0, 0, height);

    gradient.addColorStop(0, "rgba(33,148,255,1)");
    gradient.addColorStop(1, "rgba(33,148,255,0)");

    new Chart(earns, {
      type: "line",
      data: {
        labels: [
          "Jan",
          "Feb",
          "Mar",
          "Apr",
          "May",
          "June",
          "July",
          "Aug",
          "Sep",
          "Oct",
          "Nov",
          "Dec",
        ],
        datasets: [
          {
            label: "Earnings",
            data: [
              10000, 80000, 75000, 90000, 95000, 60000, 70000, 85000, 72000,
              78000, 74000, 50000,
            ],
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 20000,
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
        },
      },
    });
  }
});
