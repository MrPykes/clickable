<?php

/**
 * Template Name: Revenue Template
 */

get_header('admin');

// Create the nonce and print it into a JS variable
$nonce = wp_create_nonce('wp_rest');


// Invoices
$args = [
    'post_type'      => 'invoice-creator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'status',
            'value'   => ['PAID'],
            'compare' => 'IN',
        ],
    ],
];
$invoices_query = new WP_Query($args);
$invoice_data = [];

if ($invoices_query->have_posts()) {
    while ($invoices_query->have_posts()) {
        $invoices_query->the_post();
        $date_submitted = get_field('date_submitted');

        // Fix format YYYY-MM-DD → DD/MM/YYYY
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date_submitted, $matches)) {
            $date_submitted = "{$matches[3]}/{$matches[2]}/{$matches[1]}";
        }

        $task_channel = get_field('your_tasks')[0]['tasks_channel'] ?? '';

        $channel_name = '';
        if (!empty($task_channel)) {
            if (is_array($task_channel)) {
                // In case it's stored as array of IDs
                $channel_name = get_the_title($task_channel[0]);
            } else {
                // Single ID
                $channel_name = get_the_title($task_channel);
            }
        }

        $invoice_data[] = [
            'id'      => get_the_ID(),
            'date'    => $date_submitted,
            'invoices' => get_field('amount'),
            'status'  => get_field('status'),
            'channel' => $channel_name,
        ];
    }
    wp_reset_postdata();
}

// Revenue
// $args = [
//     'post_type'      => 'channel-insight',
//     'posts_per_page' => -1,
//     'post_status'    => 'publish',
// ];
// $revenue_query = new WP_Query($args);
// $revenue_data = [];

// if ($revenue_query->have_posts()) {
//     while ($revenue_query->have_posts()) {
//         $revenue_query->the_post();

//         $date_revenue = get_field('date_create');
//         $date_expense = get_field('channel_expense_date');

//         if (!empty($date) && preg_match('/\d{2}\/\d{2}\/\d{4}/', (string) $date)) {
//             // process revenue
//             $date_revenue = "{$matches[3]}/{$matches[2]}/{$matches[1]}";
//         }
//         if (!empty($dateExpense) && preg_match('/\d{2}\/\d{2}\/\d{4}/', (string) $dateExpense)) {
//             // process expense
//             $date_expense = "{$matches[3]}/{$matches[2]}/{$matches[1]}";
//         }

//         // Revenue record
//         $revenue_data[] = [
//             'id'       => get_the_ID(),
//             'date'     => $date_revenue,
//             'channel'  => get_field('channel_name'),
//             'revenue'  => get_field('revenue_amount'),
//             'expenses' => 0
//         ];

//         // Expense record
//         $revenue_data[] = [
//             'id'       => get_the_ID(),
//             'date'     => $date_revenue,
//             'channel'  => get_field('channel_name'),
//             'revenue'  => 0,
//             'expenses' => get_field('channel_expense_amount')
//         ];
//     }
//     wp_reset_postdata();
// }
$args = [
    'post_type'      => 'channel-insight',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
];
$revenue_query = new WP_Query($args);
$revenue_data = [];

if ($revenue_query->have_posts()) {
    while ($revenue_query->have_posts()) {
        $revenue_query->the_post();
        $post_id    = get_the_ID();
        $channel    = get_field('channel_name', $post_id);

        // --- Loop revenue repeater ---
        if (have_rows('your_channel_insights', $post_id)) {
            while (have_rows('your_channel_insights', $post_id)) {
                the_row();

                $date_revenue = get_sub_field('date_create_insight');
                $revenue      = (float) get_sub_field('revenue_amount') ?: 0;

                $revenue_data[] = [
                    'id'       => $post_id,
                    'date'     => $date_revenue,
                    'channel'  => $channel,
                    'revenue'  => $revenue,
                    'expenses' => 0
                ];
            }
        }

        // --- Loop expense repeater ---
        if (have_rows('your_channel_expense', $post_id)) {
            while (have_rows('your_channel_expense', $post_id)) {
                the_row();

                $date_expense = get_sub_field('channel_expense_date');
                $expense      = (float) get_sub_field('channel_expense_amount') ?: 0;

                $revenue_data[] = [
                    'id'       => $post_id,
                    'date'     => $date_expense,
                    'channel'  => $channel,
                    'revenue'  => 0,
                    'expenses' => $expense
                ];
            }
        }
    }
    wp_reset_postdata();
}


// Updated revenue
$args = [
    'post_type'      => 'channel-insight',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
];
$updated_revenue_query = new WP_Query($args);
$updated_revenue_data = [];

if ($updated_revenue_query->have_posts()) {
    while ($updated_revenue_query->have_posts()) {
        $updated_revenue_query->the_post();

        $date_create = get_field('date_create');

        // skip if empty/null
        if (empty($date_create)) {
            continue;
        }
        $updated_revenue_data[] = [
            'id'      => get_the_ID(),
            'date'    => $date_create,
            'channel' => get_field('channel_name'),
            'revenue' => get_field('revenue_amount'),
            'dateExpense' => get_field('channel_expense_date'),
            'expenses' => get_field('channel_expense_amount')
        ];
    }
    wp_reset_postdata();
}

// Expenses
// $args = [
//     'post_type'      => 'channel-expense',
//     'posts_per_page' => -1,
//     'post_status'    => 'publish',
// ];
// $expense_query = new WP_Query($args);
// $expense_data = [];

// if ($expense_query->have_posts()) {
//     while ($expense_query->have_posts()) {
//         $expense_query->the_post();
//         $expense_data[] = [
//             'id'       => get_the_ID(),
//             'date'     => get_field('expenses_date'),
//             'expenses' => get_field('amount_expenses'),
//             'channel'  => get_field('channel_name'),
//         ];
//     }
//     wp_reset_postdata();
// }



?>
<script>
    if (typeof wpApiSettings === 'undefined') {
        var wpApiSettings = {
            nonce: "<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>"
        };
    }
</script>
<script type="application/json" id="invoices-data">
    <?php echo wp_json_encode($invoice_data); ?>
</script>

<!-- <script type="application/json" id="expenses-data">
    < ?php echo wp_json_encode($expense_data); ?>
</script> -->

<script type="application/json" id="revenue-data">
    <?php echo wp_json_encode($revenue_data); ?>
</script>

<script type="application/json" id="updated-revenue-data">
    <?php echo wp_json_encode($updated_revenue_data); ?>
</script>

<style>

</style>

<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar');  ?>
    <div class="main-content">
        <main class="flex-grow-1" x-data="reportData()">
            <div class="rev-nav">
                <h1>Revenue</h1>
                <!-- <p>Welcome!</p> -->
            </div>
            <div class="rev-nav-option" x-data="">
                <div class="graph-year-filter">
                    <img src="/wp-content/uploads/2025/03/calendar.png" alt="">
                    <select class="form-select yearFilter" x-model="selectedYear" @change="filterByDate">
                        <template x-for="(year, index) in years" :key="index">
                            <option :value="year" x-text="year"></option>
                        </template>
                    </select>
                </div>
            </div>
            <div class="card revenue">
                <!-- View Toggle Dropdown -->
                <div class="toggle-container">
                    <label>Select view:</label>
                    <select class="form-select" x-model="view">
                        <option value="list">List</option>
                        <option value="graph">Graph</option>
                    </select>
                </div>
                <!-- List View -->
                <div x-show="view === 'list'" class="row roundbar-graph">
                    <!-- Confirmation Modal -->
                    <div x-show="showSaveConfirmation" class="confirmation-modal" style="display: none;">
                        <div class="modal-content">
                            <h3>Confirm Save</h3>
                            <p>Are you sure you want to save changes?</p>
                            <div class="modal-actions">
                                <button @click="submitConfirmed" class="confirm-btn">Save Changes</button>
                                <button @click="cancelEdit" class="cancel-btn">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <!-- No Data Found Modal -->
                    <div x-show="noDataFound" class="confirmation-modal" style="display: none;">
                        <div class="modal-content">
                            <h3 class="error-nodata">Error!</h3>
                            <p x-text="noDataMessage"></p>
                            <button
                                @click="noDataFound = false; cancelEdit()"
                                class="confirm-btn">
                                Close
                            </button>
                        </div>
                    </div>
                    <template x-for="monthData in reportData['list']" :key="monthData.month">
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card">
                                <h5 x-text="monthData.month"></h5>
                                <template x-for="channel in monthData.channels" :key="channel.name">
                                    <!-- <template x-if="channel.revenue && Number(channel.revenue) > 0"> -->
                                    <!-- <template x-if="channel.revenue && Number(channel.revenue) >= 0"> -->
                                    <div class="mt-2">
                                        <div class="list-nav">
                                            <h6 x-text="channel.name"></h6>
                                            <button @click="toggleChannelEdit(channel.name,monthData.month)">
                                                <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                            </button>
                                        </div>
                                        <div class="tbl-list">
                                            <table class="list-rev">
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <p>Revenue:</p>
                                                        </td>
                                                        <td class=" opt-list">
                                                            <template x-if="!(editing.channel === channel.name && editing.field === 'revenue')">
                                                                <span :class="channel.name + '-' + monthData.month + '-revenue'"
                                                                    x-text="'$' + Number(channel.revenue || 0).toLocaleString()"></span>
                                                            </template>
                                                            <input class="edit-data"
                                                                x-show="editing.channel === channel.name && visibleMonth == monthData.month && editing.field === 'revenue'"
                                                                type="number" :value="channel.revenue"
                                                                @blur.prevent="confirmSave($event, 'revenue', channel.name,monthData.month)"
                                                                @keyup.enter.prevent="confirmSave($event, 'revenue', channel.name,monthData.month)">
                                                            <!-- x-show="visibleChannel === channel.name && !(editing.channel === channel.name && editing.field === 'revenue')" -->
                                                            <div class="edits-icon"
                                                                x-show="visibleChannel == channel.name && visibleMonth == monthData.month"
                                                                @click="toggleEdit(channel.name, monthData.month,'revenue')">
                                                                <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <p>Expenses:</p>
                                                        </td>
                                                        <td>
                                                            <span x-text="'$' + ((channel.invoices || 0) + (channel.expenses || 0)).toLocaleString()"></span>
                                                        </td>
                                                    </tr>
                                                    <!-- <tr>
                                                            <td><p>Contractor Expense:</p></td>
                                                            <td class="opt-list">
                                                                <template x-if="!(editing.channel === channel.name && editing.field === 'invoices')">
                                                                    <span x-text="'$' + channel.invoices.toLocaleString()"></span>
                                                                </template>
                                                                <input class="edit-data" x-show="editing.channel === channel.name && visibleMonth == monthData.month && editing.field === 'invoices'"
                                                                type="number"
                                                                :value="channel.invoices"
                                                                @blur.prevent="confirmSave($event, 'invoices', channel.name,monthData.month)"
                                                                @keyup.enter.prevent="confirmSave($event, 'invoices', channel.name,monthData.month)">
                                                                <div class="edits-icon"
                                                                    x-show="visibleChannel == channel.name && visibleMonth == monthData.month"
                                                                    @click="toggleEdit(channel.name, monthData.month,'invoices')">
                                                                    <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                                                </div>
                                                            </td>
                                                        </tr> -->
                                                    <!-- <tr>
                                                            <td><p>Channel Expense:</p></td>
                                                            <td class="opt-list">
                                                                <template x-if="!(editing.channel === channel.name && editing.field === 'expenses')">
                                                                    <span x-text="'$' + channel.expenses.toLocaleString()"></span>
                                                                </template>
                                                                <input class="edit-data" 
                                                                    x-show="editing.channel === channel.name && visibleMonth == monthData.month && editing.field === 'expenses'"
                                                                    type="number" :value="channel.expenses"
                                                                    @blur.prevent="confirmSave($event, 'expenses', channel.name,monthData.month)"
                                                                    @keyup.enter.prevent="confirmSave($event, 'expenses', channel.name,monthData.month)">
                                                                <div class="edits-icon"
                                                                    x-show="visibleChannel == channel.name && visibleMonth == monthData.month"
                                                                    @click="toggleEdit(channel.name, monthData.month,'expenses')">
                                                                    <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                                                </div>
                                                            </td>
                                                        </tr> -->
                                                    <tr>
                                                        <td>
                                                            <p>Profit:</p>
                                                        </td>
                                                        <td>
                                                            <span x-text="'$' + (((channel.revenue || 0) - ((channel.invoices || 0) + (channel.expenses || 0))).toLocaleString())"></span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- </template> -->
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <!-- Graph View -->
                <div x-show="view === 'graph'" class="row graph-view">
                    <template x-for="channelData in reportData.graph" :key="channelData.channel">
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <h5 x-text="channelData.channel"></h5>
                                <div class="d-flex" flex="row">
                                    <div class="col-lg-9">
                                        <canvas :id="'chart-'+channelData.channel"></canvas>
                                    </div>
                                    <div class="col-lg-3">
                                        <ul>
                                            <li class="graph-revenue">
                                                <i class="icon"></i>
                                                <p>Revenue <span x-text="'$' + channelData.totalRevenue.toLocaleString()"></span></p>
                                            </li>
                                            <li class="graph-contractor">
                                                <i class="icon"></i>
                                                <p>Expenses <span x-text="'$' + channelData.allExpenses.toLocaleString()"></span></p>
                                            </li>
                                            <!-- <li class="graph-contractor">
                                                <i class="icon"></i>
                                                <p>Contractor <span x-text="'$' + channelData.totalInvoices.toLocaleString()"></span></p>
                                            </li>
                                            <li class="graph-expense">
                                                <i class="icon"></i>
                                                <p>Expenses <span x-text="'$' + channelData.totalExpenses.toLocaleString()"></span></p>
                                            </li> -->
                                            <li class="graph-profit">
                                                <p>Net Proft <span x-text="'$' + channelData.totalNetProfit.toLocaleString()"></span></p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <script>
                function reportData() {
                    return {
                        visibleChannel: null,
                        visibleMonth: null,
                        view: 'list',

                        invoices: [],
                        expenses: [],
                        revenue: [],
                        updatedRevenue: [],

                        years: [],
                        selectedYear: '',

                        init() {
                            try {
                                this.invoices = JSON.parse(document.getElementById("invoices-data").textContent);

                                // this.expenses = JSON.parse(document.getElementById("expenses-data").textContent);
                                this.revenue = JSON.parse(document.getElementById("revenue-data").textContent);
                                this.updatedRevenue = JSON.parse(document.getElementById("updated-revenue-data").textContent);
                            } catch (e) {
                                console.error("Failed to parse reportData JSON:", e);
                                this.invoices = [];
                                this.revenue = [];
                                this.updatedRevenue = [];
                            }

                            this.filterByDate();
                            this.selectedYear = Math.max(...this.years);
                        },
                        parseDateFlexible(str) {
                            if (str.includes('-')) {
                                // already yyyy-mm-dd
                                const [y, m, d] = str.split('-');
                                return {
                                    year: y,
                                    month: m.padStart(2, '0'),
                                    day: d
                                };
                            }

                            const parts = str.split('/');
                            if (parts.length !== 3) return null;

                            let [p1, p2, p3] = parts; // could be m/d/y or d/m/y

                            // detect which is day
                            if (parseInt(p1, 10) > 12) {
                                // first part > 12 → must be DD/MM/YYYY
                                return `${p1}/${p2}/${p3}`;
                            } else {
                                // otherwise treat as MM/DD/YYYY → swap
                                return `${p2}/${p1}/${p3}`;
                            }
                        },
                        get reportData() {
                            const monthOrder = [
                                "January", "February", "March", "April", "May", "June",
                                "July", "August", "September", "October", "November", "December"
                            ];

                            const formatMonth = (year, month) =>
                                new Date(`${year}-${month}-01`).toLocaleString("en-US", {
                                    month: "long"
                                });

                            const reports = {};
                            const reportsGraph = {};
                            const yearsSet = new Set();

                            // Merge revenue + expenses into one stream
                            // const mergedData = [...this.revenue, ...this.expenses, ...this.invoices];
                            const mergedData = [...this.updatedRevenue, ...this.invoices];

                            // mergedData.forEach(({ date, revenue = 0, expenses = 0, channel }) => {

                            mergedData.forEach((data, index) => {
                                const date = this.parseDateFlexible(data.date) || 0;
                                const revenue = data.revenue || 0;
                                const expenses = data.expenses || data.invoices || 0;
                                const channel = data.channel || 0;
                                if (!date || date == null || typeof date !== "string" || !date.includes("/")) return;

                                const [day, month, year] = date.split("/");
                                yearsSet.add(year);
                                if (this.selectedYear && year !== String(this.selectedYear)) return;

                                const monthName = formatMonth(year, month);

                                // Initialize containers
                                reports[monthName] ??= {};
                                reports[monthName][channel] ??= {
                                    revenue: 0,
                                    expenses: 0,
                                    invoices: 0
                                };

                                reportsGraph[channel] ??= {};
                                reportsGraph[channel][monthName] ??= {
                                    revenue: 0,
                                    expenses: 0,
                                    invoices: 0
                                };


                                // Aggregate
                                reports[monthName][channel].revenue += parseInt(revenue) || 0;
                                reports[monthName][channel].expenses += parseInt(expenses) || 0;

                                reportsGraph[channel][monthName].revenue += parseInt(revenue) || 0;
                                reportsGraph[channel][monthName].expenses += parseInt(expenses) || 0;
                            });

                            // Merge invoices (paid)
                            // Object.entries(this.invoiceTotals()).forEach(([year, months]) => {
                            //     if (this.selectedYear && year !== String(this.selectedYear)) return;

                            //     Object.entries(months).forEach(([month, channels]) => {
                            //         const monthName = formatMonth(year, month);

                            //         Object.entries(channels).forEach(([channel, amount]) => {
                            //             reports[monthName] ??= {};
                            //             reports[monthName][channel] ??= { revenue: 0, expenses: 0, invoices: 0 };

                            //             reportsGraph[channel] ??= {};
                            //             reportsGraph[channel][monthName] ??= { revenue: 0, expenses: 0, invoices: 0 };

                            //             reports[monthName][channel].invoices = amount;
                            //             reportsGraph[channel][monthName].invoices = amount;
                            //         });
                            //     });
                            // });

                            this.years = [...yearsSet].sort((a, b) => b - a);
                            // console.log('report', reportsGraph);

                            // Build final return objects
                            return {
                                list: Object.entries(reports)
                                    .sort(([a], [b]) => monthOrder.indexOf(a) - monthOrder.indexOf(b))
                                    .map(([month, channels]) => ({
                                        month,
                                        channels: Object.entries(channels).map(([name, {
                                            revenue,
                                            expenses,
                                            invoices
                                        }]) => ({
                                            name,
                                            revenue,
                                            expenses,
                                            invoices
                                        }))
                                    })),
                                graph: Object.entries(reportsGraph).map(([channel, months]) => {
                                    let totalRevenue = 0,
                                        totalInvoices = 0,
                                        totalExpenses = 0;

                                    const sortedMonths = Object.entries(months)
                                        .sort(([a], [b]) => monthOrder.indexOf(a) - monthOrder.indexOf(b))
                                        .map(([month, {
                                            revenue = 0,
                                            invoices = 0,
                                            expenses = 0
                                        }]) => {
                                            totalRevenue += revenue;
                                            totalInvoices += invoices;
                                            totalExpenses += expenses;
                                            return {
                                                name: month,
                                                revenue,
                                                invoices,
                                                expenses
                                            };
                                        });

                                    return {
                                        channel,
                                        months: sortedMonths,
                                        totalRevenue,
                                        totalInvoices,
                                        totalExpenses,
                                        allExpenses: (totalInvoices + totalExpenses),
                                        totalNetProfit: totalRevenue - (totalInvoices + totalExpenses)
                                    };
                                })
                            };
                        },

                        editing: {
                            channel: null,
                            field: null
                        },

                        invoiceTotals() {
                            const totals = {};
                            // this.invoices.forEach(inv => {
                            //     if (!inv.date || !inv.channel || !inv.invoices) {
                            //         console.log('Skipping invoice - missing data:', inv);
                            //         return;
                            //     }

                            //     const [day, month, year] = inv.date.split('/');
                            this.invoices.forEach(inv => {
                                if (!inv.date || !inv.channel || !inv.invoices) {
                                    // console.log('Skipping invoice - missing data:', inv);
                                    return;
                                }
                                if (typeof inv.date !== "string" || !inv.date.includes("/")) {
                                    // console.warn("Skipping invoice with invalid date:", inv);
                                    return;
                                }
                                const [day, month, year] = inv.date.split('/');

                                const channel = inv.channel;
                                const amount = parseFloat(inv.invoices) || 0;

                                if (!totals[year]) totals[year] = {};
                                if (!totals[year][month]) totals[year][month] = {};
                                if (!totals[year][month][channel]) totals[year][month][channel] = 0;

                                totals[year][month][channel] += amount;
                            });
                            return totals;
                        },
                        showSaveConfirmation: false,
                        pendingField: null,
                        pendingValue: null,
                        pendingChannel: null,
                        noDataFound: false,
                        noDataMessage: '',
                        toggleEdit(channel, month, field) {
                            this.editing = {
                                channel: channel,
                                month: month,
                                year: this.selectedYear,
                                field: field
                            };
                        },
                        toggleChannelEdit(channel, month) {
                            if (this.visibleChannel === channel && this.visibleMonth === month) {
                                this.visibleChannel = null;
                                this.visibleMonth = null;
                            } else {
                                this.visibleChannel = channel;
                                this.visibleMonth = month;
                            }
                        },
                        confirmSave(event, field, channel, month) {
                            this.pendingValue = parseFloat(event.target.value);
                            this.pendingField = field;
                            this.pendingChannel = channel;
                            this.pendingMonth = month;
                            this.showSaveConfirmation = true;
                        },
                        submitConfirmed() {
                            this.updateData(this.editing);
                        },
                        updateData(filter) {
                            const monthMap = {
                                January: "01",
                                February: "02",
                                March: "03",
                                April: "04",
                                May: "05",
                                June: "06",
                                July: "07",
                                August: "08",
                                September: "09",
                                October: "10",
                                November: "11",
                                December: "12"
                            };

                            let filteredData = [];
                            let endpoint = '';
                            let payload = {
                                id: null,
                                channel: filter.channel,
                                month: filter.month,
                                year: filter.year
                            };

                            switch (filter.field) {
                                case 'revenue':
                                    // filteredData = this.revenue.filter(item => {
                                    filteredData = this.updatedRevenue.filter(item => {
                                        // const [day, month, year] = item.date.split('/');
                                        if (!item.date || typeof item.date !== "string" || !item.date.includes("/")) return false;
                                        const [day, month, year] = item.date.split('/');

                                        return (
                                            item.channel == filter.channel &&
                                            month == monthMap[filter.month] &&
                                            year == String(filter.year)
                                        );
                                    });
                                    endpoint = '/wp-json/clickable/v1/update-revenue';
                                    payload.revenue = this.pendingValue;
                                    break;

                                case 'expenses':
                                    // filteredData = this.expenses.filter(item => {
                                    filteredData = this.updatedRevenue.filter(item => {
                                        // const [day, month, year] = item.date.split('/');
                                        if (!item.date || typeof item.date !== "string" || !item.date.includes("/")) return false;
                                        const [day, month, year] = item.date.split('/');

                                        return (
                                            item.channel == filter.channel &&
                                            month == monthMap[filter.month] &&
                                            year == String(filter.year)
                                        );
                                    });
                                    endpoint = '/wp-json/clickable/v1/update-expense';
                                    payload.expenses = this.pendingValue;
                                    break;

                                case 'invoices':
                                    filteredData = this.invoices.filter(inv => {
                                        if (!inv.date || !inv.channel) return false;
                                        // const [day, month, year] = inv.date.split('/');
                                        if (!item.date || typeof item.date !== "string" || !item.date.includes("/")) return false;
                                        const [day, month, year] = item.date.split('/');

                                        return (
                                            inv.channel == filter.channel &&
                                            month == monthMap[filter.month] &&
                                            year == String(filter.year)
                                        );
                                    });
                                    endpoint = '/wp-json/clickable/v1/update-invoice';
                                    payload.invoice = this.pendingValue;
                                    break;
                            }

                            if (filteredData.length === 0) {
                                this.noDataFound = true;
                                this.noDataMessage = `No ${filter.field} data found for ${filter.channel} in ${filter.month} ${filter.year}`;
                                return;
                            }

                            payload.id = filteredData[0].id;

                            fetch(endpoint, {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-WP-Nonce": wpApiSettings.nonce
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify(payload)
                                })
                                .then(response => response.json())
                                .then(data => {
                                    // Update the local data
                                    if (filter.field === 'expenses') {
                                        const index = this.expenses.findIndex(item => item.id == data.id);
                                        if (index !== -1) this.expenses[index].expenses = parseFloat(data.expenses);
                                    } else if (filter.field === 'revenue') {
                                        const index = this.revenue.findIndex(item => item.id == data.id);
                                        if (index !== -1) this.revenue[index].revenue = parseFloat(data.revenue);
                                    } else if (filter.field === 'invoices') {
                                        const index = this.invoices.findIndex(item => item.id == data.id);
                                        if (index !== -1) {
                                            this.invoices[index].invoices = parseFloat(data.invoice);
                                        }
                                    }
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Changes saved!',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    this.filterByDate();
                                })
                                .catch(error => {
                                    console.error("Error updating " + filter.field + ":", error);
                                });

                            this.resetEditState();
                        },
                        cancelEdit() {
                            this.resetEditState();
                        },
                        resetEditState() {
                            this.showSaveConfirmation = false;
                            this.editing = {
                                channel: null,
                                field: null
                            };
                            this.pendingChannel = null;
                            this.pendingField = null;
                            this.pendingValue = null;
                        },
                        filterByDate() {
                            this.reportData
                            // console.log('report data', this.reportData);

                            this.renderChart();
                        },
                        renderChart() {
                            Alpine.nextTick(() => {
                                // console.log(this.reportData.graph);
                                this.reportData["graph"].forEach(report => {
                                    let canvasId = 'chart-' + report.channel;
                                    let canvas = document.getElementById(canvasId);
                                    if (!canvas) {
                                        console.error("Canvas not found:", canvasId);
                                        return;
                                    }
                                    let ctx = canvas.getContext('2d');
                                    if (Chart.getChart(canvasId)) {
                                        Chart.getChart(canvasId).destroy();
                                    }
                                    new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: Object.values(report.months).map(ch => ch.name || 0),
                                            datasets: [{
                                                    label: 'Revenue',
                                                    data: Object.values(report.months).map(ch => ch.revenue || 0),
                                                    backgroundColor: '#2194FF',
                                                    borderRadius: 10
                                                },
                                                // {
                                                //     label: 'Contractor Expenses',
                                                //     data: Object.values(report.months).map(ch => ch.invoices || 0),
                                                //     backgroundColor: '#00ACBE',
                                                //     borderRadius: 10
                                                // },
                                                {
                                                    label: 'Expenses',
                                                    data: Object.values(report.months).map(ch => ch.expenses || 0),
                                                    backgroundColor: '#A3D2FF',
                                                    borderRadius: 10
                                                }
                                            ]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            onHover: (event, chartElement) => {
                                                event.native.target.style.cursor = chartElement.length ? 'pointer' : 'default';
                                            },
                                            scales: {
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
                                                    enabled: true,
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
                                });
                            });
                        },
                        formatCurrency(value) {
                            return "$" + new Intl.NumberFormat().format(value);
                        }
                    };
                }
            </script>
        </main>
    </div>
</div>


<style>
    table tbody tr:hover>td {
        background-color: transparent;
    }

    .rev-nav-option .revenue-date-picker {
        width: 25%;
    }

    .rev-nav-option {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 24px;
    }

    .rev-nav {
        margin-bottom: 0;
    }

    .rev-nav h1 {
        color: #101010;
        font-weight: 600;
        font-size: 30px;
        letter-spacing: -0.03em;
    }

    .rev-nav p {
        color: #101010;
        font-weight: 300;
        font-size: 16px;
    }

    .toggle-container {
        display: flex;
        /* justify-content: space-between; */
        align-items: center;
        padding: 15px;
    }

    canvas {
        width: 100% !important;
        min-height: 300px !important;
    }

    .revenue .roundbar-graph ul {
        list-style: none;
        border-left: 1px solid #2194FF;
        padding-left: 0;
        margin-left: 2rem;
    }

    .revenue .roundbar-graph ul li {
        padding-left: 20px;
        display: flex;
        justify-content: space-between;
        flex-direction: row;
    }

    ul {
        list-style: none;
    }

    li.graph-revenue,
    li.graph-expense,
    li.graph-contractor {
        display: flex;
        flex-direction: row;
    }

    li.graph-revenue p,
    li.graph-expense p,
    li.graph-contractor p,
    li.graph-profit p {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
    }

    li.graph-profit p {
        font-weight: 600;
        color: #101010;
    }

    li.graph-profit span {
        font-weight: 700;
        font-size: 18px;
    }

    .graph-view ul li:last-child {
        border-top: 1px solid #ddd;
        padding-top: 15px;
    }

    .icon {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 2px;
        margin-right: 5px;
        margin-top: 5px;
    }

    .graph-revenue .icon {
        background-color: #2194FF;
        /* Blue */
    }

    .graph-expense .icon {
        background-color: #A3D2FF;
        /* Light Blue */
    }

    .graph-contractor .icon {
        background-color: #00ACBE;
        /* Light Blue */
    }

    .revenue {
        padding: 40px;
        border-radius: 10px;
        border: none;
    }

    .toggle-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-direction: row;
        padding: 10px;
        margin-bottom: 20px;
        border: solid 1px #D0D0D0;
        width: 220px;
        border-radius: 4px;
    }

    .toggle-container label {
        color: #101010;
        font-weight: 600;
        font-size: 14px;
    }

    .toggle-container .form-select {
        width: 100px;
        border: none;
        color: #717171;
        font-weight: 300;
        font-size: 14px;
        padding: 0;
    }

    .toggle-container .form-select:focus {
        border-color: transparent;
    }

    .row .card {
        border: solid 1px #E7E7E7;
        border-radius: 10px;
        box-shadow: unset !important;
        padding: 40px;
    }

    .card h5 {
        color: #101010;
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 40px;
    }

    .card h6 {
        color: #000000;
        font-weight: 500;
        font-size: 18px;
        margin: 0;
    }

    .card p {
        color: #717171;
        font-weight: 300;
        font-size: 12px;
        margin-bottom: 12px;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .card span {
        color: #101010;
        font-weight: 700;
        font-size: 14px;
        /* margin-bottom: 12px; */
    }

    .graph-year-filter {
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 10px;
        background-color: #fff;
        border-radius: 4px;
        width: 100px;
    }

    .graph-year-filter select.form-select.yearFilter {
        width: 100%;
        border: unset;
        background-color: transparent;
        padding: 0 0 0 10px;
        --bs-form-select-bg-img: none;
    }

    .form-select.yearFilter:focus {
        box-shadow: none;
        border-color: transparent;
    }

    .confirmation-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .confirm-btn {
        background-color: #2194FF;
        color: white;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button.confirm-btn:hover {
        color: #fff;
        background-color: #142B41;
        text-decoration: none;
    }

    .cancel-btn {
        background-color: transparent;
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #101010;
    }

    button.cancel-btn:hover {
        color: #101010;
        background-color: #2194FF80;
        text-decoration: none;
    }

    .list-nav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .list-nav button,
    button:focus:not(:focus-visible) {
        border: solid 1px #0165C2;
        border-radius: 10px;
        padding: 10px;
        display: none;
    }

    .list-nav button:hover {
        background-color: transparent;
    }

    .edit-data {
        width: 80px !important;
        height: 20px;
    }

    .tbl-list {
        padding-left: 24px;
        margin: 0 0 12px 0;
        border-left: solid 1px #2194FF;
    }

    table.list-rev tr {
        border-bottom: unset !important;
    }

    table.list-rev td {
        padding: 0;
    }

    td.opt-list {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }

    .modal-content h3.error-nodata {
        color: red;
    }

    .modal-content p {
        font-size: 16px;
    }

    .flex-grow-1 {
        padding: 0 20px;
    }

    /*------------------- Media Queries -------------*/
    @media only screen and (max-width: 1024px) {
        .search-container {
            width: 50%;
        }

        .row {
            display: flex;
            flex-direction: row;
        }

        .col-lg-4 {
            width: 50%;
        }

        .card .col-lg-9 {
            width: 100%;
        }

        .card .col-lg-3 {
            width: 100%;
        }

        .card .d-flex {
            flex-direction: column;
        }
    }

    @media only screen and (max-width: 890px) {
        .flex-grow-1 {
            padding: 0;
        }

        .revenue {
            padding: 30px;
        }

        .row {
            display: flex;
            flex-direction: column;
        }

        .col-lg-4 {
            width: 100%;
        }

        .col-lg-9 {
            margin-bottom: 24px;
        }

        .row .card {
            padding: 20px;
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
            margin-left: 0px !important;
            width: 520px;
        }
    }

    @media only screen and (max-width: 425px) {
        .main-content {
            overflow: hidden;
            margin-left: 100px !important;
        }

        .rev-nav-option {
            flex-direction: column !important;
            align-items: flex-end !important;
        }

        .search-container {
            width: 100%;
            margin-bottom: 24px;
        }

        .rev-nav-option .revenue-date-picker {
            width: 65% !important;
        }

        .revenue {
            padding: 20px !important;
        }

        .revenue .toggle-container {
            display: flex;
            flex-direction: row;
            width: 100%;
            align-items: center;
        }

        .row .card {
            padding: 20px !important;
        }

        canvas {
            height: 100% !important;
            width: 100% !important;
        }
    }
</style>

<?php get_footer('admin'); ?>