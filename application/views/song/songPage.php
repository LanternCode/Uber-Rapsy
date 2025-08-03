<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">UberRapsy</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <?php if (!empty($searchQuery)): ?>
        <a class="optionsURL" href="<?=base_url("songSearch?searchQuery=".$searchQuery)?>">Wróć do wyszukiwarki</a>
    <?php endif; ?>
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
            <p id="confirmation" class="optionsURL">Przesuń suwak by ocenić utwór!</p>
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
    <form id="toplist" method="post" data-url="<?=base_url("song/autoSave")?>">
        <input type="hidden" name="songId" value="<?=$song->SongId?>">
        <div class="song-container">
            <div class="song-header">
                <div class="">
                    <h2 class="song-title"><?=$song->SongURL != '' ? '<a href="https://youtu.be/'.$song->SongURL.'">' : ''?><?=$song->SongTitle?><?=$song->SongURL != '' ? '</a>' : ''?></h2>
                    <p class="song-authors"><?=$song->SongChannelName?> (<?=$song->SongReleaseYear?>)</p>
                </div>
                <div class="song-awards">
                    <?php foreach ($songAwards as $award): ?>
                        <p class=""><?=$award->award?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="song-content">
                <img src="<?=$song->SongThumbnailURL?>" alt="Song Thumbnail" class="song-thumbnail" />
                <div class="song-grades">
                    <p id="myRating">Moja Ocena: <?=$myRating != 0 ? $myRating : 'Nieoceniona' ?></p>
                    <p>Ocena Adama: <?=$song->SongGradeAdam != 0 ? $song->SongGradeAdam : 'Nieoceniona' ?></p>
                    <p>Ocena Kościelnego: <?=$song->SongGradeChurchie != 0 ? $song->SongGradeChurchie : 'Nieoceniona' ?></p>
                    <p id="commAvg">Średnia Społeczności: <?=$communityAverage != 0 ? $communityAverage : 'Nieoceniona' ?></p>
                </div>
            </div>
            <div class="song-slider">
                <input id="gradeSlider" type="range" min="1" max="10" step="0.5" value="<?=$myRating ?? 0?>" name="songGrade">
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
    <div class="reviews-container">
        <div class="my-review-section">
            <h3>Moja recenzja</h3>
            <?php if (!empty($myReview)): ?>
                <a href="<?=base_url('song/showReview?reviewId='.$myReview->reviewId)?>"><?=$myReview->username?>: <?=$myReview->reviewTitle?></a>
            <?php elseif ($userAuthenticated): ?>
                <a href="<?=base_url('song/reviewSong?songId='.$song->SongId)?>">+ Zrecenzuj utwór</a>
            <?php else: ?>
                <p><a href="<?=base_url('login')?>">Zaloguj</a> lub <a href="<?=base_url('newAccount')?>">zarejestruj się</a> i zacznij recenzować z RAPPAR!</p>
            <?php endif; ?>
        </div><br>
        <div class="user-reviews-section">
            <h3>Recenzje użytkowników RAPPAR</h3>
            <?php if ($songReviewCount > 0): ?>
                <?php foreach ($userReviews as $userReview): ?>
                    <?=$userReview->username?>: <a href="<?=base_url('song/showReview?reviewId='.$userReview->reviewId)?>"><?=$userReview->reviewTitle?></a><br>
                <?php endforeach; ?>
            <?php elseif (!empty($myReview)): ?>
                <p>Na ten moment jesteś jedyną osobą, która dodała recenzję. Zaproś znajomych i oceniajcie razem w RAPPAR!</p>
            <?php else: ?>
                <p>Na ten moment nikt nie zrecenzował tego utworu. Podziel się swoją opinią z innymi dodając recenzję!</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<script type="text/javascript" src="<?=base_url('scripts/autoSaveGrade.js')?>"></script>