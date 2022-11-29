<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<a href="<?=base_url('playlistDashboard')?>"><-- Wróć do panelu zarządzania playlistami</a><br><br>

<p>ID w lokalnej bazie danych: <?=$playlist->ListId?></p><br>
<p>ID playlisty na YT: <?=$playlist->ListUrl?></p><br>

<p>Nazwa playlisty: <?=$playlist->ListName?></p><br>
<p>Data dodania playlisty: <?=$playlist->ListCreatedAt?></p><br>

<?php if(count($playlistLog) > 0): ?>
    <h3>Historia Playlisty</h3>
        <?php foreach($playlistLog as $log): ?>
            <?=$log->Description?> <?=$log->Timestamp?> <br> 
        <?php endforeach; ?>
<?php else: ?>
    <h3>Ta playlista nie posiada żadnej historii.</h3>
<?php endif; ?>
