<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("importSongs")?>">Dodaj Nowe Nuty</a>
    <a class="optionsURL" href="<?=base_url("manualImport")?>">Importuj Manualnie</a>
    <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
    <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("songSearch")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Strumień" name="searchQuery" required/>
        <input type="submit" value="Szukaj" />
    </form>
</header>
<main>
    <br><br><br>
    <h2>Dodano następujący utwór do RAPPAR</h2><br>
    <div class="song-container songBackground">
        <div class="song-header songBackground">
            <div class="songBackground">
                <h2 class="song-title songBackground"><?=$songTitle?></h2>
                <p class="song-meta songBackground">
                    <span class="song-authors"><?=$songAuthor?></span><span class="song-year">(<?=$songReleaseYear?>)</span>
                </p>
            </div>
            <div class="song-awards songBackground">
                <p class="songBackground">Nagrody</p>
            </div>
        </div>
        <div class="song-content songBackground">
            <img src="<?=$songThumbnailLink?>" alt="Song Thumbnail" class="song-thumbnail" />
            <div class="song-grades">
                <p>Moja Ocena</p>
                <p>Ocena Adama</p>
                <p>Ocena Kościelnego</p>
                <p>Średnia Społeczności</p>
            </div>
        </div>
    </div>
    <div class="centered">
        Kliknij <a class="big-button" href="<?=base_url('song/manualImport')?>"> tutaj</a> aby powrócić do manualnego dodawania utworów,
        lub <a class="big-button" href="<?=base_url('songPage?songId='.$insertedSongId)?>">tutaj</a> aby przejść do dodanego utworu.
    </div>
</main>