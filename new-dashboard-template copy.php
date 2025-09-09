<?php

/**
 * Template Name: New Dashboard Template -- copy
 */

get_header('admin');

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


// 1. Get all roles from "team-roles"
$args = array(
    'post_type'      => 'team-roles',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
);
$role_query = new WP_Query($args);
$roles = [];

if ($role_query->have_posts()) {
    while ($role_query->have_posts()) {
        $role_query->the_post();
        $role_name = get_the_title();
        $roles[$role_name] = 0; // initialize all roles to 0
    }
}
wp_reset_postdata();

// 2. Count members + collect team data
$args = array(
    'post_type'      => 'team',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
);
$team_query = new WP_Query([
    'post_type'      => 'team',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);

$total_members = 0;
$members = [];

if ($team_query->have_posts()) {
    while ($team_query->have_posts()) {
        $team_query->the_post();
        $total_members++;

        // Fields
        $fullname      = get_field('fullname');
        $profile_photo = get_field('profile_photo');
        $role          = get_field('role'); // relationship to team-roles
        $your_channel  = get_field('your_channel'); // relationship array

        // fallback for profile photo
        if (!$profile_photo) {
            $profile_photo = '/wp-content/uploads/2025/08/teamFallback.svg';
        }

        // Resolve role name
        $role_name = '';
        if ($role && is_array($role) && !empty($role)) {
            $role_name = get_the_title($role[0]);

            // Increment role count
            if (isset($roles[$role_name])) {
                $roles[$role_name]++;
            }
        }

        // Process channels
        $channels = [];
        if ($your_channel && is_array($your_channel)) {
            foreach ($your_channel as $channel_post) {
                $channel_name          = get_the_title($channel_post);
                $channel_profile_photo = get_field('channel_profile_photo', $channel_post);

                // fallback for channel image
                if (!$channel_profile_photo) {
                    $channel_profile_photo = '/wp-content/uploads/2025/08/vidicon.svg';
                }

                $channels[] = [
                    'channel_name' => $channel_name,
                    'channel_img'  => $channel_profile_photo,
                ];
            }
        }

        $members[] = [
            'fullname' => $fullname,
            'profile_photo' => $profile_photo,
            'role' => $role_name,
            'channels' => $channels,
        ];
    }
}
wp_reset_postdata();

$data = [
    'totalMembers' => $total_members,
    'roles'        => $roles,
    'members'      => $members,
];


echo '<pre>';
print_r($roles);
echo '</pre>';
// echo '<pre>';
// print_r($team_members);
// echo '</pre>';

?>

<!-- 
<script type="application/json" id="insight-data">
    < ?php echo json_encode($data) ?>
</script>
<script type="application/json" id="roles">
    < ?php echo json_encode($roles); ?>
</script> -->
<!-- <script type="application/json" id="team-data">
    < ?php echo json_encode($data); ?>
</script> -->
<!-- <script>
    window.teamData = < ?php echo wp_json_encode($data); ?>;
</script> -->


<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar'); ?>
    <div class="main-content">
        <div x-data="statsCard()">
            <main class="flex-grow-1">
                <div class="header-db">
                    <div class="titleNav">
                        <h1>Your Dashboard</h1>
                        <p>Welcome, John! Lorem ipsum dolor sit amet, consect etur adipiscing elit. Suspendisse varius enim in eros elementum tristique. </p>
                    </div>
                    <div class="channelSelectFilter">
                        <select x-model="selectedChannels" x-on:change="handleChannelChange">
                            <template x-for="option in channelOptions" :key="option">
                                <option x-text="`${option} Channels Selected`" :value="option"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="filterNav">
                        <div class="channelDisplay">
                            <button class="channeldDisplayBtn"><img src="/wp-content/uploads/2025/07/channelbtnfallback.svg" alt="">
                                Channel 1</button>
                            <button class="channeldDisplayBtn"><img src="/wp-content/uploads/2025/07/channelbtnfallback.svg" alt="">
                                Channel 2</button>
                            <button class="channeldDisplayBtn"><img src="/wp-content/uploads/2025/07/channelbtnfallback.svg" alt="">
                                Channel 3</button>
                        </div>
                        <div class="statDaysFilter">
                            <select x-model="selectedDaterange" x-on:change="handleDateRangeChange">
                                <option value=7>Last 7 days</option>
                                <option value=28>Last 28 days</option>
                                <option value=90>Last 90 days</option>
                                <option value=365>Last 365 days</option>
                            </select>
                        </div>
                    </div>
                    <div class="analyticsStat">
                        <div class="analyticsNav">
                            <div>
                                <button class="financeBtn"><a href="/revenue">Finances</a></button>
                            </div>
                            <div class="switchNav">
                                <label>Individual Channels</label>
                                <label class="switch">
                                    <input type="checkbox" x-model="isCombined">
                                    <span class="slider round"></span>
                                </label>
                                <label>Combined Analytics</label>
                            </div>
                        </div>
                        <div class="statGraph">
                            <!-- Showing Combined Analytics -->
                            <div class="analtyicsStatFilter row">
                                <!-- Views -->
                                <div class="analyticsStatCard col-3"
                                    @click="activeTab = 'tabViews'"
                                    :class="{ 'activeCard': activeTab === 'tabViews'}">
                                    <div class="statLabel">
                                        <div class="iconLabel"><img src="/wp-content/uploads/2025/02/views.svg" alt=""></div>
                                        <h4>Views</h4>
                                    </div>
                                    <div class="statInform">
                                        <div class="statIndicate">
                                            <h3 x-text="totalViews.toLocaleString()">300k</h3>
                                            <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                        </div>
                                        <p>About the same as usual</p>
                                    </div>
                                </div>
                                <!-- Estimated Revenue -->
                                <div class="analyticsStatCard col-3"
                                    @click="activeTab = 'tabRevenue'"
                                    :class="{ 'activeCard': activeTab === 'tabRevenue' }">
                                    <div class="statLabel">
                                        <div class="iconLabel"><img src="/wp-content/uploads/2025/02/total-revenue.svg" alt=""></div>
                                        <h4>Estimated Revenue</h4>
                                    </div>
                                    <div class="statInform">
                                        <div class="statIndicate">
                                            <h3 x-text="'$' + totalRevenue.toLocaleString()">$50,739.19</h3>
                                            <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                        </div>
                                        <p>About the same as usual</p>
                                    </div>
                                </div>
                                <!-- Expenses -->
                                <div class="analyticsStatCard col-3"
                                    @click="activeTab = 'tabExpenses'"
                                    :class="{ 'activeCard': activeTab === 'tabExpenses' }">
                                    <div class="statLabel">
                                        <div class="iconLabel"><img src="/wp-content/uploads/2025/02/total-expenses.svg" alt=""></div>
                                        <h4>Expenses</h4>
                                    </div>
                                    <div class="statInform">
                                        <div class="statIndicate">
                                            <h3 x-text="'$' + totalExpenses.toLocaleString()">$21,938.58</h3>
                                            <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                        </div>
                                        <p>About the same as usual</p>
                                    </div>
                                </div>
                                <!-- Gross Profit -->
                                <div class="analyticsStatCard col-3"
                                    @click="activeTab = 'tabGross'"
                                    :class="{ 'activeCard': activeTab === 'tabGross' }">
                                    <div class="statLabel">
                                        <div class="iconLabel"><img src="/wp-content/uploads/2025/08/grossprof.svg" alt=""></div>
                                        <h4>Gross Profit</h4>
                                    </div>
                                    <div class="statInform">
                                        <div class="statIndicate">
                                            <h3 x-text="'$' + totalGross.toLocaleString()">$28,800.61</h3>
                                            <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                        </div>
                                        <p>About the same as usual</p>
                                    </div>
                                </div>
                            </div>


                            <div x-show="activeTab === 'tabRevenue'">
                                <h3>Estimated Revenue</h3>
                            </div>
                            <div x-show="activeTab === 'tabExpenses'">
                                <h3>Expenses</h3>
                            </div>
                            <div x-show="activeTab === 'tabGross'">
                                <h3>Gross Profit</h3>
                            </div>

                            <div class="statGraph">
                                <div class="col-12">
                                    <canvas id="analyticsChartViews"></canvas>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="statDetailed">
                    <!-- <div class="realtimeRecord col-md-6">
                        <div class="row realtimeRecordNav">
                            <div class="col-md-6 d-flex justify-content-between" style="flex-basis: 60%;">
                                <div class="flex-grow-1 pe-4 border-end">
                                    <h4>Real Time</h4>
                                    <p>
                                        <img src="/wp-content/uploads/2025/08/realtimeicon.svg" alt="">
                                        Updating live
                                    </p>
                                </div>
                                <div class="ps-4">
                                    <h4>416,000</h4>
                                    <p>Subscribers</p>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex flex-column align-items-end" style="flex-basis: 40%;">
                                <button class="liveCountBtn"><a href="/coming-soon">See Live Count</a></button>
                            </div>
                        </div>
                        <div>
                            <div>
                                <h4>50,237</h4>
                                <div class="d-flex align-items-center gap-2">
                                    <span>Views</span>
                                    <i class="fa fa-circle" style="font-size:5px; color: #717171;"></i>
                                    <span>Last 48 hours</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <canvas id="realtimeChartViews"></canvas>
                        </div>
                        <div class="topContentInfo">
                            <table class="tbl-topContent">
                                <thead>
                                    <th>Top Content</th>
                                    <th>Views</th>
                                </thead>
                                <tbody>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div>
                                                <img src="/wp-content/uploads/2025/08/topcontentfallback.png" alt="">
                                            </div>
                                            <div>
                                                <h3>This is just a placeholder title that</h3>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h4>29,967</h4>
                                        </div>
                                    </td>
                                </tbody>
                            </table>
                        </div>
                    </div> -->
                    <div class="allTeam col-md-6">
                        <div class="allTeamNav row">
                            <div class="col-md-6">
                                <h4>Team Members</h4>
                                <p x-text="totalMembers + ' Total Team Members'"> Total Team Members</p>
                            </div>
                            <div class="col-md-6 d-flex flex-column align-items-end">
                                <button class="viewBtn">
                                    <a href="/team/">View All</a>
                                </button>
                            </div>
                        </div>
                        <div>
                            <table>
                                <thead>
                                    <th>
                                        Member
                                        <img src="/wp-content/uploads/2025/08/teamsort.svg" alt="Sort Icon">
                                    </th>
                                    <!-- <th>
                                        Country
                                        <img src="/wp-content/uploads/2025/08/teamsort.svg" alt="Sort Icon">
                                    </th> -->
                                    <th>
                                        Channel
                                        <img src="/wp-content/uploads/2025/08/teamsort.svg" alt="Sort Icon">
                                    </th>
                                </thead>
                                <tbody>
                                    <template x-for="member in members" :key="member.fullname">
                                        <tr>
                                            <!-- Member -->
                                            <td>
                                                <div class="perTeam">
                                                    <div>
                                                        <img :src="member.profile_photo" alt="" />
                                                    </div>
                                                    <div>
                                                        <h6 x-text="member.fullname"></h6>
                                                        <p x-text="member.role"></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Channel(s) -->
                                            <td>
                                                <template x-for="channel in member.channels" :key="channel.channel_name">
                                                    <div class="perTeamChannel">
                                                        <img :src="channel.channel_profile_photo" alt="">
                                                        <h6 x-text="channel.channel_name"></h6>
                                                    </div>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="recordDetailed col-md-6 d-flex flex-column gap-2" style="padding-right: 15px;">
                        <div class="perChannelMembers">
                            <div>
                                <h4 x-text="totalMembers"> Total</h4>
                                <p>Total Channels Members</p>
                            </div>
                            <div>
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
                            <div class="statCostNav">
                                <div class="col-md-6">
                                    <h4>Statistics</h4>
                                    <p>Average costs</p>
                                </div>
                                <!-- <div class="col-md-6 d-flex flex-column align-items-end">
                                    <button class="iconic" @click="open = !open">
                                        <img src="/wp-content/uploads/2025/08/blueFilter.svg" alt="Filter Icon" class="h-6 w-6">
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition class="drop-content">
                                        <div class="drop-div">
                                            <label>Date:</label>
                                            <input type="date">
                                        </div>
                                    </div>
                                </div> -->
                                <div class="table-filter col-md-6">
                                    <img src="/wp-content/uploads/2025/03/calendar.png" alt="">
                                    <select type="date" x-model="filterMonth" @change="renderChart">
                                        <option value="">Month</option>
                                        <template x-for="month in availableMonths" :key="month">
                                            <option :value="month" x-text="new Date(2000, month-1).toLocaleString('default', {month: 'long'})"></option>
                                        </template>
                                    </select>
                                    <select x-model="filterYear" @change="renderChart">
                                        <option value="">Year</option>
                                        <template x-for="year in availableYears" :key="year">
                                            <option :value="year" x-text="year"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 contractCost">
                                <span><img src="/wp-content/uploads/2025/08/contractoricon.svg" alt="" class="h-6 w-6">Contractor</span>
                                <div>
                                    <h3>$1,900</h3>
                                    <p>Average Contractor Cost</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="allTeam">
                    <div class="allTeamNav row">
                        <div class="col-md-6">
                            <h4>Team Members</h4>
                            <p>101 Total Team Members</p>
                        </div>
                        <div class="col-md-6 d-flex flex-column align-items-end">
                            <button class="viewBtn">
                                <a href="/team/">View All</a>
                            </button>
                        </div>
                    </div>
                    <div>
                        <table>
                            <thead>
                                <th>
                                    Member
                                    <img src="/wp-content/uploads/2025/08/teamsort.svg" alt="Sort Icon">
                                </th>
                                <th>
                                    Country
                                    <img src="/wp-content/uploads/2025/08/teamsort.svg" alt="Sort Icon">
                                </th>
                                <th>
                                    Channel
                                    <img src="/wp-content/uploads/2025/08/teamsort.svg" alt="Sort Icon">
                                </th>
                            </thead>
                            <tbody>
                                <td>
                                    <div class="perTeam">
                                        <div>
                                            <img src="/wp-content/uploads/2025/08/teamFallback.svg" alt="">
                                        </div>
                                        <div>
                                            <h6>Screen Name</h6>
                                            <p>Role here</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="perTeamCountry">
                                        <h6>U.S.A</h6>
                                    </div>
                                </td>
                                <td>
                                    <div class="perTeamChannel">
                                        <img src="/wp-content/uploads/2025/07/channelbtnfallback.svg" alt="">
                                        <h6>Channel Name</h6>
                                    </div>
                                </td>
                            </tbody>
                        </table>
                    </div>
                </div> -->

            </main>
        </div>
    </div>
</div>
<script>
    function statsCard() {
        return {
            open: false,
            activeTab: 'tabViews',
            isCombined: true,
            selectedChannels: 3,
            selectedDaterange: 7,
            channelOptions: ['3', '4', '5'],
            filteredData: [],
            totalViews: 0,
            totalRevenue: 0,
            totalExpenses: 0,
            totalGross: 0,
            handleChannelChange() {
                this.renderAnalyticsChart();
            },
            handleDateRangeChange() {
                this.renderAnalyticsChart();
            },
            data: [],
            roles: [],
            totalRoles: 0,
            totalMembers: 0,
            roles: {},
            members: [],
            init() {
                console.log('laoding');

                try {
                    const raw = document.getElementById('insight-data').textContent;
                    console.log('roles', raw);
                    this.data = JSON.parse(raw);
                    console.log('data', this.data);

                } catch (e) {
                    this.data = [];
                    console.error("Failed to parse JSON:", e);
                }
                try {
                    const raw = document.getElementById('roles').textContent;

                    this.roles = JSON.parse(raw);

                    this.totalRoles = Object.values(this.roles).reduce((sum, count) => sum + count, 0);
                } catch (e) {
                    this.roles = [];
                    console.error("Failed to parse JSON:", e);
                }

                try {
                    if (window.teamData) {
                        this.totalMembers = window.teamData.totalMembers ?? 0;
                        this.roles = window.teamData.roles ?? {};
                        this.members = window.teamData.members ?? [];
                    } else {
                        console.warn("teamData not found in window.");
                    }
                } catch (error) {
                    console.error(" Failed to initialize team data:", error);
                    // fallback defaults
                    this.totalMembers = 0;
                    this.roles = {};
                    this.members = [];
                }

                this.handleChannelChange(); // optionally call on init

                // Watch for changes in activeTab
                this.$watch('activeTab', (value) => {
                    this.renderAnalyticsChart();
                });
                this.$watch('isCombined', (value) => {
                    this.renderAnalyticsChart();
                });

                this.renderRealtimeChartViews();

            },
            insightDataFilter() {
                this.totalViews = 0;
                this.totalRevenue = 0;
                this.totalExpenses = 0;
                this.totalGross = 0;
                const days = parseInt(this.selectedDaterange);
                const now = new Date();
                const threshold = new Date(now.setDate(now.getDate() - days));

                this.totalViews = 0;
                this.totalRevenue = 0;
                this.totalExpenses = 0;
                this.totalGross = 0;

                this.filteredData = this.data.reduce((acc, item) => {
                    if (!item.date) return acc;

                    const [day, month, year] = item.date.split('/');
                    const itemDate = new Date(`${year}-${month}-${day}`);
                    if (itemDate < threshold) return acc;

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
                const ctx = canvas.getContext("2d"); //Get 2D context here


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

                if (Chart.getChart("analyticsChartViews")) {
                    Chart.getChart("analyticsChartViews").destroy();
                }

                let labels = [];
                let value = [];

                if (this.isCombined) {


                    // First pass: collect all unique dates
                    this.data.forEach(item => {
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

                    //Sort labels chronologically
                    labels.sort((a, b) => a.date - b.date);

                    // Extract final label strings
                    const labelStrings = labels.map(l => l.label);

                    // Second pass: fill channel values
                    this.data.forEach(item => {
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
                        const baseColor = this.getBlueShade(index, Object.keys(value).length);
                        const gradient = this.createGradient(ctx, baseColor);

                        return {
                            label: channel,
                            data: value[channel],
                            borderWidth: 2,
                            fill: true,
                            backgroundColor: gradient,
                            borderColor: baseColor,
                        };
                    });

                    data = {
                        labels: labelStrings,
                        datasets: datasets
                    };

                    // data = {
                    //     labels: labels,
                    //     datasets: [{
                    //             label: 'Channel #1',
                    //             data: channel1,
                    //             backgroundColor: gradient1,
                    //             borderColor: '#2194FF',
                    //             fill: true,
                    //             tension: 0.3,
                    //             pointRadius: 0,
                    //             stack: 'stack1',
                    //         },
                    //         {
                    //             label: 'Channel #2',
                    //             data: channel2,
                    //             backgroundColor: gradient2,
                    //             borderColor: '#70B0FF',
                    //             fill: true,
                    //             tension: 0.3,
                    //             pointRadius: 0,
                    //             stack: 'stack1',
                    //         },
                    //         {
                    //             label: 'Channel #3',
                    //             data: channel3,
                    //             backgroundColor: gradient3,
                    //             borderColor: '#B4D2FF',
                    //             fill: true,
                    //             tension: 0.3,
                    //             pointRadius: 0,
                    //             stack: 'stack1',
                    //         }
                    //     ]
                    // };

                } else {
                    // If individual, use only channel 1
                    this.data.forEach(item => {
                        if (!item.date) return;

                        // Format date: "29/04/2025" => "April 29, 2025"
                        const [day, month, year] = item.date.split('/');
                        const dateObj = new Date(`${year}-${month}-${day}`);
                        const formattedDate = dateObj.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
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
                            label: 'Channel',
                            data: value,
                            backgroundColor: gradient1,
                            borderColor: '#2194FF',
                            fill: true,
                            tension: 0.3,
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
                const ctx = canvas.getContext("2d"); // Get 2D context here

                const labels = ['Jan 27', 'Feb 1', 'Feb 5', 'Feb 10', 'Feb 14', 'Feb 19', 'Feb 23'];
                const channel1 = [30_000, 50_000, 70_000, 60_000, 65_000, 80_000, 85_000];


                // if (!finalRevenue.length || !finalExpenses.length) {
                //     ctx.clearRect(0, 0, canvas.width, canvas.height);
                //     ctx.font = '16px Arial';
                //     ctx.textAlign = 'center';
                //     ctx.fillText('No data found', canvas.width / 2, canvas.height / 2);
                //     return;
                // }

                // if (Chart.getChart("viewsChart")) {
                //     Chart.getChart("viewsChart").destroy();
                // }

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

                // if (!finalRevenue.length || !finalExpenses.length) {
                //     ctx.clearRect(0, 0, canvas.width, canvas.height);
                //     ctx.font = '16px Arial';
                //     ctx.textAlign = 'center';
                //     ctx.fillText('No data found', canvas.width / 2, canvas.height / 2);
                //     return;
                // }

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
        };
    }
</script>
<style>
    .main-content {
        padding: 56px 64px;
    }

    .header-db {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 100px;
    }

    .titleNav {
        width: 900px;
    }

    .channelSelectFilter {
        width: 230px;
    }

    .channelSelectFilter select {
        padding: 14px 12px;
        border: solid 1px transparent !important;
        background-color: transparent;
        color: #2194FF;
        font-size: 16px;
        font-weight: 700;
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
    }

    .analyticsStat {
        padding: 24px 24px 40px 24px;
        background-color: #FFFFFF;
        border-radius: 16px;
        margin-bottom: 16px;
    }

    .analyticsStat .analyticsNav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-bottom: 40px;
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
        padding: 5px;
        background-color: #FFEE94;
        width: 30px;
        height: 30px;
        object-fit: contain;
    }

    .analyticsStatCard .iconLabel img {
        border-radius: 50%;
        padding: 5px;
        background-color: transparent;
        width: 30px;
        height: 30px;
        object-fit: contain;
    }

    .activeCard {
        border: 1px solid #2194FF;
        background-color: #F5FAFF;
    }

    .activeCard .iconLabel img {
        border-radius: 50%;
        padding: 5px;
        background-color: #FFEE94;
        width: 30px;
        height: 30px;
        object-fit: contain;
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
        font-size: 12px;
        font-weight: 400;
        color: #717171;
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
        gap: 8px
    }

    .switchNav {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
    }

    /* The switch - the box around the slider */
    .switchNav .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    /* Hide default HTML checkbox */
    .switchNav .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .switchNav .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        /* background-color: #ccc; */
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
        /* background-color: #2196F3; */
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

    /* Rounded sliders */
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
        overflow: hidden;
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

    .statCostNav {
        display: flex;
        flex-direction: row;
        align-items: center;
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
        width: 24px;
        height: 24px;
        object-fit: contain;
    }

    .allTeam table thead img {
        margin-left: 4px;
        width: 12px;
        height: 12px;
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

    .table-filter {
        display: flex;
        flex-direction: row;
        align-items: center;
        border: solid 1px #D0D0D0;
        border-radius: 4px;
        padding: 10px;
        gap: 10px;
        height: 50px;
    }

    .table-filter select {
        border: unset;
        padding: 0;
    }

    .table-filter img {
        width: 12px;
        height: 13px;
    }
</style>