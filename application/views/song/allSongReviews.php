<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">RAPPAR</a>
    <?php if ($userLoggedIn): ?>
        <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
    <?php endif; ?>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("songPage?songId=".$song->SongId)?>">Wróc do utworu</a>
    <?php if ($isReviewer): ?>
        <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
    <?php endif; ?>
    <?php if ($userLoggedIn): ?>
        <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
    <?php else: ?>
        <a class="optionsURL" href="<?=base_url("login?src=song/allReviews?songId=".$song->SongId)?>">Zaloguj się</a>
    <?php endif; ?>
</header>
<main>
    <br><br><br>
    <div class="reviews-container">
        <div class="user-reviews-section">
            <h3>Wszystkie recenzje utworu <?=$song->SongTitle?></h3>
            <?php if (count($songReviews) > 0): ?>
                <?php foreach ($songReviews as $songReview): ?>
                    <br><?=$songReview->username?>: <a href="<?=base_url('song/showReview?reviewId='.$songReview->reviewId)?>"><?=$songReview->reviewTitle?></a>  (<?=$songReview->reviewTotal?>/90 - <?=number_format(($songReview->reviewTotal/90)*100, 2)?>%)<br>
                    <div class="review-excerpt-wrap">
                        <div class="review-excerpt">
                            <?=$songReview->reviewTextContent?>
                        </div>
                        <a class="read-more" target="_blank" href="<?=base_url('song/showReview?reviewId='.$songReview->reviewId)?>">… Czytaj dalej</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Na ten moment nikt nie zrecenzował tego utworu.</p>
                <?php if ($userLoggedIn): ?>
                    <a href="<?=base_url('song/reviewSong?songId='.$song->SongId)?>">+ Zrecenzuj utwór</a>
                <?php else: ?>
                    <p><a href="<?=base_url('login?src=songPage?songId='.$song->SongId)?>">Zaloguj</a> lub <a href="<?=base_url('newAccount?src=songPage?songId='.$song->SongId)?>">zarejestruj się</a> i zacznij recenzować z RAPPAR!</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>