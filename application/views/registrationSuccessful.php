<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class='registrationBox'>
    <h4 class='successfulRegistration'>Witaj! Twoje konto właśnie zostało stworzone! Jesteś teraz zalogowany!</h4>
    <?php if (!empty($redirectSource)): ?>
        <h4><a href="<?=base_url($redirectSource)?>">Kliknij tutaj</a>, żeby przejść do strony, która skierowała cię do rejestracji!</h4>
    <?php endif; ?>
</div>