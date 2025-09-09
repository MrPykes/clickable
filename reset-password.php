<?php

/**
 * Template Name: Reset Password Template
 */
get_header('admin');

?>
<style>
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

    input {
        margin-bottom: 10px;
    }
</style>

<div class="container-fluid p-0 parent-main">
    <?php echo do_shortcode('[elementor-template id="2707"]'); ?>
</div>

<script>
    const params = new URLSearchParams(window.location.search);
    document.querySelector('[name="token"]').value = params.get('token') || '';
    document.querySelector('[name="team_id"]').value = params.get('team_id') || '';

    document.getElementById("resetForm").addEventListener("submit", function(e) {
        e.preventDefault();

        let newPassword = this.querySelector("input[name='new_password']").value;
        let confirmPassword = this.querySelector("input[name='confirm_password']").value;
        let token = this.querySelector("input[name='token']").value;
        let teamId = this.querySelector("input[name='team_id']").value;
        let messageBox = document.getElementById("resetMessage");

        if (newPassword !== confirmPassword) {
            messageBox.textContent = "Passwords do not match.";
            messageBox.style.color = "red";
            return;
        }

        fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    action: "reset_password",
                    new_password: newPassword,
                    token: token,
                    team_id: teamId
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