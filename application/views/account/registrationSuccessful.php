<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("search")?>" target="_blank">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Rajaner" name="searchQuery" />
        <input type="submit" value="Szukaj" />
    </form>
</header>
<main>
    <div class='registrationBox'>
        <br>
        <h4 class='successfulRegistration'>Witaj w RAPPAR! Twoje konto właśnie zostało aktywowane! Teraz możesz oceniać, recenzować i dodawać nowe utwory, a także układać je w playlisty i dzielić się nimi z innymi użytkownikami!<br></h4>
        <?php if (!empty($redirectSource)): ?>
            <h4><a href="<?=base_url($redirectSource)?>">Kliknij tutaj</a>, żeby przejść do strony, która skierowała cię do rejestracji!</h4>
        <?php endif; ?>
    </div>
</main>