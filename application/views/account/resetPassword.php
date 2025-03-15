<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div>
    <h2>Zresetuj hasło</h2>
    <div>
        <div>
            <?php if (isset($errorMessage)) {
                echo '<span>'.$errorMessage.'</span>';
            } ?>
        </div>
        <form method="POST" action="<?=base_url('forgottenPassword/reset?qs='.$key)?>">
            <label>Wprowadź nowe hasło:</label><br />
            <input type="password" name="newPassword" required />
            <br /><br />

            <label>Ponownie wprowadź nowe hasło:</label><br />
            <input type="password" name="newPasswordRepeated" required />
            <br /><br />

            <input class="btn btn-info" type="submit" value="Ustaw nowe hasło" />
        </form>
    </div>
    <div>
        <a href="<?=base_url('login')?>">
            <button><- Wróć do logowania</button>
        </a>
    </div>
</div>