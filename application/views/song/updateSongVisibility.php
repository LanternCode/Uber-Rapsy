<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <?php if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <a class="optionsURL" href="<?=base_url("songPage?songId=".$song->SongId)?>">Przejdź do utworu</a>
        <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'reviewer'): ?>
            <a class="optionsURL" href="<?=base_url("song/edit?songId=".$song->SongId)?>">Wróć do edytowania utworu</a>
            <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
        <?php endif; ?>
        <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <?php endif; ?>
</header>
<br><br><br><br>
<h2><?= $song->SongVisible === "1" ? "Ukrywasz" : "Upubliczniasz" ?> następujący utwór:</h2><br><br>
<div class="song-container songBackground">
    <div class="song-header songBackground">
        <div class="songBackground">
            <h2 class="song-title songBackground"><?=$song->SongURL != '' ? '<a href="https://youtu.be/<?=$song->SongURL?>">' : ''?><?=$song->SongTitle?><?=$song->SongURL != '' ? '</a>' : ''?></h2>
            <p class="song-authors songBackground"><?=$song->SongChannelName?> (<?=$song->SongReleaseYear?>)</p>
        </div>
        <div class="song-awards songBackground">
            <?php foreach($songAwards as $award): ?>
                <p class="songBackground"><?=$award->award?></p>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="song-content songBackground">
        <img src="<?=$song->SongThumbnailURL?>" alt="Song Thumbnail" class="song-thumbnail" />
        <div class="song-grades">
            <p>Moja Ocena: <?=$myRating != 0 ? $myRating : 'Nieoceniona' ?></p>
            <p>Ocena Adama: <?=$song->SongGradeAdam != 0 ? $song->SongGradeAdam : 'Nieoceniona' ?></p>
            <p>Ocena Kościelnego: <?=$song->SongGradeChurchie != 0 ? $song->SongGradeChurchie : 'Nieoceniona' ?></p>
            <p>Średnia Społeczności: <?=$communityAverage != 0 ? $communityAverage : 'Nieoceniona' ?></p>
        </div>
    </div>
</div>

<h3>Kontynuować?</h3><br>

<?php if ($src === 'search'): ?>
    <a href="<?= base_url('song/updateVisibility?songId='.$song->SongId.'&switch=true&src=search&query='.$searchQuery)?>">Tak</a><br>
    <a href="<?=base_url('songSearch?searchQuery='.$searchQuery)?>">Powrót</a>
<?php else: ?>
    <a href="<?= base_url('song/updateVisibility?songId='.$song->SongId.'&switch=true&src=edit')?>">Tak</a><br>
    <a href="<?= base_url('song/edit?songId='.$song->SongId) ?>">Powrót</a>
<?php endif; ?>
