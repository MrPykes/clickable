<?php
global $post;
get_header('admin');

// Channels Data
// $channels_data = [];
// $current_status = '';
// $args = array(
//     'post_type'      => 'channel',
//     // 's'              => $channel ?? '',
//     'posts_per_page' => -1,
//     'post_status'    => 'publish',
// );
// $channels_query = new WP_Query($args);

// if ($channels_query->have_posts()) {
//     while ($channels_query->have_posts()) {
//         $channels_query->the_post();

//         $status_field   = get_field_object('channel_status');
//         $status_choices = $status_field ? $status_field['choices'] : [];
//         $current_status = get_field('channel_status');

//         $image = get_field('channel_profile_photo');
//         $image_url = $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg';

//         $channels_data[] = [
//             'id'     => get_the_ID(),
//             'name'   => get_field('channel_name'),
//             'desc'   => get_field('channel_description'),
//             'status' => $current_status,
//             'image'  => $image_url,
//         ];
//     }
// }

// $new_row = array(
//     'date_create_insight' => date('Y-m-d'), // YYYY-MM-DD
//     'channel_views'       => 2,
//     'channel_subscribers' => 1,
//     'channel_videos'      => 32,
//     'revenue_amount'      => 142,
// );


// $post_id = 3431;
// $field_key = 'your_channel_insights';

// $tasks_channel_insights = get_field($field_key, $post_id); // may return array or false

// if (! $tasks_channel_insights) {
//     // no existing value yet
//     update_field($field_key, array($new_row), $post_id);
// } else {
//     // ensure it's an array before merging
//     if (! is_array($tasks_channel_insights)) {
//         $tasks_channel_insights = array();
//     }

//     // append new data as another row
//     $tasks_channel_insights[] = $new_row;

//     // save back
//     update_field($field_key, $tasks_channel_insights, $post_id);
// }



wp_reset_postdata();

if (have_posts()) {
    while (have_posts()) {
        the_post();

        $status_field   = get_field_object('channel_status');
        $status_choices = $status_field ? $status_field['choices'] : [];
        $current_status = get_field('channel_status');

        $image = get_field('channel_profile_photo');
        $image_url = $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg';

        $channels_data[] = [
            'id'     => get_the_ID(),
            'name'   => get_field('channel_name'),
            'desc'   => get_field('channel_description'),
            'status' => $current_status,
            'image'  => $image_url,
        ];
    }
}
wp_reset_postdata();

// echo '<pre>';
// print_r($channels_data);
// echo '</pre>';
// Team Data (members & non-members)
$current_channel_id = get_the_ID();
$members     = [];
$non_members = [];

$team_query = new WP_Query([
    'post_type'      => 'team',
    'posts_per_page' => -1,
    'meta_query'     => [
        [
            'key'     => 'contractor_status',
            'value'   => 'Active',
            'compare' => '='
        ]
    ]
]);

if ($team_query->have_posts()) {
    while ($team_query->have_posts()) {
        $team_query->the_post();

        $fullname = get_field('fullname');
        if (!$fullname) {
            continue;
        }

        $image         = get_field('profile_photo');
        $your_channels = get_field('your_channel');
        $channel_names = [];
        $is_member     = false;

        if ($your_channels) {
            foreach ($your_channels as $channel_post_id) {
                $channel_names[] = get_field('channel_name', $channel_post_id);
                if ((int)$channel_post_id === $current_channel_id) {
                    $is_member = true;
                }
            }
        }

        $image = get_field('profile_photo');
        $image_url = '';

        if (is_array($image) && isset($image['sizes']['medium'])) {
            $image_url = $image['sizes']['medium'];
        } else {
            $image_url = '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg';
        }

        $team_data = [
            'id'          => get_the_ID(),
            'name'        => $fullname,
            'yourchannel' => $channel_names,
            'role'        => get_field('role'),
            'status'      => get_field('contractor_status'),
            'image'       => $image_url,
            'link'        => get_permalink(),
        ];


        if ($is_member) {
            $members[] = $team_data;
        } else {
            $non_members[] = $team_data;
        }
    }
}
wp_reset_postdata();

$team_data = [
    'members'     => $members,
    'non_members' => $non_members,
];

// Insights 
$insights = [];
$current_channel = get_the_ID();
$channel_name = get_field('channel_name', $current_channel);

$insight_query = new WP_Query([
    'post_type'      => 'channel-insight',
    'posts_per_page' => -1,
    'orderby'        => 'date_create',
    'order'          => 'ASC',
    'meta_query'     => [
        [
            'key'     => 'channel_name',
            'value'   => $channel_name,
            'compare' => '='
        ]
    ]
]);

if ($insight_query->have_posts()) {
    while ($insight_query->have_posts()) {
        $insight_query->the_post();
        $insight_id = get_the_ID();
        // $date_create = get_field('date_create');
        // $insights['allData'][] = [
        //     'idy'         => $insight_id,
        //     'views'       => (int) get_field('channel_views') ?: 0,
        //     'revenue'     => (float) get_field('revenue_amount') ?: 0,
        //     'subscribers' => (int) get_field('channel_subscribers') ?: 0,
        //     'videos' => (int) get_field('channel_videos') ?: 0,
        //     'expenses' => (int) get_field('channel_expense_amount') ?: 0,
        //     'date'        => $date_create
        // ];
        $channel_insights = get_field('your_channel_insights', $insight_id);

        if ($channel_insights) {
            foreach ($channel_insights as $row) {
                $date_create   = $row['date_create_insight'];
                $views         = $row['channel_views'];
                $subs          = $row['channel_subscribers'];
                $videos        = $row['channel_videos'];
                $revenue       = $row['revenue_amount'];
            }
        }


        $channel_expenses = get_field('your_channel_expenses');

        $channel_expenses_amount = 0;
        if ($channel_expenses) {
            foreach ($channel_expenses as $row) {
                $date        = $row['channel_expense_date'];
                $description = $row['channel_expenses_description'];
                $amount      = $row['channel_expense_amount'];

                $channel_expenses_amount += (float) $amount;
            }
        }

        $insights['allData'][] = [
            'idy'         => $insight_id,
            'views'       => (int) $views ?: 0,
            'revenue'     => (float) $revenue ?: 0,
            'subscribers' => (int) $subs ?: 0,
            'videos'      => (int) $videos ?: 0,
            'expenses'   => (int) $channel_expenses_amount ?: 0,
            'date'        => $date_create
        ];
    }
}
wp_reset_postdata();


//  All Channels
$channels_data = [];
$channels_query = new WP_Query([
    'post_type'      => 'channel',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);

if ($channels_query->have_posts()) {
    while ($channels_query->have_posts()) {
        $channels_query->the_post();
        $image = get_field('channel_profile_photo');
        $image_url = (!empty($image) && is_array($image) && isset($image['sizes']['medium']))
            ? $image['sizes']['medium']
            : '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg';

        $channels_data[] = [
            'id'     => get_the_ID(),
            'name'   => get_field('channel_name'),
            'desc'   => get_field('channel_description'),
            'status' => get_field('channel_status'),
            'channelUrl' => get_field('channel_url'),
            'image'  => $image_url,
        ];
    }
}
wp_reset_postdata();
// echo '<pre>';
// print_r($insights);
// echo '</pre>';
?>
<script type="application/json" id="channels-data">
    <?php echo wp_json_encode($channels_data); ?>
</script>
<script type="application/json" id="team-data">
    <?php echo wp_json_encode($team_data); ?>
</script>
<script type="application/json" id="channel-status">
    <?php echo wp_json_encode($current_status); ?>
</script>
<script type="application/json" id="insights-data">
    <?php echo wp_json_encode($insights); ?>
</script>


<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar'); ?>
    <div class="main-content">
        <div class="view-channel-section" x-data="channelInsights">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/channel/">CHANNELS</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo get_field('channel_name') ?></li>
                </ol>
            </nav>
            <div>
                <div class="channel-nav">
                    <div class="view-channel-desc">
                        <h1><?php echo get_field('channel_name') ?></h1>
                        <p><?php echo get_field('channel_description') ?></p>
                    </div>
                    <div class="channelNavBtn">
                        <button class="btn-edit" @click="showModal = true">
                            <i class="fa-solid fa-edit"></i>
                            Edit Channel
                        </button>
                    </div>
                </div>
                <!--  Edit Function Modal -->
                <div class="hidden confirmation-modal" x-show="showModal"
                    x-transition x-bind:class="{ 'hidden': !showModal }" x-data="channelDetails()">
                    <div class="modal-content">
                        <div>
                            <h3>Update Channel?</h3>
                            <div style="display: flex; flex-direction: row; justify-content: space-between;">
                                <div style="display: flex; flex-direction: row; column-gap: 50px; align-items: center; ">
                                    <div>
                                        <label class="input-label">Status:</label>
                                        <p style="color: #008000;" x-text="currentStatus">
                                            <!-- <php echo get_field("channel_status", get_the_ID()); ?> -->
                                        </p>
                                        <select x-show="statusOpt" x-transition x-model="currentStatus">
                                            <option value="Active">Active</option>
                                            <option value="Archived">Archived</option>
                                        </select>
                                    </div>
                                    <button style="border: solid 1px #2194FF;" @click="statusOpt = true">
                                        <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                    </button>
                                </div>
                                <button class="btn-dlt" @click="confirmDelete(<?php echo get_the_ID(); ?>)">
                                    <i class="fa-solid fa-xmark"></i> Delete Channel
                                </button>
                            </div>
                        </div>
                        <!-- Edit Form -->
                        <div class="formCont">
                            <form>
                                <input type="hidden" name="post_id" value="<?php echo get_the_ID() ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="input-label">Channel Name</label>
                                            <input type="text" class="form-control"
                                                x-model="channelName"
                                                :placeholder="channelName">
                                        </div>
                                        <div class="mb-3">
                                            <label class="input-label">Description</label>
                                            <textarea rows="4"
                                                x-model="channelDescription"
                                                :placeholder="channelDescription"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="input-label">URL:</label>
                                            <input type="url"
                                                x-model="channelUrl"
                                                :placeholder="channelUrl">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="display:flex; flex-direction:column; gap:10px;">
                                        <h4>Add New Insights</h4>
                                        <div class="mb-3">
                                            <label class="input-label">Date:</label>
                                            <input type="date" @click="$el.showPicker && $el.showPicker()" name="date_create">
                                        </div>
                                        <div class="mb-3">
                                            <label class="input-label">Views:</label>
                                            <input type="number" placeholder="Enter channel views" name="channel_views">
                                        </div>
                                        <div class="mb-3">
                                            <label class="input-label">Subscribers:</label>
                                            <input type="number" placeholder="Enter channel subscribers" name="channel_subscribers">
                                        </div>
                                        <div class="mb-3">
                                            <label class="input-label">Videos:</label>
                                            <input type="number" placeholder="Enter channel videos" name="channel_videos">
                                        </div>
                                        <div class="mb-3">
                                            <label class="input-label">Revenue:</label>
                                            <input type="number" placeholder="Enter channel revenue" name="revenue_amount">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="input-label">Channel Profile Photo</label>
                                    <input type="file" @change="uploadImage">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="input-label">Assign New Contractor</label>
                                        <select class="form-control" multiple x-model="selectedContractors">
                                            <template x-for="member in nonMembers" :key="member.id">
                                                <option :value="member.id" x-text="member.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-md-6" x-show="selectedContractors.length > 0">
                                        <label class="input-label">Selected Contractors</label>
                                        <!-- <input type="text" class="form-control" readonly :value="selectedContractorNames"> -->
                                        <textarea :value="selectedContractorNames" rows="4" readonly></textarea>
                                    </div>
                                </div>

                                <div class="modal-actions">
                                    <button @click="submitConfirmed" class="confirm-btn">Save Changes</button>
                                    <button type="button" @click="showModal = false" class="cancel-btn">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Channel Insights -->
            <div class="channel-info">
                <div class="head-info">
                    <h2 style="font-size: 16px; font-weight: 700;">Channel Information</h2>
                    <template x-if="Array.isArray(availableMonths) && availableMonths.length">
                        <div class="tbl-date-filter">
                            <img src="/wp-content/uploads/2025/03/calendar.png" alt="">
                            <div class="relative">
                                <button class="tbl-select" @click="showMonthDropdown = !showMonthDropdown; showYearDropdown = false" x-text="selectedMonth"></button>

                                <ul x-show="showMonthDropdown" @click.outside="showMonthDropdown = false" class="drop">
                                    <!-- <template x-for="month in availableMonths" :key="month">
                                    <li>
                                        <button
                                            @click="
                                                selectedMonth = new Date(2000, month-1).toLocaleString('default', { month: 'long' });
                                                filterMonth = month;
                                                showMonthDropdown = false;
                                                getMonthYearFilteredData();"
                                            x-text="new Date(2000, month-1).toLocaleString('default', { month: 'long' })"></button>
                                    </li>
                                </template> -->
                                    <template x-if="Array.isArray(availableMonths) && availableMonths.length">
                                        <template x-for="month in availableMonths" :key="month">
                                            <li>
                                                <button
                                                    @click="
            selectedMonth    = new Date(2000, month - 1).toLocaleString('default', { month: 'long' });
            filterMonth      = month;
            showMonthDropdown = false;
            getMonthYearFilteredData();
          "
                                                    x-text="new Date(2000, month - 1).toLocaleString('default', { month: 'long' })"></button>
                                            </li>
                                        </template>
                                    </template>
                                </ul>
                            </div>
                            <div class="relative">
                                <button class="tbl-select" @click="showYearDropdown = !showYearDropdown; showMonthDropdown = false" x-text="selectedYear"></button>
                                <ul x-show="showYearDropdown" @click.outside="showYearDropdown = false" class="drop">
                                    <template x-for="year in availableYears" :key="year">
                                        <li>
                                            <button
                                                @click="
                                                selectedYear = year;
                                                filterYear = year;
                                                showYearDropdown = false;
                                                getMonthYearFilteredData();"
                                                x-text="year"></button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                </div>
                <form method="POST" x-on:submit.prevent="submitForm($event)">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
                    <input type="hidden" name="update_insights" value="1">
                    <input type="hidden" name="post_id" value="<?php echo get_the_ID() ?>">
                    <!-- <input type="hidden" name="insight_id" x-bind:value="insights.idy"> -->
                    <input type="hidden" name="insight_id" x-model="getFilteredTableTotals().postIds">
                    <input type="hidden" name="filter_month" x-model="filterMonth">
                    <input type="hidden" name="filter_year" x-model="filterYear">
                    <?php wp_nonce_field('update_insights', 'update_insights_nonce'); ?>

                    <!-- Save changes modal -->
                    <div x-show="showSaveConfirmation" x-data="{ showModal: false }"
                        class=" hidden confirmation-modal" x-bind:class="{ 'hidden': !showSaveConfirmation }">
                        <div class="modal-content">
                            <h3>Confirm Save</h3>
                            <p>Are you sure you want to save these changes?</p>
                            <div class="modal-actions">
                                <button @click="submitConfirmed" class="confirm-btn">Save Changes</button>
                                <button @click="cancelEdit" class="cancel-btn">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <table class="tbl-insights">
                            <tr>
                                <td>Views</td>
                                <td class="insights-data">
                                    <template x-if="!editing.views">
                                        <span class="tbl-views" x-text="getFilteredTableTotals().totalViews.toLocaleString()"></span>
                                    </template>
                                    <input x-show="editing.views" type="number" name="channel_views"
                                        x-model="filteredTableData ? filteredTableData[0].views : insights.totalViews"
                                        @blur.prevent="confirmSave($event, 'views')"
                                        @keyup.enter.prevent="confirmSave($event, 'views')">
                                    <div class="edits-icon" x-show="isMonthYearSelected()" @click="editing.views = true">
                                        <!-- <div class="edits-icon" @click="editing.views = true"> -->
                                        <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Revenue</td>
                                <td class="insights-data">
                                    <template x-if="!editing.revenue">
                                        <span class="tbl-revenue" x-text="'$' + (getFilteredTableTotals().totalRevenue.toLocaleString())"></span>
                                    </template>
                                    <input x-show="editing.revenue" type="number" name="revenue_amount"
                                        x-model="filteredTableData ? filteredTableData[0].revenue : insights.totalRevenue"
                                        @blur.prevent="confirmSave($event, 'revenue')"
                                        @keyup.enter.prevent="confirmSave($event, 'revenue')">
                                    <div class="edits-icon" x-show="isMonthYearSelected()" @click="editing.revenue = true">
                                        <!-- <div class="edits-icon" @click="editing.revenue = true"> -->
                                        <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Subscriber Gain</td>
                                <td class="insights-data">
                                    <template x-if="!editing.subscribers">
                                        <span class="tbl-subscribers" x-text="getFilteredTableTotals().totalSubscribers.toLocaleString()"></span>
                                    </template>
                                    <input x-show="editing.subscribers" type="number" name="channel_subscribers"
                                        x-model="filteredTableData ? filteredTableData[0].subscribers : insights.totalSubscribers"
                                        @blur.prevent="confirmSave($event, 'subscribers')"
                                        @keyup.enter.prevent="confirmSave($event, 'subscribers')">
                                    <div class="edits-icon" x-show="isMonthYearSelected()" @click="editing.subscribers = true">
                                        <!-- <div class="edits-icon" @click="editing.subscribers = true"> -->
                                        <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Channel Expenses</td>
                                <td class="insights-data">
                                    <template x-if="!editing.expenses">
                                        <span class="tbl-expenses" x-text="getFilteredTableTotals().totalExpenses.toLocaleString()"></span>
                                    </template>
                                    <!-- <input x-show="editing.expenses" type="number" name="channel_expense_amount"
                                        x-model="filteredTableData ? filteredTableData[0].expenses : insights.totalExpenses"
                                        @blur.prevent="confirmSave($event, 'expenses')"
                                        @keyup.enter.prevent="confirmSave($event, 'expenses')"> -->
                                    <!-- <div class="edits-icon" @click="editing.expenses = true">
                                        <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                    </div> -->
                                </td>
                            </tr>
                            <tr>
                                <td>Channel Videos</td>
                                <td class="insights-data">
                                    <template x-if="!editing.videos">
                                        <span class="tbl-videos" x-text="getFilteredTableTotals().totalVideos.toLocaleString()"></span>
                                    </template>
                                    <input x-show="editing.videos" type="number" name="channel_videos"
                                        x-model="filteredTableData ? filteredTableData[0].videos : insights.totalVideos"
                                        @blur.prevent="confirmSave($event, 'videos')"
                                        @keyup.enter.prevent="confirmSave($event, 'videos')">
                                    <div class="edits-icon" @click="editing.videos = true">
                                        <img src="/wp-content/uploads/2025/02/edit.svg" alt="">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
            </div>

            <!-- Stats Cards -->
            <div>
                <div class="row mt-4 stats-row">
                    <div class="col-md-4">
                        <!-- Total Views -->
                        <div class="card" @click="activeTab = 'views'"
                            :class="activeTab === 'views' ? 'border border-primary' : ''">
                            <div class="card-body">
                                <h5 class="card-title d-flex">
                                    <div class="icon"><img src="/wp-content/uploads/2025/02/views.svg" alt=""></div>
                                    Total Views
                                </h5>
                                <h2 class="card-text" x-text="insights.totalViews.toLocaleString()"></h2>
                                <p x-text="viewGrowthPercentage.percentage + ' This month'"> This month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Total Subscribers -->
                        <div class="card" @click="activeTab = 'subscribers'"
                            :class="activeTab === 'subscribers' ? 'border border-primary' : ''">
                            <div class="card-body">
                                <h5 class="card-title d-flex">
                                    <div class="icon"><img src="/wp-content/uploads/2025/02/subscriber.svg" alt=""></div>
                                    Total Subscribers
                                </h5>
                                <h2 class="card-text" x-text="insights.totalSubscribers.toLocaleString()"></h2>
                                <p x-text="subscribersGrowthPercentage.percentage + ' This month'">+0% This month</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Total Revenue -->
                        <div class="card" @click="activeTab = 'revenue'"
                            :class="activeTab === 'revenue' ? 'border border-primary' : ''">
                            <div class="card-body">
                                <h5 class="card-title d-flex">
                                    <div class="icon"><img src="/wp-content/uploads/2025/02/total-revenue.svg" alt=""></div>
                                    Total Revenue
                                </h5>
                                <h2 class="card-text" x-text="'$'+ insights.totalRevenue.toLocaleString()"></h2>
                                <p x-text="revenueGrowthPercentage.percentage + ' This month'">+0% This month</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Views Section -->
                <div class="graph-container" id="graphContainer">
                    <div class=" total-graph" x-show="activeTab === 'views'">
                        <div class="head-info">
                            <h2 style="font-size: 16px; font-weight: 700; color: #101010;">Views</h2>
                            <div class="date-rev-range">
                                <input type="date" @click="$el.showPicker && $el.showPicker()" id="startDatePicker" class="rev-range" x-model="startDate" @change="drawChart"> -
                                <input type="date" @click="$el.showPicker && $el.showPicker()" id="lastDatePicker" class="rev-range" x-model="lastDate" @change="drawChart">
                            </div>
                        </div>
                        <div class="container-fluid mt-4 p-0">
                            <div class="row p-0">
                                <div class="col-md">
                                    <canvas id="viewChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- subscriber section -->
                <div class="graph-container" id="graphContainer">
                    <div class="total-graph" x-show="activeTab === 'subscribers'">
                        <div class="head-info">
                            <h2 style="font-size: 16px; font-weight: 700; color: #101010;">Subscribers</h2>
                            <div class="date-rev-range">
                                <input type="date" @click="$el.showPicker && $el.showPicker()" id="startDatePicker" class="rev-range" x-model="startDate" @change="drawChart"> -
                                <input type="date" @click="$el.showPicker && $el.showPicker()" id="lastDatePicker" class="rev-range" x-model="lastDate" @change="drawChart">
                            </div>
                        </div>
                        <div class="container-fluid mt-4 p-0">
                            <div class="row p-0">
                                <div class="col-md">
                                    <canvas id="subscriberChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- revenue section -->
                <div class="graph-container" id="graphContainer">
                    <div class="total-graph" x-show="activeTab === 'revenue'">
                        <div class="head-info">
                            <h2 style="font-size: 16px; font-weight: 700; color: #101010;">Revenue</h2>
                            <div class="date-rev-range">
                                <input type="date" @click="$el.showPicker && $el.showPicker()" id="startDatePicker" class="rev-range" x-model="startDate" @change="drawChart"> -
                                <input type="date" @click="$el.showPicker && $el.showPicker()" id="lastDatePicker" class="rev-range" x-model="lastDate" @change="drawChart">
                            </div>
                        </div>
                        <div class="container-fluid mt-4 p-0">
                            <div class="row p-0">
                                <div class="col-md">
                                    <canvas id="revenChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Contractors section -->
            <div class="the-contractors" x-data="teamFilter()">
                <div class="cont-nav">
                    <h2 style="font-size:16px; font-weight: 700; color: #101010;">Contractors</h2>
                    <div class="contractor-navoption">
                        <button @click="open = !open" class="iconic">
                            <img src="/wp-content/uploads/2025/04/sidebar-ddown.svg" alt="Filter Icon" class="h-6 w-6">
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="drop-content">
                            <div class="drop-div" x-data="{ statusDropdown: false }">
                                <label class="drop-title">Status:</label>
                                <button class="drop-option2 w-full text-left flex justify-between items-center" @click="statusDropdown = !statusDropdown">
                                    <span x-text="selectedStatus || 'All'"></span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul class="drop-option3 mt-1" x-show="statusDropdown" x-transition @click.away="statusDropdown = false">
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedStatus === 'All' ? 'active' : ''"
                                            @click="selectedStatus = ''; filterItems(); statusDropdown = false">
                                            All
                                        </button>
                                    </li>
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedStatus === 'Active' ? 'active' : ''"
                                            @click="selectedStatus = 'Active'; filterItems(); statusDropdown = false">
                                            Active
                                        </button>
                                    </li>
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedStatus === 'Archived' ? 'active' : ''"
                                            @click="selectedStatus = 'Archived'; filterItems(); statusDropdown = false">
                                            Archived
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="drop-div" x-data="{ roleDropdown: false }">
                                <label class="drop-title">Role:</label>
                                <button class="drop-option2 w-full text-left flex justify-between items-center" @click="roleDropdown = !roleDropdown">
                                    <span x-text="selectedRole || 'All'"></span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul class="drop-option4 mt-1" x-show="roleDropdown" x-transition @click.away="roleDropdown = false">
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedRole === 'All' ? 'active' : ''"
                                            @click="selectedRole = ''; filterItems(); roleDropdown = false">
                                            All
                                        </button>
                                    </li>
                                    <template x-for="role in roles" :key="role">
                                        <li>
                                            <button
                                                class="btn w-full text-left"
                                                :class="selectedRole === role ? 'active' : ''"
                                                @click="selectedRole = role; filterItems(); roleDropdown = false"
                                                x-text="role">
                                            </button>
                                        </li>
                                    </template>
                                    <!-- <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedRole === 'Scriptwriter' ? 'active' : ''"
                                            @click="selectedRole = 'Scriptwriter'; filterItems(); statusDropdown = false">
                                            Scriptwriter
                                        </button>
                                    </li>
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedRole === 'Editor' ? 'active' : ''"
                                            @click="selectedRole = 'Editor'; filterItems(); statusDropdown = false">
                                            Editor
                                        </button>
                                    </li>
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedRole === 'Voiceover Artist' ? 'active' : ''"
                                            @click="selectedRole = 'Voiceover Artist'; filterItems(); statusDropdown = false">
                                            Voiceover Artist
                                        </button>
                                    </li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contractors-grid">
                    <template x-for="member in paginatedMembers" :key="member.id">
                        <a :href="member.link">
                            <div>
                                <div class="each-contractor">
                                    <div>
                                        <img :src="member.image" alt="Profile Photo" class="cont-photo">
                                    </div>
                                    <div class="details">
                                        <h3 x-text="member.name" class="name"></h3>
                                        <p x-text="member.role" class="role"></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
                <!-- Pagination Controls -->
                <div class="pagination-controls">
                    <button class="prev-btn" @click="prevPage" :disabled="currentPage === 1">
                        <img class="prev-icon" src="/wp-content/uploads/2025/02/Vector-5.svg" alt="Previous"> Previous
                    </button>
                    <template x-for="page in totalPages" :key="page">
                        <button class="btn" :class="currentPage === page ? 'active' : ''" @click="currentPage = page" x-text="page"></button>
                    </template>
                    <button class="nxt-btn" @click="nextPage" :disabled="currentPage === totalPages">Next
                        <img class="next-icon" src="/wp-content/uploads/2025/03/next.png" alt="Next">
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.querySelectorAll(".dropdown-item").forEach(item => {
        item.addEventListener("click", function() {
            document.getElementById("dropdownButton").innerText = this.getAttribute("data-value");
        });
    });

    var ajax_object = {
        ajaxurl: "<?php echo admin_url('admin-ajax.php'); ?>",
        update_insights_nonce: "<?php echo wp_create_nonce('ajax_nonce'); ?>"
    };

    function channelDetails() {
        return {
            statusOpt: false,
            allChannels: [],
            currentStatus: [],
            teamData: [],

            currentStatus: "<?php echo esc_js($current_status); ?>",
            init() {
                try {
                    this.allChannels = JSON.parse(document.getElementById("channels-data").textContent);
                    this.currentStatus = JSON.parse(document.getElementById("channel-status").textContent);
                    this.teamData = JSON.parse(document.getElementById("team-data").textContent);
                } catch (e) {
                    console.error("Failed to parse channel/team JSON:", e);
                    this.allChannels = [];
                    this.currentStatus = [];
                    this.teamData = [];
                }
            },
            // Delete Button but it will be archived and draft
            confirmDelete(channelId) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you really want to delete channel <?php echo get_field('channel_name') ?>? You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    customClass: {
                        confirmButton: 'my-confirm-btn',
                        cancelButton: 'my-cancel-btn'
                    },
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.archiveChannel(channelId);
                        Swal.fire({
                            title: "Deleted!",
                            text: "Channel has been deleted.",
                            icon: "success",
                            confirmButtonText: "OK",
                            customClass: {
                                confirmButton: 'okay-confirm-btn'
                            }
                        }).then(() => {
                            Swal.fire({
                                title: "Redirecting...",
                                text: "Please wait...",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                this.showModal = false;
                                window.location.href = "/channels";
                            }, 1000);
                        });
                    }
                });
            },

            archiveChannel(channelId) {
                fetch('/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: (() => {
                            const formData = new FormData();
                            formData.append('action', 'archive_channel');
                            formData.append('channel_id', channelId);
                            return formData;
                        })()
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) {
                            // location.href = '/channel-list';
                        }
                    })
                    .catch(err => {
                        console.error('Failed to archive:', err);
                    });
            },
            // Form update
            channelName: "<?php echo esc_js(get_field('channel_name')); ?>",
            channelDescription: `<?php echo esc_js(get_field('channel_description')); ?>`,
            channelUrl: `<?php echo esc_js(get_field('channel_url')); ?>`,
            views: `<?php echo esc_js(get_field('channel_views')); ?>`,

            // submitConfirmed(event) {
            //     event.preventDefault();

            //     // Confirmation prompt before saving
            //     Swal.fire({
            //         title: "Save Changes?",
            //         text: "Do you want to update this channel’s details?",
            //         icon: "question",
            //         showCancelButton: true,
            //         customClass: {
            //             confirmButton: 'my-confirm-btn',
            //             cancelButton: 'my-cancel-btn'
            //         },
            //         confirmButtonText: "Yes, update it!"
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             // Loading indicator
            //             Swal.fire({
            //                 title: "Updating...",
            //                 text: "Please wait while we save your changes.",
            //                 allowOutsideClick: false,
            //                 allowEscapeKey: false,
            //                 didOpen: () => {
            //                     Swal.showLoading();
            //                 }
            //             }).then(() => {
            //                 Swal.fire({
            //                     title: "Updating...",
            //                     text: "Please wait...",
            //                     allowOutsideClick: false,
            //                     allowEscapeKey: false,
            //                     didOpen: () => {
            //                         Swal.showLoading();
            //                     }
            //                 });
            //                 setTimeout(() => {
            //                     this.showModal = false;
            //                     location.reload();
            //                 }, 1000);
            //             });

            //             const formData = new FormData();
            //             formData.append('action', 'update_channel_details');
            //             formData.append('channel_id', < ?php echo get_the_ID(); ?>);
            //             formData.append('channel_name', this.channelName);
            //             formData.append('post_title', this.channelName);
            //             formData.append('channel_description', this.channelDescription);
            //             formData.append('channel_url', this.channelUrl);
            //             formData.append('selected_contractors', JSON.stringify(this.selectedContractors));
            //             formData.append('channel_status', this.currentStatus);
            //             formData.append('views', this.views);

            //             if (this.channelImage) {
            //                 formData.append('channel_profile_photo', this.channelImage);
            //             }

            //             fetch('/wp-admin/admin-ajax.php', {
            //                     method: 'POST',
            //                     body: formData
            //                 })
            //                 .then(res => res.json())
            //                 .then(response => {
            //                     if (response.success) {
            //                         Swal.fire({
            //                             title: "Updated!",
            //                             text: "Channel details have been updated.",
            //                             icon: "success",
            //                             confirmButtonText: "OK"
            //                         }).then(() => {
            //                             this.showModal = false;
            //                             // Optionally reload:
            //                             // location.reload();
            //                         });
            //                     } else {
            //                         Swal.fire("Error", "Failed to update the channel.", "error");
            //                     }
            //                 })
            //                 .catch(error => {
            //                     Swal.fire("Error", "Something went wrong.", "error");
            //                     console.error(error);
            //                 });
            //         }
            //     });
            // },

            submitConfirmed(event) {
                event.preventDefault();

                Swal.fire({
                    title: "Save Changes?",
                    text: "Do you want to update this channel’s details?",
                    icon: "question",
                    showCancelButton: true,
                    customClass: {
                        confirmButton: 'my-confirm-btn',
                        cancelButton: 'my-cancel-btn'
                    },
                    confirmButtonText: "Yes, save it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show saving loader
                        Swal.fire({
                            title: "Saving...",
                            text: "Please wait while we save your changes.",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });


                        const formData = new FormData();
                        formData.append('action', 'update_channel_details');
                        formData.append('channel_id', <?php echo get_the_ID(); ?>);
                        formData.append('channel_name', this.channelName);
                        formData.append('post_title', this.channelName);
                        formData.append('channel_description', this.channelDescription);
                        formData.append('channel_url', this.channelUrl);
                        formData.append('selected_contractors', JSON.stringify(this.selectedContractors));
                        formData.append('channel_status', this.currentStatus);
                        formData.append('views', this.views);

                        if (this.channelImage) {
                            formData.append('channel_profile_photo', this.channelImage);
                        }

                        fetch('/wp-admin/admin-ajax.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(response => {
                                if (!response.success) {
                                    // Error (e.g. duplicate channel URL) → keep modal open
                                    Swal.fire({
                                        title: "Error",
                                        text: response.data?.message || "Failed to update channel.",
                                        icon: "error"
                                    });
                                    return Promise.reject(response.data?.message || "Failed to update channel.");
                                }

                                // If channel update is successful → check insights
                                let dateVal = document.querySelector('[name="date_create"]').value;
                                let viewsVal = document.querySelector('[name="channel_views"]').value;
                                let subsVal = document.querySelector('[name="channel_subscribers"]').value;
                                let videosVal = document.querySelector('[name="channel_videos"]').value;
                                let revenueVal = document.querySelector('[name="revenue_amount"]').value;

                                if (dateVal || viewsVal || subsVal || videosVal || revenueVal) {
                                    const insightData = new FormData();
                                    insightData.append('action', 'add_new_channel_insight');
                                    insightData.append('channel_id', <?php echo get_the_ID(); ?>);
                                    insightData.append('channel_name', this.channelName);
                                    insightData.append('date_create', dateVal);
                                    insightData.append('channel_views', viewsVal);
                                    insightData.append('channel_subscribers', subsVal);
                                    insightData.append('channel_videos', videosVal);
                                    insightData.append('revenue_amount', revenueVal);

                                    return fetch('/wp-admin/admin-ajax.php', {
                                        method: 'POST',
                                        body: insightData
                                    }).then(r => r.json());
                                }

                                return {
                                    success: true
                                };
                            })
                            .then(finalRes => {
                                if (finalRes.success) {
                                    // Success → close modal + reload
                                    Swal.fire({
                                        title: "Success!",
                                        text: "Channel details have been saved.",
                                        icon: "success",
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        this.showModal = false;
                                        location.reload();
                                    });
                                } else {
                                    // Error saving insight → keep modal open
                                    Swal.fire("Error", finalRes.data || "Failed to save insight.", "error");
                                }
                            })
                            .catch(error => {
                                // Catch any unexpected errors → keep modal open
                                // Swal.fire("Error", error.message, "error");
                                Swal.fire({
                                    title: "Error",
                                    text: error.data.message,
                                    icon: "error"
                                });
                                console.error(error);
                            });
                    }
                });
            },

            channelImage: null,
            uploadImage(event) {
                this.channelImage = event.target.files[0];
            },
            get nonMembers() {
                return this.teamData.non_members;
            }
        };
    }

    function channelInsights() {
        return {
            activeTab: 'views',
            selectedMonth: '',
            selectedYear: '',
            showMonthDropdown: false,
            showYearDropdown: false,
            showModal: false,
            showDeleteModal: false,

            // Load PHP data from DOM
            insights: [],
            allChannels: [],
            selectedContractors: [],

            get selectedContractorNames() {
                return this.selectedContractors
                    .map(id => {
                        const match = this.nonMembers.find(m => m.id == id);
                        return match ? match.name : null;
                    })
                    .filter(name => name)
                    .join(', ');
            },

            editing: {
                views: false,
                revenue: false,
                subscribers: false
            },
            dates: null,
            startDate: null,
            lastDate: null,
            minDate: null,
            maxDate: null,
            chartInstance: null,
            revenueChartInstance: null,
            subscriberChartInstance: null,
            filterMonth: null,
            filterYear: null,
            selectedMonth: (new Date()).toLocaleString('default', {
                month: 'long'
            }),
            selectedYear: new Date().getFullYear(),
            filterMonth: (new Date()).getMonth() + 1,
            filterYear: new Date().getFullYear(),
            insightID: null,
            filteredTableData: null,
            availableMonths: [],
            availableYears: [],
            showSaveConfirmation: false,
            pendingChanges: null,
            revenueGrowthPercentage: 0,
            viewGrowthPercentage: 0,
            subscribersGrowthPercentage: 0,
            dates() {
                return this.insights.allData.map(item => {
                    const [day, month, year] = item.date.split('/').map(Number);
                    return new Date(year, month - 1, day);
                });
            },
            setInitialDates() {
                if (this.dates.length) {
                    // Store min/max dates for the date pickers
                    const minDate = new Date(Math.min(...this.dates));
                    const maxDate = new Date(Math.max(...this.dates));
                    this.minDate = minDate.toISOString().split('T')[0];
                    this.maxDate = maxDate.toISOString().split('T')[0];
                }
            },

            init() {
                try {
                    const rawInsights = document.getElementById('insights-data')?.textContent;
                    this.insights = rawInsights ? JSON.parse(rawInsights) : {};
                } catch (e) {
                    this.insights = {};
                    console.error("Failed to parse insights-data JSON:", e);
                }
                try {
                    const rawChannels = document.getElementById('channels-data')?.textContent;
                    this.allChannels = rawChannels ? JSON.parse(rawChannels) : [];
                } catch (e) {
                    this.allChannels = [];
                    console.error("Failed to parse channels-data JSON:", e);
                }
                const unfiltered = this.getUnfilteredTotals();

                this.setInitialDates();

                this.insights.totalViews = unfiltered.totalViews;
                this.insights.totalRevenue = unfiltered.totalRevenue;
                this.insights.totalSubscribers = unfiltered.totalSubscribers;
                this.insights.totalExpenses = unfiltered.totalExpenses;
                this.insights.totalVideos = unfiltered.totalVideos;
                this.extractAvailableMonthsYears();
                // console.log('insights', this.insights);


                const sortedData = this.insights.allData.slice().sort((a, b) => {
                    const aDate = this.parseDate(a.date);
                    const bDate = this.parseDate(b.date);
                    return bDate - aDate;
                });


                if (sortedData.length > 0) {
                    const [month, day, year] = sortedData[0].date.split('/').map(Number);
                    // this.filterMonth = month;
                    // this.filterYear = year;
                    this.getMonthYearFilteredData();
                }
                this.updateCharts();
                this.showSaveConfirmation = false;
            },

            isMonthYearSelected() {
                return this.filterMonth && this.filterYear;
            },
            extractAvailableMonthsYears() {
                const months = new Set();
                const years = new Set();

                this.insights.allData.forEach(item => {
                    const [month, day, year] = item.date.split('/').map(Number);
                    months.add(month);
                    years.add(year);
                });

                this.availableMonths = Array.from(months).sort((a, b) => a - b);
                this.availableYears = Array.from(years).sort((a, b) => a - b);

            },
            extractAvailableMonthsYears1() {
                let uniqueDates = {};

                this.insights.allData.forEach(item => {
                    const [day, month, year] = item.date.split('/').map(Number);
                    uniqueDates[`${month}-${year}`] = [month, year];
                });

                this.availableMonths = Array.from(new Set(
                    Object.values(uniqueDates).map(d => d[0]) // month
                )).sort((a, b) => a - b);

                this.availableYears = Array.from(new Set(
                    Object.values(uniqueDates).map(d => d[1]) // year
                )).sort((a, b) => a - b);
            },

            getMonthYearFilteredData() {
                if (!this.filterMonth || !this.filterYear) {
                    this.filteredTableData = null;
                    return;
                }
                this.filteredTableData = this.insights.allData.filter(item => {
                    const [month, day, year] = item.date.split('/').map(Number);

                    return (month === parseInt(this.filterMonth)) &&
                        (year === parseInt(this.filterYear));
                });
            },
            getFilteredTableTotals() {
                if (this.filteredTableData && this.filteredTableData.length > 0) {
                    return {
                        totalViews: this.filteredTableData.reduce((sum, item) => sum + item.views, 0),
                        totalRevenue: this.filteredTableData.reduce((sum, item) => sum + item.revenue, 0),
                        totalSubscribers: this.filteredTableData.reduce((sum, item) => sum + item.subscribers, 0),
                        totalExpenses: this.filteredTableData.reduce((sum, item) => sum + item.expenses, 0),
                        totalVideos: this.filteredTableData.reduce((sum, item) => sum + item.videos, 0),
                        postIds: this.filteredTableData.map(item => item.idy)
                    };
                }
                return {
                    totalViews: this.insights.totalViews,
                    totalRevenue: this.insights.totalRevenue,
                    totalSubscribers: this.insights.totalSubscribers,
                    totalVideos: this.insights.totalVideos,
                    totalExpenses: this.insights.totalExpenses,
                    postIds: []
                };
            },
            getFilteredData() {
                if (!this.startDate || !this.lastDate) return this.insights.allData;
                // console.log('filtering from', this.startDate, 'to', this.lastDate);

                const startDate = new Date(this.startDate);
                const endDate = new Date(this.lastDate);
                const data = this.insights.allData.filter(item => {
                    const itemDate = this.parseDate(item.date);
                    return itemDate >= startDate && itemDate <= endDate;
                });
                return data;
            },
            confirmSave(event, field) {
                event.preventDefault();
                // return;
                this.pendingChanges = {
                    event,
                    field
                };
                this.showSaveConfirmation = true;
            },
            submitConfirmed() {
                if (this.pendingChanges && this.pendingChanges.event) {
                    this.submitForm(this.pendingChanges.event);
                    Swal.fire({
                        icon: 'success',
                        title: 'Changes saved!',
                        showConfirmButton: true,
                        timer: 1500
                    });
                }
                this.pendingChanges = null;
                this.editing.views = false;
                this.editing.revenue = false;
                this.editing.subscribers = false;
                this.editing.expenses = false;
                this.editing.videos = false;
                this.showSaveConfirmation = false;
            },
            submitForm(event) {
                event.preventDefault();
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    console.error("Invalid form reference");
                    return;
                }
                // document.querySelector('.modal-content').classList.add('form-out');
                // document.querySelector('.loader').classList.add('loader-in');
                const formData = new FormData(form);


                // Raw values before parsing
                const date_create = formData.get('date_create');
                const viewsVal = formData.get('channel_views');
                const revenueVal = formData.get('revenue_amount');
                const subsVal = formData.get('channel_subscribers');
                const videosVal = formData.get('channel_videos');

                // ❌ Check if any are empty
                // if (!date_create || date_create.trim() === "") {
                //     Swal.fire({
                //         icon: 'warning',
                //         title: 'Missing Date',
                //         text: 'Please fill in date fields before saving.',
                //     });
                //     return; // stop submission
                // }
                const data = {
                    "action": "update_channel_insights_post",
                    "id": formData.get("insight_id"),
                    "channel_views": parseInt(formData.get('channel_views')) || 0,
                    "revenue_amount": parseFloat(formData.get('revenue_amount')) || 0,
                    "channel_subscribers": parseInt(formData.get('channel_subscribers')) || 0,
                    "channel_videos": parseInt(formData.get('channel_videos')) || 0,
                    "filter_month": this.filterMonth,
                    "filter_year": this.filterYear,
                    "update_insights_nonce": ajax_object.update_insights_nonce
                };
                jQuery.ajax({
                    type: "POST",
                    dataType: "json",
                    url: ajax_object.ajaxurl,
                    // url: '/wp-admin/admin-ajax.php',
                    data: data,
                    success: (response) => {
                        if (response.success) {
                            // alert("Insights updated successfully");
                            const views = document.querySelector(`span.tbl-views`);
                            views.textContent = response.data.views;
                            const revenue = document.querySelector(`span.tbl-revenue`);
                            revenue.textContent = '$' + response.data.revenue;
                            const subscribers = document.querySelector(`span.tbl-subscribers`);
                            subscribers.textContent = response.data.subscribers;
                            // const expenses = document.querySelector(`span.tbl-expenses`);
                            // expenses.textContent = response.data.expenses;
                            const videos = document.querySelector(`span.tbl-videos`);
                            videos.textContent = response.data.videos;
                            // this.getFilteredTableTotals();
                            // location.reload();

                            // setTimeout(function() {
                            //     // document.querySelector('.modal-content').classList.remove('form-out');
                            //     document.querySelector('.loader').classList.remove('loader-in');
                            //     document.querySelector('.checked').classList.add('checked-in');
                            // }, 2000);
                            // setTimeout(function() {
                            //     document.querySelector('.checked').classList.remove('checked-in');
                            //     // document.querySelector('.confirmation-modal').classList.add('hidden');
                            //     document.querySelector('.modal-content').classList.remove('form-out');
                            //     this.showSaveConfirmation = true;
                            //     this.pendingChanges = null;
                            //     this.editing.views = false;
                            //     this.editing.revenue = false;
                            //     this.editing.subscribers = false;
                            // }, 3000);
                        } else {
                            alert('Error: ' + response.data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        alert('An error occurred while saving');
                    }
                });
            },
            cancelEdit() {
                // Reset the edited value to its original state
                if (this.pendingChanges) {
                    const field = this.pendingChanges.field;
                    const input = this.pendingChanges.event.target;
                    if (this.filteredTableData) {
                        // For filtered data
                        const originalValue = this.filteredTableData[0][field];
                        input.value = originalValue;
                    } else {
                        // For unfiltered data
                        const originalValue = this.insights[field];
                        input.value = originalValue;
                    }
                }
                // Close confirmation and reset editing state
                this.showSaveConfirmation = false;
                this.pendingChanges = null;
                this.editing.views = false;
                this.editing.revenue = false;
                this.editing.subscribers = false;
                this.editing.expenses = false;
                this.editing.videos = false;
            },
            parseDate(dateStr) {
                const [day, month, year] = dateStr.split('/');
                return new Date(`${year}-${month}-${day}`);
            },
            getUnfilteredTotals() {
                return {
                    totalViews: this.insights.allData.reduce((sum, item) => sum + item.views, 0),
                    totalRevenue: this.insights.allData.reduce((sum, item) => sum + item.revenue, 0),
                    totalSubscribers: this.insights.allData.reduce((sum, item) => sum + item.subscribers, 0),
                    totalVideos: this.insights.allData.reduce((sum, item) => sum + item.videos, 0),
                    totalExpenses: this.insights.allData.reduce((sum, item) => sum + item.expenses, 0)
                };
            },
            getTotals() {
                const filteredData = this.getFilteredData();
                const data = {
                    views: filteredData.map(item => [item.views, item.date]),
                    revenue: filteredData.map(item => [item.revenue, item.date]),
                    subscribers: filteredData.map(item => [item.subscribers, item.date])
                };
                return data;
            },
            get revenueData() {
                const data = this.getFilteredData();

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
                let viewsByYearMonth = {};
                let subscriberByYearMonth = {};

                data.forEach(({
                    revenue,
                    views,
                    subscribers,
                    date
                }) => {
                    const [month, day, year] = date.split('/');
                    const paddedMonth = month.padStart(2, '0');

                    // revenue data
                    if (!revenueByYearMonth[year]) {
                        revenueByYearMonth[year] = {};
                    }

                    if (!revenueByYearMonth[year][paddedMonth]) {
                        revenueByYearMonth[year][paddedMonth] = 0;
                    }

                    revenueByYearMonth[year][paddedMonth] += parseInt(revenue);

                    // views data
                    if (!viewsByYearMonth[year]) {
                        viewsByYearMonth[year] = {};
                    }

                    if (!viewsByYearMonth[year][paddedMonth]) {
                        viewsByYearMonth[year][paddedMonth] = 0;
                    }

                    viewsByYearMonth[year][paddedMonth] += parseInt(views);

                    // subscribers data
                    if (!subscriberByYearMonth[year]) {
                        subscriberByYearMonth[year] = {};
                    }

                    if (!subscriberByYearMonth[year][paddedMonth]) {
                        subscriberByYearMonth[year][paddedMonth] = 0;
                    }

                    subscriberByYearMonth[year][paddedMonth] += parseInt(subscribers);
                });

                const years = Object.keys(revenueByYearMonth);
                let labels = [];
                let values = [];

                this.revenueGrowthPercentage = this.getMonthlyChange(revenueByYearMonth);
                this.viewGrowthPercentage = this.getMonthlyChange(viewsByYearMonth);
                this.subscribersGrowthPercentage = this.getMonthlyChange(subscriberByYearMonth);


                if (years.length === 1) {
                    const year = years[0];
                    const months = Object.keys(revenueByYearMonth[year]).sort();
                    labels = months.map(month => monthNames[month]);

                    revenueValues = months.map(month => revenueByYearMonth[year][month]);
                    viewsValues = months.map(month => viewsByYearMonth[year][month]);
                    subscribersValues = months.map(month => subscriberByYearMonth[year][month]);
                } else {
                    years.sort();
                    labels = years;
                    revenueValues = years.map(year => {
                        const total = Object.values(revenueByYearMonth[year])
                            .reduce((sum, val) => sum + val, 0);
                        return total;
                    });
                    viewsValues = years.map(year => {
                        const total = Object.values(viewsByYearMonth[year])
                            .reduce((sum, val) => sum + val, 0);
                        return total;
                    });
                    subscribersValues = years.map(year => {
                        const total = Object.values(subscriberByYearMonth[year])
                            .reduce((sum, val) => sum + val, 0);
                        return total;
                    });
                }
                // console.log('revenueValues', revenueValues);
                // console.log('viewsValues', viewsValues);
                // console.log('subscribersValues', subscribersValues);

                return {
                    'revenue': [
                        labels,
                        revenueValues
                    ],
                    'views': [
                        labels,
                        viewsValues
                    ],
                    'subscribers': [
                        labels,
                        subscribersValues
                    ]
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
            getInitialTotals() {
                return {
                    totalViews: this.insights.allData.reduce((sum, item) => sum + item.views, 0),
                    totalRevenue: this.insights.allData.reduce((sum, item) => sum + item.revenue, 0),
                    totalSubscribers: this.insights.allData.reduce((sum, item) => sum + item.subscribers, 0),
                    totalVideos: this.insights.allData.reduce((sum, item) => sum + item.videos, 0),
                    totalExpenses: this.insights.allData.reduce((sum, item) => sum + item.expenses, 0)
                };
            },
            getCurrentTotals() {
                const filteredData = this.getFilteredData();
                return {
                    totalViews: filteredData.reduce((sum, item) => sum + item.views, 0),
                    totalRevenue: filteredData.reduce((sum, item) => sum + item.revenue, 0),
                    totalSubscribers: filteredData.reduce((sum, item) => sum + item.subscribers, 0),
                    totalVideos: filteredData.reduce((sum, item) => sum + item.videos, 0),
                    totalExpenses: filteredData.reduce((sum, item) => sum + item.expenses, 0)
                };
            },
            updateCharts() {
                this.chartViews();
                this.revenueChart();
                this.subscriberChart();
            },
            chartViews() {
                const viewChart = document.getElementById("viewChart");
                if (!viewChart) return;
                const biew = viewChart.getContext("2d");
                const height = viewChart.clientHeight || 456;
                const gradient = biew.createLinearGradient(0, 0, 0, height);
                gradient.addColorStop(0, "rgba(33,148,255,1)");
                gradient.addColorStop(1, "rgba(33,148,255,0)");
                const filtered = this.getTotals();
                // Aggregate data by year
                const yearlyData = {};


                filtered.views.forEach(item => {
                    const year = item[1].split("/")[2];
                    if (!yearlyData[year]) {
                        yearlyData[year] = 0;
                    }
                    yearlyData[year] += item[0];
                });

                // const years = Object.keys(yearlyData);
                // const values = years.map(year => yearlyData[year]);
                const years = this.revenueData.views[0];
                const values = this.revenueData.views[1];
                // console.log('years', years);
                // console.log('values', values);

                if (this.chartInstance) {
                    this.chartInstance.destroy();
                }
                this.chartInstance = new Chart(biew, {
                    type: "line",
                    data: {
                        labels: years,
                        datasets: [{
                            label: "Views",
                            data: values,
                            backgroundColor: gradient,
                            borderColor: "transparent",
                            borderWidth: 0,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 20000,
                                    callback: function(value) {
                                        return value.toLocaleString();
                                    }
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
            },
            subscriberChart() {
                const subscriberChart = document.getElementById("subscriberChart");
                if (!subscriberChart) return;
                const ctx = subscriberChart.getContext("2d");
                const height = subscriberChart.clientHeight || 456;
                const gradien = ctx.createLinearGradient(0, 0, 0, height);
                gradien.addColorStop(0, "rgba(33,148,255,1)");
                gradien.addColorStop(1, "rgba(33,148,255,0)");
                const filtered = this.getTotals();
                // Aggregate data by year
                const yearlyData = {};
                filtered.subscribers.forEach(item => {
                    const year = item[1].split("/")[2];
                    if (!yearlyData[year]) {
                        yearlyData[year] = 0;
                    }
                    yearlyData[year] += item[0];
                });
                // const years = Object.keys(yearlyData);
                // const values = years.map(year => yearlyData[year]);

                const years = this.revenueData.subscribers[0];
                const values = this.revenueData.subscribers[1];
                if (this.subscriberChartInstance) {
                    this.subscriberChartInstance.destroy();
                }
                this.subscriberChartInstance = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: years,
                        datasets: [{
                            label: "Subscribers",
                            data: values,
                            backgroundColor: gradien,
                            borderColor: "transparent",
                            borderWidth: 0,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 20000,
                                    callback: function(value) {
                                        return value.toLocaleString();
                                    }
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                        },
                    },
                });
            },
            revenueChart() {
                const revenChart = document.getElementById("revenChart");
                if (!revenChart) return;
                const ctx = revenChart.getContext("2d");
                const height = revenChart.height;
                const filtered = this.getTotals();

                // Aggregate data by year
                const yearlyData = {};
                filtered.revenue.forEach(item => {
                    const year = item[1].split("/")[2];
                    if (!yearlyData[year]) {
                        yearlyData[year] = 0;
                    }
                    yearlyData[year] += item[0];
                });

                const years = this.revenueData.revenue[0];
                const values = this.revenueData.revenue[1];

                if (this.revenueChartInstance) {
                    this.revenueChartInstance.destroy();
                }

                this.revenueChartInstance = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: years,
                        datasets: [{
                            label: "Revenue",
                            data: values,
                            backgroundColor: "#2194FF",
                            borderRadius: 10,
                        }],
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
                            },
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
                            },
                        },
                    },
                });
            },

            drawChart() {
                this.updateCharts();
            },
            resetTableFilter() {
                this.filterMonth = null;
                this.filterYear = null;
                this.filteredTableData = null;
            },
            resetDates() {
                this.startDate = null;
                this.lastDate = null;
                // Keep the unfiltered totals
                const unfiltered = this.getUnfilteredTotals();
                this.insights.totalViews = unfiltered.totalViews;
                this.insights.totalRevenue = unfiltered.totalRevenue;
                this.insights.totalSubscribers = unfiltered.totalSubscribers;
                this.insights.totalVideos = unfiltered.totalVideos;
                this.insights.totalExpenses = unfiltered.totalExpenses;
                this.updateCharts();
            },
            isFilterActive() {
                return this.filterMonth || this.filterYear;
            }
        };
    }

    function teamFilter() {
        return {
            open: false,
            selectedChannel: '',
            selectedRole: '',
            selectedStatus: '',
            currentPage: 1,
            itemsPerPage: window.innerWidth <= 1199 ? 6 : 12,
            teamData: <?php
                        $current_channel_id = get_the_ID();
                        $members = [];
                        $non_members = [];

                        $team_query = new WP_Query([
                            'post_type' => 'team',
                            'posts_per_page' => -1,
                            'meta_query' => [
                                [
                                    'key' => 'contractor_status',
                                    'value' => ['Active', 'Archived'],
                                    'compare' => 'IN'
                                ]
                            ]
                        ]);

                        if ($team_query->have_posts()) {
                            while ($team_query->have_posts()) : $team_query->the_post();
                                $fullname = get_field('fullname');
                                if (!$fullname) {
                                    continue;
                                }

                                $image = get_field('profile_photo');
                                $your_channels = get_field('your_channel');
                                $channel_names = [];
                                $is_member = false;

                                if ($your_channels) {
                                    foreach ($your_channels as $channel_post_id) {
                                        $channel_names[] = get_field('channel_name', $channel_post_id);
                                        if ((int)$channel_post_id === $current_channel_id) {
                                            $is_member = true;
                                        }
                                    }
                                }

                                $role_field = get_field('role');
                                $role_title = '';
                                if ($role_field) {
                                    if (is_array($role_field)) {
                                        // If multiple roles
                                        $role_title = array_map(function ($post) {
                                            return get_the_title($post);
                                        }, $role_field);
                                    } else {
                                        // Single role
                                        $role_title = get_the_title($role_field);
                                    }
                                }

                                $team_data = [
                                    'id' => get_the_ID(),
                                    'name' => $fullname,
                                    'yourchannel' => $channel_names,
                                    // 'role' => get_field('role'),
                                    'role' => $role_title,
                                    'status' => get_field('contractor_status'),
                                    'image' => $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/02/Placeholder-Image-13.png',
                                    'link' => get_permalink(),
                                ];

                                if ($is_member) {
                                    $members[] = $team_data;
                                } else {
                                    $non_members[] = $team_data;
                                }
                            endwhile;
                        }
                        wp_reset_postdata();

                        echo json_encode([
                            'members' => $members,
                            'non_members' => $non_members
                        ]);
                        ?>,
            roles: <?php
                    $roles = [];
                    $role_query = new WP_Query([
                        'post_type' => 'team-roles',
                        'posts_per_page' => -1,
                        'post_status' => 'publish'
                    ]);
                    while ($role_query->have_posts()): $role_query->the_post();
                        $roles[] = get_the_title();
                    endwhile;
                    wp_reset_postdata();

                    $roles = array_unique($roles);
                    sort($roles);

                    echo json_encode(array_values($roles));
                    ?>,

            get teamMembers() {
                return this.teamData.members;
            },

            get nonMembers() {
                return this.teamData.non_members;
            },

            get channels() {
                return [...new Set(this.teamMembers.flatMap(m => m.yourchannel || []))];
            },

            // get roles() {
            //     return [...new Set(this.teamMembers.map(m => m.role))];
            // },

            get statuses() {
                return [...new Set(this.teamMembers.map(m => m.status))];
            },

            // get filteredMembers() {
            //     return this.teamMembers.filter(member => {
            //         const matchesChannel = this.selectedChannel === '' || (member.yourchannel && member.yourchannel.includes(this.selectedChannel));
            //         const matchesRole = this.selectedRole === '' || member.role === this.selectedRole;
            //         const matchesStatus = this.selectedStatus === '' || member.status === this.selectedStatus;
            //         return matchesChannel && matchesRole && matchesStatus;
            //     });
            // },
            get filteredMembers() {
                return this.teamMembers.filter(member => {
                    const matchesChannel = this.selectedChannel === '' ||
                        (member.channel && member.channel.includes(this.selectedChannel));

                    const matchesRole =
                        this.selectedRole === '' ||
                        (Array.isArray(member.role) ?
                            member.role.includes(this.selectedRole) :
                            member.role === this.selectedRole);
                    const matchesStatus = this.selectedStatus === '' || this.selectedStatus === 'All' || member.status === this.selectedStatus;
                    return matchesChannel && matchesRole && matchesStatus;


                });
            },


            get paginatedMembers() {
                let start = (this.currentPage - 1) * this.itemsPerPage;
                return this.filteredMembers.slice(start, start + this.itemsPerPage);
            },

            get totalPages() {
                return Math.ceil(this.filteredMembers.length / this.itemsPerPage) || 1;
            },

            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },

            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },

            filterItems() {
                this.currentPage = 1;
            },

            updateItemsPerPage() {
                this.itemsPerPage = window.innerWidth <= 1199 ? 6 : 12;
                this.currentPage = 1;
            },

            init() {
                this.filterItems();
                window.addEventListener('resize', () => this.updateItemsPerPage());
            }
        };
    }
</script>


<style>
    .view-channel-section {
        padding: 56px 64px;
    }

    .breadcrumb {
        font-weight: 300;
        font-size: 14px;
        column-gap: 24px;
        /* margin-bottom: 40px; */
        background-color: transparent !important;
        padding: 0px !important;
        text-transform: uppercase;
    }

    .breadcrumb-item a {
        color: #717171;
        text-decoration: none;
    }

    /* .breadcrumb-item+.breadcrumb-item::before {
        float: left;
        padding-right: var(--bs-breadcrumb-item-padding-x);
        color: var(--bs-breadcrumb-divider-color);
        content: var(--bs-breadcrumb-divider, ">");
        margin-right: 24px;
    } */
    .breadcrumb-item+.breadcrumb-item::before {
        content: "";
        display: inline-block;
        width: 14px;
        height: 14px;
        background-image: url('/wp-content/uploads/2025/07/Vector.svg');
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        margin-right: 24px;
        margin-top: 3px;
    }


    .breadcrumb-item.active {
        color: #101010 !important;
    }

    .channel-nav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 40px;
    }

    .btn-dlt {
        padding: 10px;
        background-color: #EB4335;
        border-radius: 10px;
        color: #fff;
        border: solid 1px #EB4335;
        box-shadow: 0px 10px 20px #D6E1EB;
        transition: box-shadow 0.3s ease-in-out;
    }

    .btn-edit {
        padding: 15px 20px;
        background-color: #EB4335;
        border-radius: 10px;
        color: #fff;
        border: solid 1px #EB4335;
        box-shadow: 0px 10px 20px #D6E1EB;
        transition: box-shadow 0.3s ease-in-out;
    }

    .btn-edit:hover,
    .btn-dlt:hover,
    .btn-edit:focus,
    .btn-dlt:focus {
        background-color: transparent;
        border-radius: 10px;
        color: #EB4335;
        border: solid 1px #EB4335;
        box-shadow: 0px 10px 20px #D6E1EB;
    }

    .hidden {
        display: none;
    }

    .cont-nav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 40px;
    }

    .contractor-navoption {
        margin-bottom: 24px;
        display: flex;
        flex-direction: row-reverse;
        align-items: flex-start;
        gap: 10px;
    }

    button.iconic {
        padding: 10px 14.5px;
        border: solid 1px #D0D0D0 !important;
        background-color: #fff !important;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    button.iconic:hover {
        padding: 10px 14.5px;
        border: solid 1px #D0D0D0 !important;
        background-color: #FFF !important;
    }

    .drop-content {
        display: flex;
        align-items: center;
        column-gap: 10px;
    }

    div.drop-div {
        display: flex;
        align-items: center;
        background-color: #fff;
        border-radius: 10px;
        padding: 13px 20px 13px 20px;
        gap: 10px;
    }

    .drop-title {
        font-family: Sora;
        font-size: 14px;
        font-weight: 600;
        color: #101010;
    }

    .drop-option {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
        text-transform: capitalize;
    }

    select.drop-option {
        border: none;
        background-color: #fff;
        border-radius: 10px;
    }

    .channel-info {
        padding: 40px;
        background-color: #fff;
        border-radius: 10px;
    }

    .info-details {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .info-details .icon {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        column-gap: 10px;
    }

    .icon p {
        font-weight: 600;
        color: #101010;
    }

    .info-details p {
        font-size: 14px;
        font-weight: 300;
        color: #101010;
    }

    .total-graph {
        padding: 32px 40px;
        border-radius: 10px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .head-info {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 52px;
    }

    .stats-row {
        margin-bottom: 20px;
    }

    .stats-row .card {
        padding: 32px 40px;
        border: solid 1px #fff;
    }

    .stats-row .card:hover,
    .stats-row .card:active,
    .stats-row .card:focus,
    .active-card {
        border: solid 1px #2194FF;
        cursor: pointer;
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

    canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .the-contractors {
        padding: 32px 40px;
        background-color: #fff;
        border-radius: 10px;
    }

    .contractors-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        column-gap: 40px;
        row-gap: 24px;
        max-width: 100%;
    }

    a:focus,
    a:hover {
        text-decoration: none !important;
    }

    a:hover .each-contractor .details h3,
    a:hover .each-contractor .details p {
        color: #2194FF;
    }

    .each-contractor {
        display: flex;
        align-items: center;
        text-decoration: none !important;
    }

    .each-contractor .details {
        padding: 10px 16px;
    }

    .each-contractor .details .name {
        font-size: 16px;
        font-weight: 600;
        color: #101010;
    }

    .each-contractor .details .role {
        font-size: 14px;
        font-weight: 300;
        color: #717171;
    }

    .each-contractor .cont-photo {
        max-width: 100px;
        max-height: 100px;
        border-radius: 10px;
    }

    .pagination-controls {
        margin-top: 40px;
        text-align: right;
        /* column-gap: 30px; */
    }

    .pagination-controls .prev-btn,
    .nxt-btn {
        border: none !important;
        padding: 0 !important;
        color: #101010 !important;
        font-family: inherit;
        font-size: 14px;
        font-weight: 300;
    }

    .pagination-controls .prev-btn {
        margin-right: 30px;
    }

    .pagination-controls .nxt-btn {
        margin-left: 30px;
    }

    .pagination-controls .prev-btn:hover,
    .nxt-btn:hover {
        background-color: transparent;
    }

    .pagination-controls .prev-btn:focus,
    .nxt-btn:focus {
        background-color: transparent;
    }

    .pagination-controls button img {
        height: 12px;
    }

    .pagination-controls .prev-btn .prev-icon {
        margin-right: 5px;
    }

    .pagination-controls .nxt-btn .next-icon {
        margin-left: 5px;
    }

    .view-channel-desc {
        width: 50%;
    }

    .view-channel-desc h1 {
        font-size: 30px;
        font-weight: 600;
        color: #101010;
        margin-bottom: 24px;
    }

    .view-channel-desc p {
        font-size: 16px;
        font-weight: 300;
        color: #101010;
        margin: 0;
        padding: 0;
    }

    .pagination-controls .btn-check:checked+.btn,
    .btn.active,
    .btn.show,
    .btn:first-child:active,
    :not(.btn-check)+.btn:active {
        background-color: #FFEE94;
        border-radius: 4px;
        border-color: transparent;
        -webkit-box-shadow: unset !important;
    }

    .channel-info .head-info input.channel-info-date,
    .total-graph .head-info input.views-graph-date {
        width: 10%;
        border: solid 1px #D0D0D0;
        border-radius: 4px;
        padding: 10px;
    }

    .date-rev-range {
        display: flex;
        flex-direction: row;
        align-items: center;
        border: solid 1px #D0D0D0;
        border-radius: 4px;
    }

    .date-rev-range .rev-range {
        border: unset;
        width: 45%;
        cursor: pointer;
    }

    .tbl-insights tr {
        border: unset;
    }

    .tbl-insights .insights-data {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        align-items: center;
    }

    .tbl-insights td {
        padding: 0 0 24px 0;
        color: #101010;
    }

    /* .edits-icon {
        display: none;
    } */
    .edits-icon:hover {
        cursor: pointer;
    }

    .tbl-insights td>span {
        font-weight: 600;
        color: #101010;
    }

    .tbl-insights input[type=number] {
        width: 30%;
        border: solid 1px #2194FF;
    }

    .table-filter {
        display: flex;
        flex-direction: row;
        align-items: center;
        border: solid 1px #D0D0D0;
        border-radius: 4px;
        padding: 10px;
        gap: 10px;
        width: 20%;
    }

    .table-filter select {
        border: unset;
        padding: 0;
    }

    .table-filter img {
        width: 12px;
        height: 13px;
    }

    table tbody tr:hover>td,
    table tbody tr:hover>th {
        background-color: transparent;
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

    .modal-content h3 {
        text-transform: uppercase;
        font-weight: 600;
    }

    .modal-content .formCont {
        overflow-x: hidden;
        overflow-y: auto;
        padding: 10px 20px;
    }

    /* Scrollbar width */
    .formCont::-webkit-scrollbar {
        width: 2px;
    }

    /* Scrollbar Track */
    .formCont::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    /* Scrollbar Thumb */
    .formCont::-webkit-scrollbar-thumb {
        background-color: #2194FF;
        border-radius: 10px;
    }

    .modal-content {
        padding: 32px 12px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .confirm-btn,
    button.swal2-confirm.swal2-styled {
        background-color: #2194FF;
        color: white;
        padding: 10px !important;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button.confirm-btn:hover,
    button.swal2-confirm.swal2-styled:hover {
        color: #fff;
        background-color: #142B41;
        text-decoration: none;
    }

    .cancel-btn {
        background-color: transparent;
        padding: 10px !important;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #101010;
    }

    button.cancel-btn:hover {
        color: #101010;
        background-color: #2194FF80;
        text-decoration: none;
    }

    .delete-btn {
        background-color: red;
        color: white;
        padding: 0.5rem 1rem;
        border: solid 1px red;
        border-radius: 4px;
        cursor: pointer;
    }

    .delete-btn:hover {
        background-color: transparent;
        border: solid 1px red;
    }

    [x-cloak] {
        display: none !important;
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

    .drop-div .drop-option2 {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
        text-transform: capitalize;
        width: 150px;
        padding: 0;
        @apply bg-white border border-gray-200 rounded shadow-md p-2;
        overflow-y: auto;
        display: flex;
        justify-content: space-between;
    }

    button.drop-option2 {
        border: unset;
    }

    .drop-option2 svg.w-4.h-4.ml-2 {
        width: 20px;
    }

    .drop-option3 {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
        text-transform: capitalize;
        position: absolute;
        z-index: 50;
        width: 215px;
        border-radius: 10px;
        margin-bottom: -202px;
        margin-left: -23px;
    }

    .drop-option4 {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
        text-transform: capitalize;
        position: absolute;
        z-index: 50;
        width: 220px;
        border-radius: 10px;
        margin-bottom: -260px;
        margin-left: -20px;
        max-height: 300px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .drop-option4::-webkit-scrollbar {
        width: 2px;
    }

    .drop-option4::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .drop-option4::-webkit-scrollbar-thumb {
        background-color: #2194FF;
        border-radius: 10px;
    }

    .active {
        @apply bg-blue-100 text-blue-600;
    }

    /* loader */
    .modal-content.form-out {
        opacity: 0;
        transform: scale(0.5);
    }

    .loader,
    .checked {
        text-align: center;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 5;
        transition: all 0s ease-in-out;
        opacity: 0;
    }

    .loader.loader-in {
        opacity: 1;
    }

    .checked {
        transition: all 0.4s ease-in-out;
        transform-origin: center center;
        transform: translate(-50%, -50%) scale(0.2);
    }

    .checked i {
        font-size: 5rem;
    }

    .checked.checked-in {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }

    .dots-loader-master {
        display: inline-flex;
    }

    .dots-loader {
        width: 50px;
        height: 50px;
        background-color: #2194FF;
        border-radius: 50%;
        margin-right: 5px;
        opacity: 0;
    }

    .loader-in .dots-loader1 {
        animation: loading1 3s 2 0s backwards;
    }

    .loader-in .dots-loader2 {
        animation: loading2 3s 2 0s backwards;
    }

    .loader-in .dots-loader3 {
        animation: loading3 3s 2 0s backwards;
    }

    .loader-in .dots-loader4 {
        animation: loading4 3s 2 0s backwards;
    }

    @keyframes loading1 {
        0% {
            opacity: 0;
            transform: translateX(0px);
        }

        30% {
            opacity: 1;
            transform: translateX(5px);
        }

        100% {
            opacity: 0;
            transform: translateX(5px);
        }
    }

    @keyframes loading2 {
        0% {
            opacity: 0;
        }

        20% {
            opacity: 0;
            transform: translateX(0px);
        }

        50% {
            opacity: 1;
            transform: translateX(5px);
        }

        100% {
            opacity: 0;
            transform: translateX(5px);
        }
    }

    @keyframes loading3 {
        0% {
            opacity: 0;
        }

        40% {
            opacity: 0;
            transform: translateX(0px);
        }

        70% {
            opacity: 1;
            transform: translateX(5px);
        }

        100% {
            opacity: 0;
            transform: translateX(5px);
        }
    }

    @keyframes loading4 {
        0% {
            opacity: 0;
        }

        60% {
            opacity: 0;
            transform: translateX(0px);
        }

        90% {
            opacity: 1;
        }

        100% {
            opacity: 0;
            transform: translateX(5px);
        }
    }

    .tbl-date-filter {
        display: flex;
        flex-direction: row;
        width: 200px;
        align-items: center;
        border: solid 1px #D0D0D0;
        border-radius: 4px;
        padding: 10px;
        gap: 10px;
    }

    .tbl-date-filter img {
        width: 12px;
        height: 13px;
    }

    .tbl-select {
        border: unset;
    }

    .drop {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
        text-transform: capitalize;
        position: absolute;
        z-index: 50;
        top: 340px;
        width: 110px;
        border-radius: 10px;
        border: solid 1px;
        padding: 10px;
    }

    button.tbl-select {
        padding: 0;
        width: 70px;
    }

    div.channelNavBtn {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .my-confirm-btn,
    .okay-confirm-btn {
        background-color: #2194FF !important;
        color: #fff !important;
        border: solid 1px #2194FF;
        border-radius: 4px;
        padding: 10px !important;
        font-size: 14px;
    }

    .my-confirm-btn:hover,
    .okay-confirm-btn:hover {
        background-color: transparent !important;
        color: #2194FF !important;
    }

    .my-cancel-btn {
        color: #101010 !important;
        border: 1px solid #ddd !important;
        background-color: transparent !important;
        border-radius: 4px;
        padding: 10px !important;
        font-size: 14px;
    }

    .my-cancel-btn:hover {
        background-color: #2194FF80 !important;
        color: #101010 !important;
    }

    h2.swal2-title {
        font-size: 24px;
        color: #101010 !important;
    }

    div.swal2-html-container {
        font-size: 18px;
    }

    .swal2-icon.swal2-success {
        border-color: #28a745;
        color: #28a745;
    }

    .swal2-icon.swal2-success [class^="swal2-success-line"] {
        background-color: #28a745;
    }

    .swal2-icon.swal2-warning {
        border-color: red;
        color: red;
    }

    .swal2-icon.swal2-question {
        border-color: #2194FF;
        color: #2194FF;
    }

    /* <!-- media queries --> */
    @media only screen and (max-width: 1440px) {

        .channel-info .head-info input.channel-info-date,
        .total-graph .head-info input.views-graph-date {
            width: 20%;
        }

        .table-filter {
            width: 25%;
        }
    }

    @media only screen and (max-width: 1199px) {
        .the-contractors .contractors-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }

        .cont-nav {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            margin-bottom: 40px;
            align-items: flex-start;
        }

        .contractor-navoption {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .drop-content {
            display: flex;
            row-gap: 10px;
            flex-direction: row;
            align-items: stretch;
        }

        div.drop-div {
            display: flex;
            justify-content: space-between;
        }

        .drop-title {
            margin-right: 8px;
        }
    }

    @media only screen and (max-width: 1024px) {
        .stats-row {
            display: flex;
            flex-direction: column;
            row-gap: 10px;
        }

        .stats-row .card {
            padding: 24px 40px;
        }

        .col-md-4 {
            width: 100%;
        }

        .view-channel-section {
            padding: 50px 20px;
        }

        .channel-info .head-info input.channel-info-date,
        .total-graph .head-info input.views-graph-date {
            width: 25%;
        }

        .table-filter {
            width: 40%;
        }
    }

    @media only screen and (max-width: 960px) {
        .the-contractors .contractors-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }

        .drop-content {
            display: flex;
            row-gap: 10px;
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media only screen and (max-width: 768px) {
        .stats-row .card {
            padding: 15px 20px;
        }

        .the-contractors .contractors-grid .each-contractor {
            display: flex;
            align-items: center;
            flex-direction: column;
        }

        .channel-nav {
            display: flex;
            flex-direction: column;
        }

        .view-channel-desc {
            width: 100%;
        }

        div.channelNavBtn {
            width: 100%;
            align-items: flex-end;
        }

        .view-channel-desc p {
            margin-bottom: 24px;
        }

        .view-channel-section {
            padding: 0;
        }

        .head-info {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .channel-info .head-info input.channel-info-date,
        .total-graph .head-info input.views-graph-date {
            width: 100%;
        }

        .table-filter {
            width: 100%;
        }

        .cont-nav {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            margin-bottom: 40px;
            align-items: flex-start;
        }

        .contractor-navoption {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .drop-content {
            display: flex;
            row-gap: 10px;
            flex-direction: column;
            align-items: stretch;
        }

        div.drop-div {
            display: flex;
            justify-content: space-between;
        }

        .drop-title {
            margin-right: 8px;
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

        .view-channel-section {
            padding: 0;
        }

        .cont-nav {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            margin-bottom: 40px;
            align-items: flex-start;
        }

        .contractor-navoption {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .drop-content {
            display: flex;
            row-gap: 10px;
            flex-direction: column;
            align-items: stretch;
        }

        div.drop-div {
            display: flex;
            justify-content: space-between;
        }

        .drop-title {
            margin-right: 8px;
        }

    }

    @media only screen and (max-width: 425px) {
        .breadcrumb {
            column-gap: 10px;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            margin-right: 10px;
        }

        .view-channel-desc h1 {
            margin-top: 0px;
        }

        .view-channel-desc {
            width: 100%;
        }

        .channel-info,
        .total-graph,
        .the-contractors {
            padding: 20px;
        }

        .stats-row .card {
            padding: 22px 20px;
        }

        .head-info {
            display: flex;
            flex-direction: column;
        }

        .the-contractors .cont-nav {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .the-contractors .contractors-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .contractors-grid {
            column-gap: 10px;
            row-gap: 10px;
        }

        .pagination-controls .prev-btn {
            margin-right: 0;
        }

        .pagination-controls .nxt-btn {
            margin-left: 0;
        }

        .date-rev-range .rev-range {
            width: 48%;
        }

        .tbl-insights .insights-data {
            gap: 5px;
        }

        .the-contractors .cont-nav {
            display: flex;
            flex-direction: row;
            align-items: stretch;
        }

        .contractor-navoption {
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
            width: 80px;
        }

        .drop-content {
            display: flex;
            align-items: stretch;
            row-gap: 10px;
            flex-direction: column;
            width: 58vw;
        }

        div.drop-div {
            display: flex;
            justify-content: space-between;
        }

        .drop-title {
            margin-right: 8px;
        }

        .btn-edit {
            padding: 10px;
        }
    }

    @media only screen and (max-width: 410px) {
        .the-contractors .contractors-grid {
            grid-template-columns: repeat(1, 1fr);
        }

        .stats-row .card .card-body {
            padding: 0px;
        }

        .stats-row .card h2 {
            font-size: 20px;
            margin-bottom: 0px;
        }

        .stats-row .card h5 {
            display: flex;
            flex-direction: column;
            align-items: start;
            margin-bottom: 0px;
        }

        .the-contractors .cont-nav {
            display: flex;
            flex-direction: column;
        }

        .contractor-navoption {
            width: 100%;
        }

        .drop-content {
            width: 50vw;
        }
    }
</style>
<?php get_footer('admin'); ?>