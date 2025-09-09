<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * Template Name: Dashboard Template
 */

get_header('admin');


$paid_args = array(
    'post_type'      => 'invoice-creator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => array(
        array(
            'key'     => 'status',  // Change this to the actual meta key storing the invoice status
            'value'   => array('paid'),
            'compare' => 'IN'
        )
    )
);
$paid_invoices_query = new WP_Query($paid_args);
$paid_invoice_data = [];

if ($paid_invoices_query->have_posts()) {
    while ($paid_invoices_query->have_posts()) {
        $paid_invoices_query->the_post();
        $paid_invoice_data[] = [
            'id'         => get_the_ID(),
            'invoiceNumber'   => get_field('invoice_number'),
            'fileName'   => get_field('file_name') ? get_field('file_name')['filename'] : '',
            'discordName' => get_field('discord_name'),
            'dueDate'     => get_field('due_date'),
            'date'       => get_field('date_submitted'),
            'amount'     => get_field('amount'),
            'status'     => get_field('status'),
            'platform'   => get_field('payment_method'),
            // 'channel'    => get_field('tasks') ? get_field('tasks')[0]['tasks_channel'] : '',
            'role'    => get_field('role'),
        ];
    }
    wp_reset_postdata();
}


$paid_args = array(
    'post_type'      => 'invoice-creator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => array(
        array(
            'key'     => 'status',  // Change this to the actual meta key storing the invoice status
            'value'   => array('pending'),
            'compare' => 'IN'
        )
    )
);
$pending_invoices_query = new WP_Query($paid_args);
$pending_invoice_data = [];

if ($pending_invoices_query->have_posts()) {
    while ($pending_invoices_query->have_posts()) {
        $pending_invoices_query->the_post();


        $pending_invoice_data[] = [
            'id'         => get_the_ID(),
            'invoiceNumber'   => get_field('invoice_number'),
            'fileName'   => get_field('file_name'),
            // 'fileName'   => get_field('file_name') ? get_field('file_name')['filename'] : '',
            'discordName' => get_field('discord_name'),
            'dueDate'     => get_field('due_date'),
            'date'       => get_field('date_submitted'),
            'amount'     => get_field('amount'),
            'status'     => get_field('status'),
            'platform'   => get_field('payment_method'),
            // 'channel'    => get_field('tasks') ? get_field('tasks')[0]['tasks_channel'] : '',
            'role'    => get_field('role'),
        ];
    }
    wp_reset_postdata();
}
?>
<style>
    .header-db h1 {
        color: #101010;
        font-weight: 600;
        font-size: 30px;
        letter-spacing: -0.03em;
        font-style: normal;
    }

    .header-db p {
        color: #101010;
        font-weight: 300;
        font-size: 16px;
    }

    .flex-grow-1 {
        padding: 50px;
    }

    .stats-row {
        margin-bottom: 20px;
    }

    .stats-row .card {
        padding: 32px 40px;
        border: solid 1px #fff;
    }

    .stats-row .card:hover,
    .stats-row .card:active {
        border: solid 1px #2194FF;
    }

    .stats-row .card h5 {
        column-gap: 10px;
        font-size: 16px;
        font-weight: 300;
        color: #101010;
        margin-bottom: 40px;
        align-items: center;
    }

    .stats-row .icon {
        background-color: #FFEE94;
        padding: 10px;
        border-radius: 40px;
    }

    .stats-row .card h2 {
        font-size: 30px;
        font-weight: 600;
        letter-spacing: -0.03em;
        color: #101010;
        margin-bottom: 24px;
    }

    .stats-row .card p {
        font-size: 14px;
        font-weight: 600;
        color: #2194FF;
    }

    .chart-container {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
    }

    .expenses-summary {
        text-align: right;
    }

    .expenses-summary p {
        font-weight: bold;
    }

    #yearlyExpenses li,
    .expenses-summary p {
        display: flex;
        justify-content: space-between;
    }

    .container {
        display: flex;
        gap: 20px;
    }

    .chart-container {
        flex: 2;
    }

    .data-list {
        flex: 1;
        border-left: 1px solid #ddd;
        /* padding-left: 20px; */
    }

    .card .expenses-nav {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 40px;
    }

    .form-control {
        width: 25% !important;
    }

    .data-list .expense-value {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .data-list .expenses-total {
        border-top: solid 1px #E7E7E7;
        padding-top: 24px;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        font-style: normal;
    }

    .expense-range-btn {
        display: flex;
        flex-direction: row;
        align-items: center;
        border: solid 1px #D0D0D0;
        border-radius: 4px;
    }

    .expense-range-btn .expense-date-range,
    .expense-range-btn .revenue-date-range,
    .expense-range-btn .netprof-date-range {
        border: unset;
    }

    /* .netinfo {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        font-style: normal;
    } */

    .net-revenue .icon {
        background-color: #2194FF;
    }

    /* .data-list .icon {
        background-color: #A3D2FF;
    } */
</style>
<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar');  ?>
    <div class="main-content">
        <main class="flex-grow-1">
            <div class="header-db">
                <h1>Your Dashboard</h1>
                <p>Welcome!</p>
            </div>
            <!-- Stats Cards -->
            <div x-data="statsCard()">
                <div class="row mt-4 stats-row">
                    <div class="col-md-4">
                        <!-- Total Expenses -->
                        <div class="card" @click="activeTab = 'expense'" x-data="expensesChart">
                            <div class="card-body">
                                <h5 class="card-title d-flex">
                                    <div class="icon"><img src="/wp-content/uploads/2025/02/total-expenses.svg" alt=""></div>
                                    Total Expenses
                                </h5>
                                <h2 class="card-text" x-text="'$' + totalFilteredExpenses.toLocaleString()"></h2>
                                <p x-text="calculateTotalAndPercentage.percentage + ' This month'"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Total Revenue -->
                        <div class="card" @click="activeTab = 'revenue'" x-data="revenueData">
                            <div class="card-body">
                                <h5 class="card-title d-flex">
                                    <div class="icon"><img src="/wp-content/uploads/2025/02/total-revenue.svg" alt=""></div>
                                    Total Revenue
                                </h5>
                                <h2 class="card-text" x-text="'$'+ totalFilteredRevenue.toLocaleString()"></h2>
                                <p x-text="revenueGrowthPercentage.percentage + ' This month'"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Total Net Profit -->
                        <div class="card" @click="activeTab = 'netprofit'" x-data="financeData">
                            <div class="card-body">
                                <h5 class="card-title d-flex">
                                    <div class="icon"><img src="/wp-content/uploads/2025/02/total-net-profit.svg" alt=""></div>
                                    Total Net Profit
                                </h5>
                                <h2 class="card-text" x-text="'$' + totalFilteredNetProfit.toLocaleString()"></h2>
                                <p x-text="netProfitPercentage + ' This month'"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <!-- Total Expenses -->
                    <div class=" container-fluid mt-4 p-0" x-data="expensesChart">
                        <div class="card p-4" x-show="activeTab === 'expense'">
                            <div class="row">
                                <div class="col-md-12 expenses-nav">
                                    <h5 class="fw-bold card-title">Expenses</h5>
                                    <div class="expense-range-btn">
                                        <!-- <input type="date" id="startDatePicker" x-model="firstDate" class="expense-date-range" @change="filterData()"> - -->
                                        <input type="date" id="startDatePicker" x-model="firstDate" class="expense-date-range" @click="$el.showPicker && $el.showPicker()" @change="filterData()"> -
                                        <input type="date" id="endDatePicker" x-model="lastDate" class="expense-date-range" @click="$el.showPicker && $el.showPicker()" @change="filterData()">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-9">
                                    <canvas id="expensesChart"></canvas>
                                </div>
                                <div class="data-list">
                                    <!-- <template x-for="(value, index) in expensesData" :key="index">
                                        <div class="expense-value">
                                            <span x-text="index"></span>
                                            <strong x-text="'$' + value.toLocaleString()"></strong>
                                        </div>
                                    </template> -->
                                    <template x-for="(value, index) in expensesData.values" :key="index">
                                        <div class="expense-value">
                                            <span x-text="expensesData.labels[index]"></span>
                                            <strong x-text="'$' + value.toLocaleString()"></strong>
                                        </div>
                                    </template>
                                    <p class="expenses-total">
                                        <span style="font-size: 16px; font-weight: 600; color: #101010;">Expenses</span>
                                        <span style="font-size: 18px; font-weight: 700; color: #101010;" x-text="'$' + totalFilteredExpenses.toLocaleString()"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Revenue -->
                    <style>
                        .revenue-date-range {
                            appearance: auto;
                            -webkit-appearance: auto;
                            cursor: pointer;
                        }

                        .net-revenue .icon {
                            background-color: #2194FF;
                            /* Blue */
                        }

                        .net-expense .icon {
                            background-color: #A3D2FF;
                            /* Light Blue */
                        }

                        .data-list .icon {
                            display: flex;
                            width: 10px;
                            height: 10px;
                            border-radius: 2px;
                            margin-top: 5px;
                            margin-right: 5px;
                        }

                        .net-revenue,
                        .net-expense {
                            display: flex;
                            flex-direction: row;
                        }

                        .col-md-9 {
                            height: 300px;
                            width: 70%;
                        }

                        .netinfo {
                            margin-right: 150px;
                        }
                    </style>
                    <div class="container-fluid mt-4 p-0" x-data="revenueData" x-init="renderChart()">
                        <div class="card p-4" x-show="activeTab === 'revenue'">
                            <div class="row">
                                <div class="col-md-12 expenses-nav">
                                    <h5 class="fw-bold card-title">Revenue</h5>
                                    <div class="expense-range-btn">
                                        <input type="date" id="startDatePicker" class="revenue-date-range" x-model="startDate" @click="$el.showPicker && $el.showPicker()" @change="renderChart()"> -
                                        <input type="date" id="endDatePicker" class="revenue-date-range" x-model="endDate" @click="$el.showPicker && $el.showPicker()" @change="renderChart()">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-9">
                                    <canvas id="revenChart"></canvas>
                                </div>
                                <div class="data-list">
                                    <!-- <template x-for="(value, index) in revenueData" :key="index">
                                        <div class="expense-value">
                                            <span x-text="index"></span>
                                            <strong x-text="'$' + value.toLocaleString()"></strong>
                                        </div>
                                    </template> -->

                                    <template x-for="(value, index) in revenueData.values" :key="index">
                                        <div class="expense-value">
                                            <span x-text="revenueData.labels[index]"></span>
                                            <strong x-text="'$' + value.toLocaleString()"></strong>
                                        </div>
                                    </template>
                                    <p class="expenses-total">
                                        <span style="font-size: 16px; font-weight: 600; color: #101010;">Revenue</span>
                                        <span style="font-size: 18px; font-weight: 700; color: #101010;" x-text="'$' + totalFilteredRevenue.toLocaleString()"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Net Profit -->
                    <div class="container-fluid mt-4 p-0" x-data="financeData">
                        <div class="card p-4" x-show="activeTab === 'netprofit'">
                            <div class="row">
                                <div class="col-md-12 expenses-nav">
                                    <h5 class="fw-bold card-title">Net Profit</h5>
                                    <div class="expense-range-btn">
                                        <input type="date" id="startDatePicker" class="netprof-date-range" @click="$el.showPicker && $el.showPicker()" @change="renderChart()" x-model="firstDate"> -
                                        <input type="date" id="endDatePicker" class="netprof-date-range" @click="$el.showPicker && $el.showPicker()" @change="renderChart()" x-model="lastDate">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-9">
                                    <canvas id="netChart"></canvas>
                                </div>
                                <div class="data-list">
                                    <ul>
                                        <li class="net-revenue">
                                            <i class="icon"></i>
                                            <p class="netinfo">Revenue</p>
                                            <strong x-text="'$' + totalFilteredRevenue.toLocaleString()"></strong>
                                        </li>
                                        <li class="net-expense">
                                            <i class="icon"></i>
                                            <p class="netinfo">Expenses</p>
                                            <strong x-text="'$' + totalFilteredExpenses.toLocaleString()"></strong>
                                        </li>
                                        <li class="expenses-total">
                                            <p class="netinfo">Net Profit</p>
                                            <strong x-text="'$' + totalFilteredNetProfit.toLocaleString()"></strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function statsCard() {
                    return {
                        activeTab: 'expense',
                    }
                }

                function expensesChart() {
                    return {
                        chart: null,
                        invoices: [...paidInvoiceFilter().invoices, ...pendingInvoiceFilter().invoices],
                        data: {
                            labels: [2019, 2020, 2021, 2022, 2023, 2024, 2025],
                            values: [9000, 11000, 13000, 12000, 10000, 5000, 10000]
                        },
                        filteredData: {
                            labels: [],
                            values: []
                        },
                        firstDate: '',
                        lastDate: '',
                        totalRevenue: revenueData().totalRevenue,
                        totalNetProfit: 0,
                        totalExpenses: 0,
                        calculateTotalAndPercentage: 0,
                        init() {
                            console.log('The invoices: ', this.invoices);

                            this.setInitialDates();
                            this.filteredData.labels = [...this.data.labels];
                            this.filteredData.values = [...this.data.values];
                            this.renderChart();
                            const totalAmount = this.calculateTotalAndPercentage.totalAmount || 0;
                            this.totalNetProfit = this.totalRevenue - totalAmount;

                        },
                        get dates() {
                            return this.invoices.map(invoice => {
                                const [day, month, year] = invoice.date.split('/').map(Number);
                                return new Date(year, month - 1, day);
                            });
                        },
                        setInitialDates() {
                            if (this.dates.length) {
                                this.firstDate = new Date(Math.min(...this.dates)).toISOString().split('T')[0];
                                this.lastDate = new Date().toISOString().split('T')[0];
                            }
                        },
                        get totalFilteredExpenses() {
                            // return Object.values(this.expensesData).reduce((sum, val) => sum + val, 0);
                            return this.expensesData.values.reduce((sum, value) => sum + value, 0);
                        },
                        get calculateTotalAndPercentage1() {
                            const today = new Date();
                            const currentMonth = today.getMonth() + 1; // getMonth() is zero-based
                            const currentYear = today.getFullYear();
                            const totalAmount = this.invoices.reduce((sum, invoice) => sum + Number(invoice.amount), 0);
                            const monthTotal = this.invoices
                                .filter(invoice => {
                                    const [day, month, year] = invoice.date.split("/").map(Number);
                                    return month === currentMonth && year === currentYear;
                                })
                                .reduce((sum, invoice) => sum + Number(invoice.amount), 0);
                            const percentage = (monthTotal / totalAmount) * 100;
                            return {
                                totalAmount,
                                monthTotal,
                                percentage: percentage.toFixed(2) + "%"
                            };
                        },
                        getMonthlyChange(data) {
                            const now = new Date();
                            let year = now.getFullYear();
                            let month = now.getMonth() + 1; // JavaScript months are 0-indexed

                            // Format year and month strings
                            const thisMonth = String(month).padStart(2, '0');
                            const lastMonthDate = new Date(year, month - 2); // subtract 1 for JS 0-index, another 1 for "last" month
                            const lastMonthYear = lastMonthDate.getFullYear();
                            const lastMonth = String(lastMonthDate.getMonth() + 1).padStart(2, '0');

                            const thisMonthValue = data[year]?.[thisMonth] ?? 0;
                            const lastMonthValue = data[lastMonthYear]?.[lastMonth] ?? 0;

                            // Calculate totalAmount of all expenses
                            let totalAmount = 0;
                            let change = 0;
                            for (const y in data) {
                                for (const m in data[y]) {
                                    totalAmount += data[y][m];
                                }
                            }

                            if (lastMonthValue === 0) {
                                change = 0;
                            } else {
                                change = ((thisMonthValue - lastMonthValue) / lastMonthValue) * 100;
                            }

                            return {
                                totalAmount,
                                percentage: Math.abs(change).toFixed(2) + "%",
                            };
                        },
                        get filteredexpensesData() {
                            const parseDate = (dateStr) => {
                                const [day, month, year] = dateStr.split('/');
                                return new Date(`${year}-${month}-${day}`);
                            };
                            const filteredInvoicesByDate = this.invoices.filter(invoice => {
                                const invoiceDate = parseDate(invoice.date);
                                return invoiceDate >= parseDate(this.firstDate) && invoiceDate <= parseDate(this.lastDate);
                            });
                            return filteredInvoicesByDate;
                        },

                        get expensesData() {
                            const data = this.filteredexpensesData;

                            const monthNames = {
                                "01": "January",
                                "02": "February",
                                "03": "March",
                                "04": "April",
                                "05": "May",
                                "06": "June",
                                "07": "July",
                                "08": "August",
                                "09": "September",
                                "10": "October",
                                "11": "November",
                                "12": "December"
                            };

                            let expensesByYearMonth = {};

                            data.forEach(({
                                date,
                                amount
                            }) => {
                                const [day, month, year] = date.split('/');
                                const paddedMonth = month.padStart(2, '0');

                                if (!expensesByYearMonth[year]) {
                                    expensesByYearMonth[year] = {};
                                }

                                if (!expensesByYearMonth[year][paddedMonth]) {
                                    expensesByYearMonth[year][paddedMonth] = 0;
                                }

                                expensesByYearMonth[year][paddedMonth] += parseInt(amount);
                            });

                            this.calculateTotalAndPercentage = this.getMonthlyChange(expensesByYearMonth);
                            const years = Object.keys(expensesByYearMonth);
                            let labels = [];
                            let values = [];

                            if (years.length === 1) {
                                const year = years[0];
                                const months = Object.keys(expensesByYearMonth[year]).sort();
                                labels = months.map(month => monthNames[month]);
                                values = months.map(month => expensesByYearMonth[year][month]);
                            } else {
                                years.sort();
                                labels = years;
                                values = years.map(year => {
                                    const total = Object.values(expensesByYearMonth[year])
                                        .reduce((sum, val) => sum + val, 0);
                                    return total;
                                });
                            }

                            return {
                                labels,
                                values
                            };
                        },
                        get expensesData1() {
                            const invoices = this.filteredexpensesData;
                            let expensesData = {};
                            invoices.forEach(({
                                date,
                                amount,
                            }) => {
                                let year = date.split("/")[2]; // Extract the year
                                amount = parseInt(amount); // Convert amount to integer
                                // Add to specific channel revenue
                                if (!expensesData) {
                                    expensesData = {};
                                }
                                expensesData[year] = (expensesData[year] || 0) + amount;
                                this.totalExpenses += amount;
                            });
                            return expensesData;
                        },
                        renderChart() {
                            const ctx = document.getElementById('expensesChart').getContext('2d');
                            if (Chart.getChart("expensesChart")) {
                                Chart.getChart("expensesChart").destroy();
                            }
                            // Check for no data
                            if (
                                !this.expensesData.labels.length ||
                                !this.expensesData.values.length
                            ) {
                                // Clear canvas and show text
                                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                                ctx.font = '16px Arial';
                                ctx.textAlign = 'center';
                                ctx.fillText('No data found', ctx.canvas.width / 2, ctx.canvas.height / 2);
                                return;
                            }

                            // Create chart if there's data
                            if (ctx) {
                                this.chart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: this.expensesData.labels,
                                        datasets: [{
                                            label: 'Expenses',
                                            data: this.expensesData.values.map(Number),
                                            backgroundColor: "#2194FF",
                                            borderRadius: 10,
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return '$' + value.toLocaleString();
                                                    }
                                                }
                                            }
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        const value = context.parsed.y;
                                                        return `${context.dataset.label}: $${value}`;
                                                    }
                                                }
                                            },
                                            legend: {
                                                display: false
                                            }
                                        }
                                    }
                                });
                            }
                        },
                        filterData() {
                            setTimeout(() => { // Prevent infinite loop
                                const startYear = parseInt(document.getElementById('startDatePicker').value.split('-')[0]) || 2019;
                                const endYear = parseInt(document.getElementById('endDatePicker').value.split('-')[0]) || 2025;
                                this.filteredData.labels = this.data.labels.filter(year => year >= startYear && year <= endYear);
                                const startIndex = this.data.labels.indexOf(this.filteredData.labels[0]);
                                const endIndex = this.data.labels.indexOf(this.filteredData.labels[this.filteredData.labels.length - 1]) + 1;
                                this.filteredData.values = this.data.values.slice(startIndex, endIndex);
                                this.renderChart();
                            }, 0);
                        }
                    };
                }

                function revenueData() {
                    return {
                        startDate: '',
                        endDate: '',
                        chart: null,
                        data: {
                            labels: ["2020", "2021", "2022", "2023", "2024", "2025"],
                            values: [15000, 20000, 18500, 14000, 12500, 10000]
                        },
                        revenue: channelRevenue().revenue,
                        filteredLabels: [],
                        filteredValues: [],
                        revenueGrowthPercentage: 0,
                        get totalRevenue() {
                            return 123;
                        },
                        get totalFilteredRevenue() {

                            return this.revenueData.values.reduce((sum, value) => sum + value, 0);
                        },
                        get revenueGrowthPercentage1() {
                            const revenues = this.filteredValues;
                            if (revenues.length < 2) return "0%";

                            const lastYearRevenue = revenues[revenues.length - 2]; // Previous year
                            const currentYearRevenue = revenues[revenues.length - 1]; // Current year

                            if (lastYearRevenue > 0) {
                                const growth = ((currentYearRevenue - lastYearRevenue) / lastYearRevenue) * 100;
                                return growth.toFixed(2) + "%";
                            }
                            return "0%";
                        },
                        getMonthlyChange(data) {
                            const now = new Date();
                            let year = now.getFullYear();
                            let month = now.getMonth() + 1;
                            // Format year and month strings
                            const thisMonth = String(month).padStart(2, '0');
                            const lastMonthDate = new Date(year, month - 2);
                            const lastMonthYear = lastMonthDate.getFullYear();
                            const lastMonth = String(lastMonthDate.getMonth() + 1).padStart(2, '0');
                            const thisMonthValue = data[year]?.[thisMonth] ?? 0;
                            const lastMonthValue = data[lastMonthYear]?.[lastMonth] ?? 0;
                            // Calculate totalAmount of all expenses
                            let totalAmount = 0;
                            let change = 0;
                            for (const y in data) {
                                for (const m in data[y]) {
                                    totalAmount += data[y][m];
                                }
                            }
                            if (lastMonthValue === 0) {
                                change = 0;
                            } else {
                                change = ((thisMonthValue - lastMonthValue) / lastMonthValue) * 100;
                            }
                            return {
                                totalAmount,
                                percentage: Math.abs(change).toFixed(2) + "%",
                            };
                        },
                        init() {
                            this.setInitialDates();
                            // console.log('the revenues: ', this.revenueData);

                        },
                        get dates() {
                            return this.revenue.map(revenue => {
                                const [day, month, year] = revenue.date.split('/').map(Number);
                                return new Date(year, month - 1, day);
                            });
                        },
                        setInitialDates() {
                            if (this.dates.length) {
                                this.startDate = new Date(Math.min(...this.dates)).toISOString().split('T')[0];
                                this.endDate = new Date().toISOString().split('T')[0];
                            }
                        },
                        // get filteredrevenueData() {
                        //     const parseDate = (dateStr) => {
                        //         const [day, month, year] = dateStr.split('/');
                        //         return new Date(`${year}-${month}-${day}`);
                        //     };
                        //     const filteredRevenueByDate = this.revenue.filter(revenue => {
                        //         const revenueDate = parseDate(revenue.date);
                        //         return revenueDate >= parseDate(this.startDate) && revenueDate <= parseDate(this.endDate);
                        //     });
                        //     return filteredRevenueByDate;
                        // },
                        // get revenueData() {
                        //     const revenue = this.filteredrevenueData;
                        //     let revenueData = {};
                        //     revenue.forEach(({
                        //         date,
                        //         amount,
                        //     }) => {
                        //         let year = date.split("/")[2]; // Extract the year
                        //         amount = parseInt(amount); // Convert amount to integer
                        //         // Add to specific channel revenue
                        //         if (!revenueData) {
                        //             revenueData = {};
                        //         }
                        //         revenueData[year] = (revenueData[year] || 0) + amount;
                        //         this.totalExpenses += amount;
                        //     });
                        //     return revenueData;
                        // },
                        get filteredRevenue() {
                            const parseDate = (dateStr) => {
                                const [day, month, year] = dateStr.split('/');
                                return new Date(`${year}-${month}-${day}`);
                            };
                            const filteredRevenueByDate = this.revenue.filter(revenue => {
                                const revenueDate = parseDate(revenue.date);
                                return revenueDate >= parseDate(this.startDate) && revenueDate <= parseDate(this.endDate);
                            });
                            return filteredRevenueByDate;
                        },
                        get revenueData() {
                            const data = this.filteredRevenue;
                            const monthNames = {
                                "01": "January",
                                "02": "February",
                                "03": "March",
                                "04": "April",
                                "05": "May",
                                "06": "June",
                                "07": "July",
                                "08": "August",
                                "09": "September",
                                "10": "October",
                                "11": "November",
                                "12": "December"
                            };

                            let revenueByYearMonth = {};

                            data.forEach(({
                                date,
                                amount
                            }) => {
                                const [day, month, year] = date.split('/');
                                const paddedMonth = month.padStart(2, '0');

                                if (!revenueByYearMonth[year]) {
                                    revenueByYearMonth[year] = {};
                                }

                                if (!revenueByYearMonth[year][paddedMonth]) {
                                    revenueByYearMonth[year][paddedMonth] = 0;
                                }

                                revenueByYearMonth[year][paddedMonth] += parseInt(amount) || 0;
                            });
                            const years = Object.keys(revenueByYearMonth);
                            let labels = [];
                            let values = [];

                            this.revenueGrowthPercentage = this.getMonthlyChange(revenueByYearMonth);

                            if (years.length === 1) {
                                const year = years[0];
                                const months = Object.keys(revenueByYearMonth[year]).sort();
                                labels = months.map(month => monthNames[month]);
                                values = months.map(month => revenueByYearMonth[year][month]);
                            } else {
                                years.sort();
                                labels = years;
                                values = years.map(year => {
                                    const total = Object.values(revenueByYearMonth[year])
                                        .reduce((sum, val) => sum + val, 0);
                                    return total;
                                });
                            }

                            return {
                                labels,
                                values
                            };
                        },

                        renderChart() {
                            const ctx = document.getElementById("revenChart").getContext("2d");

                            if (Chart.getChart("revenChart")) {
                                Chart.getChart("revenChart").destroy();
                            }

                            // Check for no data
                            if (
                                !this.revenueData.labels.length ||
                                !this.revenueData.values.length
                            ) {
                                // Clear canvas and show text
                                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                                ctx.font = '16px Arial';
                                ctx.textAlign = 'center';
                                ctx.fillText('No data found', ctx.canvas.width / 2, ctx.canvas.height / 2);
                                return;
                            }

                            // Create chart if there's data
                            if (ctx) {
                                this.chart = new Chart(ctx, {
                                    type: "bar",
                                    data: {
                                        labels: this.revenueData.labels,
                                        datasets: [{
                                            label: "Revenue",
                                            data: this.revenueData.values.map(Number),
                                            backgroundColor: "#2194FF",
                                            borderRadius: 10,
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return '$' + value.toLocaleString();
                                                    }
                                                }
                                            }
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        const value = context.parsed.y;
                                                        return `${context.dataset.label}: $${value.toLocaleString()}`;
                                                    }
                                                }
                                            },
                                            legend: {
                                                display: false
                                            }
                                        }
                                    }
                                });
                            }
                        },
                        filterRev() {
                            Alpine.nextTick(() => {
                                // Ensure startDate and endDate are correctly set
                                const startYear = this.startDate ? parseInt(this.startDate.split("-")[0]) : 2019;
                                const endYear = this.endDate ? parseInt(this.endDate.split("-")[0]) : new Date().getFullYear();

                                const years = this.data.labels.map(Number);

                                // Find closest match if the year isn't in the labels
                                let startIndex = years.findIndex(y => y >= startYear);
                                let endIndex = years.findIndex(y => y > endYear) - 1;

                                // If not found, default to first and last available
                                if (startIndex === -1) startIndex = 0;
                                if (endIndex === -1 || endIndex < startIndex) endIndex = years.length - 1;

                                // console.log(`Filtering from ${startYear} to ${endYear}`);
                                // console.log(`Indexes: startIndex=${startIndex}, endIndex=${endIndex}`);

                                this.filteredLabels = this.data.labels.slice(startIndex, endIndex + 1);
                                this.filteredValues = this.data.values.slice(startIndex, endIndex + 1);

                                this.renderChart();
                            });
                        }
                    };
                }

                function financeData() {
                    return {
                        invoices: [...paidInvoiceFilter().invoices, ...pendingInvoiceFilter().invoices],
                        revenue: [revenueData().revenue],
                        firstDate: "",
                        lastDate: "",
                        // totalFilteredExpenses: 0,
                        // totalFilteredRevenue: 42,
                        // totalFilteredNetProfit: 0,
                        totalExpenses: 0,
                        totalRevenue: 0,
                        netProfitPercentage: 0,
                        init() {
                            this.setInitialDates();
                            this.renderChart();
                        },
                        get dates() {
                            const isValidDate = date => !isNaN(date.getTime());

                            const invoiceDate = this.invoices
                                .map(invoice => {
                                    const [day, month, year] = invoice.date?.split('/').map(Number) || [];
                                    const date = new Date(year, month - 1, day);
                                    return isValidDate(date) ? date : null;
                                })
                                .filter(Boolean);

                            const revenueDate = this.revenue
                                .map(revenue => {
                                    const [day, month, year] = revenue.date?.split('/').map(Number) || [];
                                    const date = new Date(year, month - 1, day);
                                    return isValidDate(date) ? date : null;
                                })
                                .filter(Boolean);

                            return [...invoiceDate, ...revenueDate];

                        },
                        setInitialDates() {
                            if (this.dates.length) {
                                this.firstDate = new Date(Math.min(...this.dates)).toISOString().split('T')[0];
                                this.lastDate = new Date().toISOString().split('T')[0];
                            }
                        },
                        get expenses() {
                            return expensesChart().filteredexpensesData;
                        },
                        get filteredexpensesData() {
                            const parseDate = (dateStr) => {
                                const [day, month, year] = dateStr.split('/');
                                return new Date(`${year}-${month}-${day}`);
                            };
                            const filteredInvoicesByDate = this.invoices.filter(invoice => {
                                const invoiceDate = parseDate(invoice.date);

                                return invoiceDate >= parseDate(this.firstDate) && invoiceDate <= parseDate(this.lastDate);
                            });
                            return filteredInvoicesByDate;
                        },
                        get expensesData() {
                            const invoices = this.filteredexpensesData;
                            let expensesData = {};
                            invoices.forEach(({
                                date,
                                amount,
                            }) => {
                                let year = date.split("/")[2]; // Extract the year
                                amount = parseInt(amount); // Convert amount to integer
                                // Add to specific channel revenue
                                if (!expensesData) {
                                    expensesData = {};
                                }

                                expensesData[year] = (expensesData[year] || 0) + amount;
                                this.totalExpenses += amount;
                            });
                            return expensesData;
                        },
                        get filteredrevenueData() {
                            const parseDate = (dateStr) => {
                                const [day, month, year] = dateStr.split('/');
                                return new Date(`${year}-${month}-${day}`);
                            };
                            const filteredRevenueByDate = this.revenue[0].filter(revenue => {
                                const revenueDate = parseDate(revenue.date);

                                return revenueDate >= parseDate(this.firstDate) && revenueDate <= parseDate(this.lastDate);
                            });
                            return filteredRevenueByDate;
                        },
                        get revenueData() {
                            const revenue = this.filteredrevenueData;
                            let revenueData = {};
                            revenue.forEach(({
                                date,
                                amount,
                            }) => {
                                let year = date.split("/")[2]; // Extract the year
                                amount = parseInt(amount); // Convert amount to integer
                                // Add to specific channel revenue
                                if (!revenueData) {
                                    revenueData = {};
                                }

                                revenueData[year] = (revenueData[year] || 0) + amount;
                                this.totalExpenses += amount;
                            });
                            return revenueData;
                        },
                        getMonthlyChange(data) {
                            const now = new Date();
                            let year = now.getFullYear();
                            let month = now.getMonth() + 1; // JavaScript months are 0-indexed

                            // Format year and month strings
                            const thisMonth = String(month).padStart(2, '0');
                            const lastMonthDate = new Date(year, month - 2); // subtract 1 for JS 0-index, another 1 for "last" month
                            const lastMonthYear = lastMonthDate.getFullYear();
                            const lastMonth = String(lastMonthDate.getMonth() + 1).padStart(2, '0');

                            const thisMonthValue = data[year]?.[thisMonth] ?? 0;
                            const lastMonthValue = data[lastMonthYear]?.[lastMonth] ?? 0;

                            // Calculate totalAmount of all expenses
                            let totalAmount = 0;
                            let change = 0;
                            for (const y in data) {
                                for (const m in data[y]) {
                                    totalAmount += data[y][m];
                                }
                            }

                            if (lastMonthValue === 0) {
                                change = 0;
                            } else {
                                change = ((thisMonthValue - lastMonthValue) / lastMonthValue) * 100;
                            }

                            return {
                                totalAmount,
                                percentage: Math.abs(change).toFixed(2) + "%",
                            };
                        },
                        get totalRevenue() {
                            return this.revenue.reduce((sum, val) => sum + val, 0);
                        },
                        get totalExpenses() {
                            return this.expenses.reduce((sum, val) => sum + val, 0);
                        },
                        get totalNetProfit() {
                            return this.revenue - this.expenses;
                        },
                        get totalFilteredRevenue() {
                            return Object.values(this.revenueData).reduce((sum, val) => sum + val, 0);
                        },
                        get totalFilteredNetProfit() {
                            return this.totalFilteredRevenue - this.totalFilteredExpenses;
                        },
                        get totalFilteredExpenses() {
                            return Object.values(this.expensesData).reduce((sum, val) => sum + val, 0);
                        },
                        get netProfitPercentage() {
                            return this.totalRevenue > 0 ?
                                ((this.totalNetProfit / this.totalRevenue) * 100).toFixed(2) + "%" :
                                "0%"; // Avoid division by zero
                        },
                        updateTotals() {
                            this.totalRevenue = this.revenue.reduce((sum, val) => sum + val, 0);
                            this.totalExpenses = this.expenses.reduce((sum, val) => sum + val, 0);
                        },

                        filterData() {
                            setTimeout(() => { // Prevent infinite loop
                                const startYear = parseInt(document.getElementById('startDatePicker').value.split('-')[0]) || 2019;
                                const endYear = parseInt(document.getElementById('endDatePicker').value.split('-')[0]) || 2025;
                                this.filteredData.labels = this.data.labels.filter(year => year >= startYear && year <= endYear);
                                const startIndex = this.data.labels.indexOf(this.filteredData.labels[0]);
                                const endIndex = this.data.labels.indexOf(this.filteredData.labels[this.filteredData.labels.length - 1]) + 1;
                                this.filteredData.values = this.data.values.slice(startIndex, endIndex);
                            }, 0);
                        },
                        data: {
                            revenue: [9000, 11000, 13000, 12000, 10000, 5000, 10000],
                            expenses: [9000, 11000, 13000, 12000, 10000, 5000, 10000]
                        },
                        renderChart() {
                            const revenueData = Object.values(this.revenueData);
                            const expensesData = Object.values(this.expensesData);
                            const expensesYear = Object.keys(this.expensesData);
                            const revenueYear = Object.keys(this.revenueData);

                            // Collect all unique years and sort them
                            const allYears = Array.from(new Set([...revenueYear, ...expensesYear])).sort();

                            // Initialize final arrays
                            const finalRevenue = [];
                            const finalExpenses = [];

                            // Fill the arrays based on available data
                            allYears.forEach(year => {
                                const revIndex = revenueYear.indexOf(year);
                                const expIndex = expensesYear.indexOf(year);

                                finalRevenue.push(revIndex !== -1 ? revenueData[revIndex] : 0);
                                finalExpenses.push(expIndex !== -1 ? expensesData[expIndex] : 0);
                            });

                            const ctx = document.getElementById("netChart");

                            // Check for no data
                            if (
                                !finalRevenue.length ||
                                !finalExpenses.length
                            ) {
                                // Clear canvas and show text
                                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                                ctx.font = '16px Arial';
                                ctx.textAlign = 'center';
                                ctx.fillText('No data found', ctx.canvas.width / 2, ctx.canvas.height / 2);
                                return;
                            }

                            if (Chart.getChart("netChart")) {
                                Chart.getChart("netChart").destroy();
                            }
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: allYears,
                                    datasets: [{
                                            label: 'Revenue',
                                            data: finalRevenue,
                                            backgroundColor: '#2194FF',
                                            borderRadius: 10,
                                            borderWidth: 1
                                        },
                                        {
                                            label: 'Expenses',
                                            data: finalExpenses,
                                            backgroundColor: '#A3D2FF',
                                            borderRadius: 10,
                                            borderWidth: 1
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        x: {
                                            stacked: false
                                        },
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                stepSize: 5000,
                                                callback: function(value) {
                                                    return '$' + value.toLocaleString();
                                                }
                                            }
                                        }
                                    },
                                    plugins: {
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    const value = context.parsed.y;
                                                    return `${context.dataset.label}: $${value.toLocaleString()}`;
                                                }
                                            }
                                        },
                                        legend: {
                                            display: false
                                        }
                                    }
                                }
                            });
                        }
                    }
                };
            </script>
            <!-- Cost Per Role & Channel Revenue Sections -->
            <style>
                .card li.list-group-item {
                    color: #717171 !important;
                    font-weight: 300;
                    font-size: 14px;
                    padding: 0 !important;
                    margin-bottom: 25px;
                }

                .card>.list-group {
                    border-top: unset;
                    border-bottom: unset;
                    margin-top: 40px;
                }

                .list-group {
                    --bs-list-group-border-color: none;
                }

                .card li.list-group-item span {
                    color: #101010;
                    font-weight: 700;
                    font-size: 14px;
                    font-style: normal;
                }

                .card li.list-group-item img {
                    vertical-align: top;
                    margin-right: 6px;
                }

                .cost-nav {
                    display: flex;
                    flex-direction: row;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 24px;
                }

                .cost-nav .form-control {
                    width: 35% !important;
                }

                .channel-rev-nav {
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 24px;
                }

                .date-rev-range {
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    border: solid 1px #D0D0D0;
                    border-radius: 4px;
                }

                .form-select {
                    margin-bottom: 40px;
                }

                .cost-nav h5,
                .channel-rev-nav h5 {
                    color: #101010;
                    font-weight: 600;
                    font-size: 16px;
                    font-style: normal;
                }

                .date-rev-range .rev-range {
                    border: unset;
                }

                input[type=date] {
                    outline: unset;
                }

                .cost-date {
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                    width: 180px;
                    border: solid 1px #D0D0D0;
                    border-radius: 4px;
                    padding: 8px 16px;
                    gap: 10px;
                }

                .cost-date img,
                .channel-rev-nav img {
                    width: 12px;
                    height: 13px;
                }

                ul {
                    list-style-type: none;
                    background-color: #fff;
                    padding: 20px;
                }

                li button {
                    border: unset;
                    color: #101010;
                    font-weight: 300;
                    font-size: 14px;
                    padding: 0;
                }

                li button.btn {
                    padding: 16px 20px;
                }

                li button.btn.active,
                button:hover,
                button:focus {
                    background-color: transparent;
                    color: #101010;
                }

                .cost-select {
                    border: unset;
                    padding: 0;
                    color: #101010;
                    font-weight: 300;
                    font-size: 14px;
                }

                .drop {
                    font-family: Sora;
                    font-size: 14px;
                    font-weight: 300;
                    color: #717171;
                    text-transform: capitalize;
                    position: absolute;
                    z-index: 50;
                    top: 80px;
                    width: 110px;
                    border-radius: 10px;
                    border: solid 1px;
                    padding: 10px;
                }

                .drop li {
                    margin-bottom: 10px;
                }
            </style>
            <div class="container-fluid mt-4">
                <div class=" row g-4">
                    <div class="col-lg-6 ps-lg-0 d-flex" x-data="costRole()">
                        <div class=" card p-4 d-flex flex-grow-1">
                            <div class="cost-nav">
                                <h5>Cost Per Role</h5>
                                <div class="date-rev-range">
                                    <input type="date" class="rev-range" @click="$el.showPicker && $el.showPicker()" x-model="firstDate"> -
                                    <input type="date" class="rev-range" @click="$el.showPicker && $el.showPicker()" x-model="lastDate">
                                </div>
                                <!-- <div class="cost-date">
                                    <img src="/wp-content/uploads/2025/03/calendar.png" alt="">
                                    <div class="relative">
                                        <button class="cost-select" @click="showMonthDropdown = !showMonthDropdown; showYearDropdown = false" x-text="selectedMonth"></button>
                                        <ul x-show="showMonthDropdown" @click.outside="showMonthDropdown = false" class="drop">
                                            <template x-for="month in availableMonths" :key="month">
                                                <li>
                                                    <button 
                                                        @click="
                                                            selectedMonth = new Date(2000, month-1).toLocaleString('default', { month: 'long' });
                                                            filterMonth = month;
                                                            showMonthDropdown = false;
                                                            getMonthYearFilteredData();
                                                        "
                                                        x-text="new Date(2000, month-1).toLocaleString('default', { month: 'long' })"
                                                        
                                                    ></button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div class="relative">
                                        <button class="cost-select" @click="showYearDropdown = !showYearDropdown; showMonthDropdown = false" x-text="selectedYear"></button>
                                        <ul x-show="showYearDropdown" @click.outside="showYearDropdown = false" class="drop">
                                            <template x-for="year in availableYears" :key="year">
                                                <li>
                                                    <button 
                                                        @click="
                                                            selectedYear = year;
                                                            filterYear = year;
                                                            showYearDropdown = false;
                                                            getMonthYearFilteredData();
                                                        "
                                                        x-text="year"
                                                        
                                                    ></button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div> -->
                            </div>
                            <select class="form-select mt-2" x-model="selectedChannel">
                                <option value="all">All channels</option>
                                <template x-for="(channel, index) in channels" :key="index">
                                    <option :value="channel" x-text="channel"></option>
                                </template>
                            </select>
                            <ul class="list-group list-group-flush">
                                <template x-if="Object.keys(costPerRolesData).length === 0">
                                    <li class="list-group-item text-muted text-center">No data found.</li>
                                </template>
                                <!-- <template x-for="(value, index) in costPerRolesData" :key="index">
                                    <li class="list-group-item">
                                        <img class="scriptwriter-icon" src="/wp-content/uploads/2025/03/scriptwriter.png" alt="Scriptwriter">
                                        <span x-text="index"></span>
                                        <span class="float-end" x-text="'$' + value.toLocaleString()"></span>
                                    </li>
                                </template> -->
                                <template x-for="(value, index) in costPerRolesData" :key="index">
                                    <li class="list-group-item">
                                        <img class="scriptwriter-icon"
                                            :src="getRoleImage(index)"
                                            :alt="index">
                                        <span x-text="index"></span>
                                        <span class="float-end" x-text="'$' + value.toLocaleString()"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6 pe-lg-0 d-flex" x-data="channelRevenue()" x-init="drawChart()">
                        <div class="card p-4 d-flex flex-grow-1">
                            <div class="channel-rev-nav">
                                <h5>Channel Revenue</h5>
                                <div class="date-rev-range">
                                    <input type="date" class="rev-range" x-model="firstDate" @click="$el.showPicker && $el.showPicker()" @change="drawChart"> -
                                    <input type="date" class="rev-range" x-model="lastDate" @click="$el.showPicker && $el.showPicker()" @change="drawChart">
                                </div>
                            </div>
                            <select class="form-select mt-2" x-model="selectedChannel" @change="drawChart">
                                <option value="all">All channels</option>
                                <template x-for="(channel, index) in channels" :key="index">
                                    <option :value="channel" x-text="channel"></option>
                                </template>
                            </select>
                            <canvas id="revenueChart" class="mt-3"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function costRole() {
                    return {
                        selectedMonth: '',
                        selectedYear: '',
                        showMonthDropdown: false,
                        showYearDropdown: false,
                        selectedChannel: 'all',
                        invoices: [...paidInvoiceFilter().invoices, ...pendingInvoiceFilter().invoices],
                        firstDate: '',
                        lastDate: '',
                        selectedMonth: (new Date()).toLocaleString('default', {
                            month: 'long'
                        }),
                        selectedYear: new Date().getFullYear(),
                        filterMonth: (new Date()).getMonth() + 1,
                        filterYear: new Date().getFullYear(),
                        availableMonths: [],
                        availableYears: [],
                        init() {
                            this.setInitialDates();
                            this.activeChannels();
                            this.extractAvailableMonthsYears();
                            this.getMonthYearFilteredData();
                        },
                        extractAvailableMonthsYears() {
                            const uniqueDates = {};
                            this.invoices.forEach(item => {
                                const [day, month, year] = item.date.split('/').map(Number);
                                uniqueDates[`${month}-${year}`] = {
                                    month,
                                    year
                                };
                            });
                            this.availableMonths = Array.from(new Set(
                                Object.values(uniqueDates).map(d => d.month)
                            )).sort((a, b) => a - b);
                            this.availableYears = Array.from(new Set(
                                Object.values(uniqueDates).map(d => d.year)
                            )).sort((a, b) => a - b);
                            // console.log('dates', this.availableMonths,this.availableYears);

                        },
                        get dates() {
                            return this.invoices.map(invoice => {
                                const [day, month, year] = invoice.date.split('/').map(Number);
                                return new Date(year, month - 1, day);
                            });
                        },
                        setInitialDates() {
                            if (this.dates.length) {
                                this.firstDate = new Date(Math.min(...this.dates)).toISOString().split('T')[0];
                                this.lastDate = new Date().toISOString().split('T')[0];
                            }
                        },
                        activeChannels() {
                            return this.invoices.filter(channel => channel.status === 'Active');
                        },
                        get channels() {
                            return [...new Set(this.invoices.map(item => item.channel))];
                        },
                        getMonthYearFilteredData() {
                            if (!this.filterMonth || !this.filterYear) {
                                this.filteredTableData = null;
                                return;
                            }
                            this.filteredTableData = this.invoices.filter(item => {
                                const [day, month, year] = item.date.split('/').map(Number);
                                return (month === parseInt(this.filterMonth)) &&
                                    (year === parseInt(this.filterYear));
                            });
                        },
                        getRoleImage(role) {
                            const roleImages = {
                                "Scriptwriter": "/wp-content/uploads/2025/03/scriptwriter.png",
                                "Editor": "/wp-content/uploads/2025/03/editor.png",
                                "Voiceover Artist": "/wp-content/uploads/2025/03/voiceartist.png"
                            };
                            return roleImages[role] || "/wp-content/uploads/2025/03/scriptwriter.png";
                        },
                        get filteredCostPerRoles() {
                            const parseDate = (dateStr) => {
                                const [day, month, year] = dateStr.split('/');
                                return new Date(`${year}-${month}-${day}`);
                            };
                            const parseInputDate = (inputStr) => new Date(inputStr); // yyyy-mm-dd from input

                            const filteredInvoicesByDate = this.invoices.filter(invoice => {
                                const invoiceDate = parseDate(invoice.date);
                                return invoiceDate >= parseInputDate(this.firstDate) && invoiceDate <= parseInputDate(this.lastDate);
                            });

                            if (this.selectedChannel === "all") return filteredInvoicesByDate;

                            const filteredInvoicesByChannel = filteredInvoicesByDate.filter(invoice => invoice.channel === this.selectedChannel);

                            return filteredInvoicesByChannel;

                            // if (!this.filterMonth || !this.filterYear) {
                            //     this.filteredTableData = null;
                            //     return;
                            // }
                            // filteredInvoicesByDate = this.invoices.filter(item => {                  
                            //     const [day, month, year] = item.date.split('/').map(Number);
                            //     return (month === parseInt(this.filterMonth)) &&
                            //         (year === parseInt(this.filterYear));
                            // });

                            // if (this.selectedChannel === "all") return filteredInvoicesByDate;

                            // const filteredInvoicesByChannel = filteredInvoicesByDate.filter(invoice => invoice.channel === this.selectedChannel);

                            // return filteredInvoicesByChannel;
                        },
                        get costPerRolesData() {
                            const invoices = this.filteredCostPerRoles;

                            let costPerRolesData = {};

                            invoices.forEach(({
                                date,
                                amount,
                                channel,
                                role
                            }) => {
                                amount = parseInt(amount);
                                costPerRolesData[role] = (costPerRolesData[role] || 0) + amount;

                            });

                            return costPerRolesData;
                        },
                    }
                }

                function channelRevenue() {
                    return {
                        selectedChannel: 'all',
                        chartInstance: null,
                        invoices: [...paidInvoiceFilter().invoices, ...pendingInvoiceFilter().invoices],
                        revenue: <?php
                                    $args = array(
                                        'post_type'      => 'channel-insight',
                                        'posts_per_page' => -1,
                                        'post_status'    => 'publish',
                                    );
                                    $revenue_query = new WP_Query($args);
                                    $revenue_data = [];

                                    if ($revenue_query->have_posts()) {
                                        while ($revenue_query->have_posts()) {
                                            $revenue_query->the_post();
                                            $revenue_data[] = [
                                                'id'            => get_the_ID(),
                                                'date'   => get_field('date_create'),
                                                'channel'   => get_field('channel_name'),
                                                'amount' => get_field('revenue_amount'),
                                                'channelViews' => get_field('channel_views'),
                                                'channelSubscribers' => get_field('channel_subscribers'),
                                            ];
                                        }
                                        wp_reset_postdata();
                                    }
                                    echo json_encode($revenue_data);
                                    ?>,
                        firstDate: '',
                        lastDate: '',
                        init() {
                            this.setInitialDates();
                            console.log('the revenues: ', this.revenue);
                        },
                        get dates() {
                            return this.revenue.map(revenue => {
                                const [day, month, year] = revenue.date.split('/').map(Number);
                                return new Date(year, month - 1, day);
                            });
                        },
                        setInitialDates() {
                            if (this.dates.length) {
                                this.firstDate = new Date(Math.min(...this.dates)).toISOString().split('T')[0];
                                this.lastDate = new Date().toISOString().split('T')[0];
                            }
                        },
                        get channels() {
                            return [...new Set(this.revenue.map(item => item.channel))];
                        },
                        // get revenueData() {
                        //     const revenue = this.revenue;

                        //     let revenueData = {
                        //         all: {}
                        //     };

                        //     revenue.forEach(({
                        //         date,
                        //         amount,
                        //         channel
                        //     }) => {
                        //         let year = date.split("/")[2]; // Extract the year
                        //         amount = parseInt(amount); // Convert amount to integer

                        //         // Add to 'all' revenue
                        //         revenueData.all[year] = (revenueData.all[year] || 0) + amount;

                        //         // Add to specific channel revenue
                        //         if (!revenueData[channel]) {
                        //             revenueData[channel] = {};
                        //         }
                        //         revenueData[channel][year] = (revenueData[channel][year] || 0) + amount;
                        //     });

                        //     return revenueData;


                        // },

                        // get filteredRevenue() {
                        //     let startYear = parseInt(this.firstDate.split("-")[0]);
                        //     let endYear = parseInt(this.lastDate.split("-")[0]);

                        //     let data = this.revenueData[this.selectedChannel];
                        //     let filtered = Object.entries(data)
                        //         .filter(([year]) => year >= startYear && year <= endYear)
                        //         .reduce((acc, [year, value]) => {
                        //             acc.labels.push(year);
                        //             acc.values.push(value);
                        //             return acc;
                        //         }, {
                        //             labels: [],
                        //             values: []
                        //         });
                        //     return filtered;
                        // },
                        // get totalRevenue() {
                        //     return this.filteredRevenue.values.reduce((sum, value) => sum + value, 0);
                        // },
                        get filteredRevenue() {
                            const parseDate = (dateStr) => {
                                const [day, month, year] = dateStr.split('/');
                                return new Date(`${year}-${month}-${day}`);
                            };
                            const filteredRevenueByDate = this.revenue.filter(revenue => {
                                const revenueDate = parseDate(revenue.date);
                                return revenueDate >= parseDate(this.firstDate) && revenueDate <= parseDate(this.lastDate);
                            });

                            if (this.selectedChannel === "all") return filteredRevenueByDate;
                            const filteredRevenueByChannel = filteredRevenueByDate.filter(revenue => revenue.channel === this.selectedChannel);

                            return filteredRevenueByChannel;
                        },
                        get revenueData() {
                            const data = this.filteredRevenue;

                            const monthNames = {
                                "01": "January",
                                "02": "February",
                                "03": "March",
                                "04": "April",
                                "05": "May",
                                "06": "June",
                                "07": "July",
                                "08": "August",
                                "09": "September",
                                "10": "October",
                                "11": "November",
                                "12": "December"
                            };

                            let revenueByYearMonth = {};

                            data.forEach(({
                                date,
                                amount
                            }) => {
                                const [day, month, year] = date.split('/');
                                const paddedMonth = month.padStart(2, '0');

                                if (!revenueByYearMonth[year]) {
                                    revenueByYearMonth[year] = {};
                                }

                                if (!revenueByYearMonth[year][paddedMonth]) {
                                    revenueByYearMonth[year][paddedMonth] = 0;
                                }

                                revenueByYearMonth[year][paddedMonth] += parseInt(amount);
                            });

                            const years = Object.keys(revenueByYearMonth);
                            let labels = [];
                            let values = [];

                            if (years.length === 1) {
                                const year = years[0];
                                const months = Object.keys(revenueByYearMonth[year]).sort();
                                labels = months.map(month => monthNames[month]);
                                values = months.map(month => revenueByYearMonth[year][month]);
                            } else {
                                years.sort();
                                labels = years;
                                values = years.map(year => {
                                    const total = Object.values(revenueByYearMonth[year])
                                        .reduce((sum, val) => sum + val, 0);
                                    return total;
                                });
                            }

                            return {
                                labels,
                                values
                            };
                        },
                        drawChart() {
                            const ctx = document.getElementById('revenueChart').getContext('2d');
                            if (this.chartInstance) {
                                this.chartInstance.destroy();
                            }

                            // Check for no data
                            if (
                                !this.revenueData.labels.length ||
                                !this.revenueData.values.length
                            ) {
                                // Clear canvas and show text
                                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                                ctx.font = '16px Arial';
                                ctx.textAlign = 'center';
                                ctx.fillText('No data found', ctx.canvas.width / 2, ctx.canvas.height / 2);
                                return;
                            }

                            // Create chart if there's data
                            if (ctx) {
                                this.chartInstance = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: this.revenueData.labels,
                                        datasets: [{
                                            label: 'Revenue',
                                            data: this.revenueData.values.map(Number), // make sure they're numbers
                                            backgroundColor: '#2194FF',
                                            borderRadius: 10
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return '$' + value.toLocaleString();
                                                    }
                                                }
                                            }
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        const label = context.dataset.label || '';
                                                        const value = context.parsed.y !== undefined ?
                                                            context.parsed.y :
                                                            context.raw; // fallback
                                                        return `${label}: $${Number(value).toLocaleString()}`;
                                                    }
                                                }
                                            },
                                            legend: {
                                                display: false,
                                                labels: {
                                                    color: 'rgb(255, 99, 132)'
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        },
                        updateChart() {
                            this.drawChart();
                        }
                    };
                }
            </script>
            <!-- Active Roster Section -->
            <style>
                /* .active-roster {
                    background-collator_asort
                } */
                .active-roster .card {
                    border-radius: 10px;
                    /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); */
                    /* text-align: center; */
                    /* padding: 20px; */
                    transition: 0.3s;
                    min-height: 450px;
                    background: transparent;
                }

                .active-roster .card img {
                    width: 100px;
                    height: 100px;
                    border-radius: 50%;
                    margin-bottom: 10px;
                }

                .owl-controls .owl-buttons {
                    text-align: center;
                    margin-top: 20px;
                }

                .owl-controls .owl-buttons .owl-prev {
                    background: #fff;
                    position: absolute;
                    top: 40%;
                    left: -40px;
                    padding: 0 18px 0 15px;
                    border-radius: 50px;
                    box-shadow: 3px 14px 25px -10px #92b4d0;
                    transition: background 0.5s ease 0s;
                }

                .owl-controls .owl-buttons .owl-next {
                    background: #fff;
                    position: absolute;
                    top: 40%;
                    right: -40px;
                    padding: 0 15px 0 18px;
                    border-radius: 50px;
                    box-shadow: -3px 14px 25px -10px #92b4d0;
                    transition: background 0.5s ease 0s;
                }

                .owl-controls .owl-buttons .owl-prev:after,
                .owl-controls .owl-buttons .owl-next:after {
                    content: "\f104";
                    font-family: FontAwesome;
                    color: #333;
                    font-size: 30px;
                }

                .owl-controls .owl-buttons .owl-next:after {
                    content: "\f105";
                }

                /* #news-slider {
                margin-top: 20px;
                } */
                .post-slide {
                    /* background: #fff; */
                    margin: 20px 15px 20px;
                    border-radius: 15px;
                    padding-top: 1px;
                    /* box-shadow: 0px 14px 22px -9px #bbcbd8; */
                }

                .card .card-img {
                    padding: 25px;
                }

                .card .card-desc {
                    padding: 20px 35px 35px 35px;
                }

                .card .card-desc p.card-info {
                    font-size: 14px;
                    color: #101010;
                    font-weight: 300;
                    font-style: normal;
                    margin-bottom: 45px;
                }

                .card .card-desc h5.card-title {
                    font-size: 24px;
                    letter-spacing: -0.01em;
                    color: #101010;
                    font-weight: 600;
                    font-style: normal;
                    margin-bottom: 25px;
                }

                .card .card-desc p.card-channel-desc {
                    font-size: 14px;
                    color: #717171;
                    font-weight: 300;
                    font-style: normal;
                }

                .active-roster h5 {
                    color: #101010;
                    font-weight: 700;
                    font-size: 16px;
                    font-style: normal;
                }

                .active-nav {
                    display: flex;
                    flex-direction: row;
                    justify-content: space-between;
                    align-items: flex-end;
                    margin-bottom: 40px;
                }
            </style>

            <div class="container-fluid card active-roster mt-4 p-4">
                <div class="active-nav">
                    <h5 class="active-roster">Active Roster</h5>
                    <a href="/channel-list" class="btn-view-all"><img class="viewall-icon" src="/wp-content/uploads/2025/03/viewall.png" alt="View All">View All</a>
                </div>
                <div class="row channel-card">
                    <div class="col-md-12">
                        <div id="news-slider" class="owl-carousel">
                            <?php
                            $args = array(
                                'post_type'      => 'channel',
                                'posts_per_page' => -1,
                                'post_status'    => 'publish',
                            );
                            $channel_query = new WP_Query($args);
                            if ($channel_query->have_posts()) {
                                while ($channel_query->have_posts()) {
                                    $channel_query->the_post();
                                    $photo = get_field('channel_profile_photo');
                                    $thumbnail = $photo['sizes']['thumbnail'] ?? '/wp-content/uploads/2025/03/Glow-Icon-_-flower-150x150.jpg';
                                    echo '<div class="post-slide">';
                                    echo '   <a href="' . get_permalink() . '">';
                                    echo '   <div class="card">';
                                    echo '   <div class="card-img">';
                                    echo '       <img src="' . $thumbnail . '" alt="' . get_the_title() . '">';
                                    echo '   </div>';
                                    echo '   <div class="card-desc">';
                                    echo '      <p class="card-info">' . get_field('channel_subscriber') . ' subscribers • ' . get_field('channel_videos') . ' videos</p>';
                                    echo '       <h5 class="card-title">' . get_the_title() . '</h5>';
                                    echo '       <p class="card-channel-desc">' . get_field('channel_description') . '</p>';
                                    echo '   </div>';
                                    echo '   </div>';
                                    echo '   </a>';
                                    echo '   </div>';
                                }
                                wp_reset_postdata();
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <script>
                jQuery(document).ready(function($) {
                    $("#news-slider").owlCarousel({
                        loop: true,
                        margin: 10,
                        nav: true,
                        dots: true,
                        autoplay: false,
                        autoplayTimeout: 3000,
                        responsive: {
                            0: { items: 1 },
                            600: { items: 2 },
                            1000: { items: 3 }
                        }
                    });
                });
            </script> -->
            <style>
                table tbody tr:hover>td,
                table tbody tr:hover>th {
                    background-color: transparent;
                }

                .btn.disabled,
                .btn:disabled,
                fieldset:disabled .btn {
                    border-color: transparent;
                }

                .btn-check:checked+.btn,
                .btn.active,
                .btn.show,
                .btn:first-child:active,
                :not(.btn-check)+.btn:active {
                    color: #101010;
                    background-color: #FFEE94;
                    border-color: transparent;
                }

                .btn-view-all {
                    padding: 10px 15px;
                    font-style: normal;
                    width: auto;
                    height: auto;
                }

                .btn-view-all img {
                    height: 10px;
                    width: 10px;
                    margin-right: 10px;
                }

                .pending-nav,
                .paid-nav {
                    display: flex;
                    flex-direction: row;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 40px;
                }

                .pending-nav h5,
                .paid-nav h5 {
                    color: #101010;
                    font-weight: 600;
                    font-size: 16px;
                    font-style: normal;
                }

                img.sort-icon {
                    width: 10px;
                    height: 10px;
                    margin-left: 10px;
                }

                .pagination {
                    margin: 40px 0 0 0;
                }

                .pagination .btn img {
                    height: 12px;
                }

                .pagination .btn .prev-icon {
                    margin-right: 5px;
                }

                .pagination .btn .next-icon {
                    margin-left: 5px;
                }

                .paid-invoice {
                    position: relative;
                }

                .invoice-tbl-wrapper {
                    overflow-x: auto;
                }

                .invoice-tbl-wrapper::-webkit-scrollbar {
                    height: 5px;
                }

                .invoice-tbl-wrapper::-webkit-scrollbar-thumb {
                    background: #336;
                    border-radius: 4px;
                }

                .invoice-tbl-wrapper::-webkit-scrollbar-track {
                    background: #f1f1f1;
                }

                .paid-invoice table {
                    width: 100%;
                    min-width: 800px;
                    border-collapse: collapse;
                    white-space: nowrap;
                }

                .invoice-tbl-wrapper .styl-inv-txt {
                    font-size: 14px;
                    font-style: normal;
                    font-weight: 300;
                    color: #717171;
                }

                .rotate-180 {
                    transform: rotate(180deg);
                }
            </style>
            <!-- Pending Invoices Section -->
            <div class="card p-4 shadow-sm mt-4 paid-invoice">
                <div x-data="pendingInvoiceFilter()">
                    <div class="pending-nav">
                        <h5>Pending Invoices</h5>
                        <div class="nav-option">
                            <div class="search-container">
                                <img class="search-icon" src="https://clickable.opt.co.nz/wp-content/uploads/2025/02/Vector-4.svg" alt="Search Icon">
                                <input type="text" placeholder="Search name here..." x-model="searchQuery" @input="filterInvoices">
                            </div>
                            <a href="/invoices" class="btn-view-all"><img class="viewall-icon" src="/wp-content/uploads/2025/03/viewall.png" alt="View All">View All</a>
                        </div>
                    </div>
                    <div class="invoice-tbl-wrapper">
                        <table border="1" width="100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Invoice Number</th>
                                    <th @click="sortBy('discordName')" class="cursor-pointer">
                                        Discord Name
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="sort-icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'discordName' && !sortAsc}">
                                    </th>
                                    <th @click="sortBy('date')" class="cursor-pointer">
                                        Date
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="sort-icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'date' && !sortAsc}">
                                    </th>
                                    <th @click="sortBy('date')" class="cursor-pointer">
                                        Date Paid
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="sort-icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'date' && !sortAsc}">
                                    </th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="invoice in paginatedInvoices()" :key="invoice.id">
                                    <tr>
                                        <td x-text="invoice.id" class="styl-inv-txt"></td>
                                        <td x-text="invoice.invoiceNumber" class="styl-inv-txt"></td>
                                        <td x-text="invoice.discordName" class="styl-inv-txt"></td>
                                        <td x-text="invoice.date" class="styl-inv-txt"></td>
                                        <td x-text="invoice.date" class="styl-inv-txt"></td>
                                        <td>
                                            <span @click="downloadInvoice(invoice)"><img class="dl-icon" src="/wp-content/uploads/2025/03/dl-icon.png" alt="Download Icon"></i></span>
                                            <span @click="deleteInvoice(invoice.id)"><img class="dlt-icon" src="/wp-content/uploads/2025/03/dlt-icon.png" alt="Delete Icon"></i></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination justify-content-end">
                        <button class="btn" @click="prevPage()" :disabled="currentPage === 1"><img class="prev-icon" src="/wp-content/uploads/2025/02/Vector-5.svg" alt="Previous"> Previous</button>
                        <template x-for="page in totalPages()" :key="page">
                            <button class="btn" :class="currentPage === page ? 'active' : ''" @click="currentPage = page" x-text="page"></button>
                        </template>
                        <button class="btn" @click="nextPage()" :disabled="currentPage === totalPages()">Next <img class="next-icon" src="/wp-content/uploads/2025/03/next.png" alt="Next"></button>
                    </div>
                </div>
            </div>

            <!-- Paid Invoices Section -->
            <div class="card p-4 shadow-sm mt-4 paid-invoice">
                <div x-data="paidInvoiceFilter()">
                    <div class="paid-nav">
                        <h5>Paid Invoices</h5>
                        <div class="nav-option">
                            <div class="search-container">
                                <img class="search-icon" src="https://clickable.opt.co.nz/wp-content/uploads/2025/02/Vector-4.svg" alt="Search Icon">
                                <input type="text" placeholder="Search name here..." x-model="searchQuery" @input="filterInvoices">
                            </div>
                            <!-- <a href="/invoices" class="btn-view-all"><i class="fa-solid fa-arrow-up-right-from-square pe-2"></i>View All</a> -->
                            <a href="/invoices" class="btn-view-all"><img class="viewall-icon" src="/wp-content/uploads/2025/03/viewall.png" alt="View All">View All</a>
                        </div>
                    </div>
                    <div class="invoice-tbl-wrapper">
                        <table border="1" width="100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Invoice Number</th>
                                    <th @click="sortBy('discordName')" class="cursor-pointer">
                                        Discord Name
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="sort-icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'discordName' && !sortAsc}">
                                    </th>
                                    <th @click="sortBy('date')" class="cursor-pointer">
                                        Date
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="sort-icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'date' && !sortAsc}">
                                    </th>
                                    <th @click="sortBy('date')" class="cursor-pointer">
                                        Date Paid
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="sort-icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'date' && !sortAsc}">
                                    </th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="invoice in paginatedInvoices()" :key="invoice.id">
                                    <tr>
                                        <td x-text="invoice.id" class="styl-inv-txt"></td>
                                        <td x-text="invoice.invoiceNumber" class="styl-inv-txt"></td>
                                        <td x-text="invoice.discordName" class="styl-inv-txt"></td>
                                        <td x-text="invoice.dueDate" class="styl-inv-txt"></td>
                                        <td x-text="invoice.date" class="styl-inv-txt"></td>
                                        <td>
                                            <span @click="downloadInvoice(invoice)"><img class="dl-icon" src="/wp-content/uploads/2025/03/dl-icon.png" alt="Download Icon"></i></span>
                                            <span @click="deleteInvoice(invoice.id)"><img class="dlt-icon" src="/wp-content/uploads/2025/03/dlt-icon.png" alt="Delete Icon"></i></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination justify-content-end">
                        <button class="btn" @click="prevPage()" :disabled="currentPage === 1"><img class="prev-icon" src="/wp-content/uploads/2025/02/Vector-5.svg" alt="Previous"> Previous</button>
                        <template x-for="page in totalPages()" :key="page">
                            <button class="btn" :class="currentPage === page ? 'active' : ''" @click="currentPage = page" x-text="page"></button>
                        </template>
                        <button class="btn" @click="nextPage()" :disabled="currentPage === totalPages()">Next <img class="next-icon" src="/wp-content/uploads/2025/03/next.png" alt="Next"></button>
                    </div>
                </div>
            </div>
            <script>
                // document.addEventListener('alpine:init', () => {
                //     Alpine.store('invoice', {
                //         invoice: false
                //     });
                // });

                function invoiceFilter() {
                    return {
                        invoices: [...paidInvoiceFilter().invoices, ...pendingInvoiceFilter().invoices],
                        selectedChannel: '',
                        get filteredInvoices() {
                            return this.invoices.filter(invoice =>
                                (this.selectedChannel === '' || invoice.channel === this.selectedChannel)
                            );
                        },

                        filterByChannel() {
                            return this.filteredInvoices;
                        },
                    };
                }

                function paidInvoiceFilter() {
                    return {
                        showFilters: false,
                        searchQuery: '',
                        selectedStatus: '',
                        selectedPlatform: '',
                        selectedChannel: '',
                        currentPage: 1,
                        itemsPerPage: 5,
                        sortKey: '',
                        sortAsc: true,
                        sortBy(key) {
                            if (this.sortKey === key) {
                                this.sortAsc = !this.sortAsc;
                            } else {
                                this.sortKey = key;
                                this.sortAsc = true;
                            }
                        },
                        init() {
                            console.log('Invoice filter initialized');
                        },
                        invoices: <?php echo json_encode($paid_invoice_data); ?>,
                        // get filteredInvoices() {
                        //     return this.invoices.filter(invoice =>
                        //         (this.selectedStatus === '' || invoice.status === this.selectedStatus) &&
                        //         (this.selectedPlatform === '' || invoice.platform === this.selectedPlatform) &&
                        //         (this.selectedChannel === '' || invoice.channel === this.selectedChannel) &&
                        //         (this.searchQuery === '' || invoice.discordName.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        //             invoice.fileName.toLowerCase().includes(this.searchQuery.toLowerCase()))
                        //     );
                        // },
                        get filteredInvoices() {
                            let result = this.invoices.filter(invoice =>
                                (this.selectedStatus === '' || invoice.status === this.selectedStatus) &&
                                (this.selectedPlatform === '' || invoice.platform === this.selectedPlatform) &&
                                (this.selectedChannel === '' || invoice.channel === this.selectedChannel) &&
                                (this.searchQuery === '' || invoice.discordName?.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                    invoice.fileName?.toLowerCase().includes(this.searchQuery.toLowerCase()))
                            );

                            if (this.sortKey) {
                                result.sort((a, b) => {
                                    let aVal = a[this.sortKey] ?? '';
                                    let bVal = b[this.sortKey] ?? '';

                                    if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                                    if (typeof bVal === 'string') bVal = bVal.toLowerCase();

                                    if (aVal < bVal) return this.sortAsc ? -1 : 1;
                                    if (aVal > bVal) return this.sortAsc ? 1 : -1;
                                    return 0;
                                });
                            }

                            return result;
                        },
                        paginatedInvoices() {
                            const start = (this.currentPage - 1) * this.itemsPerPage;
                            return this.filteredInvoices.slice(start, start + this.itemsPerPage);
                        },
                        totalPages() {
                            return Math.ceil(this.filteredInvoices.length / this.itemsPerPage);
                        },
                        nextPage() {
                            if (this.currentPage < this.totalPages()) {
                                this.currentPage++;
                            }
                        },
                        prevPage() {
                            if (this.currentPage > 1) {
                                this.currentPage--;
                            }
                        },
                        deleteInvoice(invoiceID, id) {
                            this.invoices = this.invoices.filter(invoice => invoice.id !== invoiceID);
                            if (this.currentPage > this.totalPages()) this.currentPage = this.totalPages();
                            // wp_delete_post(id);
                        },
                        downloadInvoice(invoice) {
                            let header = "Invoice Number, Channel, Date, Date, Status";
                            let content = `${invoice.invoiceNumber}, ${invoice.channel}, ${invoice.date}, ${invoice.date}, ${invoice.status}`;

                            let csvContent = `${header}\n${content}`;

                            let blob = new Blob([csvContent], {
                                type: "text/csv;charset=utf-8"
                            });
                            saveAs(blob, `${invoice.invoiceNumber}.csv`);
                        }
                    };
                }

                function pendingInvoiceFilter() {
                    return {
                        showFilters: false,
                        searchQuery: '',
                        selectedStatus: '',
                        selectedPlatform: '',
                        selectedChannel: '',
                        currentPage: 1,
                        itemsPerPage: 5,
                        sortKey: '',
                        sortAsc: true,
                        sortBy(key) {
                            if (this.sortKey === key) {
                                this.sortAsc = !this.sortAsc;
                            } else {
                                this.sortKey = key;
                                this.sortAsc = true;
                            }
                        },

                        invoices: <?php echo json_encode($pending_invoice_data); ?>,
                        // get filteredInvoices() {
                        //     return this.invoices.filter(invoice =>
                        //         (this.selectedStatus === '' || invoice.status === this.selectedStatus) &&
                        //         (this.selectedPlatform === '' || invoice.platform === this.selectedPlatform) &&
                        //         (this.selectedChannel === '' || invoice.channel === this.selectedChannel) &&
                        //         (this.searchQuery === '' || invoice.discordName.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        //             invoice.fileName.toLowerCase().includes(this.searchQuery.toLowerCase()))
                        //     );
                        // },
                        get filteredInvoices() {
                            let result = this.invoices.filter(invoice =>
                                (this.selectedStatus === '' || invoice.status === this.selectedStatus) &&
                                (this.selectedPlatform === '' || invoice.platform === this.selectedPlatform) &&
                                (this.selectedChannel === '' || invoice.channel === this.selectedChannel) &&
                                (this.searchQuery === '' || invoice.discordName?.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                    invoice.fileName?.toLowerCase().includes(this.searchQuery.toLowerCase()))
                            );

                            if (this.sortKey) {
                                result.sort((a, b) => {
                                    let aVal = a[this.sortKey] ?? '';
                                    let bVal = b[this.sortKey] ?? '';

                                    if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                                    if (typeof bVal === 'string') bVal = bVal.toLowerCase();

                                    if (aVal < bVal) return this.sortAsc ? -1 : 1;
                                    if (aVal > bVal) return this.sortAsc ? 1 : -1;
                                    return 0;
                                });
                            }

                            return result;
                        },
                        paginatedInvoices() {
                            const start = (this.currentPage - 1) * this.itemsPerPage;
                            return this.filteredInvoices.slice(start, start + this.itemsPerPage);
                        },
                        totalPages() {
                            return Math.ceil(this.filteredInvoices.length / this.itemsPerPage);
                        },
                        nextPage() {
                            if (this.currentPage < this.totalPages()) {
                                this.currentPage++;
                            }
                        },
                        prevPage() {
                            if (this.currentPage > 1) {
                                this.currentPage--;
                            }
                        },
                        deleteInvoice(invoiceID, id) {
                            this.invoices = this.invoices.filter(invoice => invoice.id !== invoiceID);
                            if (this.currentPage > this.totalPages()) this.currentPage = this.totalPages();
                            // wp_delete_post(id);
                        },
                        downloadInvoice(invoice) {
                            let header = "Invoice Number, Channel, Date, Date, Status";
                            let content = `${invoice.invoiceNumber}, ${invoice.channel}, ${invoice.date}, ${invoice.date}, ${invoice.status}`;

                            let csvContent = `${header}\n${content}`;

                            let blob = new Blob([csvContent], {
                                type: "text/csv;charset=utf-8"
                            });
                            saveAs(blob, `${invoice.invoiceNumber}.csv`);
                        }
                    };
                }
            </script>
        </main>
    </div>
</div>

<!-- Media queries -->
<style>
    @media only screen and (max-width: 1440px) {
        .channel-rev-nav {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .date-rev-range {
            width: 100%;
        }

        .netinfo {
            margin-right: 20px;
        }
    }

    @media only screen and (max-width: 1024px) {
        .stats-row {
            display: flex;
            flex-direction: column;
            row-gap: 24px;
        }

        .stats-row .col-md-4 {
            width: 100%;
        }

        .container-fluid .mt-4 .row.g-4 {
            display: flex;
            flex-direction: column;
        }

        .container-fluid .mt-4 .row.g-4 .col-lg-6 {
            width: 100%;
            padding: 0;
        }

        .pending-nav,
        .paid-nav {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .paid-invoice .search-container {
            width: 400px;
        }

        .paid-invoice .nav-option {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
        }

        .data-list {
            border-left: unset;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            margin-top: 20px;
        }

        .net-revenue,
        .net-expense {
            justify-content: space-between;
        }

        .netinfo {
            margin-right: 0;
        }
    }

    @media only screen and (max-width: 768px) {
        .flex-grow-1 {
            padding: 0;
        }

        .form-control {
            width: 50% !important;
        }

        .card.p-4 .row.mt-4 {
            display: flex;
            flex-direction: column;
            row-gap: 24px;
        }

        .card.p-4 .row.mt-4 .col-md-9 {
            width: 100%;
        }

        .card.p-4 .row.mt-4 .data-list {
            width: 100%;
            border-left: unset;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .channel-rev-nav {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .date-rev-range {
            width: 100%;
        }

        .paid-invoice .search-container {
            width: 230px;
        }

        .card .expenses-nav {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .card .expenses-nav h5 {
            margin-bottom: 24px;
        }
    }

    @media only screen and (max-width: 767px) {
        .parent-main {
            display: flex;
            flex-direction: row;
        }

        .menuLogoImgText {
            width: 180px;
        }

        .main-content {
            width: 520px;
        }
    }

    @media only screen and (max-width: 425px) {
        .stats-row .card {
            padding: 22px 20px;
        }

        .card .expenses-nav {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .form-control,
        .cost-nav .form-control {
            width: 100% !important;
        }

        .cost-nav {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .active-nav {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .paid-invoice .nav-option {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .paid-invoice .search-container {
            width: 100%;
            margin-bottom: 24px;
        }

        .expense-range-btn .expense-date-range,
        .expense-range-btn .revenue-date-range,
        .date-rev-range .rev-range,
        .expense-range-btn .netprof-date-range {
            width: 45%;
        }

        .expense-range-btn,
        .channel-rev-nav .date-rev-range {
            width: 100%;
        }

        .cost-nav h5,
        .channel-rev-nav h5 {
            margin-bottom: 24px;
        }

        .pagination button.btn,
        .btn:disabled {
            padding: 5px;
        }

        .form-select {
            margin-bottom: 0;
        }

        .card>.list-group {
            margin-top: 20px;
        }

        .drop {
            top: 120px;
            width: 90px;
        }

        .cost-date {
            width: 235px;
        }
    }
</style>

<?php get_footer('admin'); ?>