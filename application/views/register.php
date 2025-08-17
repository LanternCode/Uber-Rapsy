<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<form action="<?=base_url('newAccount?src='.$redirectSource)?>" class="register--form" method="post">
    <h1>Witaj w RAPPAR! Cieszymy się że chcesz dołączyć do naszej społeczności!</h1>
    <h2>Teraz wystarczy, że wypełnisz poniższy formularz i wciśniesz przycisk!</h2>

    <div class="register--section">
        <label>Nazwa użytkownika:<br>
            <span class="register--grayed">Treści które upublicznisz będą widoczne pod tą nazwą!</span>
        </label><br>
        <input type="text" name="register--username" class="register--input" value="<?=isset($setUsername) ? $setUsername : ''?>" required><br>
        <?=isset($usernameTooShort) ? $usernameTooShort : ''?>
        <?=isset($usernameTooLong) ? $usernameTooLong : ''?>
    </div>

    <div class="register--section">
        <label>Adres Email:</label><br>
        <input type="email" name="register--email" class="register--input" value="<?=isset($setEmail) ? $setEmail : ''?>" required><br>
        <?=isset($emailFormatInvalid) ? $emailFormatInvalid : ''?>
        <?=isset($emailTooLong) ? $emailTooLong : ''?>
        <?=isset($emailRepeated) ? $emailRepeated : ''?>
    </div>

    <div class="register--section">
        <label>Hasło:</label><br>
        <input type="password" name="register--password" class="register--input" value="<?=isset($setPassword) ? $setPassword : ''?>" required><br>
        <?=isset($passwordTooShort) ? $passwordTooShort : ''?>
        <?=isset($passwordTooLong) ? $passwordTooLong : ''?>
    </div>

    <div class="register--section">
        <label>Powtórz hasło:</label><br>
        <input type="password" name="register--password__repetition" class="register--input" value="<?=isset($setPasswordRepetition) ? $setPasswordRepetition : ''?>" required><br>
        <?=isset($passwordRepetitionNotMatching) ? $passwordRepetitionNotMatching : ''?>
    </div>

    <div class="register--section">
        <label><br>
            <input type="checkbox" name="register--TOS" <?=isset($setTOS) ? $setTOS : ''?> required>
            Akceptuję <a href="<?=base_url( 'TermsOfService' )?>" target="_blank">Zasady Użytkowania</a> platformy RAPPAR.
            <?=isset($termsOfServiceDenied) ? $termsOfServiceDenied : ''?>
        </label><br>
    </div>

    <input type="submit" class="btn btn-info" value="Załóż konto">
    <input type="hidden" name="formSubmitted" value="true">

</form>

<div class="session--hub">
    <a href="<?=base_url('login')?>" role="button" class="btn btn-primary">Powrót do logowania</a>
</div>