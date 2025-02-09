<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h4>Usuwasz następującą playlistę:</h4><br>
<h5>UWAGA! Tej akcji nie można cofnąć!</h5><br><br>

<p>Nazwa playlisty: <?=$playlist->ListName?></p><br>
<p>Link do playlisty: <a target="_blank" href="https://www.youtube.com/playlist?list=<?=$playlist->ListUrl?>"><?=$playlist->ListName?></a></p></br>
<p>Opis playlisty: <?=$playlist->ListDesc?></p><br>
<p>Data dodania playlisty: <?=$playlist->ListCreatedAt?></p><br>
<p>Playlista publiczna: <?=$playlist->ListPublic === "1" ? "Tak" : "Nie"?></p><br><br>

Kontynuować?<br>

<a href="<?=base_url('playlist/deleteLocal?del=true&playlistId='.$playlist->ListId.'&src='.$redirectSource)?>">Permanentnie usuń playlistę</a><br>
<a href="<?=base_url('playlist/details?listId='.$playlist->ListId.'&src='.$redirectSource)?>">Nie, Powrót</a>