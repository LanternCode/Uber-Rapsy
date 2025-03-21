<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h4>Usuwasz następującą piosenkę z lokalnej bazy danych:</h4><br>
<h5>UWAGA! Tej akcji nie można cofnąć!</h5><br><br>

<p>Id piosenki w bazie danych: <?=$song->SongId?></p><br>
<p>Nazwa piosenki: <?=$song->SongTitle?></p><br>
<p>Link do piosenki: <a target="_blank" href="https://www.youtu.be/<?=$song->SongURL?>"><?=$song->SongTitle?></a></p></br>
<p>Miniatura piosenki: </p><br>
<img src="<?=$song->SongThumbnailURL?>" alt="Miniatura piosenki"><br>
<p>Unikalne ID piosenki w playliście: <?=$song->SongPlaylistItemsId?></p><br>
<p>Ocena Adama: <?=$song->SongGradeAdam?></p><br>
<p>Ocena Kościelnego: <?=$song->SongGradeChurchie?></p><br>
<p>Playlista: <?=$playlist->ListName?></p><br><br>

Kontynuować?<br>

<a href="<?=base_url('playlist/delSong?songId='.$song->SongId.'&src='.$redirectSource.'&delete=true')?>">Permanentnie usuń piosenkę</a><br>
<a href="<?=base_url('playlist/details?playlistId='.$song->ListId.'&src='.$redirectSource)?>">Nie, Powrót</a>