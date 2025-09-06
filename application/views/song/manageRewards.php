<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("myPlaylists")?>">Moje Konto i Playlisty</a>
    <a class="optionsURL" href="<?=base_url("frontpage")?>">Toplisty RAPPAR</a>
    <a class="optionsURL" href="<?=base_url("songPage?songId=".$song->SongId)?>">Wróc do utworu</a>
    <a class="optionsURL" href="<?=base_url("song/edit?songId=".$song->SongId)?>">Edytuj Utwór</a>
    <a class="optionsURL" href="<?=base_url("adminDashboard")?>">Panel Sterowania</a>
    <a class="optionsURL" href="<?=base_url("logout")?>">Wyloguj się</a>
</header>
<br><br><br><br>
<h3>Obecne nagrody utworu <?=$song->SongTitle?>:</h3><br>
<div>
    <?php if (count($songAwards) > 0): ?>
        <?php foreach ($songAwards as $award): ?>
            <p class="song-awards centered"><?=$award->award?> <a href="<?=base_url('song/awards?songId='.$song->SongId.'&delAward='.$award->id)?>" title="Usuń Nagrodę">❌</a></p><br>
        <?php endforeach; ?>
    <?php else: ?>
        <h4>Utwór nie posiada żadnych nagród.</h4><br>
    <?php endif; ?>
</div>
<h3>Dodaj nową nagrodę</h3><br>
<form method="post" action="<?=base_url('song/awards?songId='.$song->SongId)?>">
    <label>Nazwa Nagrody:
        <input type="text" name="awardName" placeholder="Nuta Roku 2025">
        <input type="submit" value="Dodaj Nagrodę">
        <?=isset($awardError) ? '<br>'.$awardError : ''?>
    </label>
</form>