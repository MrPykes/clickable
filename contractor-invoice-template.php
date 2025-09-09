<?php

/**
 * Template Name: Contractor Invoice Template
 */

get_header('admin');
?>

<!-- < ?php
$payment_method = new WP_Query([
    'post_type' => 'payment-method',
    'post_status' => 'publish',
    'posts_per_page' => -1,
]);

$payment_method_data = [];
if ($payment_method->have_posts()) {
    while ($payment_method->have_posts()) {
        $payment_method->the_post();
        $payment_method_data[] = get_the_title();
    }
}
wp_reset_postdata();

?> -->

<?php
function generate_unique_invoice_number()
{
    do {
        // Generate invoice number
        $now       = current_time('timestamp'); // WP timezone safe
        $year      = date('Y', $now);
        $month     = date('m', $now);
        $day       = date('d', $now);
        $randomNum = wp_rand(10000, 99999);

        $newInvoice = "INV-{$day}{$month}{$year}-{$randomNum}";

        // Check if it exists using WP_Query
        $query = new WP_Query([
            'post_type'      => 'invoice-number',
            'title'          => $newInvoice, // direct title match
            'posts_per_page' => 1,
            'fields'         => 'ids', // we only need IDs
        ]);
    } while ($query->have_posts()); // loop until unique

    wp_reset_postdata();

    return $newInvoice;
}
$newInvoice = generate_unique_invoice_number();

// $post_id = wp_insert_post([
//     'post_title'  => $newInvoice,
//     'post_type'   => 'invoice-number',
//     'post_status' => 'publish',
// ]);

?>

<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar');  ?>

    <div class="main-content">
        <div class="inv-creator">
            <?php echo do_shortcode('[elementor-template id="508"]'); ?>
        </div>
    </div>
</div>
<!-- <script>
    document.addEventListener("DOMContentLoaded", function () {
        const invoiceInput = document.querySelector('#invoiceform_id [name="invoice_number"]');

        invoiceInput.addEventListener("click", function () {
            // Check if already filled to avoid overwriting
            if (invoiceInput.value.trim() !== '') return;

            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const randomNum = Math.floor(10000 + Math.random() * 90000); // 5-digit random

            const generatedNumber = `INV-${day}${month}${year}-${randomNum}`;
            invoiceInput.value = generatedNumber;
        });
    });
</script> -->
<!-- <script type="application/json">
    < ?php echo json_encode($payment_method_data); ?>
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const paymentMethods = JSON.parse(
            document.querySelector('script[type="application/json"]').textContent
        );

        // Get all select elements with name='form_fields[payment_method]'
        const selects = document.querySelectorAll("select[name='form_fields[payment_method]']");

        selects.forEach(function(select) {
            // Clear existing options
            select.innerHTML = "";

            if (paymentMethods.length > 0) {
                paymentMethods.forEach(function(option) {
                    const opt = new Option(option, option);
                    select.add(opt);
                });
            } else {
                select.add(new Option("No Payment Methods available", ""));
            }
        });
    });
</script> -->
<script type="application/json" id="invoice-number">
    <?php echo json_encode($newInvoice) ?>
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const invoiceInput = document.querySelector('#invoiceform_id [name="invoice_number"]');

        try {
            const insightRaw = document.getElementById('invoice-number')?.textContent;
            // this.data = insightRaw ? JSON.parse(insightRaw) : [];
            invoiceInput.value = insightRaw ? JSON.parse(insightRaw) : [];
        } catch (e) {
            invoiceInput.value = [];
            console.error("Failed to parse insight-data JSON:", e);
        }
        // invoiceInput.addEventListener("click", function () {
        //     if (invoiceInput.value.trim() !== '') return;

        // generateUniqueInvoice();

        // function generateUniqueInvoice() {
        //     const now = new Date();
        //     const year = now.getFullYear();
        //     const month = String(now.getMonth() + 1).padStart(2, '0');
        //     const day = String(now.getDate()).padStart(2, '0');
        //     const randomNum = Math.floor(10000 + Math.random() * 90000);
        //     const newInvoice = `INV-${day}${month}${year}-${randomNum}`;

        //     // AJAX request to check if it exists
        //     fetch('< ?php echo admin_url("admin-ajax.php"); ?>?action=check_invoice_number&invoice_number=' + newInvoice)
        //         .then(response => response.json())
        //         .then(data => {
        //             if (data.exists) {
        //                 // Try again if it exists
        //                 generateUniqueInvoice();
        //             } else {
        //                 invoiceInput.value = newInvoice;
        //             }
        //         });
        // }
        // });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dateform = document.querySelector('#invoiceform_id [name="period_covered"]');

        if (dateform && dateform.value.trim() === '') {
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = String(now.getFullYear()).slice(-2);

            const formattedDate = `${day}/${month}/${year}`;
            dateform.value = formattedDate;
        }
    });
</script>




<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        const deductionAddButton = document.querySelector('.elementor-field-group-deduct_end .repeater-field-button-add');
        const deductionWarpItem = document.querySelector('.elementor-field-group-deduct_end .repeater-field-warp-item');

        if (deductionAddButton && deductionWarpItem) {
            deductionAddButton.addEventListener('click', function (e) {
            e.preventDefault();
            
            const hiddenFields = deductionWarpItem.querySelectorAll('.repeater-field-item[style*="display: none"], .repeater-field-item:not([style])');

            if (hiddenFields.length > 0) {
                hiddenFields[0].style.display = 'block';
            }
            });
        }
    });
</script> -->

<style>
    .invoice-cont .form-invoice div.elementor-field-type-upload {
        margin-bottom: 64px !important;
        border: dashed 1px #D0D0D0;
        padding: 40px;
        border-radius: 24px;
        flex-direction: column;
    }

    .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler {
        font-family: sora;
        padding: 40px 40px 60px 40px !important;
        border: dashed 1px #D0D0D0;
        border-radius: 24px;
        height: 260px !important;
    }

    .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler .elementor-dragandrophandler-inner .elementor-text-drop::before {
        content: url('/wp-content/uploads/2025/02/Vector-1.svg');
        width: 24px;
        height: 16px;
        margin-bottom: 27px;
    }

    .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler .elementor-dragandrophandler-inner .elementor-text-drop {
        font-size: 16px;
        font-weight: 700;
        color: #101010;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler .elementor-dragandrophandler-inner .elementor-text-or {
        font-size: 14px;
        font-weight: 300;
        color: #717171;
    }

    .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler .elementor-dragandrophandler-inner .elementor-text-browser a {
        font-size: 14px;
        font-weight: 600;
        color: #717171;
        display: inline !important;
    }

    .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler .elementor-dragandrophandler-inner .elementor-text-browser {
        padding: 10px !important;
    }

    .invoice-cont .form-invoice div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_7f01e71 {
        margin-top: 64px !important;
        margin-bottom: 0 !important;
    }

    .invoice-cont .form-invoice div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_67f3e30 {
        margin-bottom: 0 !important;
    }

    .invoice-cont .form-invoice div.elementor-field-repeater-end {
        display: flex;
        flex-direction: column-reverse;
        align-items: flex-end;
    }

    .invoice-cont .form-invoice .elementor-field-type-repeater.elementor-field-group.elementor-column div.repeater-field-footer a.repeater-field-button-add {
        color: #2194FF !important;
        background-color: #ff00 !important;
    }

    .invoice-cont .form-invoice .elementor-field-type-repeater.elementor-field-group.elementor-column div.repeater-field-footer a.repeater-field-button-add:hover {
        color: #0165C2 !important;
        background-color: #ff00 !important;
    }

    .invoice-cont .form-invoice .elementor-field-type-repeater.elementor-field-group.elementor-column div.repeater-field-header div.repeater-field-header-acctions i.repeater-icon.icon-cancel-1.repeater-field-header-acctions-remove {
        color: #FF4444;
    }

    .invoice-cont .form-invoice .elementor-field-type-repeater.elementor-field-group.elementor-column div.repeater-field-header div.repeater-field-header-acctions i.repeater-icon.icon-down-open.repeater-field-header-acctions-toogle {
        display: none;
    }

    .invoice-cont .form-invoice div.repeater-field-item {
        padding: 24px 24px 24px 24px;
        background-color: #EAF3FB;
        border-radius: 12px;
        margin-bottom: 12px;
    }

    .invoice-cont .form-invoice div.repeater-field-item div.repeater-field-header div.repeater-field-header-title {
        font-weight: 700;
        color: #101010;
        font-size: 18px;
    }

    .invoice-cont .form-invoice div.elementor-field-repeater-end {
        margin-bottom: 64px;
        margin-top: -72px;
    }

    .invoice-cont .form-invoice div.elementor-field-type-email {
        margin-bottom: 64px !important;
    }

    .invoice-table {
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .invoice-table thead {
        border-top: solid 1px #717171;
        border-bottom: solid 1px #717171;
    }

    .invoice-cont .invoice-table tr {
        border: unset;
    }

    .amount {
        font-weight: bold;
        color: #000;
    }

    .subtotal,
    .total {
        font-weight: 700;
    }

    .inv-creator {
        padding: 20px;
    }

    .elementor-field-group-deduct_end .repeater-field-warp-item .repeater-field-item {
        display: none;
    }

    div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_722e93b {
        display: flex;
    }

    div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_722e93b span,
    div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_ebb11c9 span,
    div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_5e5d69d span {
        color: #101010;
        font-weight: 300;
        font-size: 14px;
        padding-bottom: 12px;
    }

    div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_722e93b input,
    div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_ebb11c9 input,
    div.elementor-field-type-html.elementor-field-group.elementor-column.elementor-field-group-field_5e5d69d input {
        background-color: #ffffff;
        border: solid 1px #D0D0D0;
        border-radius: 10px;
        padding: 6px 16px;
        width: 100%;
        height: 47px;
        color: #7a7a7a;
        font-weight: 300;
        font-size: 14px;
    }

    /* <!-- Media Queries --------------------> */
    @media screen and (max-width: 768px) {
        .invoice-table {
            width: 100%;
            font-size: 14px;
            border-collapse: collapse;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 8px;
            white-space: nowrap;
        }

        .invoice-table tbody,
        .invoice-table tfoot {
            display: table-row-group;
        }

        .invoice-table tr {
            display: table-row;
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
            padding: 0;
        }

        .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler {
            padding: 20px !important;
        }

        .invoice-cont .form-invoice div.repeater-field-item {
            padding: 14px;
        }

        .tbl-wrap {
            overflow-x: auto;
            white-space: nowrap;
            width: 100%;
        }

        .invoice-table {
            min-width: 600px;
        }

        .tbl-wrap::-webkit-scrollbar {
            height: 5px;
        }

        .tbl-wrap::-webkit-scrollbar-thumb {
            background: #336;
            border-radius: 4px;
        }

        .tbl-wrap::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
    }

    @media only screen and (max-width: 425px) {
        .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler .elementor-dragandrophandler-inner .elementor-text-drop {
            font-size: 12px !important;
        }

        .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler {
            padding: 10px !important;
        }

        .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler .elementor-dragandrophandler-inner .elementor-text-browser a {
            padding: 10px 15px !important;
        }

        .invoice-cont .form-invoice .elementor-field-type-file_upload .elementor-dragandrophandler-container .elementor-dragandrophandler {
            height: 220px !important;
        }

        .invoice-cont .form-invoice div.elementor-field-repeater-end {
            margin-bottom: 0;
        }

        .invoice-cont .form-invoice div.repeater-field-item {
            padding: 10px 5px;
        }

        .elementor-field-repeater-end {
            padding: 0;
        }

        .elementor-field-type-repeater.elementor-field-group.elementor-column .elementor-field-group-task_end.elementor-col-100,
        .elementor-field-type-repeater.elementor-field-group.elementor-column .elementor-field-group-deduct_end.elementor-col-100 {
            padding: 0;
        }
    }
</style>
<?php get_footer('admin'); ?>