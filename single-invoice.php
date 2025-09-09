<?php get_header('admin'); ?>
<style>
    .view-invoice-section{
        padding: 56px 64px;
    }
    .breadcrumb{
        font-weight: 300;
        font-size: 14px;
        column-gap: 24px;
        margin-bottom: 40px;
    }
    .breadcrumb-item a{
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
    .breadcrumb-item.active{
        color: #101010;
    }
    /* PDF button */
    .btn-pdf {
        background-color: #2194FF;
        border-radius: 10px;
        color: #fff;
        border: none;
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
    .pdf-cont-desc span ,
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
    .pdf-table {
        margin-bottom: 80px;
    }
    .pdf-pay-info h2{
        color: #101010;
        font-weight: 600;
        font-size: 24px;
        letter-spacing: -0.01em;
        margin-bottom: 32px;
    }
    .pdf-table table td, table th {
        border: none;
    }
    .pdf-table table tbody>tr:nth-child(odd)>td, table tbody>tr:nth-child(odd)>th {
        background-color: transparent;
    }
    .pdf-table table caption+thead tr:first-child td, table caption+thead tr:first-child th, 
    table colgroup+thead tr:first-child td, table colgroup+thead tr:first-child th, 
    table thead:first-child tr:first-child td, table thead:first-child tr:first-child th {
        border-block-start: 1px solid #717171 !important;
        border-block-end: 1px solid #717171 !important;
    }
    .pdf-table table tfoot th, table thead th {
        font-weight: 700;
        font-size: 16px;
        color:#717171;
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
</style>


<div class="container-fluid p-0">
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
            <div class="pdf-cont">
                <div class="pdf-cont-desc">
                    <div>
                        <span>Billed to</span>
                        <h2>Company Name</h2>
                        <p>Long Company Street Address Here</p>
                    </div>
                    <div>
                        <span>Pay to</span>
                        <h2><?php echo get_field('fullname',get_the_ID(  )); ?></h2>
                        <p>Long Member Home Street Address Here</p>
                    </div>
                    <div>
                        <span>Invoice Number</span>
                        <h2><?php echo get_field('invoice_number',get_the_ID(  )); ?></h2>
                    </div>
                </div>
                <div class="pdf-cont-date">
                    <span>Period Covered</span>
                    <h2>Jan. 01 - Jan. 31, 2025</h2>
                </div>
                <div class="pdf-table">
                    <?php 
                        if( have_rows('tasks', get_the_ID()) ): 
                            $subtotal = 0;
                            $total_deductions = 0;
                            $overall_total =0;
                        ?>
                            <table class="tbl-pdf">
                                <thead class="">
                                    <tr>
                                        <th style="width: 50%;">DESCRIPTION</th>
                                        <th class="" style="width: 20%;">HOURS</th>
                                        <th class="" style="width: 10%;">RATE</th>
                                        <th class="text-end" style="width: 20%;">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- tasks hours & rate -->
                                    <?php 
                                        while( have_rows('tasks', get_the_ID()) ): the_row();
                                        $description = get_sub_field('tasks_description'); 
                                        $hours = get_sub_field('tasks_hours'); 
                                        $rate = get_sub_field('tasks_rate');
                                        
                                        $days = floor($hours / 8);
                                        $remaining_hours = $hours % 8; 
                                        $days_text = ($days > 0 ? $days . ' Days' : '') . ($remaining_hours > 0 ? " $remaining_hours hrs" : '');
                                        
                                        $amount = ($hours * $rate);
                                        $subtotal += $amount;
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($description); ?></td>
                                        <td class=""><?php echo esc_html($hours); ?> (<?php echo esc_html($days_text); ?>)</td>
                                        <td class="">$<?php echo esc_html($rate); ?>/hr</td>
                                        <td class="text-end" style="font-weight: 700; color: #101010;">$<?php echo number_format($amount); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <!-- deductions hours & rate -->
                                    <?php if( have_rows('deductions', get_the_ID()) ): ?>
                                        <?php while( have_rows('deduct_description') ): the_row(); 
                                            $deduct_hours = get_sub_field('deduct_hours');
                                            $deduct_rate = get_sub_field('deduct_rate');

                                            $deduct_amount = $deduct_hours * $deduct_rate;
                                            $total_deductions += $deduct_amount;
                                        ?>
                                        <?php endwhile; ?>
                                        <?php endif; ?>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="d fw-bold" style="font-size: 16px; font-weight: 700; color: #717171; border-bottom: solid 1px #717171 !important;">SUBTOTAL</td>
                                        
                                        <td class="text-end fw-bold" style="border-bottom: solid 1px #717171 !important;">$<?php echo number_format($subtotal); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <?php $overall_total = $subtotal - $total_deductions; ?>
                                        <td class=" fw-bold" style="font-size: 16px; font-weight: 700; color: #717171;">TOTAL</td>
                                        <td class="text-end fw-bold" style="font-size: 24px; font-weight: 700; color: #101010;">$<?php echo number_format($overall_total); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                    <?php endif; ?>
                </div>
                <div class="pdf-pay-info">
                    <span>Payment Information</span>
                    <h2><?php echo get_field('fullname',get_the_ID(  )); ?></h2>
                    <h2><?php echo get_field('payment_method',get_the_ID(  )); ?></h2>
                    <h2><?php echo get_field('pay_email',get_the_ID(  )); ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Queries -->
<style>
    @media only screen and (max-width: 1162px){
        .view-invoice-section {
            padding: 50px 0 0 0;
        }
        .pdf-cont {
            padding: 40px;
        }
    }
    @media only screen and (max-width: 768px){
        .pdf-cont-desc {
            display: flex;
            flex-direction: column;
        }
    }
    
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    document.querySelector('.btn-pdf').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4'); // Portrait mode, millimeters, A4 size

        const pdfContent = document.querySelector('.pdf-cont');

        html2canvas(pdfContent, { scale: 2 }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const imgWidth = 210;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            doc.addImage(imgData, 'PNG', 0, 10, imgWidth, imgHeight);
            doc.save('Clickable_Invoice_Receipt.pdf'); 
        });
    });
</script>


<?php get_footer('admin'); ?>