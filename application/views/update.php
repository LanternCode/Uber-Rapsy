<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Zapisywanie ocen</h1>
<br><br>
<?php if($searchQuery): ?>
    <a href="<?=base_url('search?SearchQuery='.$searchQuery)?>">Powrót do wyników wyszukiwania</a>
<?php elseif (isset($playlistId) && is_numeric($playlistId)): ?>
    <a href="<?=base_url('playlist?listId='.$playlistId)?>">Powrót do playlisty</a>
<?php else: ?>
    <a href="<?=base_url()?>">Powrót do strony głównej</a>
<?php endif; ?>
<br><br><br>
<h2>Wynik Zapisu:</h2>
<h3>Na playliście odnaleziono <?=$processedSongsCount-1?> utwór/utwory/utworów, zaktualizowano <?=$processedAndUpdatedSongsCount?></h3>
<?php if(isset($displayErrorMessage) && $displayErrorMessage !== ''): ?>
    <br><br>
    <h3>Wystąpił błąd w zapisywaniu ocen :/</h3>
    <h4><?=$displayErrorMessage?></h4>
    <h4>Jeśli po wykonaniu instrukcji, ponowne podjęcie tej samej akcji nie zadziała, niezwłocznie skontaktuj się z administracją.</h4>
    <br><br>
<?php endif; ?>
<?=$resultMessage?>
<br><br>

