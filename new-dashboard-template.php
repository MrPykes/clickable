<?php

/**
 * Template Name: New Dashboard Template
 */

get_header('admin');

$userId = $_SESSION['userID'] ?? null;
$fullName = $_SESSION['fullName'] ?? null;
$userEmail = $_SESSION['userEmail'] ?? null;

if (empty($fullName) && is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $fullName = trim($current_user->first_name . ' ' . $current_user->last_name);

    // Optional fallback if first/last names are empty
    if (empty($fullName)) {
        $fullName = $current_user->display_name;
    }
}

$parts = explode(' ', trim($fullName));

if (count($parts) > 1) {
    array_pop($parts);
}

$firstName = implode(' ', $parts);

// Return filtered/sanitized data
$args = array(
    'post_type'      => 'channel-insight',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
);
$revenue_query = new WP_Query($args);
$data = [];

if ($revenue_query->have_posts()) {
    while ($revenue_query->have_posts()) {
        $revenue_query->the_post();
        $data[] = [
            'id'            => get_the_ID(),
            'date'   => get_field('date_create'),
            'channel'   => get_field('channel_name'),
            'revenue' => get_field('revenue_amount'),
            'views' => get_field('channel_views'),
            'subscribers' => get_field('channel_subscribers'),
            'expenses' => get_field('channel_expense_amount'),
        ];
    }
    wp_reset_postdata();
}

// Query for roles count
$args = [
    'post_type'      => 'team',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'contractor_status',
            'value'   => 'Active',
            'compare' => '='
        ]
    ]
];

$revenue_query = new WP_Query($args);
$roles = [];
$teams = [];

if ($revenue_query->have_posts()) {
    while ($revenue_query->have_posts()) {
        $revenue_query->the_post();

        // Roles count
        // ----------------
        $role = get_field('role');
        if ($role && is_array($role) && !empty($role)) {
            $role_name = get_the_title($role[0]);
            if (!isset($roles[$role_name])) {
                $roles[$role_name] = 0;
            }
            $roles[$role_name]++;
        }

        // Teams data
        // ----------------
        $fullname = get_field('fullname');
        if (!$fullname) {
            continue;
        }

        $image         = get_field('profile_photo');
        $image_url     = is_array($image) && isset($image['sizes']['medium'])
            ? $image['sizes']['medium']
            : '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg';

        $your_channels = get_field('your_channel');
        $channel_names = [];
        $channel_logo  = '';
        if ($your_channels) {
            foreach ($your_channels as $channel_post_id) {
                $channel_names[] = get_field('channel_name', $channel_post_id);
                if (!$channel_logo) {
                    $channel_logo = get_field('channel_profile_photo', $channel_post_id);
                }
            }
        }

        $teams[] = [
            'id'            => get_the_ID(),
            'name'          => $fullname,
            'role'          => $role ? get_the_title($role[0]) : '',
            'status'        => get_field('contractor_status'),
            'profile_photo' => $image_url,
            'channels'      => array_map(function ($ch_id) {
                return [
                    'name' => get_field('channel_name', $ch_id),
                    'logo' => (get_field('channel_profile_photo', $ch_id)['sizes']['thumbnail']
                        ?? '/wp-content/uploads/2025/07/channelbtnfallback.svg'),
                ];
            }, $your_channels ?: []),
            'link'          => get_permalink(),
        ];
    }
    wp_reset_postdata();
}

ksort($roles);

// $channel = new WP_Query([
//     'post_type' => 'channel',
//     'post_status' => 'publish',
//     'posts_per_page' => -1,
// ]);

// $channel_data = [];
// if ($channel->have_posts()) {
//     while ($channel->have_posts()) {
//         $channel->the_post();
//         $channel_data[] = [
//             'id'   => get_the_ID(),
//             'name' => get_the_title(),
//         ];
//     }
// }
// wp_reset_postdata();

$channel = new WP_Query([
    'post_type'      => 'channel',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_query'     => [
        [
            'key'     => 'channel_status',
            'value'   => 'active',
            'compare' => '='
        ]
    ]
]);

$channel_data = [];
if ($channel->have_posts()) {
    while ($channel->have_posts()) {
        $channel->the_post();

        $image     = get_field('channel_profile_photo');
        $image_url = (is_array($image) && isset($image['sizes']['thumbnail']))
            ? $image['sizes']['thumbnail']
            : '/wp-content/uploads/2025/07/channelbtnfallback.svg';

        $channel_data[] = [
            'id'    => get_the_ID(),
            'name'  => get_the_title(),
            'photo' => $image_url,
        ];
    }
}
wp_reset_postdata();


$invoice = new WP_Query([
    'post_type' => 'invoice-creator',
    'post_status' => 'publish',
    'posts_per_page' => -1,
]);

$invoice_data = [];
if ($invoice->have_posts()) {
    while ($invoice->have_posts()) {
        $invoice->the_post();
        $invoice_data[] = [
            'id'   => get_the_ID(),
            'amount' => get_field('amount'),
            'date' => get_field('date_submitted'),
        ];
    }
}
wp_reset_postdata();

?>

<script type="application/json" id="insight-data">
    <?php echo json_encode($data) ?>
</script>
<script type="application/json" id="team-data">
    <?php echo json_encode($teams) ?>
</script>
<script type="application/json" id="roles-data">
    <?php echo json_encode($roles); ?>
</script>
<script type="application/json" id="channel-data">
    <?php echo json_encode($channel_data); ?>
</script>
<script type="application/json" id="invoice-data">
    <?php echo json_encode($invoice_data); ?>
</script>


<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar'); ?>
    <div class="main-content">
        <div x-data="statsCard">
            <main class="flex-grow-1">
                <div class="header-db">
                    <div class="titleNav col-md-6">
                        <h1 style="font-size: 30px; font-weight:600; color: #101010;">Your Dashboard</h1>
                        <p>Welcome, <?php echo $firstName; ?>!</p>
                    </div>

                    <!-- Dropdown -->
                    <div class="channel-dropdown relative w-64" x-data>
                        <div
                            @click="open = !open"
                            class="border rounded px-3 py-2 cursor-pointer bg-white"
                            style="width:200px">
                            <span x-show="selectedChannels.length === 0">Select Channels</span>
                            <span x-show="selectedChannels.length > 0" x-text="`${selectedChannels.length} selected`"></span>
                        </div>

                        <!-- Dropdown list -->
                        <div
                            x-show="open"
                            @click.away="open = false"
                            class="absolute left-0 top-full mt-1 min-w-[16rem] w-max bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto z-[9999]"
                            style="width:200px">

                            <template x-for="option in channelOptions" :key="option.id">
                                <div
                                    class="flex items-center px-3 py-2 hover:bg-gray-100 cursor-pointer"
                                    @click.stop="toggleChannel(option.name)">
                                    <input type="checkbox" class="mr-2 hidden" :checked="isSelected(option.name)">
                                    <span x-text="option.name"></span>
                                </div>
                            </template>
                            <div class="px-3 py-2 border-t text-sm text-gray-500 cursor-pointer hover:bg-gray-100"
                                @click.stop="clearChannels">
                                Clear selection
                            </div>
                        </div>
                    </div>

                </div>
                <div>
                    <div class="filterNav">
                        <!-- Channel display -->
                        <div class="channelDisplay">
                            <template x-for="channel in selectedChannels" :key="channel">
                                <button class="channeldDisplayBtn" disabled>
                                    <!-- <img :src="photo" alt="" class="w-4 h-4"> -->
                                    <span x-text="channel"></span>
                                </button>
                            </template>
                        </div>

                        <div class="statDaysFilter">
                            <select x-model="selectedDaterange" x-on:change="handleDateRangeChange">
                                <option value="today">Today</option>
                                <option value="7">Last 7 days</option>
                                <option value="14">Last 14 days</option>
                                <option value="28">Last 28 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="180">Last 6 months</option>
                                <option value="365">Last 365 days</option>
                                <option value="ytd">Year to Date (YTD)</option>
                                <option value="all">All Time</option>
                                <option value="custom">Custom</option> <!-- Added Custom option -->
                            </select>
                        </div>

                        <!-- Custom Date Range Modal -->
                        <div
                            x-show="showCustomModal"
                            x-transition
                            class="custom-date-filter fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                            <div class="bg-white p-6 rounded shadow-lg w-80">
                                <h2 class="text-lg font-bold mb-4">Select Custom Date Range</h2>
                                <div class="flex flex-col gap-2">
                                    <label>
                                        Start Date:
                                        <input type="date" x-model="customStart" class="border px-2 py-1 rounded w-full" />
                                    </label>
                                    <label>
                                        End Date:
                                        <input type="date" x-model="customEnd" class="border px-2 py-1 rounded w-full" />
                                    </label>
                                </div>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button @click="applyCustomRange()" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Apply</button>
                                    <button @click="closeModal()" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="analyticsStat">
                        <div class="analyticsNav">
                            <div>
                                <button class="financeBtn"><a href="/revenue">Finances</a></button>
                            </div>
                            <div class="switchNav">
                                <label class="switchLabel1">Individual Channels</label>
                                <label class="switch">
                                    <input type="checkbox" x-model="isCombined">
                                    <span class="slider round"></span>
                                </label>
                                <label class="switchLabel2">Combined Analytics</label>
                            </div>
                        </div>
                        <div class="statGraph">
                            <!-- Showing Combined Analytics -->
                            <div class="analtyicsStatFilter row">
                                <!-- Views -->
                                <div class="analyticsStatCard col-3" @click="activeTab = 'tabViews'" :class="{ 'activeCard': activeTab === 'tabViews' }">
                                    <div class="statLabel">
                                        <div class="iconLabel"><img src="/wp-content/uploads/2025/09/estViews.svg" alt=""></div>
                                        <h4>Views</h4>
                                    </div>
                                    <div class="statInform">
                                        <div class="statIndicate">
                                            <h3 x-text="totalViews.toLocaleString()">300k</h3>
                                            <div class="icon"><img :src="arrowIcon(percentViews)" src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                        </div>
                                        <p x-text="viewsChangeMsg">About the same as usual</p>
                                    </div>
                                </div>
                                <!-- Estimated Revenue -->
                                <div class="analyticsStatCard col-3"
                                    @click="activeTab = 'tabRevenue'" :class="{ 'activeCard': activeTab === 'tabRevenue' }">
                                    <div class="statLabel">
                                        <div class="iconLabel"><img src="/wp-content/uploads/2025/09/estRevenue.svg" alt=""></div>
                                        <h4>Estimated Revenue</h4>
                                    </div>
                                    <div class="statInform">
                                        <div class="statIndicate">
                                            <h3 x-text="'$' + totalRevenue.toLocaleString()">$50,739.19</h3>
                                            <div class="icon"><img :src="arrowIcon(percentRevenue)" src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                        </div>
                                        <p x-text="revenueChangeMsg">About the same as usual</p>

                                    </div>
                                </div>
                                <!-- Expenses -->
                                <div class="analyticsStatCard col-3" @click="activeTab = 'tabExpenses'" :class="{ 'activeCard': activeTab === 'tabExpenses' }">
                                    <div class="statLabel">
                                        <div class="iconLabel"><img src="/wp-content/uploads/2025/09/estExpenses.svg" alt=""></div>
                                        <h4>Expenses</h4>
                                    </div>
                                    <div class="statInform">
                                        <div class="statIndicate">
                                            <h3 x-text="'$' + totalExpenses.toLocaleString()">$21,938.58</h3>
                                            <div class="icon"><img :src="arrowIcon(percentExpenses)" src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                        </div>
                                        <p x-text="expensesChangeMsg">About the same as usual</p>

                                    </div>
                                </div>
                                <!-- Gross Profit -->
                                <div class="analyticsStatCard col-3" @click="activeTab = 'tabGross'" :class="{ 'activeCard': activeTab === 'tabGross' }">
                                    <div class="statLabel">
                                        <div class="iconLabel"><img src="/wp-content/uploads/2025/09/estGross.svg" alt=""></div>
                                        <h4>Gross Profit</h4>
                                    </div>
                                    <div class="statInform">
                                        <div class="statIndicate">
                                            <h3 x-text="'$' + totalGross.toLocaleString()">$28,800.61</h3>
                                            <div class="icon"><img :src="arrowIcon(percentGross)" src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                        </div>
                                        <p x-text="grossChangeMsg">About the same as usual</p>

                                    </div>
                                </div>
                            </div>
                            <div class="statGraph">
                                <div class="col-12">
                                    <canvas id="analyticsChartViews"></canvas>
                                </div>
                                <!-- <div class="col-12">
                                    <canvas id="myChart"></canvas>
                                </div>
                                <script>
                                    const ctx = document.getElementById("myChart").getContext("2d");

                                    // 20 labels (Jan → Mar 2025)
                                    const labels = [
                                        "Jan 20, 2025", "Jan 23, 2025", "Jan 26, 2025", "Jan 29, 2025",
                                        "Feb 1, 2025", "Feb 4, 2025", "Feb 7, 2025", "Feb 10, 2025",
                                        "Feb 13, 2025", "Feb 16, 2025", "Feb 19, 2025", "Feb 22, 2025",
                                        "Feb 25, 2025", "Feb 28, 2025", "Mar 2, 2025", "Mar 5, 2025",
                                        "Mar 8, 2025", "Mar 11, 2025", "Mar 14, 2025", "Mar 17, 2025"
                                    ];

                                    // 6 datasets
                                    const datasets = [{
                                            label: "Channel #1",
                                            data: [20000, 25000, 30000, 40000, 35000, 42000, 50000, 48000, 46000, 52000, 60000, 58000, 62000, 65000, 70000, 68000, 66000, 71000, 72000, 74000]
                                        },
                                        {
                                            label: "Channel #2",
                                            data: [15000, 18000, 20000, 22000, 21000, 25000, 27000, 26000, 24000, 28000, 30000, 32000, 31000, 33000, 35000, 37000, 36000, 38000, 40000, 42000]
                                        },
                                        {
                                            label: "Channel #3",
                                            data: [10000, 12000, 14000, 16000, 15000, 18000, 19000, 21000, 23000, 22000, 24000, 26000, 25000, 27000, 28000, 30000, 32000, 31000, 33000, 34000]
                                        },
                                        {
                                            label: "Channel #4",
                                            data: [5000, 8000, 7000, 9000, 8500, 10000, 12000, 11000, 13000, 12500, 14000, 15000, 14500, 16000, 17000, 16500, 18000, 18500, 19000, 20000]
                                        },
                                        {
                                            label: "Channel #5",
                                            data: [12000, 14000, 16000, 15000, 17000, 19000, 21000, 20000, 22000, 23000, 25000, 24000, 26000, 27000, 28000, 29000, 30000, 31000, 32000, 33000]
                                        },
                                        {
                                            label: "Channel #6",
                                            data: [8000, 10000, 12000, 11000, 13000, 15000, 16000, 17000, 19000, 18000, 20000, 21000, 22000, 23000, 25000, 24000, 26000, 27000, 28000, 29000]
                                        }
                                    ];

                                    // Shared soft blue gradient
                                    function createBlueGradient(ctx) {
                                        const gradient = ctx.createLinearGradient(0, 0, 0, 450);
                                        gradient.addColorStop(0, "rgba(33, 150, 243, 0.4)");
                                        gradient.addColorStop(1, "rgba(33, 150, 243, 0.05)");
                                        return gradient;
                                    }

                                    const blueGradient = createBlueGradient(ctx);

                                    const chartData = {
                                        labels,
                                        datasets: datasets.map(ds => ({
                                            label: ds.label,
                                            data: ds.data,
                                            fill: true,
                                            backgroundColor: blueGradient,
                                            borderColor: "rgba(33, 150, 243, 1)",
                                            tension: 0.4,
                                            borderWidth: 1.5,
                                            pointRadius: 0
                                        }))
                                    };

                                    new Chart(ctx, {
                                        type: "line",
                                        data: chartData,
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            interaction: {
                                                mode: "index",
                                                intersect: false
                                            },
                                            stacked: true,
                                            plugins: {
                                                legend: {
                                                    display: true,
                                                    labels: {
                                                        usePointStyle: true,
                                                        pointStyle: "circle",
                                                        font: {
                                                            size: 11
                                                        }
                                                    }
                                                },
                                                tooltip: {
                                                    mode: "index",
                                                    intersect: false,
                                                    callbacks: {
                                                        label: function(context) {
                                                            // Format values like 50.0k
                                                            let value = context.raw;
                                                            return `${context.dataset.label}: ${(value / 1000).toFixed(1)}k`;
                                                        }
                                                    }
                                                }
                                            },
                                            scales: {
                                                x: {
                                                    ticks: {
                                                        autoSkip: true,
                                                        maxRotation: 45,
                                                        minRotation: 0
                                                    },
                                                    grid: {
                                                        drawOnChartArea: false,
                                                        drawBorder: false,
                                                        color: "rgba(0,0,0,0.05)",
                                                        lineWidth: 1
                                                    }
                                                },
                                                y: {
                                                    ticks: {
                                                        callback: value => (value / 1000).toFixed(1) + "k"
                                                    },
                                                    grid: {
                                                        drawBorder: false,
                                                        color: "rgba(0,0,0,0.05)",
                                                        lineWidth: 1
                                                    }
                                                }
                                            }
                                        }
                                    });
                                </script> -->
                            </div>
                        </div>
                    </div>
                </div>


                <div class="statDetailed">
                    <div class="allTeam col-md-8">
                        <div class="allTeamNav row">
                            <div class="navOpt col-md-6">
                                <h4>Team Members</h4>
                                <p x-text="`${activeTeams.length} Total Team Members`"></p>
                            </div>
                            <div class="navOpt col-md-6 d-flex flex-column align-items-end">
                                <button class="viewBtn">
                                    <a href="/team/">View All</a>
                                </button>
                            </div>
                        </div>
                        <div class="teamTable">
                            <table>
                                <thead>
                                    <th>
                                        Member
                                        <img src="/wp-content/uploads/2025/08/teamsort.svg" alt="Sort Icon">
                                    </th>
                                    <th>
                                        Channel
                                    </th>
                                </thead>
                                <tbody>
                                    <template x-for="team in sortedTeams" :key="team.id">
                                        <tr>
                                            <td>
                                                <div class="perTeam">
                                                    <div
                                                        class="team-prof"
                                                        :style="`background-image: url('${team.profile_photo}'); 
                                                            background-size: cover; background-position: center; 
                                                            background-repeat: no-repeat; width: 36px;
                                                            height: 36px; border-radius: 40px;`">
                                                    </div>
                                                    <div>
                                                        <h6 x-text="team.name"></h6>
                                                        <p x-text="team.role"></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="perTeamChannel">
                                                    <template x-for="(channel, index) in team.channels.slice(0, 3)" :key="index">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <h6 class="tag" x-text="channel.name"></h6>
                                                        </div>
                                                    </template>
                                                    <template x-if="team.channels.length > 3">
                                                        <span class="text-xs text-gray-500">+ more</span>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="recordDetailed col-md-4 d-flex flex-column gap-2" style="padding-right: 15px;">
                        <div class="perChannelMembers">
                            <div>
                                <h4 x-text="totalRoles">101</h4>
                                <p>Total Channels Members</p>
                            </div>
                            <div class="roleTable">
                                <table>
                                    <thead>
                                        <th>Role</th>
                                        <th>No.</th>
                                    </thead>
                                    <tbody>
                                        <template x-for="(count, role) in roles" :key="role">
                                            <tr>
                                                <td x-text="role"></td>
                                                <td x-text="count"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="statRecord">
                            <div class="row statCostNav">
                                <div class="statOpt col-md-4">
                                    <h4>Statistics</h4>
                                    <p>Average costs</p>
                                </div>
                                <div class="statOptFilter col-md-8 d-flex flex-column align-items-end">
                                    <div class="drop-content">
                                        <div class="table-filter">
                                            <label class="block">
                                                <select x-model.number="selectedMonth">
                                                    <option>Month</option>
                                                    <template x-for="(name, idx) in monthNames" :key="idx">
                                                        <option :value="idx + 1" x-text="name"></option>
                                                    </template>
                                                </select>
                                            </label>
                                            <label class="block">
                                                <select x-model.number="selectedYear">
                                                    <template x-for="y in years" :key="y">
                                                        <option :value="y" x-text="y"></option>
                                                    </template>
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="contractCost">
                                    <span><img src="/wp-content/uploads/2025/08/contractoricon.svg" alt="" class="h-6 w-6">Contractor</span>
                                    <div>
                                        <h3 x-text="'$' + average"></h3>

                                        <p>Average Contractor Cost</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

<script>
    function statsCard() {

        return {
            open: false,
            showModal: false,
            activeTab: 'tabViews',
            isCombined: true,
            selectedChannels: [],
            selectedDaterange: 'all',
            showCustomModal: false,
            customStart: '',
            customEnd: '',
            channelOptions: [],
            filteredData: [],
            totalViews: 0,
            totalRevenue: 0,
            totalExpenses: 0,
            totalGross: 0,
            data: [],
            roles: [],
            totalRoles: 0,
            teams: [],
            sortedTeams: [],
            revenueGrowthPercentage: 0,
            viewGrowthPercentage: 0,
            subscribersGrowthPercentage: 0,
            grossProfitPercentage: 0,
            totalViews: 0,
            totalRevenue: 0,
            totalExpenses: 0,
            totalGross: 0,
            percentViews: 0,
            percentRevenue: 0,
            percentExpenses: 0,
            percentGross: 0,
            invoiceData: [],
            selectedMonth: null,
            selectedYear: null,
            monthNames: [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ],
            handleChannelChange() {
                this.renderAnalyticsChart();
            },
            handleDateRangeChange() {
                if (this.selectedDaterange === 'custom') {
                    this.showCustomModal = true;
                } else {
                    this.renderAnalyticsChart();
                }
            },
            // Apply custom date range
            applyCustomRange() {
                if (!this.customStart || !this.customEnd) {
                    alert("Please select both start and end dates.");
                    return;
                }
                this.renderAnalyticsChart();
                this.showCustomModal = false;
                this.selectedDaterange = 'all';
            },

            // Close custom date range modal
            closeModal() {
                this.showCustomModal = false;
                this.selectedDaterange = 'all'; 
            },
            toggleChannel(channel) {
                if (this.selectedChannels.includes(channel)) {
                    this.selectedChannels = this.selectedChannels.filter(c => c !== channel);
                } else {
                    this.selectedChannels.push(channel);
                }
                this.handleChannelChange();
            },
            isSelected(channel) {
                return this.selectedChannels.includes(channel);
            },
            clearChannels() {
                this.selectedChannels = [];
                this.handleChannelChange();
            },
            init() {
                try {
                    const insightRaw = document.getElementById('insight-data')?.textContent;
                    this.data = insightRaw ? JSON.parse(insightRaw) : [];
                } catch (e) {
                    this.data = [];
                    console.error("Failed to parse insight-data JSON:", e);
                }
                // console.log('this.data:', this.data);

                try {
                    const teamRaw = document.getElementById('roles-data')?.textContent;
                    this.roles = teamRaw ? JSON.parse(teamRaw) : {};
                    this.totalRoles = Object.values(this.roles).reduce((sum, count) => sum + count, 0);
                } catch (e) {
                    this.roles = {};
                    console.error("Failed to parse roles JSON:", e);
                }
                try {
                    const teamRaw = document.getElementById('team-data')?.textContent;
                    this.teams = teamRaw ? JSON.parse(teamRaw) : [];
                    this.sortedTeams = this.teams.sort((a, b) =>
                        a.name.localeCompare(b.name)
                    );
                } catch (e) {
                    this.teams = [];
                    console.error("Failed to parse team-data JSON:", e);
                }
                try {
                    const channelRaw = document.getElementById('channel-data')?.textContent;
                    this.channels = channelRaw ? JSON.parse(channelRaw) : [];
                    this.channelOptions = this.channels.sort((a, b) =>
                        a.name.localeCompare(b.name)
                    );
                } catch (e) {
                    this.channels = [];
                    console.error("Failed to parse channel-data JSON:", e);
                }
                try {
                    const invoiceRaw = document.getElementById('invoice-data')?.textContent;
                    this.invoiceData = invoiceRaw ? JSON.parse(invoiceRaw) : [];
                } catch (e) {
                    this.invoiceData = [];
                    console.error("Failed to parse invoice-data JSON:", e);
                }

                this.handleChannelChange(); // optionally call on init

                // Watch for changes in activeTab
                this.$watch('activeTab', (value) => {
                    this.renderAnalyticsChart();
                });
                this.$watch('isCombined', (value) => {
                    this.renderAnalyticsChart();
                });

                const now = new Date();
                this.selectedMonth = now.getMonth() + 1; // 1–12
                this.selectedYear = now.getFullYear();

                // console.log('the team: ', this.sortedTeams);
                // console.log('Initialized with month:', this.selectedMonth, 'year:', this.selectedYear);

            },
            get activeTeams() {
                return this.sortedTeams.filter(t => t.status === "Active");
            },
            get total() {
                return this.filtered.reduce((sum, r) => sum + (parseFloat(r.amount) || 0), 0);
            },
            get count() {
                return this.filtered.length;
            },
            get average() {
                return this.formatCompact(this.count > 0 ? this.total / this.count : 0);
            },
            get years() {

                const ys = this.invoiceData.map(r => this.normalizeDate(r.date).getFullYear());
                // unique, then numeric sort (largest first)
                return [...new Set(ys)].sort((a, b) => b - a);
            },
            // 4. helpers
            normalizeDate(dateStr) {
                if (dateStr.includes('-')) {
                    return new Date(dateStr); // YYYY-MM-DD
                } else {
                    const [m, d, y] = dateStr.split('/'); // MM/DD/YYYY
                    return new Date(`${y}-${m}-${d}`);
                }
            },
            formatCompact(num) {
                const abs = Math.abs(num);
                if (abs >= 1_000_000_000) return (num / 1_000_000_000).toFixed(2) + 'B';
                if (abs >= 1_000_000) return (num / 1_000_000).toFixed(2) + 'M';
                if (abs >= 1_000) return (num / 1_000).toFixed(2) + 'K';
                return num.toFixed(2);
            },
            get filtered() {
                return this.invoiceData.filter(r => {
                    const d = this.normalizeDate(r.date);
                    return (d.getMonth() + 1) == this.selectedMonth &&
                        d.getFullYear() == this.selectedYear;
                });
            },
            insightDataFilter12() {
                this.totalViews = 0;
                this.totalRevenue = 0;
                this.totalExpenses = 0;
                this.totalGross = 0;
                const days = parseInt(this.selectedDaterange);
                const now = new Date();
                const threshold = new Date(now.setDate(now.getDate() - days));

                this.filteredData = this.data.reduce((acc, item) => {
                    if (!item.date) return acc;

                    // Filter by selected channels (if any)
                    if (this.selectedChannels.length > 0 && !this.selectedChannels.includes(item.channel)) {
                        return acc;
                    }

                    // Parse item date
                    const [day, month, year] = item.date.split('/');
                    const itemDate = new Date(`${year}-${month}-${day}`);
                    const today = new Date();
                    let thresholdStart = null;
                    let thresholdEnd = null;

                    switch (this.selectedDaterange) {
                        case "today":
                            thresholdStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                            if (itemDate < thresholdStart) return acc;
                            break;
                        case "yesterday":
                            thresholdStart = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 1);
                            if (
                                itemDate.getFullYear() !== thresholdStart.getFullYear() ||
                                itemDate.getMonth() !== thresholdStart.getMonth() ||
                                itemDate.getDate() !== thresholdStart.getDate()
                            ) {
                                return acc;
                            }
                            break;
                        case "ytd":
                            thresholdStart = new Date(today.getFullYear(), 0, 1); // Jan 1 this year
                            if (itemDate < thresholdStart) return acc;
                            break;
                        case "all":
                            // no date filtering
                            break;
                        case "custom":
                            if (!this.customStart || !this.customEnd) return acc;
                            thresholdStart = new Date(this.customStart);
                            thresholdEnd = new Date(this.customEnd);
                            // include the entire end day
                            thresholdEnd.setHours(23, 59, 59, 999);
                            if (itemDate < thresholdStart || itemDate > thresholdEnd) return acc;
                            // this.
                            break;
                        default:
                            // numeric values (7, 14, 28, 90, 180, 365…)
                            thresholdStart = new Date(today);
                            thresholdStart.setDate(today.getDate() - parseInt(this.selectedDaterange));
                            if (itemDate < thresholdStart) return acc;
                    }

                    // Accumulate totals
                    const views = parseInt(item.views) || 0;
                    const revenue = parseInt(item.revenue) || 0;
                    const expenses = parseInt(item.expenses) || 0;

                    this.totalViews += views;
                    this.totalRevenue += revenue;
                    this.totalExpenses += expenses;
                    this.totalGross += revenue - expenses;

                    acc.push(item);
                    return acc;
                }, []);

            },
            insightDataFilter() {
                // Reset totals
                this.totalViews = 0;
                this.totalRevenue = 0;
                this.totalExpenses = 0;
                this.totalGross = 0;
                let numericRange = null;
                const today = new Date();
                let currentStart, currentEnd, previousStart, previousEnd;

                // ---------- 1. Resolve date windows ----------
                if (!isNaN(parseInt(this.selectedDaterange))) {
                    numericRange = parseInt(this.selectedDaterange, 10);
                    // const days = numericRange;
                    // e.g. "7" means last 7 days
                    const days = parseInt(this.selectedDaterange, 10);
                    currentEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59, 999);
                    currentStart = new Date(currentEnd);
                    currentStart.setDate(currentStart.getDate() - (days - 1));

                    previousEnd = new Date(currentStart);
                    previousEnd.setDate(previousEnd.getDate() - 1);
                    previousStart = new Date(previousEnd);
                    previousStart.setDate(previousStart.getDate() - (days - 1));

                } else if (this.selectedDaterange === "today") {
                    currentStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                    currentEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59, 999);

                    previousStart = new Date(currentStart);
                    previousStart.setDate(previousStart.getDate() - 1);
                    previousEnd = new Date(previousStart);
                    previousEnd.setHours(23, 59, 59, 999);

                } else if (this.selectedDaterange === "ytd") {
                    currentStart = new Date(today.getFullYear(), 0, 1);
                    currentEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59, 999);

                    previousStart = new Date(today.getFullYear() - 1, 0, 1);
                    previousEnd = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate(), 23, 59, 59, 999);

                } else if (this.selectedDaterange === "custom") {
                    if (!this.customStart || !this.customEnd) return; // nothing to filter
                    currentStart = new Date(this.customStart);
                    currentEnd = new Date(this.customEnd);
                    currentEnd.setHours(23, 59, 59, 999);

                    // span in days
                    const msInDay = 86400000;
                    const spanDays = Math.round((currentEnd - currentStart) / msInDay) + 1;

                    previousEnd = new Date(currentStart);
                    previousEnd.setDate(previousEnd.getDate() - 1);
                    previousStart = new Date(previousEnd);
                    previousStart.setDate(previousStart.getDate() - (spanDays - 1));

                } else if (this.selectedDaterange === "all") {
                    currentStart = currentEnd = previousStart = previousEnd = null;
                }

                // ---------- 2. Helpers ----------
                const parseItemDate = (dateStr) => {
                    const [day, month, year] = dateStr.split('/');
                    return new Date(`${year}-${month}-${day}`);
                };

                const isInRange = (date, start, end) => {
                    if (!start || !end) return true; // 'all'
                    return date >= start && date <= end;
                };

                // ---------- 3. Filter ----------
                const currentData = [];
                const previousData = [];

                this.data.forEach(item => {
                    if (!item.date) return;
                    if (this.selectedChannels.length && !this.selectedChannels.includes(item.channel)) return;

                    const itemDate = parseItemDate(item.date);
                    const views = parseInt(item.views) || 0;
                    const revenue = parseFloat(item.revenue) || 0;
                    const expenses = parseFloat(item.expenses) || 0;

                    if (isInRange(itemDate, currentStart, currentEnd)) {
                        currentData.push(item);
                        this.totalViews += views;
                        this.totalRevenue += revenue;
                        this.totalExpenses += expenses;
                        this.totalGross += revenue - expenses; // gross profit
                    } else if (isInRange(itemDate, previousStart, previousEnd)) {
                        previousData.push(item);
                    }
                });

                this.filteredData = currentData;
                this.previousFilteredData = previousData;

                // ---------- 4. Totals for previous ----------
                let prevViews = 0,
                    prevRevenue = 0,
                    prevExpenses = 0,
                    prevGross = 0;

                previousData.forEach(item => {
                    const revenue = parseFloat(item.revenue) || 0;
                    const expenses = parseFloat(item.expenses) || 0;

                    prevViews += parseInt(item.views) || 0;
                    prevRevenue += revenue;
                    prevExpenses += expenses;
                    prevGross += revenue - expenses;
                });

                // ---------- 5. Percentage change ----------
                const pct = (curr, prev) => (prev ? ((curr - prev) / prev) * 100 : null);

                this.percentViews = pct(this.totalViews, prevViews);
                this.percentRevenue = pct(this.totalRevenue, prevRevenue);
                this.percentExpenses = pct(this.totalExpenses, prevExpenses);
                this.percentGross = pct(this.totalGross, prevGross);

                // Raw gross difference if you want it
                this.grossDiff = this.totalGross - prevGross;

                // ---------- 7. Build display strings ----------
                const explain = (value) => {
                    if (value === null || Math.abs(value) < 0.0001) {
                        return "About the same as usual";
                    }
                    // add ↑ or ↓ with sign and 2 decimals


                    const arrow = value > 0 ? "↑" : "↓";
                    return `${arrow} ${value.toFixed(2)}%`;
                };

                this.viewsChangeMsg = `${this.formatPercentChange(this.percentViews, numericRange)}`;
                this.revenueChangeMsg = `${this.formatPercentChange(this.percentRevenue, numericRange)}`;
                this.expensesChangeMsg = `${this.formatPercentChange(this.percentExpenses, numericRange)}`;
                this.grossChangeMsg = `${this.formatPercentChange(this.percentGross, numericRange)}`;
            },
            formatPercentChange(value, numericRange) {
                if (value === null || Math.abs(value) < 0.0001) {
                    return "About the same as usual";
                } else {
                    const suffix = numericRange ? ` vs last ${numericRange} days` : "";
                    return `${value.toFixed(2)}% ${suffix}`;
                }
            },
            arrowIcon(value) {
                if (value == null || value == "") return "/wp-content/uploads/2025/07/asusual.svg";
                return value > 0 ? "/wp-content/uploads/2025/07/morethanusual.svg" : "/wp-content/uploads/2025/07/lessthanusual.svg";
            },
            // Function to generate blue shades dynamically
            getBlueShade(index, total) {
                // index starts at 0
                const startHue = 210; // blue hue
                const saturation = 80; // %
                const lightnessStart = 40; // darker blue
                const lightnessEnd = 70; // lighter blue

                const lightness = lightnessStart + ((lightnessEnd - lightnessStart) / (total - 1)) * index;
                return `hsl(${startHue}, ${saturation}%, ${lightness}%)`;
            },
            // Function to generate gradient based on blue shade
            createGradient(ctx, baseColor) {
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, baseColor.replace('hsl', 'hsla').replace('%)', '%, 0.6)'));
                gradient.addColorStop(1, baseColor.replace('hsl', 'hsla').replace('%)', '%, 0)'));
                return gradient;
            },
            renderAnalyticsChart() {
                this.insightDataFilter();

                const canvas = document.getElementById("analyticsChartViews");
                if (!canvas) return;
                const ctx = canvas.getContext("2d"); // Get 2D context here

                // Destroy existing chart if present
                const existingChart = Chart.getChart(canvas);
                if (existingChart) {
                    existingChart.destroy();
                }

                let data = {
                    labels: [],
                    datasets: []
                };

                // Gradients
                const gradient1 = ctx.createLinearGradient(0, 0, 0, 400);
                gradient1.addColorStop(0, 'rgba(33, 148, 255, 0.6)');
                gradient1.addColorStop(1, 'rgba(33, 148, 255, 0)');

                const gradient2 = ctx.createLinearGradient(0, 0, 0, 400);
                gradient2.addColorStop(0, 'rgba(120, 180, 255, 0.6)');
                gradient2.addColorStop(1, 'rgba(120, 180, 255, 0)');

                const gradient3 = ctx.createLinearGradient(0, 0, 0, 400);
                gradient3.addColorStop(0, 'rgba(180, 210, 255, 0.6)');
                gradient3.addColorStop(1, 'rgba(180, 210, 255, 0)');


                if (this.filteredData.length === 0) {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.font = '16px Arial';
                    ctx.textAlign = 'center';
                    ctx.fillText('No data found', canvas.width / 2, canvas.height / 2);
                    return;
                }

                // if (Chart.getChart("analyticsChartViews")) {
                //     Chart.getChart("analyticsChartViews").destroy();
                // }

                let labels = [];
                let value = [];

                if (this.isCombined) {
                    // First pass: collect all unique dates
                    this.filteredData.forEach(item => {
                        if (!item.date) return;

                        const [day, month, year] = item.date.split('/');
                        const dateObj = new Date(`${year}-${month}-${day}`);

                        const formattedDate = dateObj.toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                        });

                        if (!labels.some(l => l.label === formattedDate)) {
                            labels.push({
                                date: dateObj,
                                label: formattedDate
                            });
                        }
                    });

                    // Sort labels chronologically
                    labels.sort((a, b) => a.date - b.date);

                    // Extract final label strings
                    const labelStrings = labels.map(l => l.label);

                    // Second pass: fill channel values
                    this.filteredData.forEach(item => {
                        if (!item.date) return;

                        const [day, month, year] = item.date.split('/');
                        const dateObj = new Date(`${year}-${month}-${day}`);
                        const formattedDate = dateObj.toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                        });

                        const dateIndex = labelStrings.indexOf(formattedDate);
                        if (dateIndex === -1) return;

                        if (!value[item.channel]) {
                            value[item.channel] = Array(labelStrings.length).fill(0);
                        }

                        let valToAdd = 0;
                        if (this.activeTab === 'tabViews') {
                            valToAdd = parseInt(item.views) || 0;
                        } else if (this.activeTab === 'tabRevenue') {
                            valToAdd = parseInt(item.revenue) || 0;
                        } else if (this.activeTab === 'tabExpenses') {
                            valToAdd = parseInt(item.expenses) || 0;
                        } else if (this.activeTab === 'tabGross') {
                            valToAdd = (parseInt(item.revenue) || 0) - (parseInt(item.expenses) || 0);
                        }

                        value[item.channel][dateIndex] += valToAdd;
                    });

                    const datasets = Object.keys(value).map((channel, index) => {

                        return {
                            label: channel,
                            data: value[channel],
                            // borderWidth: 0,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 0,
                            backgroundColor: gradient1,
                            borderColor: '#6aa4daff',
                            stack: 'stack1',
                        };
                    });

                    data = {
                        labels: labelStrings,
                        datasets: datasets
                    };

                } else {
                    // If individual, use only channel 1
                    this.filteredData.forEach(item => {
                        if (!item.date) return;

                        // Format date: "29/04/2025" => "April 29, 2025"
                        const [day, month, year] = item.date.split('/');
                        const dateObj = new Date(`${year}-${month}-${day}`);
                        
                        const formattedDate = dateObj.toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                        });

                        labels.push(formattedDate);

                        let val = 0;
                        if (this.activeTab == 'tabViews') {
                            val = parseInt(item.views) || 0;
                        } else if (this.activeTab == 'tabRevenue') {
                            val = parseInt(item.revenue) || 0;
                        } else if (this.activeTab == 'tabExpenses') {
                            val = parseInt(item.expenses) || 0;
                        } else if (this.activeTab == 'tabGross') {
                            val = parseInt(item.revenue) - parseInt(item.expenses) || 0;
                        } else {
                            console.log("active tab not found");
                        }
                        value.push(val);
                    });
                    if (value.length === 0) {
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.font = '16px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText('No data found', canvas.width / 2, canvas.height / 2);
                        return;
                    }
                    data = {
                        labels: labels,
                        datasets: [{
                            label: 'Single Channel',
                            data: value,
                            backgroundColor: gradient1,
                            borderColor: '#2194FF',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            stack: 'stack1',

                        }]
                    };
                }

                // Chart init
                new Chart(ctx, {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        stacked: true, // stack datasets
                        scales: {
                            x: {
                                stacked: true,
                                ticks: {
                                    display: true
                                },
                                grid: {
                                    display: false,
                                    drawBorder: false,
                                    drawTicks: false
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return `${(value / 1000).toFixed(1)}k`;
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.parsed.y.toLocaleString()}`;
                                    }
                                }
                            }
                        }
                    }
                });
            },
            renderIndividualChartViews() {
                const canvas = document.getElementById("individualChartViews");
                if (!canvas) return;
                const ctx = canvas.getContext("2d"); // Get 2D context here

                // Destroy existing chart if present
                const existingChart = Chart.getChart(canvas);
                if (existingChart) {
                    existingChart.destroy();
                }

                const labels = ['Jan 27', 'Feb 1', 'Feb 5', 'Feb 10', 'Feb 14', 'Feb 19', 'Feb 23'];
                const channel1 = [30_000, 50_000, 70_000, 60_000, 65_000, 80_000, 85_000];

                // Gradients
                const gradient1 = ctx.createLinearGradient(0, 0, 0, 400);
                gradient1.addColorStop(0, 'rgba(33, 148, 255, 0.6)');
                gradient1.addColorStop(1, 'rgba(33, 148, 255, 0)');

                // Chart init
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Channel #1',
                            data: channel1,
                            backgroundColor: gradient1,
                            borderColor: '#2194FF',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            stack: 'stack1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        stacked: true, // stack datasets
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return `${(value / 1000).toFixed(1)}k`;
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.parsed.y.toLocaleString()}`;
                                    }
                                }
                            }
                        }
                    }
                });
            },
            renderRealtimeChartViews() {
                const canvas = document.getElementById("realtimeChartViews");
                const ctx = canvas.getContext("2d"); // Get 2D context here

                const labels = ['', '', '', '', '', '', ''];
                const channel1 = [30_000, 50_000, 70_000, 60_000, 65_000, 80_000, 85_000];

                if (Chart.getChart("realtimeChartViews")) {
                    Chart.getChart("realtimeChartViews").destroy();
                }

                // Chart init
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Channel #1',
                            data: channel1,
                            backgroundColor: '#2194FF',
                            borderColor: '#000000ff',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 0,
                            stack: 'stack1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                            axis: undefined // disables vertical hover line
                        },
                        plugins: {
                            tooltip: {
                                enabled: true,
                                mode: 'nearest', // or 'point'
                                intersect: false,
                            },
                            legend: {
                                display: false,
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                ticks: {
                                    display: false
                                },
                                grid: {
                                    display: false,
                                    drawBorder: false,
                                    drawTicks: false
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    display: false
                                },
                                grid: {
                                    display: false,
                                    drawBorder: false,
                                    drawTicks: false
                                }
                            }
                        }
                    }
                });
            }
        }
    }
</script>



<style>
    .channel-dropdown {
        cursor: pointer;
    }
    .table-filter select {
        border: solid 1px transparent !important;
        padding: 0;
    }
    .main-content {
        padding: 56px 64px;
        overflow: hidden;
    }
    .header-db {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 100px;
    }
    .channelSelectFilter {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        width: 230px;
    }
    .channelSelectFilter select {
        padding: 14px 12px;
        border: solid 1px transparent !important;
        background-color: transparent;
        color: #2194FF;
        font-size: 16px;
        font-weight: 700;
        width: 230px;
    }
    .channelSelectFilter select:hover,
    .channelSelectFilter select:focus {
        border: solid 1px transparent !important;
    }
    .channelDisplay {
        display: flex;
        flex-direction: row;
        gap: 8px;
    }
    .channeldDisplayBtn {
        border: solid 2px #2194FF;
        border-radius: 31px;
        padding: 8px 16px 8px 8px;
        color: #2194FF;
        display: flex;
        gap: 8px;
        font-weight: 600;
        font-size: 14px;
        align-items: center;
    }
    .channeldDisplayBtn:hover {
        background-color: #2194FF;
    }
    .channeldDisplayBtn a,
    .financeBtn a,
    .performBtn a,
    .liveCountBtn a,
    .viewBtn a {
        color: #2194FF;
    }
    .liveCountBtn,
    .viewBtn {
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        color: #2194FF;
        border: solid 1px #2194FF;
    }
    .liveCountBtn:hover,
    .viewBtn:hover {
        background-color: #2194FF33;
        color: #2194FF;
    }
    .statDaysFilter select {
        border: solid 1px #717171;
        background-color: transparent;
        padding: 11px 16px;
        color: #717171;
        font-size: 14px;
        font-weight: 400;
        cursor: pointer;
    }
    .statDaysFilter select:hover,
    .statDaysFilter select:focus {
        border: solid 1px #717171;
    }
    .filterNav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-bottom: 16px;
        position: relative;
    }
    .filterNav .custom-date-filter {
        position: absolute;
        right: 0;
        top: 50px;
        z-index: 9;
    }
    .filterNav .custom-date-filter>div {
        padding: 15px;
    }
    .analyticsStat {
        padding: 24px;
        background-color: #ffffffff;
        border-radius: 16px;
        margin-bottom: 24px;
    }
    .analyticsStat .analyticsNav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-bottom: 40px;
    }
    canvas#analyticsChartViews {
        height: 300px;
    }
    .analyticsStatCard {
        padding: 24px;
        border-radius: 8px;
        border: solid 1px transparent;
    }
    .analyticsStatCard:hover {
        cursor: pointer;
        background-color: #F5FAFF;
        border: 1px solid #2194FF;
    }
    .analyticsStatCard:hover .iconLabel img {
        border-radius: 50%;
        background-color: #FFEE94;
        width: 30px;
        height: 30px;
        object-fit: contain;
    }
    .analyticsStatCard .iconLabel img {
        border-radius: 50%;
        background-color: transparent;
        width: 30px;
        height: 30px;
        object-fit: contain;
    }
    .activeCard .iconLabel img {
        border-radius: 50%;
        background-color: #FFEE94;
        width: 30px;
        height: 30px;
        object-fit: contain;
    }
    .activeCard {
        border: 1px solid #2194FF;
        background-color: #F5FAFF;
    }
    div.statLabel {
        margin-bottom: 24px;
    }
    div.statLabel,
    div.statIndicate {
        display: flex;
        flex-direction: row;
        gap: 8px;
        align-items: center;
    }
    div.statIndicate {
        margin-bottom: 16px;
    }
    .analyticsStat h4,
    .analyticsStat h3,
    .analyticsStat p {
        margin: 0px !important;
    }
    .analyticsStat h4 {
        font-size: 14px;
        color: #101010;
        font-weight: 600;
    }
    .analyticsStat h3 {
        font-size: 24px;
        font-weight: 600;
        color: #101010;
    }
    .analyticsStat p {
        font-size: 14px;
        font-weight: 600;
        color: #2194FF;
    }
    .row {
        justify-content: space-between;
        margin-right: 0px;
        margin-left: 0px;
    }
    .col-3 {
        width: 24%;
    }
    .analtyicsStatFilter {
        margin-bottom: 40px;
        gap: 8px;
    }
    .switchNav {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
    }
    .switchNav .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    .switchNav .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .switchNav .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to left, #FFEB80 0%, #FEDE34 100%);
        -webkit-transition: .4s;
        transition: .4s;
    }
    .switchNav .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }
    .switchNav input:checked+.slider {
        background: linear-gradient(to right, #2196F3 0%, #2196F3 100%);
    }
    .switchNav input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }
    .switchNav input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }
    .switchNav .slider.round {
        border-radius: 34px;
    }
    .switchNav .slider.round:before {
        border-radius: 50%;
    }
    .financeBtn {
        font-size: 14px;
        padding: 11px 16px;
        background-color: #2194FF;
        border-radius: 4px;
        border: solid 1px #2194FF;
        color: #FFFFFF;
    }
    .financeBtn a {
        color: #FFFFFF;
    }
    .financeBtn:hover {
        background-color: #0165C2;
    }
    .statDetailed {
        display: flex;
        flex-direction: row;
        gap: 16px;
        margin-bottom: 16px;
    }
    .realtimeRecord,
    .perChannelMembers,
    .statRecord,
    .allTeam {
        padding: 24px;
        border-radius: 16px;
        background-color: #FFFFFF;
    }
    .realtimeRecord h4,
    .statRecord h4,
    .perChannelMembers h4,
    .allTeam h4 {
        color: #101010;
        font-weight: 700;
        font-size: 20px;
    }
    .realtimeRecord p,
    .statRecord p,
    .perChannelMembers p,
    .allTeam p {
        color: #717171;
        font-size: 12px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 2px;
    }
    .realtimeRecord p img {
        width: 10px;
        height: 10px;
        object-fit: contain;
    }
    .realtimeRecord span {
        font-size: 12px;
        color: #717171;
    }
    .statRecord span {
        color: #2194FF;
        font-size: 12px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
    }
    .statRecord h3 {
        color: #101010;
        font-weight: 600;
        font-size: 24px;
        margin-top: 0px;
    }
    .statCostNav,
    .allTeamNav,
    .realtimeRecordNav {
        padding-bottom: 24px;
        margin-bottom: 24px;
        border-bottom: solid 1px #E7E7E7;
    }
    .perTeam,
    .perTeamCountry,
    .perTeamChannel {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
    }
    .perTeam h6,
    .perTeamCountry h6,
    .perTeamChannel h6 {
        font-size: 14px;
        color: #101010;
        margin-top: 0px;
        margin-bottom: 0px;
    }
    .perTeam p {
        margin-bottom: 0px;
    }
    .allTeam img {
        max-width: 36px;
        max-height: 36px;
        object-fit: cover;
        border-radius: 25px;
    }
    .allTeam table thead img {
        margin-left: 4px;
    }
    .topContentInfo img {
        border-radius: 8px;
    }
    .topContentInfo h3 {
        color: #101010;
        font-size: 14px;
        margin: 0px;
    }
    .topContentInfo h4 {
        margin: 15px 0 0 0;
    }
    table.tbl-topContent thead th {
        padding: 0px;
        color: #717171;
        font-size: 12px;
        font-weight: 400;
    }
    table.tbl-topContent thead,
    table.tbl-topContent tbody,
    table.tbl-topContent tr {
        border: unset;
    }
    table.tbl-topContent td {
        padding: 16px 0 0 0;
    }
    table tbody tr:hover {
        background-color: hsla(0, 0%, 50.2%, .1019607843);
    }
    .drop-content {
        display: flex;
        align-items: center;
        column-gap: 10px;
    }
    div.drop-div {
        display: flex;
        align-items: flex-end;
        background-color: #fff;
        border-radius: 10px;
        padding: 10px 10px 15px 10px;
        width: 250px;
        justify-content: space-between;
        gap: 10px;
    }
    .multi-select {
        position: relative;
        width: 300px;
    }
    .multi-select .selected {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 10px;
        background: #fff;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .multi-select .selected span {
        color: #333;
    }
    .multi-select .options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #fff;
        display: none;
        z-index: 1000;
    }
    .multi-select.open .options {
        display: block;
    }
    .multi-select .option {
        padding: 8px 12px;
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    .multi-select .option:hover {
        background: #f2f2f2;
    }
    .multi-select .option input {
        margin-right: 8px;
    }
    .absolute {
        position: absolute;
    }
    .table-filter {
        display: flex;
        flex-direction: row;
        align-items: center;
        border: solid 1px #D0D0D0;
        border-radius: 4px;
        padding: 10px;
        gap: 10px;
        width: 100%;
    }
    .channel-dropdown {
        z-index: 99;
    }
    /* Media Queries-------------------------------- */
    @media only screen and (max-width: 1550px) {
        .main-content {
            padding: 56px 32px;
        }
        .header-db {
            gap: 0;
        }
        .teamTable {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 800px;
        }
        .roleTable {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 500px;
        }
        .teamTable::-webkit-scrollbar,
        .roleTable::-webkit-scrollbar {
            width: 2px;
            height: 2px;
        }
        .teamTable::-webkit-scrollbar-track,
        .roleTable::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .teamTable::-webkit-scrollbar-thumb,
        .roleTable::-webkit-scrollbar-thumb {
            background-color: #2194FF;
            border-radius: 10px;
        }
        .statCostNav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .statOpt {
            width: 100%;
        }
        .statOptFilter {
            width: 100%;
        }
    }
    @media only screen and (max-width: 1200px) {
        .analyticsStatCard {
            padding: 14px;
        }
        .teamTable {
            overflow-x: auto;
            max-height: 730px;
        }
        .realtimeRecord,
        .perChannelMembers,
        .statRecord,
        .allTeam {
            padding: 15px;
        }
    }
    @media only screen and (max-width: 1024px) {
        .main-content {
            padding: 50px 40px;
        }
        .analtyicsStatFilter {
            gap: 0;
        }
    }
    @media only screen and (max-width: 880px) {
        .header-db {
            flex-direction: column;
        }
        .main-content {
            padding: 24px;
        }
        .allTeamNav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .navOpt,
        .statOpt,
        .statOptFilter {
            width: 100%;
        }
        .statCostNav {
            display: flex;
            flex-direction: column;
        }
        .filterNav {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 16px;
        }
        .analyticsStat .analyticsNav {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 20px;
        }
        .switchNav {
            gap: 10px;
            align-items: center;
        }
        .channelSelectFilter {
            width: 100%;
        }
        .titleNav {
            width: 100%;
            margin-bottom: 24px;
        }
        .analyticsStat {
            padding: 24px 15px;
        }
        .realtimeRecord,
        .perChannelMembers,
        .statRecord,
        .allTeam {
            padding: 15px 15px;
        }
        .analtyicsStatFilter {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .analyticsStatCard {
            padding: 14px 10px;
            flex: unset;
            width: 100%;
        }
    }
    @media only screen and (max-width: 768px) {
        .statDetailed {
            display: flex;
            flex-direction: column;
        }
        .allTeam {
            width: 100%;
        }
        .recordDetailed {
            padding-right: 0 !important;
            width: 100%;
        }
    }
    @media only screen and (max-width: 425px) {
        .switchNav label {
            font-size: 14px;
        }
        .switchNav .switchLabel1 {
            text-align: left;
        }
        .switchNav .switchLabel2 {
            text-align: right;
        }
        .analtyicsStatFilter {
            grid-template-columns: 1fr;
        }
        .switchNav .switch {
            width: 70px !important;
        }
        .channelDisplay {
            display: flex;
            flex-direction: column;
        }
    }
    @media only screen and (max-width: 375px) {
        .switchNav {
            display: flex;
            flex-direction: column;
        }
        .switchNav .switch {
            width: 60px;
        }
        .switchNav .switchLabel1,
        .switchNav .switchLabel2 {
            text-align: center;
        }
        .channelSelectFilter select {
            width: 180px;
        }
    }
</style>