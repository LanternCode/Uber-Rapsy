<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Usuwasz następującą piosenkę z lokalnej bazy danych:</h2><br>
<h3>UWAGA! Tej akcji nie można cofnąć! Usuniętego utworu nie będzie się dało ponownie dodać na tę playlistę!</h3><br><br>

<p>Id piosenki w bazie danych: <?=$playlistSong->id?></p><br>
<p>Nazwa piosenki: <?=$song->SongTitle?></p><br>
<p>Link do piosenki: <a target="_blank" href="https://youtu.be/<?=$song->SongURL?>"><?=$song->SongTitle?></a></p></br>
<p>Miniatura piosenki: </p><br>
<img src="<?=$song->SongThumbnailURL?>" alt="Miniatura piosenki"><br>
<p>Unikalne ID piosenki w playliście YT: <?=$playlistSong->SongPlaylistItemsId?></p><br>
<?php if ($playlist->ListOwnerId == 1): ?>
    <p>Ocena Adama: <?=$playlistSong->SongGradeAdam?></p><br>
    <p>Ocena Kościelnego: <?=$playlistSong->SongGradeChurchie?></p><br>
<?php else: ?>
    <p>Moja ocena: <?=$playlistSong->SongGradeOwner?></p><br>
<?php endif; ?>
<p>Playlista: <?=$playlist->ListName?></p><br><br>

Kontynuować?<br>

<a href="<?=base_url('playlist/delSong?songId='.$playlistSong->id.'&src='.$redirectSource.'&delete=true')?>">Permanentnie usuń piosenkę</a><br>
<a href="<?=base_url('playlist/details?playlistId='.$playlistSong->listId.'&src='.$redirectSource)?>">Nie, Powrót</a>