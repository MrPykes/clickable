<?php get_header('admin');

$post_id = get_the_ID();

$tasks = [];
$subtotal = 0;
$total_deductions = 0;
$overall_total = 0;

// Get tasks
if (have_rows('your_tasks', $post_id)) {
    while (have_rows('your_tasks', $post_id)) {
        the_row();
        $description = get_sub_field('tasks_description');
        $hours = floatval(get_sub_field('tasks_hours'));
        $rate = floatval(get_sub_field('tasks_rate'));
        $amount = $hours * $rate;
        $subtotal += $amount;

        $days = floor($hours / 8);
        $remaining_hours = $hours % 8;
        $days_text = ($days > 0 ? "{$days} Days" : '') . ($remaining_hours > 0 ? " {$remaining_hours} hrs" : '');

        $tasks[] = [
            'taskDescription' => $description,
            'taskHrsQuantity' => $hours,
            'taskHrRate' => $rate,
            'taskDaysText' => trim($days_text),
            'taskTotalAmount' => $amount
        ];
    }
}

// Get deductions
if (have_rows('your_deductions', $post_id)) {
    while (have_rows('your_deductions', $post_id)) {
        the_row();
        $deduct_hours = floatval(get_sub_field('deduct_hours'));
        $deduct_rate = floatval(get_sub_field('deduct_rate'));
        $deduct_amount = $deduct_hours * $deduct_rate;
        $total_deductions += $deduct_amount;
    }
}

$overall_total = $subtotal - $total_deductions;

// Get payment method(s)
$platforms = get_field('payment_method', $post_id);
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
$periodDate = get_field('period_covered', $post_id);
$dueDate = get_field('due_date', $post_id);
$period_date_obj = DateTime::createFromFormat('m/d/Y', $periodDate);
$due_date_obj = DateTime::createFromFormat('m/d/Y', $dueDate);

$single_invoice = [
    'id'             => $post_id,
    'invoiceNumber'  => get_field('invoice_number', $post_id),
    'discordName'    => get_field('discord_name', $post_id),
    'fullName'       => get_field('fullname', $post_id),
    'full_address'   => get_field('full_address', $post_id),
    'role'           => get_field('role', $post_id),
    'periodCovered'  => $period_date_obj->format('F j, Y'),
    'dueDate'        => $due_date_obj->format('F j, Y'),
    'date'           => get_field('date_submitted', $post_id),
    'amount'         => get_field('amount', $post_id) ?? 0,
    'status'         => get_field('status', $post_id),
    // 'platform'       => get_field('payment_method', $post_id),
    'platform'       => $platform_titles, // Now titles, not IDs
    'payEmail' => nl2br(esc_html(get_field('payment_details', $post_id) ?? get_field('pay_email', $post_id) ?? get_field('email', $post_id))),
    // 'payEmail'       => htmlentities(get_field('payment_details', $post_id) ?: ''),
    // 'payEmail'       => get_field('payment_details', $post_id),
    // 'payEmail'       => get_field('payment_details', $post_id) ?? get_field('pay_email', $post_id) ?? get_field('email', $post_id),
    'tasks'          => $tasks,
    'subtotal'       => $subtotal,
    'totalDeductions' => $total_deductions,
    'overallTotal'   => $overall_total
];

// echo wp_json_encode($single_invoice ?: []);

?>
<script type="application/json" id="invoice-data">
    <?php echo wp_json_encode($single_invoice ?: []); ?>
</script>


<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar'); ?>

    <div class="main-content">
        <div class="view-invoice-section">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/invoices/">INVOICE</a></li>
                    <li class="breadcrumb-item active" aria-current="page">VIEW INVOICE</li>
                </ol>
            </nav>
            <div class="pdf-btn">
                <button class="btn-pdf">
                    <i class="fa-solid fa-download"></i> Download as PDF
                </button>
            </div>
            <div class="pdf-cont" x-data="invoiceFilter()">
                <div class="pdf-cont-desc">
                    <div>
                        <span>Billed to</span>
                        <h2>Clickable</h2>
                        <p>Flynn James / Thomas Matheson <br>
                            21 Orchid Lane <br>
                            Maroochydore <br>
                            QLD <br>
                            4558 <br>
                            AUSTRALIA <br>
                        </p>

                    </div>
                    <div>
                        <span>Pay to</span>
                        <h2 x-text="invoice.fullName ?? 'No fullname indicated'"
                            :class="!invoice.fullName ? 'text-danger' : ''"></h2>
                        <p x-text="invoice.full_address ?? 'No address indicated'"
                            :class="!invoice.full_address ? 'text-danger' : ''"></p>
                    </div>
                    <div>
                        <span>Invoice Number</span>
                        <h2 x-text="invoice.invoiceNumber ?? 'No invoice number indicated'"
                            :class="!invoice.invoiceNumber ? 'text-danger' : ''" class="custom-inv"></h2>
                    </div>
                </div>
                <div class="pdf-cont-date">
                    <span>Period Covered</span>
                    <h2 x-text="(invoice.periodCovered && invoice.dueDate) 
                        ? `${invoice.periodCovered} - ${invoice.dueDate}` 
                        : 'No period or due date indicated'"
                        :class="!(invoice.periodCovered && invoice.dueDate) ? 'text-danger' : ''" x-cloak>
                    </h2>
                </div>
                <div class="pdf-table">
                    <div class="table-responsive">
                        <template x-if="invoiceItem().length > 0">
                            <table class="tbl-pdf">
                                <thead>
                                    <th>Description</th>
                                    <th style="text-align: center;">Quantity</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </thead>
                                <tbody>
                                    <template x-for="task in invoiceItem()" :key="task.id">
                                        <tr>
                                            <td x-text="task.taskDescription ?? 'No description indicated'"
                                                :class="!task.taskDescription ? 'text-danger' : ''">
                                            </td>
                                            <td x-text="task.taskHrsQuantity ?? 'No Quantity indicated'"
                                                :class="!task.taskHrsQuantity ? 'text-danger' : ''" style="text-align: center;">
                                            </td>
                                            <td x-text="task.taskHrRate ?? 'No Rate indicated'"
                                                :class="!task.taskHrRate ? 'text-danger' : ''">
                                            </td>
                                            <td x-text="task.taskTotalAmount">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td style="font-size: 16px; font-weight: 700; color: #717171; border-bottom: solid 1px #717171 !important;">
                                            SUBTOTAL</td>
                                        <td x-text="`$${invoice.subtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`"
                                            class="fw-bold" style="font-size: 18px; font-weight: 700; color: #101010; border-bottom: solid 1px #717171 !important;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td style="font-size: 16px; font-weight: 700; color: #717171;">TOTAL</td>
                                        <td x-text="`$${invoice.overallTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`"
                                            class="fw-bold" style="font-size: 18px; font-weight: 700; color: #101010;">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </template>

                        <template x-if="invoiceItem().length === 0">
                            <div class="text-center py-4 text-muted" style="font-style: italic;">
                                No tasks available to display.
                            </div>
                        </template>
                    </div>
                </div>
                <div class="pdf-pay-info">
                    <span>Payment Information</span>
                    <h2 x-text="invoice.fullName ?? 'No fullname indicated'"
                        :class="!invoice.fullName ? 'text-danger' : ''"></h2>
                    <h2 x-text="invoice.platform ?? 'No platform indicated'"
                        :class="!invoice.platform ? 'text-danger' : ''"></h2>
                    <p x-html="invoice.payEmail ?? 'No details indicated'"
                        :class="!invoice.payEmail ? 'text-danger' : ''"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // window.invoiceData = < ?php
    //     $post_id = get_the_ID();

    //     $tasks = [];
    //     $subtotal = 0;
    //     $total_deductions = 0;
    //     $overall_total = 0;

    //     // Get tasks
    //     if (have_rows('your_tasks', $post_id)) {
    //         while (have_rows('your_tasks', $post_id)) {
    //             the_row();
    //             $description = get_sub_field('tasks_description');
    //             $hours = floatval(get_sub_field('tasks_hours'));
    //             $rate = floatval(get_sub_field('tasks_rate'));
    //             $amount = $hours * $rate;
    //             $subtotal += $amount;

    //             $days = floor($hours / 8);
    //             $remaining_hours = $hours % 8;
    //             $days_text = ($days > 0 ? "{$days} Days" : '') . ($remaining_hours > 0 ? " {$remaining_hours} hrs" : '');

    //             $tasks[] = [
    //                 'taskDescription' => $description,
    //                 'taskHrsQuantity' => $hours,
    //                 'taskHrRate' => $rate,
    //                 'taskDaysText' => trim($days_text),
    //                 'taskTotalAmount' => $amount
    //             ];
    //         }
    //     }

    //     // Get deductions
    //     if (have_rows('your_deductions', $post_id)) {
    //         while (have_rows('your_deductions', $post_id)) {
    //             the_row();
    //             $deduct_hours = floatval(get_sub_field('deduct_hours'));
    //             $deduct_rate = floatval(get_sub_field('deduct_rate'));
    //             $deduct_amount = $deduct_hours * $deduct_rate;
    //             $total_deductions += $deduct_amount;
    //         }
    //     }

    //     $overall_total = $subtotal - $total_deductions;

    //     $single_invoice = [
    //         'id'             => $post_id,
    //         'invoiceNumber'  => get_field('invoice_number', $post_id),
    //         'discordName'    => get_field('discord_name', $post_id),
    //         'fullName'       => get_field('fullname', $post_id),
    //         'full_address'   => get_field('full_address', $post_id),
    //         'role'           => get_field('role', $post_id),
    //         'periodCovered'  => get_field('period_covered', $post_id),
    //         'dueDate'        => get_field('due_date', $post_id),
    //         'date'           => get_field('date_submitted', $post_id),
    //         'amount'         => get_field('amount', $post_id) ?? 0,
    //         'status'         => get_field('status', $post_id),
    //         'platform'       => get_field('payment_method', $post_id),
    //         'payEmail'       => get_field('pay_email', $post_id),
    //         'tasks'          => $tasks,
    //         'subtotal'       => $subtotal,
    //         'totalDeductions'=> $total_deductions,
    //         'overallTotal'   => $overall_total
    //     ];

    //     echo json_encode($single_invoice ?: []);
    // ?>;

    // function invoiceFilter() {
    //     return {
    //         invoice: window.invoiceData || {},

    //         invoiceItem() {
    //             return (this.invoice.tasks || []).map(task => {
    //                 const hours = task.taskHrsQuantity || 0;
    //                 const rate = task.taskHrRate || 0;
    //                 const amount = task.taskTotalAmount || (hours * rate);
    //                 const displayHrs = task.taskDaysText || '';
    //                 return {
    //                     id: `${this.invoice.id}-${task.taskDescription}`,
    //                     taskDescription: task.taskDescription || null,
    //                     taskHrsQuantity: `${hours} (${displayHrs})`,
    //                     taskHrRate: `$${rate.toLocaleString(undefined, {minimumFractionDigits: 2})}/hr`,
    //                     taskTotalAmount: `$${amount.toLocaleString(undefined, {minimumFractionDigits: 2})}`
    //                 };

    //             });
    //         }
    //     };
    // }
    function invoiceFilter() {
        return {
            invoice: {},

            init() {
                try {
                    const raw = document.getElementById("invoice-data")?.textContent || "{}";
                    this.invoice = JSON.parse(raw) || {};
                } catch (e) {
                    console.error("Failed to parse invoice data", e);
                    this.invoice = {};
                }
            },

            invoiceItem() {
                return (this.invoice.tasks || []).map(task => {
                    const hours = task.taskHrsQuantity || 0;
                    const rate = task.taskHrRate || 0;
                    const amount = task.taskTotalAmount || (hours * rate);
                    const displayHrs = task.taskDaysText || '';

                    return {
                        id: `${this.invoice.id}-${task.taskDescription}`,
                        taskDescription: task.taskDescription || null,
                        taskHrsQuantity: `${hours} (${displayHrs})`,
                        taskHrRate: `$${rate.toLocaleString(undefined, {minimumFractionDigits: 2})}/hr`,
                        taskTotalAmount: `$${amount.toLocaleString(undefined, {minimumFractionDigits: 2})}`
                    };
                });
            }
        };
    }

    document.querySelector('.btn-pdf').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');

        const pdfContent = document.querySelector('.pdf-cont');

        // Backup original styles
        const originalWidth = pdfContent.style.width;
        const originalPadding = pdfContent.style.padding;
        const originalFontSize = pdfContent.style.fontSize;

        // Apply desktop styles temporarily
        pdfContent.style.width = '1200px';
        pdfContent.style.padding = '40px';
        pdfContent.style.fontSize = '14px';

        // Always parse from <script id="invoice-data">
        let invoiceData = {};
        try {
            const raw = document.getElementById("invoice-data")?.textContent || "{}";
            invoiceData = JSON.parse(raw) || {};
        } catch (e) {
            console.error("Failed to parse invoice data", e);
        }

        const invoiceNumber = (invoiceData.invoiceNumber || 'NoInvoice').replace(/\s+/g, '_');
        const fullName = (invoiceData.fullName || 'NoName').replace(/\s+/g, '_').toUpperCase();
        const filename = `CLICKABLE_${invoiceNumber}_${fullName}.pdf`;

        html2canvas(pdfContent, {
            scale: 2
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const imgWidth = 210; // A4 width in mm
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            doc.addImage(imgData, 'PNG', 0, 10, imgWidth, imgHeight);
            doc.save(filename);

            // Revert styles after saving
            pdfContent.style.width = originalWidth;
            pdfContent.style.padding = originalPadding;
            pdfContent.style.fontSize = originalFontSize;
        });
    });
</script>

<style>
    .view-invoice-section {
        padding: 56px 64px;
    }

    .breadcrumb {
        font-weight: 300;
        font-size: 14px;
        column-gap: 24px;
    }

    .breadcrumb-item a {
        color: #717171;
        text-decoration: none;
    }

    .breadcrumb-item+.breadcrumb-item::before {
        float: left;
        padding-right: var(--bs-breadcrumb-item-padding-x);
        color: var(--bs-breadcrumb-divider-color);
        content: var(--bs-breadcrumb-divider, ">");
        margin-right: 24px;
    }

    .breadcrumb-item.active {
        color: #101010;
    }

    /* PDF button */
    .btn-pdf {
        background-color: #2194FF;
        border-radius: 10px;
        color: #fff;
        border: solid 1px #2194FF;
        box-shadow: 0px 10px 20px #D6E1EB;
        transition: box-shadow 0.3s ease-in-out;
    }

    .btn-pdf:hover,
    .btn-pdf:focus {
        background-color: #fff;
        border-radius: 10px;
        color: #2194FF;
        border: solid 1px #2194FF;
        box-shadow: 0px 10px 20px #D6E1EB;
    }

    .pdf-cont {
        border-radius: 10px;
        background-color: #fff;
        padding: 80px;
    }

    .pdf-cont-desc {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        column-gap: 80px;
        margin-bottom: 80px;
    }

    .pdf-btn {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 24px;

    }

    .pdf-cont-desc span,
    .pdf-cont-desc p,
    .pdf-cont-date span,
    .pdf-pay-info span {
        color: #717171;
        font-weight: 300;
        font-size: 16px;
    }

    .pdf-cont-desc h2,
    .pdf-cont-date h2 {
        color: #101010;
        font-weight: 600;
        font-size: 24px;
        letter-spacing: -0.01em;
        margin-bottom: 32px;
        margin-top: 32px;
    }

    .pdf-cont-date {
        margin-bottom: 80px;
    }

    table tbody tr:hover>td,
    table tbody tr:hover>th {
        background-color: transparent;
    }

    .pdf-table {
        margin-bottom: 80px;
    }

    .pdf-pay-info h2,
    .pdf-pay-info p {
        color: #101010;
        font-weight: 600;
        font-size: 24px;
        letter-spacing: -0.01em;
        margin-bottom: 32px;
    }

    .pdf-table table td,
    table th {
        border: none;
    }

    .pdf-table table tbody>tr:nth-child(odd)>td,
    table tbody>tr:nth-child(odd)>th {
        background-color: transparent;
    }

    .pdf-table table caption+thead tr:first-child td,
    table caption+thead tr:first-child th,
    table colgroup+thead tr:first-child td,
    table colgroup+thead tr:first-child th,
    table thead:first-child tr:first-child td,
    table thead:first-child tr:first-child th {
        border-block-start: 1px solid #717171 !important;
        border-block-end: 1px solid #717171 !important;
    }

    .pdf-table table tfoot th,
    table thead th {
        font-weight: 700;
        font-size: 16px;
        color: #717171;
    }

    .pdf-table table th {
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .pdf-table table td {
        padding-top: 40px;
        font-weight: 300;
        font-size: 18px;
    }

    .pdf-table tr {
        border: unset;
    }

    .pdf-table table td.subTotal {
        font-size: 16px;
        font-weight: 700;
        color: #717171 !important;
        border-bottom: solid 1px #717171 !important;
    }

    .pdf-table table td.total {
        font-size: 16px;
        font-weight: 700;
        color: #717171 !important;
    }

    .text-danger {
        color: red;
        font-weight: 500;
        font-style: italic;
        text-transform: uppercase;
    }

    /* <!-- Media Queries --------------> */
    @media only screen and (max-width: 1162px) {
        .view-invoice-section {
            padding: 50px 0 0 0;
        }

        .pdf-cont {
            padding: 40px;
        }
    }

    @media only screen and (max-width: 905px) {
        .view-invoice-section {
            padding: 25px 0 0 0;
        }

        .breadcrumb {
            margin-bottom: 0px;
        }

        .pdf-cont {
            padding: 30px 20px;
        }

        .pdf-cont-desc {
            column-gap: 20px;
            margin-bottom: 40px;
        }

        .pdf-cont-date {
            margin-bottom: 40px;
        }

        .pdf-cont-desc h2,
        .pdf-cont-date h2 {
            font-size: 20px;
            margin: 20px 0 20px 0;
        }

        .pdf-cont-desc h2.custom-inv {
            font-size: 14px;
        }

        .pdf-table table td {
            padding-top: 10px;
        }

        .pdf-table table tbody td {
            padding: 10px;
        }

        .pdf-pay-info h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .table-responsive::-webkit-scrollbar {
            height: 5px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #336;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .tbl-pdf {
            width: 600px;
            /* Ensures the table overflows and scrolls */
            min-width: 100%;
        }
    }

    @media only screen and (max-width: 768px) {

        .pdf-cont-desc h2,
        .pdf-cont-date h2 {
            font-size: 22px;
            margin-bottom: 22px;
            margin-top: 22px;
        }

        .pdf-pay-info h2 {
            font-size: 22px;
            margin-bottom: 22px;
        }

        .pdf-cont-desc {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            row-gap: 20px;
        }

        .pdf-table {
            width: 100%;
        }

        .pdf-table tbody tr td {
            font-size: 16px;
        }

        .tbl-pdf {
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 12px;
        }

        .tbl-pdf th,
        .tbl-pdf td {
            padding: 6px 5px;
            word-break: break-word;
            text-align: left;
        }

        .tbl-pdf th {
            font-size: 14px;
        }

        .tbl-pdf .text-end {
            text-align: right;
        }

        .pdf-table table td.subTotal,
        .pdf-table table td.total {
            font-size: 14px;
        }

        .view-invoice-section {
            padding: 0;
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
        .breadcrumb {
            margin-bottom: 15px;
        }

        .tbl-pdf th,
        .tbl-pdf td {
            padding: 8px;
        }

        .pdf-cont-desc h2,
        .pdf-cont-date h2,
        .pdf-pay-info h2,
        .pdf-table table td {
            font-size: 14px;
        }

        .pdf-table table td.text-end {
            font-size: 18px !important;
        }

    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<?php get_footer('admin'); ?>