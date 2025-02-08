<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?= $playlistUpdatedMessage ?? "" ?>
<p>Status playlisty przed zmianami: <?=$playlist->ListIntegrated ? "Zintegrowana" : "Niezintegrowana"?></p><br>
<p>Nazwa playlisty: <?=$playlist->ListName?></p><br>
<p>Podany link do playlisty: <a target="_blank" href="https://www.youtube.com/playlist?list=<?=$playlist->ListUrl?>">https://www.youtube.com/playlist?list=<?=$playlist->ListUrl?></a></p><br><br>
<h2><?=!$playlist->ListIntegrated ? "Czy chcesz zintegrować tą playlistę z Youtube?" : "Czy chcesz usunąć integrację tej playlisty z YouTube?"?></h2><br><br>
<label>Aby zintegrować playlistę, musisz podać link do tej playlisty na YT!<br>
Jeżeli zostawisz to pole puste, zostanie użyty wskazany wyżej link. Upewnij się, że jest poprawny!<br>
Jeśli usuwasz integrację, możesz wstawić tutaj nowy link który zostanie zapisany, lub zostawić pole puste.</label><br>
<form method="post" action="<?=base_url('playlist/integrate?id='.$playlist->ListId.'&status=confirm&src='.$redirectSource)?>">
    <label>Nowy link do playlisty:</label><br>
    <input type="url" name="nlink" <?=isset($playlistUpdatedStatus) && $playlistUpdatedStatus ? "disabled" : ""?>><br><br>

    <input type="submit"
        value="<?=!$playlist->ListIntegrated ? "Zintegruj playlistę" : "Usuń integrację tej playlisty z YT"?>"
        <?=isset($playlistUpdatedStatus) && $playlistUpdatedStatus ? "disabled" : ""?>
    >
</form><br><br>
<a href="<?=base_url('playlist/details?id='.$playlist->ListId.'&src='.$redirectSource)?>">Powrót</a>