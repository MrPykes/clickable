<?php 
/**
 * Template Name: Lost Password Template
 */
get_header('admin');
?>
<style>
    form.lostPass div.lostNav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }
    button.btn-form {
        font-family: sora;
        font-weight: 600;
        background-color: #2194FF; 
        color: white; 
        padding: 15px 20px 15px 20px;
        border: solid 1px #2194FF;
        border-radius: 10px;
        font-size: 14px;
        cursor: pointer;
        transition: box-shadow 0.3s ease, transform 0.2s ease;
        box-shadow: 0px 10px 20px rgba(214, 225, 235, 0.2);
    }
    button.btn-form:hover {
        background-color: #fff;
        color: #0165C2;
        border: solid 1px #0165C2;
    }
    a {
        color: #2194FF;
        font-size: 16px;
        font-weight: 300;
    }
    a:hover {
        color: #005DB4;
    }
</style>
<div class="container-fluid p-0 parent-main">
    <?php echo do_shortcode('[elementor-template id="2683"]'); ?>
</div>

<script>
document.getElementById("lostPassForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let email = this.querySelector("input[name='email_address']").value;
    let messageBox = document.getElementById("lostPassMessage");

    messageBox.textContent = "Processing...";

    fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "lost_password_request",
            email_address: email
        })
    })
    .then(res => res.json())
    .then(data => {
        messageBox.textContent = data.message;
        messageBox.style.color = data.success ? "green" : "red";
    })
    .catch(() => {
        messageBox.textContent = "Something went wrong.";
        messageBox.style.color = "red";
    });
});
</script>





<?php get_footer('admin'); ?>