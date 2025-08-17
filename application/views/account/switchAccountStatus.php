<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Profil użytkownika <?=$profile->username?></h2>
Ilość punktów RAPPAR: <?=$profile->userScore?><br>
Data założenia konta: <?=$profile->createdAt?><br><br>
Status konta: <?=$profile->accountLocked ? 'Zablokowane' : 'Aktywne'?><br><br>
<h3>Logi użytkownika</h3>
<?php if (count($logs) > 0): ?>
    <?php foreach ($logs as $log): ?>
        <?=$log->Description?> <?=$log->Timestamp?><br>
    <?php endforeach; ?>
<?php else: ?>
    <p>Ten użytkownik nie posiada żadnej historii.</p>
<?php endif; ?>
<br><br>
<h2>Zmień status konta</h2>
<h4>W momencie nałożenia lub zdjęcia blokady użytkownik otrzyma email z wpisanym powodem.</h4><br><br>
<form method="post" action="<?=base_url('user/changeAccountStatus?uid='.$userId)?>">
    <label>Powód zmiany statusu konta:</label>
    <input type="text" name="statusReason" required><br><br>
    <input type="checkbox" name="conf"> Potwierdzam zmianę statusu konta użytkownika<br><br>
    <input type="submit" class="big-button" value="<?=$profile->accountLocked ? 'Odblokuj konto' : 'Zablokuj konto'?>">
</form>