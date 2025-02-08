<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?=$playlist->ListActive === "1" ? "Ukrywasz" : "Upubliczniasz"?> następującą playlistę:<br><br>

<p>Nazwa playlisty: <?=$playlist->ListName?></p><br>
<p>Link do playlisty: <a target="_blank" href="https://www.youtube.com/playlist?list=<?=$playlist->ListUrl?>"><?=$playlist->ListName?></a></p><br>
<p>Opis playlisty: <?=$playlist->ListDesc?></p><br>
<p>Data dodania playlisty: <?=$playlist->ListCreatedAt?></p><br><br>
<h4>Playlista publiczna: <?=$playlist->ListActive === "1" ? "Tak" : "Nie"?></h4><br><br>

Kontynuować?<br>

<a href="<?=base_url('playlist/hidePlaylist?switch=true&playlistId='.$playlist->ListId.'&src='.$redirectSource)?>">Tak</a><br>
<a href="<?=base_url('playlist/details?listId='.$playlist->ListId.'&src='.$redirectSource)?>">Powrót</a>