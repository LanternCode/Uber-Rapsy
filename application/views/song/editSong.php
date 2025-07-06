<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <?php if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <a class="optionsURL" href="<?=base_url("songPage?songId=".$song->SongId)?>">Wróc do utworu</a>
        <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'reviewer'): ?>
            <?php if (!$song->SongDeleted): ?>
                <a class="optionsURL" href="<?=base_url('song/awards?songId='.$song->SongId)?>">Zarządzaj nagrodami</a>
            <?php endif; ?>
            <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
        <?php endif; ?>
        <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <?php endif; ?>
</header>
<main>
    <br><br><br><br>
    <h2>Edycja utworu</h2><br>
    <h3>Status:</h3><br>
    <?php if ($song->SongDeleted): ?>
        <p>Utwór został na stałe usunięty z RAPPAR. Jego edycja jest niemożliwa. Jeśli uważasz, że to błąd, skontaktuj się z administracją.</p>
    <?php else: ?>
        <p>Utwór jest aktywny w RAPPAR.</p><br>
        <p>Kliknij <a href="<?=base_url('song/deleteSong?songId='.$song->SongId.'&src=edit')?>">tutaj</a> żeby usunąć utwór.</p><br><br>
        <h3>Widoczność:</h3><br>
        <p>Utwór jest <?=$song->SongVisible ? 'publiczny' : 'ukryty'?>.</p><br>
        <p>Kliknij <a href="<?=base_url('song/updateVisibility?songId='.$song->SongId.'&src=edit')?>">tutaj</a> żeby zmienić jego widoczność.</p><br><br>
        <h3>Dane utworu:</h3><br>
        <form method="post" action="<?=base_url('song/edit?songId='.$song->SongId)?>" id="manualImport" enctype="multipart/form-data">
            <label>Tytuł utworu:
                <input type="text" name="songTitle" placeholder="<?=$song->SongTitle?>" value="<?=$songTitle ?? $song->SongTitle?>" required>
                <?=isset($titleError) ? '<br>'.$titleError : ''?>
            </label><br><br>
            <label>Autorzy utworu:
                <input type="text" name="songAuthor" placeholder="<?=$song->SongChannelName?>" value="<?=$songAuthor ?? $song->SongChannelName?>" required>
                <?=isset($authorError) ? '<br>'.$authorError : ''?>
            </label><br><br>
            <label>Rok wydania:
                <input type="text" name="songReleaseYear" placeholder="<?=$song->SongReleaseYear?>" value="<?=$songReleaseYear ?? $song->SongReleaseYear?>" required>
                <?=isset($yearError) ? '<br>'.$yearError : ''?>
            </label><br><br>
            <label>Link do miniaturki (jeśli zostawisz pole puste, zostanie użyta domyślna miniatura):
                <input type="text" name="songThumbnailLink" value="<?=$songThumbnailLink ?? $song->SongThumbnailURL?>">
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
                    <h2 class="song-title songBackground"><?=$songTitle ?? $song->SongTitle?></h2>
                    <p class="song-meta songBackground">
                        <span class="song-authors"><?=$songAuthor ?? $song->SongChannelName?></span><span class="song-year">(<?=$songReleaseYear ?? $song->SongReleaseYear?>)</span>
                    </p>
                </div>
                <div class="song-awards songBackground">
                    <p class="songBackground">Nagrody</p>
                </div>
            </div>
            <div class="song-content songBackground">
                <img alt="Podgląd miniatury" class="song-thumbnail" id="previewImage" src="<?=$songThumbnailLink ?? $song->SongThumbnailURL?>" />
                <div class="song-grades">
                    <p>Moja Ocena</p>
                    <p>Ocena Adama</p>
                    <p>Ocena Kościelnego</p>
                    <p>Średnia Społeczności</p>
                </div>
            </div>
        </div>
        <div class="centered">
            <input form="manualImport" type="submit" value="Zaktualizuj utwór" class="big-button">
        </div>
    <?php endif; ?>
</main>
<script type="text/javascript" src="<?=base_url('scripts/manualImporting.js')?>"></script>