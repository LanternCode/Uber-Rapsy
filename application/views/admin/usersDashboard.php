<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Witaj w panelu zarządzania użytkownikami!</h2>
<br/><br/>
<h3>Aktywni Użytkownicy</h3>
<br/><br/>
<table>
    <tr>
        <th>Nazwa Użytkownika</th>
        <th>Rola</th>
        <th>Status Konta</th>
        <th>Szczegóły</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?=$user->username?></td>
            <td><?=$user->role == 'user' ? "Użytkownik" : "Recenzent"?></td>
            <td><?=$user->accountLocked ? "Zablokowane" : "Aktywne"?></td>
            <td><a target="_blank" href="<?=base_url('user/details?uid='.$user->id)?>">Szczegóły</a></td>
        </tr>
    <?php endforeach; ?>
</table>