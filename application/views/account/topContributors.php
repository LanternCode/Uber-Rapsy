<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="centered redirectError">
    <span>🏆 Ranking najbardziej aktywnych użytkowników RAPPAR 🏆</span>
</div><br><br>
<table>
    <tr>
        <th>Miejsce w rankingu</th>
        <th>Użytkownik</th>
        <th>Ilość punktów</th>
    </tr>
    <?php foreach ($ranking as $user): ?>
        <tr>
            <td><?=$user->position == 1 ? '🥇' : ($user->position == 2 ? '🥈' : ($user->position == 3 ? '🥉' : $user->position))?></td>
            <td><?=$user->username?></td>
            <td><?=$user->userScore?></td>
        </tr>
    <?php endforeach; ?>
</table>