<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("songPage?songId=".$song->SongId)?>">Przejdź do utworu</a>
    <?php if ($isReviewer): ?>
        <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
    <?php endif; ?>
    <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
</header>
<main>
    <br><br><br>
    <a href="<?=base_url("songSearch?searchQuery=".$searchQuery)?>"><-- Powrót do wyników wyszukiwania</a>
    <h2>Dodaj utwór do playlisty</h2><br>
    <?php if (!empty($playlistIntegratedError)): ?>
        <p class="errorMessage">Ten utwór znajduje się na zintegrowanej playliście. Możesz skopiować lub przenieść do niej utwór, ale <br>
        nie możesz go dodać manualnie, chyba że zrobisz to na YouTube. Tylko utwory istniejące na YT mogą znajdować się w zintegrowanych playlistach.</p><br>
    <?php elseif (!empty($songNotUniqueError)): ?>
        <p class="errorMessage">Ten utwór już znajduje się na wybranej playliście!</p><br>
    <?php elseif (!empty($success)): ?>
        <p class="successMessage">Pomyślnie dodano utwór do playlisty!</p><br>
    <?php endif; ?>
    <h3>Jeśli utwór już znajduje się na playliście, to nie zostanie do niej dodany drugi raz.</h3>
    <div class="song-container songBackground">
        <div class="song-header songBackground">
            <div class="songBackground">
                <h2 class="song-title songBackground"><?=$song->SongTitle?></h2>
                <p class="song-meta songBackground">
                    <span class="song-authors"><?=$song->SongChannelName?></span><span class="song-year">(<?=$song->SongReleaseYear?>)</span>
                </p>
            </div>
            <div class="song-awards songBackground">
                <p class="songBackground">Nagrody</p>
            </div>
        </div>
        <div class="song-content songBackground">
            <img alt="Podgląd miniatury" class="song-thumbnail" id="previewImage" src="<?=$song->SongThumbnailURL?>" />
            <div class="song-grades">
                <p>Moja Ocena</p>
                <p>Ocena Adama</p>
                <p>Ocena Kościelnego</p>
                <p>Średnia Społeczności</p>
            </div>
        </div>
    </div>
    <label>Wybierz playlistę:</label>
    <?php if (count($userOwnedPlaylists) > 0): ?>
        <form method="post" action="<?=base_url('song/addToPlaylist?query='.$searchQuery.'&songId='.$songId)?>">
            <select name="listId">
                <?php foreach ($userOwnedPlaylists as $playlist): ?>
                    <option value="<?=$playlist->ListId?>"><?=$playlist->ListName?></option>
                <?php endforeach; ?>
            </select><br><br>
            <input type="submit" class="big-button" value="Dodaj utwór do playlisty">
        </form>
    <?php else: ?>
        <p>Nie posiadasz żadnych playlist.</p>
    <?php endif; ?>
</main>