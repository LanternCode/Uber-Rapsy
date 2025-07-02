<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h4>Witaj w panelu zarządzania użytkownikami!</h4>
<br/><br/>
<h4>Aktywni Użytkownicy</h4>
<br/><br/>
<table>
    <tr>
        <th>Nazwa Użytkownika</th>
        <th>Rola</th>
        <th>Status Konta</th>
        <th>Szczegóły</th>
    </tr>
    <?php foreach($users as $user): ?>
        <tr>
            <td><?=$user->username?></td>
            <td><?=$user->role == 'user' ? "Użytkownik" : "Recenzent"?></td>
            <td><?=$user->accountLocked ? "Zablokowane" : "Aktywne"?></td>
            <td><a href="<?=base_url('user/details?uid='.$user->id)?>" onclick="return false;">Szczegóły</a></td>
        </tr>
    <?php endforeach; ?>
</table>