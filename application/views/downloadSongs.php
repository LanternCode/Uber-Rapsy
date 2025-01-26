<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<br /><br />
<?php if(isset($refreshSuccess) && $refreshSuccess === false): ?>
    <h3>Podano niepoprawny link do playlisty na YT!</h3>
    <h4>W wolnej chwili przejdź do ustawień i zmień link na poprawny, bądź go usuń!</h4>
    <h4>Ten błąd otrzymasz także jeśli nie jesteś właścicielem playlisty.</h4>
<?php else: ?>
    <h3>Ładowanie utworów na playlistę zakończone sukcesem!</h3>
<?php endif; ?>
<br /><br />
<a href="<?=base_url('/playlist?ListId=' . $ListId)?>">Powrót do listy</a>
<br />
