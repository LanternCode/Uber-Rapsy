<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="homepage--logo">
    <h2>Zaloguj się do UberRapsów!</h2>
</div>

<?php if(isset($invalidCredentials) && $invalidCredentials) {
    echo "<h4 class='homepage--error'>Wprowadzono niepoprawne dane logowania.</h4>";
} else if (isset($userHasRegistered) && $userHasRegistered) {
    echo "<h4 class='homepage--registered'>Konto zostało stworzone, możesz się zalogować!</h4>";
} else if (isset($sessionExpired) && $sessionExpired) {
    echo "<h4 class='homepage--error'>Zostałeś wylogowany przez nieaktywność, proszę zaloguj się ponownie.</h4>";
} ?>

<div class="homepage--body">
    <div class="homepage--login__form">
        <form method="POST" action="<?=base_url('login')?>">

            <label>Adres Email:</label><br />
            <input type="email" name="userEmail" required><br /><br />

            <label>Hasło:</label>
            <input type="password" name="userPassword" autocomplete="off" required><br /><br />

            <label><input type="checkbox" name="doNotLogout" value="true">Nie wylogowuj mnie przez 7 dni</label>

            <input type="submit" class="btn btn-primary" value="Zaloguj"><br />
        </form>
        lub
        <br /><a href="<?=base_url('newAccount')?>" role="button" class="btn btn-primary">Załóż konto!</a>
        <br /><a href="<?=base_url('forgottenPassword')?>" role="button" class="btn btn-primary">Przypomnij hasło</a>
    </div>
</div>
