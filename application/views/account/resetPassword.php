<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="resetPassword--grid">
    <h2 class="resetPassword--grid--h2">Zresetuj hasło</h2>
    <div class="resetPassword--grid--form">
        <div class="resetPassword--grid--errorSpan">
            <?php if( isset( $errorMessage ) ) {
                    echo '<span class="universal--errorMessage">' . $errorMessage . '</span>';
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
    <div class="resetPassword--grid--return">
        <a href="<?=base_url('login')?>">
            <button class="btn btn-primary"><- Wróć do logowania</button>
        </a>
    </div>
</div>