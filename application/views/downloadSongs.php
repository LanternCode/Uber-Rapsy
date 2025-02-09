<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<br /><br />
<?php if($displayErrorMessage !== ''): ?>
    <h3>Wystąpił błąd w pobieraniu utworów z playlisty na YT :/</h3>
    <h4><?=$displayErrorMessage?></h4>
    <h4>Jeśli ponowne podjęcie tej samej akcji nie zadziała, niezwłocznie skontaktuj się z administracją.</h4>
<?php else: ?>
    <h3>Pomyślnie załadowano utwory z playlisty na YT!</h3>
<?php endif; ?>
<br /><br />
<a href="<?=base_url('/playlist?listId='.$listId)?>">Powrót do listy</a>
<br />
