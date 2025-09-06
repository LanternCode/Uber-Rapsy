<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <?php if ($isReviewer): ?>
        <a class="optionsURL" href="<?=base_url("manualImport")?>">Importuj Manualnie</a>
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
    <h1>Importuj utwory z YouTube</h1>
    <h3>Możesz importować wszystkie utwory z playlisty lub pojedyńczo.<br> Jeśli utwór istnieje już w bazie danych, nie zostanie dodany.
    <br>Utwory importowane z YT nie zawierają informacji o autorze (poza nazwą kanału, który przesłał je na YT), ani daty przesłania go na YT.
    <br>Te informacje możesz dodać przeglądając importowane utwory gdy już wciśniesz przycisk importowania.</h3>
    <br><br>
    <?=$error ?? ''?>
    <form method="post" action="<?=base_url('importSongs')?>">
        <h4>Link do playlisty: <input type="text" name="playlistLink"></h4>
        <br>
        <h4>Link do utworu: <input type="text" name="songLink"></h4>
        <br><br>
        <input type="submit" value="Przejrzyj importowane utwory">
    </form>
</main>