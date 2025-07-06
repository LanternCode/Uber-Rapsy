<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'reviewer'): ?>
        <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
        <a class="optionsURL" href="<?=base_url("song/edit?songId=".$song->SongId)?>">Edytuj Utwór</a>
        <?php if (!$song->SongDeleted): ?>
            <a target="_blank" class="optionsURL" href="<?=base_url('song/awards?songId='.$song->SongId)?>">Zarządzaj nagrodami</a>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['userLoggedIn']) && $_SESSION['userLoggedIn']): ?>
        <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
        <?php if (!$song->SongDeleted): ?>
            <input type="submit" form="toplist" value="Zapisz Oceny" class="optionsURL" />
        <?php endif; ?>
    <?php else: ?>
        <a class="optionsURL" href="<?=base_url("login")?>">Zaloguj się</a>
    <?php endif; ?>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("songSearch")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="text" placeholder="Strumień" name="searchQuery" required/>
        <input type="submit" value="Szukaj" />
    </form>
</header>
<main>
    <br><br><br>
    <form id="toplist" method="post" action="<?=base_url("song/saveGrades")?>">
        <input type="hidden" name="songId" value="<?=$song->SongId?>">
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
            <div class="song-slider songBackground">
                <input type="range" min="1" max="10" step="0.5" value="<?=$myRating ?? 0?>" name="songGrade">
                <div class="slider-labels">
                    <!-- n from 0..9, so label=1 => n=0, label=10 => n=9 -->
                    <span style="left: calc((0/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    1
                  </span>
                    <span style="left: calc((1/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    2
                  </span>
                    <span style="left: calc((2/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    3
                  </span>
                    <span style="left: calc((3/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    4
                  </span>
                    <span style="left: calc((4/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    5
                  </span>
                    <span style="left: calc((5/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    6
                  </span>
                    <span style="left: calc((6/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    7
                  </span>
                    <span style="left: calc((7/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    8
                  </span>
                    <span style="left: calc((8/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    9
                  </span>
                    <span style="left: calc((9/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    10
                  </span>
                </div>
            </div>
        </div>
    </form>
</main>