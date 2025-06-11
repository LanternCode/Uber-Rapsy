<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <?php if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <a class="optionsURL" href="<?=base_url("importSongs")?>">Dodaj Nowe Nuty</a>
        <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'reviewer'): ?>
            <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
        <?php endif; ?>
        <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <?php endif; ?>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("songSearch")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Strumień" name="searchQuery" required/>
        <input type="submit" value="Szukaj" />
    </form>
</header>
<main>
    <br><br><br>
    <form method="post" action="<?=base_url('manualImport')?>">
        <label>Tytuł utworu:
            <input type="text" name="songTitle" placeholder="Dawid Obserwator - Pasterka (prod. Pablo)" required>
        </label>
        <label>Autorzy utworu:
            <input type="text" name="songAuthor" placeholder="Słoń, Frosti, Popiół to kot, Leny Da Fam" required>
        </label>
        <label>Rok wydania:
            <input type="text" name="songReleaseYear" placeholder="2024" required>
        </label>
        <label>Link do miniaturki (jeśli zostawisz pole puste, zostanie użyta domyślna miniatura):
            <input type="text" name="songThumbnailLink" placeholder="2024">
        </label>
        <label>Chcę wrzucić własną miniaturę
            <input type="checkbox" name="customThumbnail">
        </label>
        <label>Plik z miniaturą:
            <input type="file" name="songThumbnailFile" placeholder="2024">
        </label>
        <input type="submit" value="Dodaj utwór do RAPPAR">
    </form>
</main>