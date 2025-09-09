<?php

/**
 * Template Name: Invoices Template
 */
get_header('admin');

// Invoices
$args = [
    'post_type'      => 'invoice-creator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
];
$invoices_query = new WP_Query($args);
$invoice_data = [];

if ($invoices_query->have_posts()) {
    while ($invoices_query->have_posts()) {
        $invoices_query->the_post();
        $tasks = get_field('your_tasks');
        $first_channel = null;

        if (!empty($tasks) && !empty($tasks[0]['tasks_channel'])) {
            $channel_post = $tasks[0]['tasks_channel'];
            $first_channel = is_array($channel_post)
                ? get_the_title($channel_post[0])
                : get_the_title($channel_post);
        }

        $date_submitted = get_field('date_submitted');
        $formatted_date = '';

        if (!empty($date_submitted)) {
            try {
                $dt = new DateTime($date_submitted);
                $formatted_date = $dt->format('d/m/Y');
            } catch (Exception $e) {
                $formatted_date = ''; // fallback if invalid date
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
        // Role handling
        $role_field = get_field('role');
        $role_title = '';
        if ($role_field) {
            if (is_array($role_field)) {
                $role_title = array_map(function ($post) {
                    return is_numeric($post) ? get_the_title($post) : (is_object($post) ? get_the_title($post->ID) : $post);
                }, $role_field);
            } else {
                $role_title = is_numeric($role_field) ? get_the_title($role_field) : (is_object($role_field) ? get_the_title($role_field->ID) : $role_field);
            }
        }

        $invoice_data[] = [
            'id'           => get_the_ID(),
            'invoiceNumber' => get_field('invoice_number'),
            'discordName'  => get_field('discord_name'),
            'fullName'     => get_field('fullname'),
            // 'role'         => get_field('role'),
            'role'            => $role_title,
            'date'         => $formatted_date,
            'amount'       => get_field('amount') ?? 0,
            'status'       => get_field('status'),
            'standard'     => get_field('standard'),
            // 'platform'     => get_field('payment_method'),
            'platform'       => $platform_titles, //  Now titles, not IDs
            'link'         => get_permalink(),
            'channel'      => $first_channel,
        ];

        // $invoice_data[] = [
        //     'id'           => get_the_ID(),
        //     'invoiceNumber'=> get_field('invoice_number'),
        //     'discordName'  => get_field('discord_name'),
        //     'fullName'     => get_field('fullname'),
        //     'role'         => get_field('role'),
        //     'date'         => date('d/m/Y', strtotime(get_field('date_submitted'))),
        //     'amount'       => get_field('amount') ?? 0,
        //     'status'       => get_field('status'),
        //     'standard'     => get_field('standard'),
        //     'platform'     => get_field('payment_method'),
        //     'link'         => get_permalink(),
        //     'channel'      => $first_channel,
        // ];
    }
    wp_reset_postdata();
}

// Channels (two arrays: one with ids+names, one with just names)
$channels = [];
$channelNames = [];

$channel_query = new WP_Query([
    'post_type'      => 'channel',
    'posts_per_page' => -1,
    'meta_query'     => [
        [
            'key'     => 'channel_status',
            'value'   => 'active',
            'compare' => '='
        ]
    ]
]);

while ($channel_query->have_posts()): $channel_query->the_post();
    $channel_name   = get_field('channel_name') ?: get_the_title();
    $channel_status = get_field('channel_status'); // ACF field for status

    if ($channel_name) {
        // For <select> (only Active channels)
        if ($channel_status === 'Active') {
            $channels[] = [
                'id'   => get_the_ID(),
                'name' => $channel_name
            ];
        }

        // For <ul> (all channels, regardless of status)
        $channelNames[] = $channel_name;
    }
endwhile;
wp_reset_postdata();



// Roles
$roles = [];
$role_query = new WP_Query([
    'post_type' => 'team-roles',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC'
]);
while ($role_query->have_posts()): $role_query->the_post();
    $roles[] = [
        'id'    => get_the_ID(),
        'title' => get_the_title()
    ];
endwhile;
wp_reset_postdata();
// $roles = array_values(array_unique($roles));
// sort($roles);

// Payment Methods
$methods = [];
$methods_query = new WP_Query([
    'post_type' => 'payment-method',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC'
]);
while ($methods_query->have_posts()): $methods_query->the_post();
    $methods[] = [
        'id'    => get_the_ID(),
        'title' => get_the_title()
    ];
endwhile;
wp_reset_postdata();
// $methods = array_values(array_unique($methods));
// sort($methods);

// Currencies
$currencies = [];
$currency_query = new WP_Query([
    'post_type' => 'team-currency',
    'posts_per_page' => -1,
    'post_status' => 'publish'
]);
while ($currency_query->have_posts()): $currency_query->the_post();
    $currencies[] = get_the_title();
endwhile;
wp_reset_postdata();
$currencies = array_values(array_unique($currencies));
sort($currencies);


// echo '<pre>';
// print_r(get_fields(3441));
// echo '</pre>';
?>

<script type="application/json" id="invoice-data">
    <?= wp_json_encode($invoice_data) ?>
</script>
<script type="application/json" id="channel-data">
    <?= wp_json_encode($channels) ?>
</script>
<script type="application/json" id="channel-names">
    <?= wp_json_encode(array_values(array_unique($channelNames))) ?>
</script>
<script type="application/json" id="role-data">
    <?= wp_json_encode(array_values($roles)) ?>
</script>
<script type="application/json" id="payment-data">
    <?= wp_json_encode(array_values($methods)) ?>
</script>
<script type="application/json" id="currency-data">
    <?= wp_json_encode(array_values($currencies)) ?>
</script>

<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar'); ?>
    <div class="main-content">
        <div class="invoice-section" x-data="invoiceFilter()">
            <div class="invoice-desc">
                <h1 style="font-size: 30px; font-weight:600; color: #101010;">Invoices</h1>
                <p style="font-size: 16px; font-weight:300; color: #101010;">View and manage detailed invoices of contractors.</p>
            </div>
            <!-- Search & Filters -->
            <div class="nav-option">
                <div class="search-container">
                    <img class="search-icon" src="/wp-content/uploads/2025/02/Vector-4.svg" alt="Search Icon">
                    <input type="text" placeholder="Search name here..." x-model="searchQuery" @input="filterInvoices">
                </div>
                <div class="btn-Nav">
                    <button class="btn-add" @click="showAdd = true">
                        <i class="fa fa-plus"></i> Add Invoice
                    </button>
                    <button class="btn-export" @click="downloadReport()">
                        <i class="fa-solid fa-download"></i> Export to CSV
                    </button>
                </div>
            </div>
            <!-- Add Invoice Modal -->
            <div class="hidden confirmation-modal" x-show="showAdd"
                x-transition x-bind:class="{ 'hidden': !showAdd }">
                <div class="modal-content">
                    <div>
                        <h2>Create an Invoice?</h2>
                    </div>
                    <div class="formCont">
                        <form id="invoiceform_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="input-label">Invoice Number:</label>
                                    <input type="text" name="invoice_number" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="input-label">Discord Name:</label>
                                    <input type="text" name="discord_name" placeholder="Enter discord name" required>
                                </div>
                            </div>
                            <div>
                                <label class="input-label">Fullname:</label>
                                <input type="text" placeholder="Enter Fullname" name="fullname" required>
                            </div>
                            <div>
                                <label class="input-label">Main Email:</label>
                                <input type="email" placeholder="Enter main email" name="email" required>
                            </div>
                            <div>
                                <label class="input-label">Full Address:</label>
                                <input type="text" placeholder="Enter full address" name="full_address" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="input-label">Channel:</label>
                                    <select class="form-control" x-model="chooseChannel" name="tasks_channel" required>
                                        <option value="" disabled>Select</option>
                                        <template x-for="(channel, index) in the_channels" :key="index">
                                            <!-- <option :value="channel" x-text="channel"></option> -->
                                            <option :value="channel.id" x-text="channel.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Role:</label>
                                    <select class="form-control" x-model="chooseRole" name="role" required>
                                        <option value="" disabled>Select a role</option>
                                        <template x-for="role in roles" :key="role.id">
                                            <option :value="role.id" x-text="role.title"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="input-label">Description:</label>
                                    <textarea rows="4" placeholder="Enter description" name="tasks_description" required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="input-label">Quantity:</label>
                                    <input type="number" placeholder="Enter hours quantity" name="tasks_hours" required>
                                    <label class="input-label">Rate:</label>
                                    <input type="number" placeholder="Enter rate" name="tasks_rate" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="input-label">Preferred Platform:</label>
                                    <select class="form-control" x-model="choosePlatform" name="payment_method" required>
                                        <option value="" disabled>Select a Platform</option>
                                        <template x-for="method in paymentMethods" :key="method.id">
                                            <option :value="method.id" x-text="method.title"></option>
                                        </template>
                                    </select>
                                    <label class="input-label">Payment Details:</label>
                                    <textarea rows="4" placeholder="Enter payment details" name="payment_details" required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label>Upload Files:</label>
                                    <input type="file" name="file_name" required>
                                    <label class="input-label">Date Submitted:</label>
                                    <input type="date" @click="$el.showPicker && $el.showPicker()" name="date_submitted" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="input-label">Period Covered:</label>
                                    <input type="date" @click="$el.showPicker && $el.showPicker()" name="period_covered" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="input-label">Due Date:</label>
                                    <input type="date" @click="$el.showPicker && $el.showPicker()" name="due_date" required>
                                </div>
                            </div>
                            <!-- <div>
                                <label class="input-label">Pay Email:</label>
                                <input type="email" placeholder="Enter pay email" name="pay_email" required>
                            </div> -->
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="input-label">Payment Status:</label>
                                    <select name="status" required>
                                        <option value="PENDING">PENDING</option>
                                        <option value="PAID">PAID</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="input-label">Standard:</label>
                                    <select name="standard" required>
                                        <option value="NONCONTRACTOR">NONCONTRACTOR</option>
                                        <option value="CONTRACTOR">CONTRACTOR</option>
                                    </select>
                                </div>
                            </div>

                            <div class="modal-actions">
                                <button type="button" @click="submitConfirmed" class="confirm-btn">Save Changes</button>
                                <button type="button" @click="showAdd = false" class="cancel-btn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Invoice Table -->
            <div class="invoice-tbl">
                <!-- Filter Section -->
                <div class="iconic-section">
                    <div class="bulk-actions">
                        <div x-show="anySelected">
                            <button @click="showActions = !showActions">BULK</button>
                            <div x-show="showActions" @click.away="showActions = false">
                                <button @click="bulkMarkPaid()" class="btn-bulk-paid">MARK AS PAID</button>
                                <button @click="bulkMarkPending()" class="btn-bulk-pending">MARK AS PENDING</button>
                            </div>
                        </div>
                    </div>
                    <div class="inv-filter">

                        <button @click="showFilters = !showFilters" class="iconic">
                            <img src="/wp-content/uploads/2025/02/sidebar-size.svg" alt="Toggle Filters">
                        </button>
                        <div class="filter-dropdown" x-show="showFilters" x-transition @click.away="showFilters = false">
                            <div class="filter-section">
                                <!-- Status Filter -->
                                <div class="drop-div statusInv" x-data="{ statusDropdown: false }">
                                    <label class="drop-title mb-1">Payment Status:</label>
                                    <button class="drop-option2 w-full text-left flex justify-between items-center"
                                        @click="statusDropdown = !statusDropdown">
                                        <span x-text="selectedStatus || 'All'"></span>
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <ul class="custom-status drop-option3 mt-1" x-show="statusDropdown"
                                        x-transition @click.away="statusDropdown = false">
                                        <li>
                                            <button class="btn w-full text-left" :class="selectedStatus === 'All' ? 'active' : ''"
                                                @click="selectedStatus = ''; statusDropdown = false">
                                                All
                                            </button>
                                        </li>
                                        <li>
                                            <button class="btn w-full text-left" :class="selectedStatus === 'PAID' ? 'active' : ''"
                                                @click="selectedStatus = 'PAID'; statusDropdown = false">Paid</button>
                                        </li>
                                        <li>
                                            <button class="btn w-full text-left" :class="selectedStatus === 'PENDING' ? 'active' : ''"
                                                @click="selectedStatus = 'PENDING'; statusDropdown = false">Pending</button>
                                        </li>
                                    </ul>
                                </div>
                                <!-- Contractor / noncontractor filter -->
                                <div class="drop-div" x-data="{ standardDropdown: false }">
                                    <label class="drop-title mb-1">Status:</label>
                                    <button class="drop-option2 w-full text-left flex justify-between items-center"
                                        @click="standardDropdown = !standardDropdown">
                                        <span x-text="selectedStandard || 'All'"></span>
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <ul class="custom-standard drop-option3 mt-1" x-show="standardDropdown"
                                        x-transition @click.away="standardDropdown = false">
                                        <li>
                                            <button class="btn w-full text-left"
                                                :class="selectedStandard === 'All' ? 'active' : ''"
                                                @click="selectedStandard = ''; standardDropdown = false">
                                                All
                                            </button>
                                        </li>
                                        <li>
                                            <button class="btn w-full text-left" :class="selectedStandard === 'CONTRACTOR' ? 'active' : ''"
                                                @click="selectedStandard = 'CONTRACTOR'; standardDropdown = false">Contractor</button>
                                        </li>
                                        <li>
                                            <button class="btn w-full text-left" :class="selectedStandard === 'NONCONTRACTOR' ? 'active' : ''"
                                                @click="selectedStandard = 'NONCONTRACTOR'; standardDropdown = false">Noncontractor</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="filter-section">
                                <!-- Role Filter -->
                                <div class="drop-div" x-data="{ roleDropdown: false }">
                                    <label class="drop-title mb-1">Role:</label>
                                    <button class="drop-option2 w-full text-left flex justify-between items-center"
                                        @click="roleDropdown = !roleDropdown">
                                        <span x-text="selectedRole || 'All'"></span>
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <ul class="custom-role drop-option3 mt-1" x-show="roleDropdown"
                                        x-transition @click.away="roleDropdown = false">
                                        <li>
                                            <button
                                                class="btn w-full text-left"
                                                :class="selectedRole == '' ? 'active' : ''"
                                                @click="selectedRole = ''; roleDropdown = false">
                                                All
                                            </button>
                                        </li>
                                        <template x-for="role in roles" :key="role.id">
                                            <li>
                                                <button
                                                    class="btn w-full text-left"
                                                    :class="selectedRole === role.title ? 'active' : ''"
                                                    @click="selectedRole = role.title; roleDropdown = false"
                                                    x-text="role.title">
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <!-- Channel Filter -->
                                <div class="drop-div" x-data="{ channelDropdown: false }">
                                    <label class="drop-title mb-1">Channel:</label>
                                    <button class="drop-option2 w-full text-left flex justify-between items-center" @click="channelDropdown = !channelDropdown">
                                        <span x-text="selectedChannel || 'All'"></span>
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <ul class="custom-channel drop-option3 mt-1 "
                                        x-show="channelDropdown"
                                        x-transition
                                        @click.away="channelDropdown = false">

                                        <li>
                                            <button class="btn w-full text-left"
                                                :class="selectedChannel === '' ? 'active' : ''"
                                                @click="selectedChannel = ''; channelDropdown = false">
                                                All
                                            </button>
                                        </li>

                                        <template x-for="channel in the_channelNames" :key="channel">
                                            <li>
                                                <button class="btn w-full text-left"
                                                    :class="selectedChannel === channel ? 'active' : ''"
                                                    @click="selectedChannel = channel; channelDropdown = false"
                                                    x-text="channel">
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <div class="invoice-tbl-wrapper">
                    <table class="tbl-data">
                        <thead>
                            <tr class="tbl-row">
                                <th class="manage-column">
                                    <input type="checkbox" @click="toggleSelectAll($event)" :checked="areAllSelected">
                                    All
                                </th>
                                <!-- <th @click="sortBy('fileName')" class="cursor-pointer">
                                    Invoice Name
                                    <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                    class="sort-icon transition-transform duration-300 inline-block ml-1"
                                    :class="{'rotate-180': sortKey === 'fileName' && !sortAsc}">
                                </th> -->
                                <th class="cursor-pointer">
                                    Invoice Number
                                </th>
                                <!-- <th @click="sortBy('discordName')" class="cursor-pointer">
                                    Discord Name
                                    <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                    class="sort-icon transition-transform duration-300 inline-block ml-1"
                                    :class="{'rotate-180': sortKey === 'discordName' && !sortAsc}">
                                </th> -->
                                <th>Channel</th>
                                <th>Role</th>
                                <th @click="sortBy('fullName')" class="cursor-pointer">
                                    Fullname
                                    <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                        class="sort-icon transition-transform duration-300 inline-block ml-1"
                                        :class="{'rotate-180': sortKey === 'fullName' && !sortAsc}">
                                </th>
                                <th @click="sortBy('date')" class="cursor-pointer">
                                    Date
                                    <!-- <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'date' && !sortAsc}"> -->
                                </th>
                                <th @click="sortBy('amount')" class="cursor-pointer">
                                    Amount
                                    <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                        class="sort-icon transition-transform duration-300 inline-block ml-1"
                                        :class="{'rotate-180': sortKey === 'amount' && !sortAsc}">
                                </th>
                                <th>Platform</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="invoice in paginatedInvoices()" :key="invoice.id">
                                <tr>
                                    <td class="check-column">
                                        <input type="checkbox" x-model="invoice.selected">
                                    </td>
                                    <td class="data-invoiceNumber"><a :href="invoice.link" x-text="invoice.invoiceNumber ?? 'No invoice number'"
                                            :class="!invoice.invoiceNumber ? 'text-danger' : ''"></a></td>
                                    <!-- <td x-text="invoice.discordName" class="data-text"></td> -->
                                    <td x-text="invoice.channel ?? 'No channel indicated'"
                                        :class="!invoice.channel ? 'text-danger' : ''"></td>
                                    <td x-text="invoice.role ?? 'No role indicated'"
                                        :class="!invoice.role ? 'text-danger' : ''"></td>
                                    <td x-text="invoice.fullName ?? 'No fullname indicated'"
                                        :class="!invoice.fullName ? 'text-danger' : ''"></td>
                                    <td x-text="invoice.date ?? 'No date indicated'"
                                        :class="!invoice.date ? 'text-danger' : ''"></td>
                                    <td x-text="'$' + invoice.amount.toLocaleString() ?? '0'"
                                        :class="!invoice.amount ? 'text-danger' : ''"></td>
                                    <td x-text="invoice.platform ?? 'No method indicated'"
                                        :class="!invoice.platform ? 'text-danger' : ''"></td>
                                    <td>
                                        <button
                                            @click="updateStatus(invoice)"
                                            :class="invoice.status === 'PAID' ? 'badge bg-success' : 'badge bg-danger'"
                                            x-text="invoice.status ?? 'No status indicated'"
                                            :class="!invoice.status ? 'text-danger' : ''">
                                        </button>
                                        <!-- <span :class="invoice.status === 'Paid' ? 'badge bg-success' : 'badge bg-danger'" x-text="invoice.status"></span> -->
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Controls -->
                <div class="pagination-controls justify-content-end">
                    <button class="prev-btn" @click="prevPage()" :disabled="currentPage === 1">
                        <img class="prev-icon" src="/wp-content/uploads/2025/02/Vector-5.svg" alt="Previous"> Previous
                    </button>
                    <template x-for="page in totalPages()" :key="page">
                        <button class="btn" :class="currentPage === page ? 'active' : ''" @click="currentPage = page" x-text="page"></button>
                    </template>
                    <button class="nxt-btn" @click="nextPage()" :disabled="currentPage === totalPages()">
                        Next <img class="next-icon" src="/wp-content/uploads/2025/03/next.png" alt="Next">
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
    document.addEventListener("DOMContentLoaded", function() {
        const invoiceInput = document.querySelector('#invoiceform_id [name="invoice_number"]');

        generateUniqueInvoice();

        function generateUniqueInvoice() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const randomNum = Math.floor(10000 + Math.random() * 90000);
            const newInvoice = `INV-${day}${month}${year}-${randomNum}`;

            // AJAX request to check if it exists
            fetch('<?php echo admin_url("admin-ajax.php"); ?>?action=check_invoice_number&invoice_number=' + newInvoice)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        // Try again if it exists
                        generateUniqueInvoice();
                    } else {
                        invoiceInput.value = newInvoice;
                    }
                });
        }
        // });
    });

    function invoiceFilter() {
        return {
            showAdd: false,
            showFilters: false,
            showActions: false,
            searchQuery: '',
            selectedStatus: '',
            selectedStandard: '',
            selectedRole: '',
            selectedChannel: '',
            chooseCurrency: '',
            choosePlatform: '',
            chooseChannel: '',
            chooseRole: '',

            currentPage: 1,
            itemsPerPage: 9,
            sortKey: '',
            sortAsc: true,

            invoices: [],
            the_channels: [],
            the_channelNames: [],
            roles: [],
            paymentMethods: [],
            currencies: [],

            init() {
                try {
                    this.invoices = JSON.parse(document.getElementById('invoice-data').textContent);
                    this.the_channels = JSON.parse(document.getElementById('channel-data').textContent);
                    this.the_channelNames = JSON.parse(document.getElementById('channel-names').textContent);
                    this.roles = JSON.parse(document.getElementById('role-data').textContent);
                    this.paymentMethods = JSON.parse(document.getElementById('payment-data').textContent);
                    this.currencies = JSON.parse(document.getElementById('currency-data').textContent);
                } catch (e) {
                    console.error("Failed to parse JSON data", e);
                }
                // console.log("Invoices", this.invoices);
                // console.log("Channels", this.the_channels);
                // console.log("Roles", this.roles);
                // console.log("Payments", this.paymentMethods);
                // console.log("Currencies", this.currencies);

            },

            sortBy(key) {
                if (this.sortKey === key) {
                    this.sortAsc = !this.sortAsc;
                } else {
                    this.sortKey = key;
                    this.sortAsc = true;
                }
            },
            async submitConfirmed() {
                let form = document.getElementById("invoiceform_id");

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                Swal.fire({
                    title: 'Save Invoice?',
                    text: 'Do you want to save this invoice?',
                    icon: 'question',
                    showCancelButton: true,
                    customClass: {
                        confirmButton: 'my-confirm-btn',
                        cancelButton: 'my-cancel-btn'
                    },
                    confirmButtonText: 'Yes, save it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Saving...",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();

                                const formEl = document.getElementById('invoiceform_id');
                                const formData = new FormData(formEl);

                                // Convert values to numbers and calculate total
                                const hours = parseFloat(formData.get('tasks_hours')) || 0;
                                const rate = parseFloat(formData.get('tasks_rate')) || 0;
                                const totalTask = hours * rate;

                                // Build tasks_data[] manually like PHP expects
                                const channels = formData.getAll('tasks_channel').map(id => parseInt(id));
                                const tasksData = [{
                                    tasks_channel: channels, // array of IDs
                                    tasks_description: formData.get('tasks_description'),
                                    tasks_hours: formData.get('tasks_hours'),
                                    tasks_rate: formData.get('tasks_rate')
                                }];

                                // Append as JSON so PHP can decode
                                formData.append('tasks_data', JSON.stringify(tasksData));
                                formData.append('action', 'save_invoice_creator');

                                fetch('/wp-admin/admin-ajax.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            setTimeout(() => {
                                                this.showModal = false; // close Alpine modal
                                                location.reload();
                                            }, 1000);
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: data.message || 'Something went wrong.'
                                            });
                                        }
                                    })
                                    .catch(err => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: err.message
                                        });
                                    });
                            }
                        });
                    }
                });
            },

            get anySelected() {
                return this.invoices.some(inv => inv.selected);
            },
            // Bulk action helpers:
            get areAllSelected() {
                return this.paginatedInvoices().length > 0 && this.paginatedInvoices().every(invoice => invoice.selected);
            },
            toggleSelectAll(event) {
                const checked = event.target.checked;
                this.paginatedInvoices().forEach(invoice => {
                    invoice.selected = checked;
                });
            },
            selectedInvoices() {
                return this.invoices.filter(invoice => invoice.selected);
            },
            updateStatus(invoice) {
                // Toggle status locally first
                const newStatus = invoice.status === 'PAID' ? 'PENDING' : 'PAID';
                const previousStatus = invoice.status;
                invoice.status = newStatus;

                // Construct the data payload
                const payload = {
                    id: invoice.id,
                    status: newStatus
                };

                // Send AJAX request to update invoice status in the database
                fetch('/wp-admin/admin-ajax.php?action=update_invoice_status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            // If update fails, revert the change and optionally show an error message
                            invoice.status = previousStatus;
                            // console.error('Status update failed:', data);
                        }
                    })
                    .catch(error => {
                        // Revert the change if the network request fails
                        invoice.status = previousStatus;
                        // console.error('Error updating status:', error);
                    });
            },
            // Bulk action helpers for Paid status
            bulkMarkPaid() {
                const selectedInvoices = this.selectedInvoices();

                // Update each invoice status to "Paid"
                selectedInvoices.forEach(invoice => {
                    invoice.status = 'PAID';
                });

                // Send a bulk update to the server
                this.updateBulkInvoiceStatus(selectedInvoices);

                // Unselect all after success
                this.invoices.forEach(inv => inv.selected = false);

                // Optionally close the bulk action menu
                this.showActions = false;
            },

            // Bulk action helpers for Pending status
            bulkMarkPending() {
                const selectedInvoices = this.selectedInvoices();

                // Update each invoice status to "Pending"
                selectedInvoices.forEach(invoice => {
                    invoice.status = 'PENDING';
                });

                // Send a bulk update to the server
                this.updateBulkInvoiceStatus(selectedInvoices);

                this.invoices.forEach(inv => inv.selected = false);
                this.showActions = false;

            },
            // Method to send the bulk update
            updateBulkInvoiceStatus(invoices) {
                fetch('/wp-admin/admin-ajax.php?action=update_invoice_status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(invoices.map(invoice => ({
                            id: invoice.id,
                            status: invoice.status
                        })))
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Bulk update successful');
                        } else {
                            console.error('Bulk update failed:', data);
                            invoices.forEach(invoice => {
                                invoice.status = invoice.previousStatus;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error updating bulk status:', error);
                        // Optionally, revert all status changes and show an error message
                        invoices.forEach(invoice => {
                            invoice.status = invoice.previousStatus;
                        });
                    });
            },
            //Filter Buttons and search query
            // get filteredInvoices1() {
            //     const q = this.searchQuery.toLowerCase();
            //     return this.invoices.filter(invoice =>
            //         (this.selectedStatus === '' || invoice.status === this.selectedStatus) &&
            //         (this.selectedStandard === '' || invoice.standard === this.selectedStandard) &&
            //         (this.selectedRole === '' || Array.isArray(invoice.role) && invoice.role.includes(this.selectedRole)) &&
            //         (this.selectedChannel === '' || invoice.channel === this.selectedChannel) &&
            //         (
            //             this.searchQuery === '' ||
            //             (invoice.discordName && invoice.discordName.toLowerCase().includes(q)) ||
            //             (invoice.fullName && invoice.fullName.toLowerCase().includes(q)) ||
            //             (invoice.invoiceNumber && invoice.invoiceNumber.toLowerCase().includes(q)) ||
            //             (invoice.channel && invoice.channel.toLowerCase().includes(q)) ||
            //             (invoice.platform && invoice.platform.toLowerCase().includes(q))
            //         )
            //     );
            // },
            get filteredInvoices() {
                const q = this.searchQuery.toLowerCase();
                return this.invoices.filter(invoice => {
                    const matchesStatus = this.selectedStatus === '' || invoice.status === this.selectedStatus;
                    const matchesStandard = this.selectedStandard === '' || invoice.standard === this.selectedStandard;
                    const matchesRole = this.selectedRole === '' || 
                        (Array.isArray(invoice.role) && invoice.role.includes(this.selectedRole));
                    const matchesChannel = this.selectedChannel === '' || invoice.channel === this.selectedChannel;

                    // normalize platform
                    let matchesPlatform = false;
                    if (invoice.platform) {
                        if (Array.isArray(invoice.platform)) {
                            matchesPlatform = invoice.platform.some(p => p.toLowerCase().includes(q));
                        } else {
                            matchesPlatform = invoice.platform.toLowerCase().includes(q);
                        }
                    }

                    const matchesSearch =
                        this.searchQuery === '' ||
                        (invoice.discordName && invoice.discordName.toLowerCase().includes(q)) ||
                        (invoice.fullName && invoice.fullName.toLowerCase().includes(q)) ||
                        (invoice.invoiceNumber && invoice.invoiceNumber.toLowerCase().includes(q)) ||
                        (invoice.channel && invoice.channel.toLowerCase().includes(q)) ||
                        matchesPlatform;
                        matchesRole;

                    return matchesStatus && matchesStandard && matchesChannel && matchesSearch;
                });
            },
            get sortedInvoices() {
                let sorted = [...this.filteredInvoices];

                if (!this.sortKey) return sorted;

                return sorted.sort((a, b) => {
                    let valA = a[this.sortKey];
                    let valB = b[this.sortKey];

                    if (this.sortKey === 'amount') {
                        valA = parseFloat(valA);
                        valB = parseFloat(valB);
                    } else if (this.sortKey === 'date') {
                        valA = new Date(valA);
                        valB = new Date(valB);
                    } else {
                        valA = valA?.toString().toLowerCase();
                        valB = valB?.toString().toLowerCase();
                    }

                    return (valA > valB ? 1 : valA < valB ? -1 : 0) * (this.sortAsc ? 1 : -1);
                });
            },
            paginatedInvoices() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                return this.sortedInvoices.slice(start, start + this.itemsPerPage);
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
            downloadReport() {
                let csvContent = "\uFEFF" + "Invoice Number,Discord Name,Fullname,Date,Amount,Payment Status,Platform,Channel, Role, Status\n" +
                    this.sortedInvoices.map(i =>
                        `"${i.invoiceNumber}","${i.discordName}","${i.fullName}","${i.date}","${i.amount}","${i.status}","${i.platform}","${i.channel}","${i.role}","${i.standard}"`).join("\n");

                let blob = new Blob([csvContent], {
                    type: "text/csv;charset=utf-8;"
                });
                let link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "Invoices.csv";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        };
    }
</script>
<style>
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

    .invoice-section {
        padding: 40px;
    }

    .invoice-desc {
        margin-bottom: 40px;
    }

    .nav-option {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 24px;
    }

    .search-container {
        display: flex;
        flex-direction: row;
        width: 500px;
        height: 50px;
        justify-content: center;
        align-items: center;
        border: 1px solid #ddd;
        padding: 15px 24px;
        border-radius: 10px;
        background-color: white !important;
    }

    .search-container input {
        padding-left: 8px;
        border: none !important;
        font-size: 16px;
    }

    .search-container input:focus {
        outline: none;
    }

    .invoice-tbl {
        padding: 40px;
        background-color: #fff;
        border-radius: 10px;
        width: 100%;
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

    .invoice-tbl table {
        width: 100%;
        min-width: 800px;
        border-collapse: collapse;
        white-space: nowrap;
    }

    .tbl-option {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .filter-dropdown {
        display: flex;
        gap: 20px;
    }

    .filter-section {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 10px;
    }

    button.iconic {
        padding: 0;
        border: solid 1px #D0D0D0 !important;
        background-color: #fff !important;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    button.iconic:hover {
        padding: 0;
        border: solid 1px #D0D0D0 !important;
        background-color: #fff !important;
    }

    .iconic-section {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
    }

    .inv-filter {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        position: sticky;
        left: 0;
        /* Stick it to the left */
        background: white;
        /* Prevent overlap with table */
        z-index: 10;
        /* Ensure it stays on top */
        padding: 10px;
    }

    div.drop-div {
        display: flex;
        align-items: center;
        background-color: #fff;
        border-radius: 10px;
        padding: 10px 10px 15px 10px;
        width: 250px;
        justify-content: space-between;
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

    /* Export button */
    .btn-export {
        background-color: #2194FF;
        border-radius: 10px;
        color: #fff;
        border: solid 1px #2194FF;
        box-shadow: 0px 10px 20px #D6E1EB;
        transition: box-shadow 0.3s ease-in-out;
    }

    .btn-export:hover,
    .btn-export:focus {
        background-color: #fff;
        border-radius: 10px;
        color: #2194FF;
        border: solid 1px #2194FF;
        box-shadow: 0px 10px 20px #D6E1EB;
    }

    /* Page controls */
    .pagination-controls {
        margin-top: 40px;
        text-align: right;
        width: 100%;
        background: white;
        padding: 10px 0;
        display: flex;
        column-gap: 0;
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

    select {
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 30px;
        color: white;
        font-size: 12px;
        font-weight: 300;
        border: unset;
    }

    .bg-success {
        background-color: #A9F6BD !important;
        color: #0A5F1F !important;
    }

    .bg-danger {
        background-color: #FFB8B4 !important;
        color: #831E18 !important;
    }

    .hidden {
        display: none;
    }

    /* table */
    table tbody>tr:nth-child(odd)>td,
    table tbody>tr:nth-child(odd)>th {
        background-color: transparent;
    }

    table td,
    table th {
        border: none;
    }

    table caption+thead tr:first-child td,
    table caption+thead tr:first-child th,
    table colgroup+thead tr:first-child td,
    table colgroup+thead tr:first-child th,
    table thead:first-child tr:first-child td,
    table thead:first-child tr:first-child th {
        border-block-start: none;
    }

    tr {
        border-bottom: solid 1px #E7E7E7;
    }

    th {
        font-weight: 700;
        color: #101010;
    }

    table.tbl-data td {
        font-weight: 300;
    }

    table.tbl-data td {
        color: #717171;
        font-weight: 300;
        font-size: 14px;
    }

    td.data-invoiceNumber a {
        color: #2194FF;
        text-decoration: underline;
    }

    table tbody tr:hover {
        background-color: #2194FF0D;
    }

    a {
        color: #2194FF;
        font-weight: 300;
        font-size: 14px;
    }

    img.sort-icon {
        width: 10px;
        height: 10px;
        margin-left: 10px;
    }

    ul {
        list-style-type: none;
        background-color: #fff;
        /* padding: 20px; */
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
        width: 180px;
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
        top: 115px;
        width: 200px;
        max-height: 300px;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 0px;
    }

    .drop-option3::-webkit-scrollbar {
        width: 2px;
    }

    .drop-option3::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .drop-option3::-webkit-scrollbar-thumb {
        background-color: #2194FF;
        border-radius: 10px;
    }

    .active {
        @apply bg-blue-100 text-blue-600;
    }

    .rotate-180 {
        transform: rotate(180deg);
    }

    .edits-icon:hover,
    .status-icon:hover {
        cursor: pointer;
    }

    .bulk-actions button {
        padding: 5px 10px;
        margin-bottom: 2px;
        background-color: #fff;
        border-radius: 10px;
        color: #101010;
        border: solid 1px #2194FF;
    }

    .bulk-actions .btn-bulk-paid {
        padding: 5px 10px;
        margin-bottom: 2px;
        background-color: #fff;
        border-radius: 10px;
        color: #0A5F1F;
        border: solid 1px #A9F6BD;
    }

    .bulk-actions .btn-bulk-pending {
        padding: 5px 10px;
        margin-bottom: 2px;
        background-color: #fff;
        border-radius: 10px;
        color: #831E18;
        border: solid 1px #FFB8B4;
    }

    .text-danger,
    .fallback {
        color: red;
        font-weight: 500;
        font-style: italic;
        text-transform: uppercase;
    }

    td.data-invoiceNumber a.text-danger {
        text-decoration: none;
    }

    div.drop-div.statusInv {
        width: 320px;
    }

    button.btn-add {
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        color: #2194FF;
        border: solid 1px #2194FF;
    }

    .btn-add:hover,
    .btn-add:focus {
        background-color: #2194FF33;
        color: #2194FF;
    }

    .modal-content {
        padding: 32px 12px;
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


    /* --------------------------------------------------- */
    /* <!-- Media Queries --> */
    @media only screen and (min-width: 1800px) {
        div.drop-div {
            width: 300px;
        }
    }
    @media only screen and (max-width: 1600px) {
        .filter-dropdown {
            flex-direction: row;
        }
        .filter-section {
            flex-direction: column;
            gap: 20px;
        }
        div.drop-div {
            width: 315px;
        }
        .drop-option3 {
            top: 110px;
        }
        .custom-standard, .custom-channel {
            top: 180px;
        }
    }

    @media only screen and (max-width: 1440px) {
        .invoice-section {
            padding: 40px 20px;
        }
        .inv-filter {
            padding: 0;
        }
        .filter-dropdown {
            gap: 5px;
        }
        .custom-standard.drop-option3,
        .custom-channel.drop-option3 {
            top: 180px;
        }
    }

    @media only screen and (max-width: 1162px) {
        .invoice-section {
            padding: 0;
        }

        .search-container {
            padding: 5px 10px 5px 10px;
            width: 400px;
        }
    }

    @media only screen and (max-width: 1024px) {
        .search-container {
            padding: 5px 10px 5px 10px;
            width: 330px;
        }
        .invoice-tbl {
            padding: 20px;
        }
        div.drop-div, div.drop-div.statusInv {
            width: 280px;
            height: 50px;
        }
        .btn-Nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .nav-option {
            align-items: flex-end;
        }
    }
    @media only screen and (max-width: 900px) {
        div.drop-div, div.drop-div.statusInv {
            width: 200px;
        }
    }
    @media only screen and (max-width: 768px) {
        .iconic-section {
            display: flex;
            flex-direction: row;
            align-items: flex-end;
            gap: 10px;
        }

        .filter-dropdown {
            display: flex;
            gap: 10px;
            flex-direction: column;
        }

        .drop-option3 {
            top: 120px;
        }

        .custom-status.drop-option3 {
            top: 100px;
        }

        .custom-role.drop-option3 {
            top: 225px;
        }

        .custom-channel.drop-option3 {
            top: 290px;
        }

        .custom-standard.drop-option3 {
            top: 165px;
        }

        .search-container {
            width: 250px;
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

    @media only screen and (max-width: 654px) {
        .nav-option {
            width: 100%;
            display: flex;
            flex-direction: row;
            row-gap: 30px;
        }
    }

    @media only screen and (max-width: 425px) {
        .search-container {
            width: 100%;
        }

        .nav-option {
            flex-direction: column-reverse;
            align-items: flex-end;
        }

        .invoice-tbl {
            padding: 20px 10px;
        }

        .pagination-controls .prev-btn {
            margin-right: 0;
        }

        .pagination-controls .nxt-btn {
            margin-left: 0;
        }

        .filter-dropdown {
            flex-direction: column;
        }

        .iconic-section {
            padding: 0;
        }

        div.drop-div,
        div.drop-div.statusInv {
            width: 250px;
        }

        .inv-filter {
            padding: 0 0 10px 0;
        }

        .custom-status.drop-option3 {
            top: 110px;
        }

        .custom-role.drop-option3 {
            top: 235px;
        }

        .custom-channel.drop-option3 {
            top: 305px;
        }

        .custom-standard.drop-option3 {
            top: 175px;
        }

        .btn-Nav {
            width: 100%;
        }
    }
    @media only screen and (max-width: 400px) {
        div.drop-div, div.drop-div.statusInv {
            width: 200px;
        }
    }
    @media only screen and (max-width: 350px) {
        div.drop-div, div.drop-div.statusInv {
            width: 150px;
        }
        .pagination-controls {
            margin-top: 20px;
            flex-direction: column;
        }
    }
</style>

<?php get_footer('admin'); ?>