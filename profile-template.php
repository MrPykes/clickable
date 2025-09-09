<?php

/**
 * Template Name: Profile Template
 */

get_header('admin');

if (!session_id()) {
    session_start();
}

// $userId = get_transient("userID");
// $fullName = get_transient("fullName");
// $userId =  $_SESSION['userID'];
// $fullName =  $_SESSION['fullName'];

$current_user = wp_get_current_user();

$userId = $_SESSION['userID'] ?? $current_user->ID;
$fullName = $_SESSION['fullName'] ?? trim($current_user->first_name . ' ' . $current_user->last_name);
$userEmail = $_SESSION['userEmail'] ?? null;
if (empty($fullName)) {
    $fullName = $current_user->display_name;
}

$token = wp_generate_password(32, false);

// PROFILE DETAILS
$profile_photo = get_field('profile_photo', $userId);

// $team_data[] = [
//     'id' => $userId,
//     'profile_photo' => $profile_photo['sizes']['medium'],
// ];

// $paymentPlatformField = get_field_object('payment_platform', $userId);
// $paymentPlatforms = $paymentPlatformField ? $paymentPlatformField['choices'] : [];


// $currencyField = get_field_object('currency', $userId);
// $currencies = $currencyField ? $currencyField['choices'] : [];

// $paymentPlatforms = get_posts([
//     'post_type'      => 'payment-method',
//     'post_status'    => 'publish', // ✅ Only published
//     'posts_per_page' => -1,        // Get all
//     'orderby'        => 'title',
//     'order'          => 'ASC',
// ]);
// $currencies = get_posts([
//     'post_type'      => 'team-currency',
//     'post_status'    => 'publish', // ✅ Only published
//     'posts_per_page' => -1,        // Get all
//     'orderby'        => 'title',
//     'order'          => 'ASC',
// ]);

$platform_data = get_posts([
    'post_type'      => 'payment-method',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'fields'         => 'ids',
]);

$paymentPlatforms = [];

foreach ($platform_data as $platform_id) {
    $paymentPlatforms[] = [
        'ID'         => (int) $platform_id, // force int
        'post_title' => get_the_title($platform_id),
    ];
}

$currency_data = get_posts([
    'post_type'      => 'team-currency',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'fields'         => 'ids',
]);

$currencies = [];

foreach ($currency_data as $currency_id) {
    $currencies[] = [
        'ID'         => (int) $currency_id, // force int
        'post_title' => get_the_title($currency_id),
    ];
}


if (isset($data['image'])) {
    update_field('profile_photo', $data['image'], $post_id);
}

// INVOICE SECTION
// $args = array(
//     'post_type'      => 'invoice-creator',
//     'posts_per_page' => -1,
//     'post_status'    => 'publish',
//     'meta_query'     => array(
//         array(
//             'key'     => 'status',
//             'value'   => array('paid', 'pending'),
//             'compare' => 'IN'
//         ),
//         array(
//             'key'     => 'user_id',
//             'value'   => $userId,
//             'compare' => '='
//         ),
//     )
// );

// $invoices_query = new WP_Query($args);
// $invoice_data = [];
// $earnings_by_month = array_fill(1, 12, 0);

// if ($invoices_query->have_posts()) {
//     while ($invoices_query->have_posts()) {
//         $invoices_query->the_post();

//         // Extract month from 'date_submitted'
//         $date_submitted = get_field('date_submitted');
//         $month = date('n', strtotime(str_replace('/', '-', $date_submitted)));

//         // Calculate invoice amount
//         $amount = (floatval(get_field('tasks')[0]['tasks_hours'] ?? 0) * floatval(get_field('tasks')[0]['tasks_rate'] ?? 0)) -
//             (floatval(get_field('deductions')[0]['deduct_hours'] ?? 0) * floatval(get_field('deductions')[0]['deduct_rate'] ?? 0));

//         // Store invoice data
//         $invoice_data[] = [
//             'id'         => get_the_ID(),
//             'invoiceNumber'   => get_field('invoice_number'),
//             'date'       => $date_submitted,
//             'status'     => get_field('status'),
//             'platform'   => get_field('payment_method'),
//             // 'channel'    => get_field('tasks')[0]['tasks_channel'],
//             'amount'     => $amount,
//             // 'earnings_by_month' => $earnings_by_month[$month] += $amount
//         ];
//         // 'earnings_by_month' => array_values($earnings_by_month)
//         $earnings_by_month[$month] += $amount;
//         // Add to earnings by month

//     }
//     wp_reset_postdata();
// }



// Invoice history query (PAID + PENDING)

$args_invoice = array(
    'post_type'      => 'invoice-creator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => 'status',
            'value'   => array('PAID', 'PENDING'),
            'compare' => 'IN'
        ),
        array(
            'relation' => 'OR',
            array(
                'key'     => 'user_id',
                'value'   => $userId,
                'compare' => '='
            ),
            array(
                'key'     => 'fullname',
                'value'   => $fullName,
                'compare' => '='
            )
        )
    )
);

$invoices_query = new WP_Query($args_invoice);
$invoice_data = [];

if ($invoices_query->have_posts()) {
    while ($invoices_query->have_posts()) {
        $invoices_query->the_post();

        $tasks      = get_field('your_tasks') ?: [];
        $deductions = get_field('your_deductions') ?: [];

        $channel_id   = $tasks[0]['tasks_channel'] ?? '';
        $channel_name = '';

        if ($channel_id) {
            if (is_array($channel_id)) {
                $first = reset($channel_id);
                if (is_numeric($first)) {
                    $channel_name = get_the_title((int) $first);
                } elseif (is_object($first) && isset($first->ID)) {
                    $channel_name = get_the_title($first->ID);
                }
            } elseif (is_numeric($channel_id)) {
                $channel_name = get_the_title((int) $channel_id);
            } elseif (is_object($channel_id) && isset($channel_id->ID)) {
                $channel_name = get_the_title($channel_id->ID);
            } else {
                $channel_name = $channel_id;
            }
        }
        // Get payment method(s)
        $platforms = get_field('payment_method');
        $platform_titles = [];

        if ($platforms) {
            if (is_array($platforms)) {
                foreach ($platforms as $platform_id) {
                    $platform_titles[] = get_the_title($platform_id);
                }
            } else {
                $platform_titles[] = get_the_title($platforms);
            }
        }

        $invoice_data[] = [
            'id'            => get_the_ID(),
            'invoiceNumber' => get_field('invoice_number'),
            'date'          => get_field('date_submitted'),
            'status'        => get_field('status'),
            // 'platform'      => get_field('payment_method'),
            'platform'       => $platform_titles, // Now titles, not IDs
            'channel'       => $channel_name,
            'hours'         => $tasks[0]['tasks_hours'] ?? '',
            'rate'          => $tasks[0]['tasks_rate'] ?? '',
            'deduct_channel' => $deductions[0]['deduct_channel'] ?? '',
            'deduct_hours'  => $deductions[0]['deduct_hours'] ?? '',
            'deduct_rate'   => $deductions[0]['deduct_rate'] ?? '',
            'amount'        => floatval(get_field('amount')) ?: 0,
            'fullName'      => get_field('fullname') ?? ''
        ];
    }
    wp_reset_postdata();
}

//  CHART SECTION (PAID ONLY) 

$args_chart = array(
    'post_type'      => 'invoice-creator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => 'status',
            'value'   => 'PAID',
            'compare' => '='
        ),
        array(
            'relation' => 'OR',
            array(
                'key'     => 'user_id',
                'value'   => $userId,
                'compare' => '=',
                'type'    => 'NUMERIC'
            ),
            array(
                'key'     => 'fullname',
                'value'   => $fullName,
                'compare' => '='
            )
        )
    )
);

$chart_query = new WP_Query($args_chart);
$chart_data  = [];
$earnings_by_month = array_fill(1, 12, 0);

if ($chart_query->have_posts()) {
    while ($chart_query->have_posts()) {
        $chart_query->the_post();

        $amount = floatval(get_field('amount')) ?: 0;
        $date   = get_field('date_submitted');

        if ($date) {
            $month = date('n', strtotime(str_replace('/', '-', $date)));
            $earnings_by_month[$month] += $amount;
        }

        $chart_data[] = [
            'date'   => $date ? date('Y-m-d', strtotime($date)) : '',
            'amount' => $amount
        ];
    }
    wp_reset_postdata();
}

// Final results
$response = [
    'invoices'          => $invoice_data,
    'chart'             => $chart_data,
    'earnings_by_month' => array_values($earnings_by_month)
];


$team_members = [];
$team_query = new WP_Query([
    'post_type' => 'team',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'post__in' => array($userId),
]);
if ($team_query->have_posts()):
    while ($team_query->have_posts()) : $team_query->the_post();
        $image = get_field('profile_photo');

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
        // $team_members[] = [
        //     'id' => get_the_ID(),
        //     'name' => get_field('fullname'),
        //     'channel' => get_field('channel'),
        //     'discord' => get_field('discord_name'),
        //     'email' => get_field('main_email'),
        //     'role' => $role_title,
        //     'full_address' => get_field('full_address'),
        //     'paymentPlatform' => intval(get_field('payment_platform')[0]),
        //     'currency' => intval(get_field('currency')[0]),
        //     'paymentPlatformID' => get_field('payment_platform'),
        //     'currencyID' => get_field('currency'),
        //     'paymentDetails' => esc_html(get_field('payment_details')),
        //     'image' => $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/02/Placeholder-Image-13.png',
        //     'payid' => get_field('email_pay_id'),
        // ];

        $team_members[] = [
            'id' => get_the_ID(),
            'name' => get_field('fullname'),
            // 'emailAdd' => get_field('email_address'),
            'channel' => get_field('channel'),
            'discord' => get_field('discord_name'),
            'email' => get_field('main_email'),
            'role' => $role_title,
            'full_address' => get_field('full_address'),

            // force single values as integers
            'paymentPlatform' => (int) (get_field('payment_platform')[0] ?? 0),
            'currency'        => (int) (get_field('currency')[0] ?? 0),

            // force arrays of IDs as integers
            'paymentPlatformID' => array_map('intval', (array) get_field('payment_platform')),
            'currencyID'        => array_map('intval', (array) get_field('currency')),

            'paymentDetails' => esc_html(get_field('payment_details')),
            'image' => $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/02/Placeholder-Image-13.png',
            'payid' => get_field('email_pay_id'),
        ];


    endwhile;
endif;
wp_reset_postdata();

// echo '<pre>';
// print_r($invoice_data);
// echo '</pre>';

?>
<script type="application/json" id="team-members">
    <?php echo json_encode($team_members) ?>
</script>
<script type="application/json" id="currencies">
    <?php echo json_encode($currencies) ?>
</script>
<script type="application/json" id="payment-platforms">
    <?php echo json_encode($paymentPlatforms) ?>
</script>
<script type="application/json" id="invoice-data">
    <?php echo wp_json_encode($invoice_data); ?>
</script>
<script type="application/json" id="earnings-by-month">
    <?php echo wp_json_encode(array_values($earnings_by_month)); ?>
</script>


<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar');  ?>
    <!-- <div class="main-content"> -->
    <div class="main-content">
        <main class="flex-grow-1 p-4" x-data="teamFilter()" x-init="showActions = false">
            <h1 class=" h3">Your Dashboard</h1>
            <p class="text-muted">Welcome, <?php echo $fullName; ?>!</p>
            <div class="container-fluid mt-4 p-0">
                <div class="card profile-container">
                    <div class="profile-header position-relative">
                    </div>
                    <div class="row m-0 z-0 main-prof">
                        <div class="col-lg-10 col-md-12">
                            <div class="row">
                                <div class="col-xl-3 col-lg-5 col-md-12 d-md-flex justify-content-lg-start justify-content-md-center">
                                    <img class="img-fluid profile-img" :src="profile.image" alt="Profile Picture">
                                </div>
                                <div class="col-xl-9 col-lg-7 col-md-12 profile-edit">
                                    <h4 class="mt-2" x-text="profile.discord ?? 'No discord name indicated.'"
                                        :class="!profile.discord ? 'text-danger' : ''"></h4>
                                    <p class="text-muted" x-text="profile.name ?? 'No name indicated.'"
                                        :class="!profile.name ? 'text-danger' : ''"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-12 profile-edit-button">
                            <div class="btn-update">
                                <img src="/wp-content/uploads/2025/05/white-edit.svg" alt="">
                                <button class="btn-edit" @click="showActions = true">
                                    Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row m-0 row-gap-4 profile-details">
                        <div class="col-lg-6 col-md-12 d-flex">
                            <div class="card w-100">
                                <div class="card-body">
                                    <h5>Details</h5>
                                    <p>Discord:<span><php echo  get_field("discord_name", $userId); ?></span></p>
                                    <p>Status: <span>Contractor</span></p>
                                    <p>Email: <span><php echo  get_field("email_address", $userId); ?></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="card w-100">
                                <div class="card-body">
                                    <h5>Payment Details</h5>
                                    <p>Platform: <span><php echo get_field("payment_platform", $userId) ?? "Paypal"; ?></span></p>
                                    <p>Currency: <span><php echo get_field("currency", $userId) ?? "USD"; ?></span></p>
                                    <p>Email/Pay ID: <span><php echo get_field("email_pay_id", $userId); ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div class="row m-0 row-gap-4 profile-details">
                        <!-- DETAILS COLUMN -->
                        <div class="col-lg-6 col-md-12 d-flex">
                            <div class="card w-100">
                                <div class="card-body">
                                    <h5>Details</h5>
                                    <p>Discord: <span x-text="profile.discord ?? 'No discord name indicated.'"
                                            :class="!profile.discord ? 'text-danger' : ''"></span></p>
                                    <p>Status: <span>Contractor</span></p>
                                    <p>Email: <span x-text="profile.email ?? 'No email indicated.'"
                                            :class="!profile.email ? 'text-danger' : ''"></span></p>
                                    <p>Address: <span x-text="profile.full_address ?? 'No address indicated.'"
                                            :class="!profile.full_address ? 'text-danger' : ''"></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- PAYMENT DETAILS COLUMN -->
                        <div class="col-lg-6 col-md-12">
                            <div class="card w-100">
                                <div class="card-body">
                                    <h5>Payment Details</h5>
                                    <p>Payment Method: <span x-text="getPlatformTitle(profile.paymentPlatform) ?? 'No method indicated.'"
                                            :class="!profile.paymentPlatform ? 'text-danger' : ''"></span></p>
                                    <p>Currency: <span x-text="getCurrencyTitle(profile.currency) ?? 'No currency indicated.'"
                                            :class="!profile.currency ? 'text-danger' : ''"></span></p>
                                    <p>Email/Pay ID: <span x-text="profile.payid ?? 'No Pay ID indicated.'"
                                            :class="!profile.payid ? 'text-danger' : ''"></span></p>
                                    <p>Role: <span x-text="profile.role ?? 'No role indicated.'"
                                            :class="!profile.role ? 'text-danger' : ''"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="photo-loader" style="display:none;">
                        <div class="loader"></div>
                    </div>
                </div>
            </div>
            <!-- Edit Profile Modal -->
            <div class="hidden profile-modal" x-show="showActions"
                x-transition x-bind:class="{ 'hidden': !showActions }">
                <div class="modal-prof">
                    <h3>Update Profile?</h3>
                    <form @submit.prevent="submitConfirmed">
                        <!-- <form> -->
                        <div>
                            <label>Profile Image:</label>
                            <input type="file" @change="uploadImage">
                        </div>
                        <div>
                            <label>Full Name:</label>
                            <input type="text" x-model="profile.name">
                        </div>
                        <div>
                            <label>Discord:</label>
                            <input type="text" x-model="profile.discord">
                        </div>
                        <div>
                            <label>Full Address:</label>
                            <input type="text" x-model="profile.full_address">
                        </div>
                        <div>
                            <label>Main Email:</label>
                            <input type="email" x-model="profile.email">
                        </div>
                        <div>
                            <label>Payment Method:</label>
                            <select x-model="profile.paymentPlatform" x-effect="$el.value = profile.paymentPlatform">
                                <template x-for="platform in paymentPlatforms" :key="platform.ID">
                                    <option :value="Number(platform.ID)"
                                        :selected="profile.paymentPlatform == platform.ID"
                                        x-text="platform.post_title"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label>Payment Details:</label>
                            <textarea name="paymentDetails" id="paymentDetails" x-model="profile.paymentDetails"></textarea>
                        </div>
                        <div>
                            <label>Currency:</label>
                            <select x-model.number="profile.currency" x-effect="$el.value = profile.currency">
                                <template x-for="currency in currencies" :key="currency.ID">
                                    <option
                                        :value="Number(currency.ID)"
                                        :selected="profile.currency == currency.ID"
                                        x-text="currency.post_title">
                                    </option>
                                </template>
                            </select>
                        </div>


                        <div>
                            <label>Email/Pay ID:</label>
                            <input type="email" x-model="profile.payid">
                        </div>
                        <!-- <div>
                            <label>Role:</label>
                            <input type="text" x-model="profile.role" readonly>
                        </div> -->
                        <div class="modal-option">
                            <button type="submit" @click="submitConfirmed" class="confirm-btn">Save</button>
                            <button type="button" @click="cancelEdit" class="cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Earnings Section -->
            <!-- <pre x-text="JSON.stringify(item.audios, null, 2)"></pre> -->
            <div class="container-fluid mt-4 p-0 earn-section" x-data="earningsTable()">
                <div class="card earning-section">
                    <div class="earn-nav">
                        <h5 class="fw-bold">Earnings</h5>
                        <button class="btn btn-secondary-outline" @click="updateChart()">2025</button>
                    </div>
                    <div class="row p-0">
                        <div class="col-md-9">
                            <canvas x-ref="chartCanvas"></canvas>
                        </div>
                        <div class="col-md-3 earnings-details">
                            <template x-for="(earning, index) in earningsData" :key="index">
                                <p>
                                    <span x-text="months[index]" style="font-size: 16px; font-weight: 300; color: #717171;"></span>
                                    <span x-text="'$' + earning.toLocaleString()" style="font-size: 16px; font-weight: 700; color: #101010;"></span>
                                </p>
                            </template>
                            <p style="border-top: solid 1px #E7E7E7; padding-top: 24px; margin-top: 24px;">
                                <span style="font-size: 16px; font-weight: 600; color: #101010;">Earnings</span>
                                <span style="font-size: 18px; font-weight: 700; color: #101010;">
                                    <span x-text="'$' + totalEarnings.toLocaleString()"></span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Roster Section -->
            <div class="container-fluid mt-4 p-0">
                <div class="card channel-section">
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5 class="fw-bold">Channels</h5>
                    </div>
                    <div class="row" x-data="channelsTable()">
                        <div class="col-md-12">
                            <!-- Show when NO channels -->
                            <template x-if="channels.length === 0">
                                <div class="text-center py-4 text-muted">No channel assigned.</div>
                            </template>
                            <!-- Show when channels exist -->
                            <div id="news-slider" class="owl-carousel" x-show="channels.length > 0" x-init="initCarousel()">
                                <template x-for="channel in channels" :key="channel.id">
                                    <div class="post-slide">
                                        <div class="card">
                                            <div class="card-body d-flex flex-row p-4 gap-3">
                                                <img class="img-fluid" width="auto" height="70" :src="channel.image" :alt="channel.title">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h5 class="fw-bold mb-1" x-text="channel.title"></h5>
                                                    <!-- <p class="mb-0" x-text="'Since ' + channel.date_created"></p> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice History Section -->
            <div class="container-fluid mt-4 p-0">
                <div class="card invoice-section">
                    <div x-data="invoiceTable()">
                        <div class="invoice-nav">
                            <h5 class="fw-bold">Invoice History</h5>
                            <div class="invoice-button" x-show="filteredInvoices().length > 0">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-sliders-h"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownButton">
                                        <li><button class="btn" @click="filterStatus = ''; currentPage = 1;">All</button></li>
                                        <li><button class="btn" @click="filterStatus = 'PAID'; currentPage = 1;">Paid</button></li>
                                        <li><button class="btn" @click="filterStatus = 'PENDING'; currentPage = 1;">Pending</button></li>
                                    </ul>
                                    <button class="btn btn-outline-secondary dl-btn" @click="downloadReport()">
                                        <img class="dl-icon" src="/wp-content/uploads/2025/03/dl-icon.png" alt="Download Icon"> Download Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="tbl-invoice-wrapper">
                            <template x-if="filteredInvoices().length === 0">
                                <div class="text-center py-4 text-muted">No invoice available.</div>
                            </template>
                            <template x-if="filteredInvoices().length > 0">
                                <table border="1" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Invoice Number</th>
                                            <th>Channel</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Platform</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="invoice in paginatedInvoices()" :key="invoice.id">
                                            <tr>
                                                <td x-text="invoice.invoiceNumber" class="styl-inv-txt"></td>
                                                <td x-text="invoice.channel" class="styl-inv-txt"></td>
                                                <td x-text="invoice.date" class="styl-inv-txt"></td>
                                                <td class="styl-inv-txt data-text">
                                                    $<span x-text="invoice.amount.toLocaleString()"></span>
                                                </td>
                                                <td x-text="invoice.platform" class="styl-inv-txt"></td>
                                                <td>
                                                    <span :class="invoice.status === 'PAID' ? 'badge bg-success' : 'badge bg-danger'"
                                                        x-text="invoice.status"></span>
                                                </td>
                                                <td>
                                                    <span @click="downloadInvoice(invoice)">
                                                        <img class="dl-icon" src="/wp-content/uploads/2025/03/dl-icon.png" alt="Download Icon">
                                                    </span>
                                                    <!-- <span @click="deleteInvoice(invoice.id)">
                                                        <img class="dlt-icon" src="/wp-content/uploads/2025/03/dlt-icon.png" alt="Delete Icon">
                                                    </span> -->
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </template>
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
            </div>
        </main>
    </div>
</div>

<script>
    function teamFilter() {
        return {
            showActions: false,
            teamMembers: [],
            // paymentPlatforms: < ?php echo json_encode($paymentPlatforms); ?>,
            // currencies: < ?php echo json_encode($currencies); ?>,
            paymentPlatforms: [],
            paymentPlatform: "",
            currencies: [],
            currency: "",
            profile: {},
            getPlatformTitle(id) {
                let item = this.paymentPlatforms.find(p => p.ID == id);
                return item ? item.post_title : null;
            },

            getCurrencyTitle(id) {
                let item = this.currencies.find(c => c.ID == id);
                return item ? item.post_title : null;
            },
            init() {

                try {
                    const raw = document.getElementById('team-members').textContent;
                    this.teamMembers = JSON.parse(raw);
                    this.currency = this.teamMembers[0].currency;
                    this.paymentPlatform = this.teamMembers[0].paymentPlatform;

                } catch (e) {
                    this.teamMembers = [];
                    console.error("Failed to parse JSON:", e);
                }
                try {
                    const raw = document.getElementById('currencies').textContent;
                    this.currencies = JSON.parse(raw);
                } catch (e) {
                    this.currencies = [];
                    console.error("Failed to parse JSON:", e);
                }

                try {
                    const raw = document.getElementById('payment-platforms').textContent;
                    this.paymentPlatforms = JSON.parse(raw);
                } catch (e) {
                    this.paymentPlatforms = [];
                    console.error("Failed to parse JSON:", e);
                }

                // const userId = < php echo json_encode($userId); ?>;
                // this.profile = this.teamMembers.find(member => member.id == userId) || {};
                this.profile = this.teamMembers[0] || {};
                // console.log('the data', this.profile);
            },
            cancelEdit() {
                this.showActions = false;
                fetch('/wp-admin/admin-ajax.php?action=delete_team_attachment', {
                        method: 'POST',
                        body: new URLSearchParams({
                            attachment_id: this.tempImage,
                        }),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Attachment deleted successfully');
                        } else {
                            console.error('Failed to delete:', data.data);
                        }
                    })
                    .catch(error => console.error('Error:', error));

            },
            // submitConfirmed() {
            //     // Show loader before starting the fetch
            //     document.getElementById('photo-loader').style.display = 'block';
            //     fetch('/wp-json/clickable/v1/update-team-profile', {
            //             method: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //             },
            //             body: JSON.stringify({
            //                 id: this.profile.id,
            //                 name: this.profile.name,
            //                 discord: this.profile.discord,
            //                 email: this.profile.email,
            //                 role: this.profile.role,
            //                 full_address: this.profile.full_address,
            //                 paymentPlatform: this.profile.paymentPlatform,
            //                 currency: this.profile.currency,
            //                 payid: this.profile.payid,
            //                 // image: this.profile.image_id || this.profile.image,
            //                 image_id: this.tempImage,
            //                 // attachment_id: this.tempProfile.attachment_id,
            //             }),
            //         })
            //         .then(response => response.json())
            //         .then(data => {
            //             // Hide loader after response
            //             document.getElementById('photo-loader').style.display = 'none';
            //             if (data.success) {
            //                 console.log('Profile updated successfully');
            //                 this.showActions = false;
            //                 this.profile.image = data.image
            //             } else {
            //                 console.error('Error updating profile:', data.error);

            //             }
            //         })
            //         .catch(error => {
            //             // Hide loader even if fetch failed
            //             document.getElementById('photo-loader').style.display = 'none';
            //             console.error('Request failed:', error);
            //         });
            // },
            submitConfirmed() {
                Swal.fire({
                    title: "Save Changes?",
                    text: "Do you want to update your profile?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes, update",
                    customClass: {
                        confirmButton: 'my-confirm-btn',
                        cancelButton: 'my-cancel-btn'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Updating...",
                            text: "Please wait while we save your changes.",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                // Swal.showLoading();

                                document.getElementById('photo-loader').style.display = 'block';

                                fetch('/wp-json/clickable/v1/update-team-profile', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            id: this.profile.id,
                                            name: this.profile.name,
                                            discord: this.profile.discord,
                                            email: this.profile.email,
                                            role: this.profile.role,
                                            full_address: this.profile.full_address,
                                            paymentPlatform: this.profile.paymentPlatform,
                                            paymentDetails: this.profile.paymentDetails,
                                            currency: this.profile.currency,
                                            payid: this.profile.payid,
                                            image_id: this.tempImage,
                                        }),
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        document.getElementById('photo-loader').style.display = 'none';

                                        if (data.success) {
                                            this.showActions = false;
                                            this.profile.image = data.image;

                                            setTimeout(() => {
                                                location.reload();
                                            }, 1000);
                                        } else {
                                            Swal.fire("Error", data.error || "Failed to update profile.", "error");
                                        }
                                    })
                                    .catch(error => {
                                        document.getElementById('photo-loader').style.display = 'none';
                                        console.error('Request failed:', error);
                                        Swal.fire("Error", "Something went wrong.", "error");
                                    });
                            }
                        });
                    }
                });
            },
            uploadImage(event) {

                const file = event.target.files[0];
                if (!file) return;

                const allowedTypes = ['image/jpeg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG and PNG files are allowed.');
                    event.target.value = '';
                    return;
                }

                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('File size must not exceed 5MB.');
                    event.target.value = '';
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('id', this.profile.id);
                formData.append('action', 'upload_team_profile_image');

                // Show loader
                document.getElementById('photo-loader').style.display = 'block';

                fetch('/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide loader
                        document.getElementById('photo-loader').style.display = 'none';
                        if (data.success) {
                            // this.profile.image = data.data.url;
                            // this.profile.image_id = data.data.id;
                            // this.tempProfile = {
                            //     imageId : data.data.id
                            // }
                            // console.log('image', data.data.url);

                            this.tempImage = data.data.attach_id
                            // console.log('this.tempImage',this.tempImage);
                        } else {
                            alert('Failed to upload image.');
                        }
                    })
                    .catch(error => {
                        // Hide loader even if there's an error
                        document.getElementById('photo-loader').style.display = 'none';
                        console.error('Error uploading:', error);
                    });
            }
        }
    }

    function earningsTable() {
        return {
            earningsData: [],
            chart: null,
            // init() {
            //     this.$watch("earningsData", () => this.updateChart());
            //     this.renderChart();
            // },
            init() {
                try {
                    // Try to load earnings JSON directly from DOM
                    let earnings = JSON.parse(document.getElementById("earnings-by-month").textContent);
                    this.earningsData = earnings || [];
                } catch (error) {
                    console.error("Error parsing earnings-by-month JSON:", error);
                    this.earningsData = [];
                }

                this.$watch("earningsData", () => this.updateChart());
                this.renderChart();

                try {
                    // Listen for updates dispatched by invoiceTable
                    this.$el.addEventListener("earnings-updated", (event) => {
                        this.earningsData = event.detail.earnings || [];
                    });
                } catch (error) {
                    console.error("Error binding earnings-updated listener:", error);
                }
            },

            months: [
                "Jan", "Feb", "Mar", "Apr", "May", "June",
                "July", "Aug", "Sep", "Oct", "Nov", "Dec"
            ],
            earningsData: invoiceTable().invoices.earnings_by_month, // Initialize with 12 months of zero earnings

            get totalEarnings() {
                return this.earningsData.reduce((acc, val) => acc + val, 0);
            },


            renderChart() {
                const ctx = this.$refs.chartCanvas.getContext("2d");


                if (this.chart) {
                    this.chart.destroy();
                }

                const height = ctx.clientHeight || 456;
                const gradient = ctx.createLinearGradient(0, 0, 0, height);
                gradient.addColorStop(0.3, "rgba(33,148,255,1)");
                gradient.addColorStop(1, "rgba(33,148,255,0)");
                this.chart = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: this.months,
                        datasets: [{
                            label: "Earnings",
                            data: Object.values(this.earningsData),
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
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                },
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
                            }
                        }
                    }
                });
            },

            updateChart() {
                if (this.chart) {
                    this.chart.destroy();
                }
                this.renderChart();
            },

            updateEarnings(event) {
                this.earningsData = event.detail.earnings;
                this.updateChart();
            }
        };
    }

    function invoiceTable() {
        return {
            // invoices: < ?php
            //             $args = array(
            //                 'post_type'      => 'invoice-creator',
            //                 'posts_per_page' => -1,
            //                 'post_status'    => 'publish',
            //                 'meta_query'     => array(
            //                     array(
            //                         'key'     => 'status',
            //                         'value'   => array('paid', 'pending'),
            //                         'compare' => 'IN'
            //                     ),
            //                     array(
            //                         'key'     => 'user_id',
            //                         'value'   => $userId,
            //                         'compare' => '='
            //                     ),
            //                 )
            //             );

            //             $invoices_query = new WP_Query($args);
            //             $invoice_data = [];
            //             $earnings_by_month = array_fill(1, 12, 0); // Initialize earnings for 12 months

            //             if ($invoices_query->have_posts()) {
            //                 while ($invoices_query->have_posts()) {
            //                     $invoices_query->the_post();

            //                     // Extract month from 'date_submitted'
            //                     $date_submitted = get_field('date_submitted');
            //                     $month = date('n', strtotime(str_replace('/', '-', $date_submitted)));

            //                     // Calculate invoice amount
            //                     $amount = (floatval(get_field('tasks')[0]['tasks_hours'] ?? 0) * floatval(get_field('tasks')[0]['tasks_rate'] ?? 0)) -
            //                         (floatval(get_field('deductions')[0]['deduct_hours'] ?? 0) * floatval(get_field('deductions')[0]['deduct_rate'] ?? 0));

            //                     // Store invoice data
            //                     $invoice_data[] = [
            //                         'id'         => get_the_ID(),
            //                         'invoiceNumber'   => get_field('invoice_number'),
            //                         'date'       => $date_submitted,
            //                         'status'     => get_field('status'),
            //                         'platform'   => get_field('payment_method'),
            //                         'channel'    => get_field('tasks')[0]['tasks_channel'],
            //                         'amount'     => $amount,
            //                         'fullName'   => $fullName
            //                         // 'earnings_by_month' => $earnings_by_month[$month] += $amount
            //                     ];
            //                     // 'earnings_by_month' => array_values($earnings_by_month)
            //                     $earnings_by_month[$month] += $amount;
            //                     // Add to earnings by month

            //                 }
            //                 wp_reset_postdata();
            //             }

            //             echo json_encode([
            //                 "data" => $invoice_data,
            //                 "earnings_by_month" => array_values($earnings_by_month)
            //             ]);
            //             ? >,

            earningsByMonth: [],
            invoices: [],
            filterStatus: '',
            currentPage: 1,
            itemsPerPage: 3,

            init() {
                try {
                    let invoiceData = JSON.parse(document.getElementById("invoice-data").textContent);
                    let earningsByMonth = JSON.parse(document.getElementById("earnings-by-month").textContent);

                    // keep the structure consistent
                    this.invoices = {
                        data: invoiceData,
                        earnings_by_month: earningsByMonth
                    };

                    this.earningsByMonth = this.invoices.earnings_by_month || [];

                    this.$dispatch('earnings-updated', {
                        earnings: this.earningsByMonth
                    });
                } catch (error) {
                    console.error("Error parsing invoice/earnings data:", error);
                    this.invoices = {
                        data: [],
                        earnings_by_month: []
                    };
                    this.earningsByMonth = [];
                }
                // console.log('the filtered: ', this.filteredInvoices());
            },

            // filteredInvoices() {
            //     return this.filterStatus ? this.invoices.data.filter(inv => inv.status === this.filterStatus) : this.invoices.data;
            // },
            filteredInvoices() {
                return this.filterStatus ?
                    this.invoices.data.filter(inv => inv.status === this.filterStatus) :
                    this.invoices.data;
            },

            paginatedInvoices() {
                let start = (this.currentPage - 1) * this.itemsPerPage;
                let end = start + this.itemsPerPage;
                return this.filteredInvoices().slice(start, end);
            },

            totalPages() {
                return Math.ceil(this.filteredInvoices().length / this.itemsPerPage);
            },

            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },

            nextPage() {
                if (this.currentPage < this.totalPages()) this.currentPage++;
            },
            // deleteInvoice(id) {
            //     Swal.fire({
            //         title: 'Are you sure?',
            //         text: "This will permanently delete the invoice.",
            //         icon: 'warning',
            //         showCancelButton: true,
            //         confirmButtonText: 'Yes, delete it!',
            //         customClass: {
            //             confirmButton: 'my-confirm-btn',
            //             cancelButton: 'my-cancel-btn'
            //         }
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             fetch('< ?php echo admin_url("admin-ajax.php"); ?>', {
            //                 method: 'POST',
            //                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            //                 body: new URLSearchParams({
            //                     action: 'delete_invoice_post',
            //                     post_id: id
            //                 })
            //             })
            //             .then(res => res.json())
            //             .then(result => {
            //                 if (result.success) {
            //                     this.invoices = this.invoices.filter(invoice => invoice.id !== id);
            //                     if (this.currentPage > this.totalPages()) this.currentPage = this.totalPages();
            //                     Swal.fire({ title: 'Deleted!', text: 'Invoice deleted.', icon: 'success', timer: 1500, showConfirmButton: false });
            //                 } else {
            //                     Swal.fire('Error', result.data || 'Failed to delete invoice.', 'error');
            //                 }
            //             })
            //             .catch(err => {
            //                 console.error('Delete failed:', err);
            //                 Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
            //             });
            //         }
            //     });
            // },
            // downloadInvoice(invoice) {
            //     let header = "Invoice Number, Channel, Fullname, Date, Amount, Status";
            //     let content = `${invoice.invoiceNumber}, ${invoice.channel}, ${inv.fullName}, ${invoice.date}, ${invoice.amount}, ${invoice.status}`;

            //     let csvContent = `${header}\n${content}`;

            //     // Sanitize and uppercase fullName
            //     let fullName = (invoice.fullName || 'Unknown').replace(/[^a-z0-9]/gi, '_').toUpperCase();

            //     let blob = new Blob([csvContent], {
            //         type: "text/csv;charset=utf-8"
            //     });

            //     saveAs(blob, `INV_${invoice.invoiceNumber}_${fullName}.csv`);
            // },
            downloadInvoice(invoice) {
                try {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF();

                    // Title: Invoice Number + Full Name
                    doc.setFontSize(16);
                    doc.text(`Invoice #${invoice.invoiceNumber} - ${invoice.fullName || 'N/A'}`, 20, 20);

                    // Invoice content
                    doc.setFontSize(12);
                    let lines = [
                        `Channel: ${invoice.channel}`,
                        `Date: ${invoice.date}`,
                        `Amount: $${invoice.amount.toLocaleString()}`,
                        `Platform: ${invoice.platform}`,
                        `Status: ${invoice.status}`
                    ];

                    let y = 40;
                    lines.forEach(line => {
                        doc.text(line, 20, y);
                        y += 10;
                    });

                    // Build filename: INVOICE_FULLNAME.PDF
                    let safeName = (invoice.fullName || "N_A").replace(/\s+/g, "-").toUpperCase();
                    let filename = `INVOICE_${safeName}.PDF`;

                    doc.save(filename);
                } catch (error) {
                    console.error("Error generating PDF:", error);
                }
            },

            downloadReport() {
                let header = "Invoice Number, Channel, Fullname, Date, Amount, Platform, Status";
                let data = this.filteredInvoices().map(inv =>
                    `${inv.invoiceNumber}, ${inv.channel}, ${inv.fullName}, ${inv.date}, ${inv.amount},${inv.platform}, ${inv.status}`
                ).join("\n");

                let csvContent = `${header}\n${data}`;

                // Get full name from any invoice (they all have the same fullName from PHP)
                let fullName = this.filteredInvoices()[0]?.fullName || 'Unknown';

                // Sanitize filename (remove illegal characters from names)
                fullName = fullName.replace(/[^a-z0-9]/gi, '_').toUpperCase();

                let blob = new Blob([csvContent], {
                    type: "text/csv;charset=utf-8"
                });
                saveAs(blob, `INVOICE_${fullName}.csv`);
            }

        };
    }

    function channelsTable() {
        return {
            channels: <?php
                        $channels = get_field("your_channel", $userId); // use correct ACF field name
                        $channel_data = [];

                        if (!empty($channels) && is_array($channels)) {
                            foreach ($channels as $channel_post) {
                                // $channel_post could be either a post ID or post object
                                $channel_id = is_object($channel_post) ? $channel_post->ID : $channel_post;

                                // Get the channel fields
                                $channel_name = get_field("channel_name", $channel_id);
                                $channel_description = get_field("channel_description", $channel_id);
                                $channel_image = get_field("channel_profile_photo", $channel_id);
                                $channel_data[] = [
                                    'id'           => $channel_id,
                                    'title'        => get_the_title($channel_id),
                                    'description'  => $channel_description,
                                    'date_created' => get_the_date('', $channel_id),
                                    'image'        => $channel_image["sizes"]["medium"] ?? ''
                                ];
                            }
                        }

                        echo json_encode($channel_data);
                        ?>,

            initCarousel() {
                this.$nextTick(() => {
                    let totalSlides = this.channels.length;

                    jQuery("#news-slider").owlCarousel({
                        items: 3,
                        itemsDesktopSmall: [1440, 2],
                        itemsMobile: [991, 1],
                        navigation: true,
                        navigationText: ["", ""],
                        pagination: true,
                        autoPlay: false,
                    });
                });
            }
        };
    }

    document.querySelectorAll(".dropdown-item").forEach(item => {
        item.addEventListener("click", function() {
            document.getElementById("dropdownButton").innerText = this.getAttribute("data-value");
        });
    });
</script>
<link rel="stylesheet" href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css" />
<script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>



<style>
    .profile-header {
        background-image: url('/wp-content/uploads/2025/02/profile-banner.png');
        height: 150px;
        border-radius: 10px 10px 0 0;
        background-repeat: no-repeat;
        background-size: cover;
    }

    .profile-img {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        border: 10px solid white;
        margin-top: -60px;
    }

    .card {
        border-radius: 10px;
    }

    .main-prof {
        padding: 40px;
    }

    .profile-details {
        padding: 40px;
    }

    .profile-details p {
        display: flex;
        justify-content: space-between;
        flex-direction: row;
    }

    .profile-details p span {
        font-weight: 600;
        font-size: 14px;
        color: #101010;
    }

    .profile-details h5 {
        font-size: 16px;
        font-weight: 700;
        color: #101010;
        margin-bottom: 40px;
    }

    .row h4 {
        font-size: 24px;
        font-weight: 600;
        color: #101010;
        margin-bottom: 18px;
    }

    .row p {
        font-size: 16px;
        font-weight: 300;
        color: #717171;
    }

    .profile-edit {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
    }

    .profile-edit-button {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        align-items: center;
        padding: 0;
    }

    button.btn-edit {
        padding: 0;
        border: unset;
        color: #FFFFFF !important;
    }

    .btn-update {
        background-color: #2194FF;
        box-shadow: -3px 14px 25px -10px #92b4d0;
        padding: 15px 20px;
        border-radius: 10px;
    }

    .btn-update:hover {
        background-color: #6494FA;
        cursor: pointer;
    }

    .profile-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9;
    }

    .profile-modal .modal-prof {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
    }

    .modal-option .confirm-btn {
        background-color: #2194FF;
        color: white;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .modal-option button.confirm-btn:hover {
        color: #fff;
        background-color: #142B41;
        text-decoration: none;
    }

    .modal-option .cancel-btn {
        background-color: transparent;
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #101010;
    }

    .modal-option button.cancel-btn:hover {
        color: #101010;
        background-color: #2194FF80;
        text-decoration: none;
    }

    .modal-option {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
        gap: 24px;
    }

    #photo-loader {
        position: fixed;
        /* or absolute if inside a container */
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
    }

    .loader {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        /* blue color */
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .text-danger {
        color: red;
        font-weight: 500;
        font-style: italic;
        text-transform: uppercase;
    }

    .chart-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 800px;
        position: relative;
    }

    .earnings-details p {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    .chart-wrapper {
        position: relative;
        width: 100%;
        height: 400px;
    }

    canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .earn-section .earn-nav button {
        border: solid 1px #D0D0D0;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 300;
        color: #101010;
    }

    .earn-section .earn-nav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-bottom: 40px;
    }

    .earning-section {
        padding: 32px 40px;
    }

    .active-roster .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 20px;
        transition: 0.3s;
        min-height: 300px;
        background: transparent;
    }

    .active-roster .card img {
        width: 80px;
        height: 80px;
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

    /* .owl-controls .owl-buttons .owl-prev:after,
    .owl-controls .owl-buttons .owl-next:after {
        content: "\f104";
        font-family: FontAwesome;
        color: #333;
        font-size: 30px;
    } */

    /* .owl-controls .owl-buttons .owl-next:after {
        content: "\f105";
    } */

    .owl-controls .owl-buttons .owl-prev:after {
        content: url('/wp-content/uploads/2025/07/prev.svg');
    }

    .owl-controls .owl-buttons .owl-next:after {
        /* content: "\f105"; */
        content: url('/wp-content/uploads/2025/07/nxt.svg');
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

    .channel-section {
        padding: 32px 40px;
    }

    .owl-carousel .img-fluid {
        /* border-radius: 50px; */
        border-radius: 50%;
        width: 80px;
        height: 80px;
    }

    .hidden {
        display: none;
    }

    .btn {
        padding: 5px 10px;
        cursor: pointer;
        margin: 2px;
        border-radius: 5px;
    }

    .btn-green {
        background-color: green;
        color: white;
    }

    .btn-red {
        background-color: red;
        color: white;
    }

    .btn-blue {
        background-color: blue;
        color: white;
    }

    .active {
        background-color: #007bff;
        color: white;
    }

    .invoice-button .dropdown [type=button]:focus,
    [type=button]:hover,
    [type=submit]:focus,
    [type=submit]:hover,
    button:focus,
    button:hover {
        color: #2A2A2A;
        background-color: transparent;
        text-decoration: none;
    }

    .invoice-button .dropdown [type=button],
    [type=submit],
    button {
        color: #2A2A2A;
        background-color: transparent;
        text-decoration: none;
        border: solid 1px #D0D0D0;
        border-radius: 4px;
    }

    .dropdown .dl-btn {
        padding: 10px;
        color: #101010;
        font-weight: 300;
        font-size: 14px;
    }

    .dropdown .dl-btn:hover {
        background-color: transparent;
    }

    .dropdown .dl-btn img {
        margin-right: 10px;
    }

    .pagination .btn.disabled,
    .btn:disabled,
    fieldset:disabled .btn {
        border-color: transparent;
    }

    .pagination .btn-check:checked+.btn,
    .btn.active,
    .btn.show,
    .btn:first-child:active,
    :not(.btn-check)+.btn:active {
        background-color: #FFEE94;
        border-radius: 4px;
        border-color: transparent;
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

    table tbody tr:hover>td,
    table tbody tr:hover>th {
        background-color: transparent;
    }

    .invoice-section {
        width: 100%;
        position: relative;
    }

    .tbl-invoice-wrapper {
        overflow-x: auto;
    }

    .invoice-section table {
        width: 100%;
        min-width: 500px;
        /* Adjust based on your column content */
        border-collapse: collapse;
        white-space: nowrap;
        /* Prevent text wrapping */
    }

    .tbl-invoice-wrapper::-webkit-scrollbar {
        height: 5px;
        /* Adjust scrollbar thickness */
    }

    .tbl-invoice-wrapper::-webkit-scrollbar-thumb {
        background: #336;
        /* Scrollbar color */
        border-radius: 4px;
    }

    .tbl-invoice-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        /* Track color */
    }

    .invoice-nav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-bottom: 40px;
    }

    .tbl-invoice-wrapper .styl-inv-txt {
        font-size: 14px;
        font-style: normal;
        font-weight: 300;
        color: #717171;
    }

    /* <!-- media queries -----------------> */
    @media only screen and (max-width: 1024px) {
        .profile-container .profile-details {
            display: flex;
            flex-direction: column;
        }

        .profile-details .col-lg-6 {
            width: 100%;
            padding: 0;
        }

        .earning-section .row.p-0 {
            display: flex;
            flex-direction: column;
        }

        .col-md-3 {
            width: 100%;
        }
    }

    @media only screen and (max-width: 768px) {
        .invoice-nav {
            display: flex;
            flex-direction: column;
        }

        .invoice-nav .invoice-button .dropdown {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
        }

        .profile-details {
            padding: 40px 10px;
        }

        .earn-section .earn-nav {
            margin-bottom: 24px;
        }

        .col-md-9 {
            width: 100%;
            margin-bottom: 24px;
        }

        .profile-edit {
            align-items: center;
        }

        .profile-edit-button {
            justify-content: center;
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
            width: 620px;
        }
    }

    @media only screen and (max-width: 425px) {
        .p-4 {
            padding: 0 !important;
        }

        .profile-details p {
            display: flex;
            flex-direction: column;
        }

        .profile-details h4.mt-2 {
            font-size: 20px;
        }

        .earning-section,
        .channel-section,
        .invoice-section {
            padding: 20px;
        }
    }
</style>

<?php get_footer('admin'); ?>