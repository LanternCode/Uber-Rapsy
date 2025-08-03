<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("importSongs")?>">Dodaj Nowe Nuty</a>
    <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'reviewer'): ?>
        <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
    <?php endif; ?>
    <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("songSearch")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Strumień" name="searchQuery" required/>
        <input type="submit" value="Szukaj" />
    </form>
</header>
<main>
    <br><br><br>
    <?=$report?>
</main>