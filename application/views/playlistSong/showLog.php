<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<a href="<?=base_url('playlist/details?playlistId='.$playlistSong->listId.'&src='.$redirectSource)?>"><-- Wróć do playlisty</a><br><br>

<p>ID w lokalnej bazie danych: <?=$playlistSong->id?></p><br>
<p>ID piosenki na YT: <?=$song->SongURL?></p><br>

<p>Nazwa nuty: <?=$song->SongTitle?></p><br>

<?php if(count($songLog) > 0): ?>
    <h3>Historia Utworu</h3>
        <?php foreach($songLog as $log): ?>
            <?=$log->Description?> <?=$log->Timestamp?> <br> 
        <?php endforeach; ?>
<?php else: ?>
    <h3>Ta nuta nie posiada żadnej historii.</h3>
<?php endif; ?>
