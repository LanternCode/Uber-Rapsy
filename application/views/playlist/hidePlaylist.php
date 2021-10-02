<?=$playlist->ListActive === "1" ? "Ukrywasz" : "Upubliczniasz"?> następującą playlistę:<br><br>

<p>Nazwa playlisty: <?=$playlist->ListName?></p><br>
<p>Link do playlisty: <a target="_blank" href="https://www.youtube.com/playlist?list=<?=$playlist->ListUrl?>"><?=$playlist->ListName?></a></p></br>
<p>Opis playlisty: <?=$playlist->ListDesc?></p><br>
<p>Data dodania playlisty: <?=$playlist->ListCreatedAt?></p><br>
<p>Playlista publiczna: <?=$playlist->ListActive === "1" ? "Tak" : "Nie"?></p><br><br>

Kontynuować?<br>

<a href="<?=base_url('playlist/hidePlaylist?hide=true&id='.$playlist->ListId)?>">Tak</a><br>
<a href="<?=base_url('playlist/details?id='.$playlist->ListId)?>">Powrót</a>