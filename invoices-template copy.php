<?php

/**
 * Template Name: Invoices Template
 */
get_header('admin');
?>

<style>
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
    /* filter dropdown */
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
        left: 0; /* Stick it to the left */
        background: white; /* Prevent overlap with table */
        z-index: 10; /* Ensure it stays on top */
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
    /* Export button */
    .btn-export {
        background-color: #2194FF;
        border-radius: 10px;
        color: #fff;
        border: none;
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
    .pagination-controls button img{
        height: 12px;
    }
    .pagination-controls .prev-btn .prev-icon {
        margin-right: 5px;
    }
    .pagination-controls .nxt-btn .next-icon {
        margin-left: 5px;
    }
    .btn.disabled, .btn:disabled, fieldset:disabled .btn {
        border-color: transparent;
    }
    .btn-check:checked+.btn, .btn.active, .btn.show, .btn:first-child:active, :not(.btn-check)+.btn:active {
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
    table tbody tr:hover>td,
    table tbody tr:hover>th {
        background-color: transparent;
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
    ul{
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
    li button.btn.active, button:hover, button:focus{
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
    .edits-icon:hover, .status-icon:hover {
        cursor: pointer;
    }
    /* .status-edit {
        display: flex;
        flex-direction: row;
        gap: 10px;
    } */
    .bulk-actions button {
        padding: 5px 10px;
        margin-bottom: 2px;
        background-color: #fff;
        border-radius: 10px;
        color: #101010;
        border: solid 1px #2194FF;
        box-shadow: 0px 10px 20px #D6E1EB;
    }
    .bulk-actions .btn-bulk-paid{
        padding: 5px 10px;
        margin-bottom: 2px;
        background-color: #fff;
        border-radius: 10px;
        color: #0A5F1F;
        border: solid 1px #A9F6BD;
        box-shadow: 0px 10px 20px #D6E1EB;
    }
    .bulk-actions .btn-bulk-pending{
        padding: 5px 10px;
        margin-bottom: 2px;
        background-color: #fff;
        border-radius: 10px;
        color: #831E18;
        border: solid 1px #FFB8B4;
        box-shadow: 0px 10px 20px #D6E1EB;
    }
    .text-danger, .fallback {
        color: red;
        font-weight: 500;
        font-style: italic;
        text-transform: uppercase;
    }
    td.data-invoiceNumber a.text-danger {
        text-decoration: none;
    }
</style>
<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar'); ?>
    <div class="main-content">
        <div class="invoice-section" x-data="invoiceFilter()">
            <div class="invoice-desc">
                <h1 style="font-size: 30px; font-weight:600; color: #101010;">Invoices</h1>
                <p style="font-size: 16px; font-weight:300; color: #101010;">View and manage detailed invoices.</p>
            </div>
            <!-- Search & Filters -->
                <div class="nav-option">
                    <div class="search-container">
                        <img class="search-icon" src="/wp-content/uploads/2025/02/Vector-4.svg" alt="Search Icon">
                        <input type="text" placeholder="Search name here..." x-model="searchQuery" @input="filterInvoices">
                    </div>
                    <button class="btn-export" @click="downloadReport()">
                        <i class="fa-solid fa-download"></i> Export to CSV
                    </button>
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
                                <!-- Filter Dropdown (Initially Hidden) -->
                                <div class="filter-dropdown" x-show="showFilters" x-transition @click.away="showFilters = false">
                                    <!-- Status Filter -->
                                    <div class="drop-div" x-data="{ statusDropdown: false }">
                                        <label class="drop-title mb-1">Status:</label>
                                        <button class="drop-option2 w-full text-left flex justify-between items-center" @click="statusDropdown = !statusDropdown">
                                            <span x-text="selectedStatus || 'All'"></span>
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <ul class="custom-status drop-option3 mt-1" x-show="statusDropdown" x-transition @click.away="statusDropdown = false" >
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
                                    <!-- Role Filter -->
                                    <div class="drop-div" x-data="{ roleDropdown: false }">
                                        <label class="drop-title mb-1">Role:</label>
                                        <button class="drop-option2 w-full text-left flex justify-between items-center" @click="roleDropdown = !roleDropdown">
                                            <span x-text="selectedRole || 'All'"></span>
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <ul class="custom-role drop-option3 mt-1" x-show="roleDropdown" x-transition @click.away="roleDropdown = false">
                                            <li>
                                                <button
                                                    class="btn w-full text-left"
                                                    :class="selectedRole == '' ? 'active' : ''"
                                                    @click="selectedRole = ''; roleDropdown = false">
                                                    All
                                                </button>
                                            </li>
                                            <template x-for="role in roles" :key="role">
                                                <li>
                                                    <button
                                                        class="btn w-full text-left"
                                                        :class="selectedRole === role ? 'active' : ''"
                                                        @click="selectedRole = role; roleDropdown = false"
                                                        x-text="role">
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
                                        <ul class="custom-channel drop-option3 mt-1 " x-show="channelDropdown" x-transition @click.away="channelDropdown = false">
                                            <li>
                                                <button
                                                    class="btn w-full text-left" :class="selectedChannel === '' ? 'active' : ''"
                                                    @click="selectedChannel = ''; channelDropdown = false">
                                                    All
                                                </button>
                                            </li>
                                            <template x-for="channel in the_channels" :key="channel">
                                                <li>
                                                    <button
                                                        class="btn w-full text-left"
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
                                    <th @click="sortBy('fullName')" class="cursor-pointer">
                                        Fullname
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'fullName' && !sortAsc}">
                                    </th>
                                    <th @click="sortBy('date')" class="cursor-pointer">
                                        Date
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'date' && !sortAsc}">
                                    </th>
                                    <th @click="sortBy('amount')" class="cursor-pointer">
                                        Amount
                                        <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'amount' && !sortAsc}">
                                    </th>
                                    <th>Status</th>
                                    <th>Platform</th>
                                    <th>Channel</th>
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
                                        <td x-text="invoice.fullName ?? 'No fullname indicated'" 
                                            :class="!invoice.fullName ? 'text-danger' : ''"></td>
                                        <td x-text="invoice.date ?? 'No date indicated'" 
                                            :class="!invoice.date ? 'text-danger' : ''"></td>
                                        <td x-text="'$' + invoice.amount.toLocaleString() ?? '0'" 
                                            :class="!invoice.amount ? 'text-danger' : ''"></td>
                                        <td>
                                            <button 
                                                @click="updateStatus(invoice)"
                                                :class="invoice.status === 'PAID' ? 'badge bg-success' : 'badge bg-danger'" 
                                                x-text="invoice.status ?? 'No status indicated'" 
                                            :class="!invoice.status ? 'text-danger' : ''"">
                                            </button>
                                            <!-- <span :class="invoice.status === 'Paid' ? 'badge bg-success' : 'badge bg-danger'" x-text="invoice.status"></span> -->
                                        </td>
                                        <td x-text="invoice.platform ?? 'No method indicated'" 
                                            :class="!invoice.platform ? 'text-danger' : ''"></td>
                                        <td x-text="invoice.channel ?? 'No channel indicated'" 
                                            :class="!invoice.channel ? 'text-danger' : ''"></td>
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

    function invoiceFilter() {
        return {
            showFilters: false,
            showActions: false,
            searchQuery: '',
            selectedStatus: '',
            selectedRole: '',
            selectedChannel: '',
            currentPage: 1,
            itemsPerPage: 9,
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
            invoices: <?php
                $args = array(
                    'post_type'      => 'invoice-creator',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                );
                $invoices_query = new WP_Query($args);
                $invoice_data = [];
                if ($invoices_query->have_posts()) {
                    while ($invoices_query->have_posts()) {
                        $invoices_query->the_post();
                        // $tasks = get_field('your_tasks');
                        // $first_channel = 'No channel';

                        // if (!empty($tasks) && !empty($tasks[0]['tasks_channel'])) {
                        //     $channel_post = $tasks[0]['tasks_channel'];
                        //     if (is_array($channel_post)) {
                        //         $first_channel = get_the_title($channel_post[0]);
                        //     } else {
                        //         $first_channel = get_the_title($channel_post);
                        //     }
                        // }
                        $tasks = get_field('your_tasks');
                        $first_channel = null;

                        if (!empty($tasks) && !empty($tasks[0]['tasks_channel'])) {
                            $channel_post = $tasks[0]['tasks_channel'];
                            if (is_array($channel_post)) {
                                $first_channel = get_the_title($channel_post[0]);
                            } else {
                                $first_channel = get_the_title($channel_post);
                            }
                        }

                        $invoice_data[] = [
                            'id'           => get_the_ID(),
                            // 'fileName'     => get_field('file_name')['filename'] ?? 'No file name',
                            'invoiceNumber'  => get_field('invoice_number'),
                            'discordName'  => get_field('discord_name'),
                            'fullName' => get_field('fullname'),
                            'role' => get_field('role'),
                            'date'         => get_field('date_submitted'),
                            'amount'       => get_field('amount') ?? 0,
                            'status'       => get_field('status'),
                            'platform'     => get_field('payment_method'),
                            'link'         => get_permalink(),
                            // 'channel'      => get_field('your_tasks')[0]['tasks_channel'] ?? 'No channel',
                            'channel'       => $first_channel,
                        ];
                    }
                    wp_reset_postdata();
                }
                echo json_encode($invoice_data ?: []);
            ?>,
            
            init() {
                // console.log('the data', this.invoices);
            },

            the_channels: <?php
                $channels = [];
                $channel_query = new WP_Query(['post_type' => 'channel', 'posts_per_page' => -1]);
                while ($channel_query->have_posts()): $channel_query->the_post();
                    $channel_name = get_field('channel_name');
                    if ($channel_name) {
                        $channels[] = $channel_name;
                        $role[] = $role;
                    }
                endwhile;
                wp_reset_postdata();
                echo json_encode(array_values(array_unique($channels)));
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
            get filteredInvoices() {
                const q = this.searchQuery.toLowerCase();
                return this.invoices.filter(invoice =>
                    (this.selectedStatus === '' || invoice.status === this.selectedStatus) &&
                    (this.selectedRole === '' || invoice.role === this.selectedRole) &&
                    (this.selectedChannel === '' || invoice.channel === this.selectedChannel) &&
                    (
                        this.searchQuery === '' ||
                        (invoice.discordName && invoice.discordName.toLowerCase().includes(q)) ||
                        (invoice.fullName && invoice.fullName.toLowerCase().includes(q)) ||
                        (invoice.invoiceNumber && invoice.invoiceNumber.toLowerCase().includes(q)) ||
                        (invoice.channel && invoice.channel.toLowerCase().includes(q)) ||
                        (invoice.platform && invoice.platform.toLowerCase().includes(q))
                    )
                );
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
                let csvContent = "\uFEFF" + "Invoice Number,Discord Name,Fullname,Date,Amount,Status,Platform,Channel, Role\n" +
                    this.sortedInvoices.map(i => 
                        `"${i.invoiceNumber}","${i.discordName}","${i.fullName}","${i.date}","${i.amount}","${i.status}","${i.platform}","${i.channel}","${i.role}"`).join("\n");

                let blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
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
<!-- Media Queries -->
<style>
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
            width: 250px;
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
            top: 110px;
        }
        .custom-role.drop-option3 {
            top: 170px;
        }
        .custom-channel.drop-option3 {
            top: 230px;
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
        div.drop-div {
            width: 230px;
        }
        .inv-filter {
            padding: 0 0 10px 0;
        }
        .custom-status.drop-option3 {
            top: 100px;
        }
        .custom-role.drop-option3 {
            top: 155px;
        }
        .custom-channel.drop-option3 {
            top: 220px;
        }
    }
</style>
<?php get_footer('admin'); ?>