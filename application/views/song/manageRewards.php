<?php ?>
<h3>Obecne nagrody utworu <?=$song->SongTitle?>:</h3><br>
<div>
    <?php foreach($songAwards as $award): ?>
        <p class="song-awards centered"><?=$award->award?> <a href="<?=base_url('song/awards?songId='.$song->SongId.'&delAward='.$award->id)?>" title="Usuń Nagrodę">❌</a></p><br>
    <?php endforeach; ?>
</div>
<h3>Dodaj nową nagrodę</h3><br>
<form method="post" action="<?=base_url('song/awards?songId='.$song->SongId)?>">
    <label>Nazwa Nagrody:
        <input type="text" name="awardName" placeholder="Nuta Roku 2025">
        <input type="submit" value="Dodaj Nagrodę">
        <?=isset($awardError) ? '<br>'.$awardError : ''?>
    </label>
</form>