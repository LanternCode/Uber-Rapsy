<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Profil użytkownika <?=$profile->username?></h2>
<br>
Ilość punktów RAPPAR: <?=$profile->userScore?><br>
Miejsce w rankingu punktowym:
<?=
    empty($scores->user_rank)
        ? 'Użytkownik nie posiada jeszcze żadnych punktów.'
        : $scores->user_rank.' '.
            ($scores->user_rank == 1
                ? '(Użytkownik zajmuje pierwsze miejsce w rankingu. Różnica punktów między pierwszym a drugim miejscem to '.$scores->to_next.'.)'
                : '(Różnica punktów między użytkownikiem a wyższym miejscem to '.((-$scores->to_next)+1).'.)')
?><br>
Status konta: <?=$profile->accountLocked ? 'Zablokowane' : 'Aktywne'?><br>
Data założenia konta: <?=$profile->createdAt?><br><br>
<h3>Logi użytkownika</h3>
<?php if (count($logs) > 0): ?>
    <?php foreach ($logs as $log): ?>
        <?=$log->Description?> <?=$log->Timestamp?><br>
    <?php endforeach; ?>
<?php else: ?>
    <p>Ten użytkownik nie posiada żadnej historii.</p>
<?php endif; ?>