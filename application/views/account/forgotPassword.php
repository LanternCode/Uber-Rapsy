<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="forgottenPassword--grid">
    <h2 class="forgottenPassword--grid--h2">Przypomnij hasło</h2>
    <div class="forgottenPassword--grid--form">
        <div class="forgottenPassword--grid--errorSpan">
            <?php if (isset($actionNotification) && $actionNotification) {
                echo '<span class="universal--errorMessage">' . $actionNotification . '</span>';
            } ?>
        </div>
        <form method="POST" action="<?=base_url('forgottenPassword')?>">
            <label>Adres email:</label><br />
            <input type="email" name="email" /><br /><br />
            <input class="btn btn-info" type="submit" value="Przypomnij hasło" />
        </form>
    </div>
    <div class="forgottenPassword--grid--return">
        <a href="<?=base_url('login')?>">
            <button class="btn btn-primary"><- Powrót do strony logowania</button>
        </a>
    </div>
</div>