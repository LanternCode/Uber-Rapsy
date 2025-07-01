<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<a href="<?=base_url('songPage?songId='.$song->SongId)?>">Przejdź do strony utworu w RAPPAR</a><br><br>

<p>ID w lokalnej bazie danych: <?=$song->SongId?></p><br>
<p>ID piosenki na YT: <?=$song->SongURL != '' ? $song->SongURL : 'Brak - utwór został dodany manualnie.'?></p><br>

<p>Nazwa nuty: <?=$song->SongTitle?></p><br>
<p>Nuta publiczna: <?=$song->SongVisible == 1 ? 'Publiczna' : 'Ukryta'?></p><br>
<p>Status nuty: <?=$song->SongDeleted == 0 ? 'Aktywna' : 'Na stałe usunięta z RAPPAR'?></p><br>

<?php if(count($songLog) > 0): ?>
    <h3>Historia Utworu</h3>
        <?php foreach($songLog as $log): ?>
            <?=$log->Description?> <?=$log->Timestamp?> <br>
        <?php endforeach; ?>
<?php else: ?>
    <h3>Ta nuta nie posiada żadnej historii.</h3>
<?php endif; ?>
