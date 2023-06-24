<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Oceny zostały zapisane!</h1>
<br><br>
<?php if(is_numeric($playlistId)): ?>
    <a href="<?=base_url('playlist?ListId='.$playlistId)?>">Powrót do playlisty</a>
<?php else: ?>
    <a href="<?=base_url()?>">Powrót do strony głównej</a>
<?php endif; ?>
<br><br><br>
<h2>Wynik Zapisu:</h2>
<?=$resultMessage?>
<br><br>

