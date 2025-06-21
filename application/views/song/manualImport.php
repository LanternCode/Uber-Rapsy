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
    <br><br><br><br>
    <h2>Manualne dodawanie utworu</h2><br>
    <form method="post" action="<?=base_url('manualImport')?>" id="manualImport" enctype="multipart/form-data">
        <label>Tytuł utworu:
            <input type="text" name="songTitle" placeholder="Dawid Obserwator - Pasterka (prod. Pablo)" required>
            <?=isset($titleError) ? '<br>'.$titleError : ''?>
        </label><br><br>
        <label>Autorzy utworu:
            <input type="text" name="songAuthor" placeholder="Słoń, Frosti, Popiół to kot, Leny Da Fam" required>
            <?=isset($authorError) ? '<br>'.$authorError : ''?>
        </label><br><br>
        <label>Rok wydania:
            <input type="text" name="songReleaseYear" placeholder="2024" required>
            <?=isset($yearError) ? '<br>'.$yearError : ''?>
        </label><br><br>
        <label>Link do miniaturki (jeśli zostawisz pole puste, zostanie użyta domyślna miniatura):
            <input type="text" name="songThumbnailLink">
            <?=isset($linkError) ? '<br>'.$linkError : ''?>
        </label><br><br>
        <label>Zamiast linku chcę wysłać plik z miniaturką:<br>
            Dozwolone formaty miniaturki: jpg, jpeg, png, gif, webp.<br>
            Minimalna rozdzielczość miniaturki: 320x180 px.<br>
            Maksymalna rozdzielczość miniaturki: 3840x2160 px.<br>
            Maksymalny rozmiar pliku miniaturki: 10 MB.<br>
            Jeśli wstawisz i link i plik, priorytet będzie miał plik. Jeśli nie spełnia wymagań, zostanie użyty link.<br>
            Jeśli i ten nie będzie poprawny, zostanie użyta domyślna miniatura.<br>
            <input type="file" name="songThumbnailFile" accept="image/jpeg,image/png,image/gif,image/webp" id="thumbnail_input">
            <?=isset($thumbnailError) ? '<br>'.$thumbnailError : ''?>
        </label><br><br>
        <div id="errorMsg" class="errorMessage"></div>
        <?=isset($songError) ? '<br>'.$songError : ''?>
    </form>
    <h2>Podgląd utworu</h2><br>
    <div class="song-container songBackground">
        <div class="song-header songBackground">
            <div class="songBackground">
                <h2 class="song-title songBackground">Tytuł</h2>
                <p class="song-meta songBackground">
                    <span class="song-authors">Autorzy</span><span class="song-year">(Rok Wydania)</span>
                </p>
            </div>
            <div class="song-awards songBackground">
                <p class="songBackground">Nagrody</p>
            </div>
        </div>
        <div class="song-content songBackground">
            <img alt="Podgląd miniatury" class="song-thumbnail" id="previewImage" />
            <div class="song-grades">
                <p>Moja Ocena</p>
                <p>Ocena Adama</p>
                <p>Ocena Kościelnego</p>
                <p>Średnia Społeczności</p>
            </div>
        </div>
    </div>
    <div class="centered">
        <input form="manualImport" type="submit" value="Dodaj utwór do RAPPAR" class="big-button">
    </div>
</main>
<script type="text/javascript" src="<?=base_url('scripts/manualImporting.js')?>"></script>