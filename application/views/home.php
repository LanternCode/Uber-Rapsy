<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Wybierz playlistę:</h2>

<table>
    <tr>
        <th>Playlista</th>
        <th>Opis</th>
    </tr>
    <?php foreach($lists as $list): ?>
        <tr>
            <td>
                <a href="<?=base_url("playlist?ListId=" . $list->ListId)?>">
                    <div>
                        <?=$list->ListName;?>
                    </div>
                </a><br />
            </td>
            <td>
                <?=$list->ListDesc?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="<?=base_url("loginYoutube")?>">Zaloguj się do YT</a>

