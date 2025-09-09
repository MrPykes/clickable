<?php

/**
 * Template Name: Miscellaneous Template
 */

get_header('admin');


// Channels (two arrays: one with ids+names, one with just names)
$channels = [];
$channelNames = [];

$channel_query = new WP_Query([
    'post_type'      => 'channel',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
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
    $channel_status = get_field('channel_status');

    // if ($channel_name && strtolower($channel_status) === 'active') {
    // For <select> (needs id + name)
    $channels[] = [
        'id'   => get_the_ID(),
        'name' => $channel_name
    ];

    // For <ul> (just names)
    $channelNames[] = $channel_name;
// }
endwhile;

wp_reset_postdata();


// Payment Methods
$methods = [];
$methods_query = new WP_Query([
    'post_type' => 'payment-method',
    'posts_per_page' => -1,
    'post_status' => 'publish'
]);
while ($methods_query->have_posts()): $methods_query->the_post();
    $methods[] = get_the_title();
endwhile;
wp_reset_postdata();
$methods = array_values(array_unique($methods));
sort($methods);

wp_enqueue_script('your-script', get_template_directory_uri() . '/path/to/myscript.js', ['jquery'], null, true);
wp_localize_script('your-script', 'my_ajax', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('wp_rest')
]);


$invoices = [];
$invoice_query = new WP_Query([
    'post_type'      => 'channel-insight',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    // 'meta_query'     => [
    //     [
    //         'key'     => 'channel_expense_amount',
    //         'value'   => 0,
    //         'compare' => '>',
    //         'type'    => 'NUMERIC'
    //     ]
    // ]
]);
while ($invoice_query->have_posts()): $invoice_query->the_post();
    // $invoices[] = [
    //     'id'                        => get_the_ID(),
    //     'link'                      => get_permalink(),
    //     'channel_invoice_number'    => get_field('channel_invoice_number'),
    //     'channel'                   => get_field('channel_name'),
    //     'channel_expenses_description' => get_field('channel_expenses_description'),
    //     'channel_expense_date'      => get_field('channel_expense_date'),
    //     'channel_expense_amount'    => (float) get_field('channel_expense_amount'),
    //     'channel_expenses_status'   => get_field('channel_expenses_status'),
    //     'selected'                  => false
    // ];
    $channel_expenses = get_field('your_channel_expenses');

    if ($channel_expenses) {
        foreach ($channel_expenses as $row) {
            $date        = $row['channel_expense_date'];
            $description = $row['channel_expenses_description'];
            $amount      = $row['channel_expense_amount'];

            $invoices[] = [
                'id'                        => get_the_ID(),
                'link'                      => get_permalink(),
                'channel_invoice_number'    => get_field('channel_invoice_number'),
                'channel'                   => get_field('channel_name'),
                'channel_expenses_description' => $description,
                'channel_expense_date'      => $date,
                'channel_expense_amount'    => (float) $amount,
                'channel_expenses_status'   => get_field('channel_expenses_status'),
                'selected'                  => false
            ];
        }
    }
endwhile;
wp_reset_postdata();


?>
<script type="application/json" id="channel-data">
    <?= wp_json_encode($channels) ?>
</script>
<script type="application/json" id="channel-names">
    <?= wp_json_encode(array_values(array_unique($channelNames))) ?>
</script>
<script type="application/json" id="payment-data">
    <?= wp_json_encode(array_values($methods)) ?>
</script>
<script type="application/json" id="invoice-data">
    <?= wp_json_encode($invoices) ?>
</script>


<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar');  ?>
    <div class="main-content">
        <div class="invoice-section" x-data="miscFilter()">
            <div class="invoice-desc">
                <h1 style="font-size: 30px; font-weight:600; color: #101010;">Miscellaneous</h1>
                <p style="font-size: 16px; font-weight:300; color: #101010;">View and manage detailed miscellaneous.</p>
            </div>
            <!-- Search & Filters -->
            <div class="nav-option">
                <div class="search-container">
                    <img class="search-icon" src="/wp-content/uploads/2025/02/Vector-4.svg" alt="Search Icon">
                    <input type="text" placeholder="Search name here..." x-model="searchQuery" @input="filterInvoices">
                </div>
                <div class="btn-Nav">
                    <button class="btn-add" @click="showAdd = true">
                        <i class="fa fa-plus"></i> Add Channel Expenses
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
                    <form id="invoiceform_id">
                        <!-- <div>
                            <label class="input-label">Invoice Number:</label>
                            <input type="text" name="channel_invoice_number" readonly>
                        </div> -->
                        <div>
                            <label class="input-label">Channel:</label>
                            <select class="form-control" x-model="chooseChannel" name="tasks_channel" required>
                                <option value="" disabled>Select a Channel</option>
                                <template x-for="(channel, index) in the_channels" :key="index">
                                    <!-- <option :value="channel" x-text="channel"></option> -->
                                    <option :value="channel.id" x-text="channel.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="input-label">Description:</label>
                            <textarea rows="4" placeholder="Enter description" name="channel_expenses_description" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="input-label">Amount:</label>
                                <input type="number" placeholder="Enter amount" name="channel_expense_amount" required>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label">Date Submitted:</label>
                                <input type="date" @click="$el.showPicker && $el.showPicker()" name="channel_expense_date" required>
                            </div>
                        </div>

                        <!-- <div class="row">
                            <div class="col-md-6">
                                <label class="input-label">Payment Status:</label>
                                <select name="channel_expenses_status">
                                    <option value="PENDING">PENDING</option>
                                    <option value="PAID">PAID</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label">Payment Method:</label>
                                <select class="form-control" x-model="choosePlatform" name="payment_method">
                                    <option value="" disabled>Select a Platform</option>
                                    <template x-for="(method, index) in paymentMethods" :key="index">
                                        <option :value="method" x-text="method"></option>
                                    </template>
                                </select>
                            </div>
                        </div> -->

                        <div class="modal-actions">
                            <button type="button" @click="submitConfirmed" class="confirm-btn">Save Changes</button>
                            <button type="button" @click="showAdd = false" class="cancel-btn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Invoice Table -->
            <div class="invoice-tbl">
                <!-- Filter Section -->
                <div class="iconic-section">
                    <div class="inv-filter">
                        <button @click="showFilters = !showFilters" class="iconic">
                            <img src="/wp-content/uploads/2025/02/sidebar-size.svg" alt="Toggle Filters">
                        </button>
                        <div class="filter-dropdown" x-show="showFilters" x-transition @click.away="showFilters = false">
                            <!-- Status Filter -->
                            <!-- <div class="drop-div" x-data="{ statusDropdown: false }">
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
                                </div> -->
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
                <div class="invoice-tbl-wrapper">
                    <table class="tbl-data">
                        <thead>
                            <tr class="tbl-row">
                                <th>No.</th>
                                <!-- <th class="manage-column">
                                        <input type="checkbox" @click="toggleSelectAll($event)" :checked="areAllSelected">
                                        All
                                    </th>
                                    <th class="cursor-pointer">
                                        Invoice Number
                                    </th> -->
                                <th @click="sortBy('channel')" class="cursor-pointer">
                                    Channel Name
                                    <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                        class="sort-icon transition-transform duration-300 inline-block ml-1"
                                        :class="{'rotate-180': sortKey === 'channel' && !sortAsc}">
                                </th>
                                <th>Description</th>
                                <th @click="sortBy('channel_expense_date')" class="cursor-pointer">
                                    Date
                                    <!-- <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                            class="sort-icon transition-transform duration-300 inline-block ml-1"
                                            :class="{'rotate-180': sortKey === 'channel_expense_date' && !sortAsc}"> -->
                                </th>
                                <th @click="sortBy('channel_expense_amount')" class="cursor-pointer">
                                    Amount
                                    <img src="/wp-content/uploads/2025/02/arrow-down.svg" alt="Sort Icon"
                                        class="sort-icon transition-transform duration-300 inline-block ml-1"
                                        :class="{'rotate-180': sortKey === 'channel_expense_amount' && !sortAsc}">
                                </th>
                                <!-- <th>Status</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(invoice, index) in paginatedInvoices()" :key="index">
                                <tr>
                                    <td x-text="((currentPage - 1) * itemsPerPage) + (index + 1)"></td>
                                    <!-- <td class="check-column">
                                            <input type="checkbox" x-model="invoice.selected">
                                        </td>
                                        <td class="data-invoiceNumber" x-text="invoice.channel_invoice_number || 'No invoice number'"
                                            :class="!invoice.channel_invoice_number ? 'text-danger' : ''">
                                            <a :href="invoice.link"
                                            ></a>
                                        </td> -->
                                    <td x-text="invoice.channel || 'No channel indicated'"
                                        :class="!invoice.channel ? 'text-danger' : ''"></td>
                                    <td x-text="invoice.channel_expenses_description || 'No description provided'"
                                        :class="!invoice.channel_expenses_description ? 'text-danger' : ''"></td>
                                    <td x-text="invoice.channel_expense_date || 'No date indicated'"
                                        :class="!invoice.channel_expense_date ? 'text-danger' : ''"></td>
                                    <td x-text="invoice.channel_expense_amount ? '$' + Number(invoice.channel_expense_amount).toLocaleString() : '$0'"
                                        :class="!invoice.channel_expense_amount ? 'text-danger' : ''"></td>
                                    <!-- <td>
                                            <button
                                                @click="updateStatus(invoice)"
                                                :class="[
                                                    invoice.channel_expenses_status === 'PAID' ? 'badge bg-success' : 'badge bg-danger',
                                                    !invoice.channel_expenses_status ? 'text-danger' : ''
                                                ]"
                                                x-text="invoice.channel_expenses_status || 'No status indicated'">
                                            </button>
                                        </td> -->
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
        const invoiceInput = document.querySelector('#invoiceform_id [name="channel_invoice_number"]');

        generateUniqueInvoice();

        function generateUniqueInvoice() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const randomNum = Math.floor(10000 + Math.random() * 90000);
            const newInvoice = `EXPENSES-${day}${month}${year}-${randomNum}`;

            // AJAX request to check if it exists
            fetch('<?php echo admin_url("admin-ajax.php"); ?>?action=check_channel_invoice_number&channel_invoice_number=' + newInvoice)
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

    function miscFilter() {
        return {
            showAdd: false,
            showFilters: false,
            showActions: false,
            selectedStatus: '',
            selectedChannel: '',
            choosePlatform: '',
            chooseChannel: '',
            sortKey: '',
            sortAsc: true,
            searchQuery: '',
            currentPage: 1,
            itemsPerPage: 9,

            invoices: [],
            the_channels: [],
            the_channelNames: [],
            paymentMethods: [],

            init() {
                try {
                    this.invoices = JSON.parse(document.getElementById('invoice-data').textContent);
                    this.the_channels = JSON.parse(document.getElementById('channel-data').textContent);
                    this.the_channelNames = JSON.parse(document.getElementById('channel-names').textContent);
                    this.paymentMethods = JSON.parse(document.getElementById('payment-data').textContent);
                } catch (e) {
                    console.error("Failed to parse miscFilter JSON:", e);
                    this.invoices = [];
                    this.the_channels = [];
                    this.the_channelNames = [];
                    this.paymentMethods = [];
                }
                // Debug
                // console.log("Channels", this.the_channels);
                // console.log("Payment Methods", this.paymentMethods);
                // console.log("The Invoices:" , this.invoices);
            },
            sortBy(key) {
                if (this.sortKey === key) {
                    this.sortAsc = !this.sortAsc;
                } else {
                    this.sortKey = key;
                    this.sortAsc = true;
                }
            },

            // async submitConfirmed() {
            //     let form = document.getElementById("invoiceform_id");
            //     let formData = new FormData(form);

            //     formData.append("action", "save_channel_insight"); 
            //     formData.append("_ajax_nonce", my_ajax.nonce);

            //     try {
            //         let response = await fetch(my_ajax.ajax_url, {
            //             method: "POST",
            //             body: formData
            //         });
            //         let result = await response.json();
            //         if(result.success){
            //             // alert("Invoice saved!");
            //             this.showAdd = false;
            //             window.location.reload();
            //         } else {
            //             alert("Error: " + result.data);
            //         }
            //     } catch (err) {
            //         console.error(err);
            //         alert("Something went wrong!");
            //     }
            // },
            async submitConfirmed() {
                let form = document.getElementById("invoiceform_id");

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                Swal.fire({
                    title: 'Save Expense?',
                    text: 'Do you want to save this expense record?',
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

                                let form = document.getElementById("invoiceform_id");
                                let formData = new FormData(form);

                                formData.append("action", "save_channel_insight");
                                formData.append("_ajax_nonce", my_ajax.nonce);

                                fetch(my_ajax.ajax_url, {
                                        method: "POST",
                                        body: formData
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Saved!',
                                                text: data.message || 'Expense saved successfully.',
                                                timer: 1500,
                                                showConfirmButton: false
                                            });
                                            setTimeout(() => {
                                                this.showAdd = false; // close Alpine modal
                                                window.location.reload();
                                            }, 1500);
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

            get filteredInvoices() {
                const q = this.searchQuery?.toLowerCase() || '';
                return this.invoices?.filter(invoice =>
                    (this.selectedStatus === '' || invoice.channel_expenses_status === this.selectedStatus) &&
                    (this.selectedChannel === '' || invoice.channel === this.selectedChannel) &&
                    (
                        this.searchQuery === '' ||
                        (invoice.channel_invoice_number && invoice.channel_invoice_number.toLowerCase().includes(q)) ||
                        (invoice.channel && invoice.channel.toLowerCase().includes(q))
                    )
                ) || [];
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
                const newStatus = invoice.channel_expenses_status === 'PAID' ? 'PENDING' : 'PAID';
                const previousStatus = invoice.channel_expenses_status;
                invoice.channel_expenses_status = newStatus;

                fetch('/wp-admin/admin-ajax.php?action=update_invoice_status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: invoice.id,
                            status: newStatus
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            invoice.channel_expenses_status = previousStatus; // revert on fail
                        }
                    })
                    .catch(() => {
                        invoice.channel_expenses_status = previousStatus;
                    });
            },
            bulkMarkPaid() {
                const selectedInvoices = this.selectedInvoices();
                selectedInvoices.forEach(inv => inv.channel_expenses_status = 'PAID');
                this.updateBulkInvoiceStatus(selectedInvoices);
                this.invoices.forEach(inv => inv.selected = false);
                this.showActions = false;
            },

            bulkMarkPending() {
                const selectedInvoices = this.selectedInvoices();
                selectedInvoices.forEach(inv => inv.channel_expenses_status = 'PENDING');
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
                            status: invoice.channel_expenses_status
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
            get sortedInvoices() {
                let sorted = [...this.filteredInvoices];

                if (!this.sortKey) return sorted;

                return sorted.sort((a, b) => {
                    let valA = a[this.sortKey];
                    let valB = b[this.sortKey];

                    if (this.sortKey === 'channel_expense_amount') {
                        valA = parseFloat(valA);
                        valB = parseFloat(valB);
                    } else if (this.sortKey === 'channel_expense_date') {
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
                let csvContent = "\uFEFF" + "No.,Channel Name,Date,Amount\n" +
                    this.sortedInvoices.map((i, index) =>
                        `"${index + 1}","${i.channel || ''}","${i.channel_expense_date || ''}","${i.channel_expense_amount || 0}"`
                    ).join("\n");

                let blob = new Blob([csvContent], {
                    type: "text/csv;charset=utf-8;"
                });
                let link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "Miscellaneous.csv";
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
        justify-content: flex-end;
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

    .edits-icon:hover,
    .status-icon:hover {
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
        /* box-shadow: 0px 10px 20px #D6E1EB; */
    }

    .bulk-actions .btn-bulk-paid {
        padding: 5px 10px;
        margin-bottom: 2px;
        background-color: #fff;
        border-radius: 10px;
        color: #0A5F1F;
        border: solid 1px #A9F6BD;
        /* box-shadow: 0px 10px 20px #D6E1EB; */
    }

    .bulk-actions .btn-bulk-pending {
        padding: 5px 10px;
        margin-bottom: 2px;
        background-color: #fff;
        border-radius: 10px;
        color: #831E18;
        border: solid 1px #FFB8B4;
        /* box-shadow: 0px 10px 20px #D6E1EB; */
    }

    button.btn-add {
        background-color: transparent;
        border-radius: 10px;
        border: solid 1px #2194FF;
        color: #2194FF;
    }

    button.btn-add a {
        color: #2194FF;
        font-size: 14px;
        font-weight: 600;
    }

    button.btn-add:hover a {
        color: #fff;
    }

    button.btn-add:hover {
        background-color: #0165C2;
        color: #fff;
    }

    /* --------------------------------------------------- */
    /* <!-- Media Queries --> */
    @media only screen and (min-width: 1800px) {
        div.drop-div {
            width: 300px;
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

        .filter-section {
            flex-direction: column;
            gap: 20px;
        }

        div.drop-div {
            width: 315px;
        }

        .custom-standard.drop-option3,
        .custom-channel.drop-option3 {
            top: 100px;
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

        div.drop-div {
            width: 300px;
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

        .custom-standard.drop-option3 {
            top: 165px;
        }

        .search-container {
            width: 100%;
        }

        .nav-option {
            flex-direction: column-reverse;
            align-items: flex-end;
            gap: 10px;
        }

        .btn-Nav {
            width: 100%;
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
        .search-container {
            width: 100%;
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
            width: 230px;
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

        .custom-standard.drop-option3 {
            top: 175px;
        }

        .btn-Nav {
            width: 100%;
        }
    }
</style>

<?php get_footer('admin'); ?>