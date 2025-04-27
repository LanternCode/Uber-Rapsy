<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h2>Dodajesz następujące utwory do RAPPAR</h2><br>
<h3>Jeśli utwór istnieje już w bazie danych, nie zostanie dodany, nawet jeśli znajduje się na liście poniżej.<br>
    Utwory importowane z YT nie zawierają informacji o autorze (poza nazwą kanału, który przesłał go na YT), ani daty przesłania go na YT.
    <br>Te informacje możesz dodać w tym momencie, zmieniając lub wpisując ją w podane poniżej pola.
    <br>Kiedy już dodasz wszystko i upewnisz się że znalezione utwory są poprawne, wciśnij przycisk na samym dole listy żeby zatwierdzić zmiany.</h3>
<form method="post" action="<?=base_url('confirmImporting')?>">
    <?php
    $i = 0;
    foreach ($songItems as $song) {
        $songPublic = true;//isset($song['snippet']['thumbnails']['medium']['url']);
        if ($songPublic): ?>
            <div class="song-container songBackground">
                <div class="song-header songBackground">
                    <div class="songBackground">
                        <h2 class="song-title songBackground"><?=$song['songTitle']?></h2>
                        <p class="song-authors songBackground"><input type="text" name="songChannelName-<?=$i?>" value="<?=$song['songChannelName']?>"> (<?=$song['songPublishedAt']?>)</p>
                    </div>
                    <div class="song-awards songBackground">
                        <p>Nagrody Społeczności</p>
                    </div>
                </div>
                <div class="song-content songBackground">
                    <img src="<?=$song['songThumbnailLink']?>" alt="Song Thumbnail" class="song-thumbnail" />
                    <div class="song-grades">
                        <p>Moja Ocena</p>
                        <p>Ocena Adama</p>
                        <p>Ocena Kościelnego</p>
                        <p>Średnia Ocen Społeczności</p>
                    </div>
                </div>
            </div>
        <?php
            $i += 1;
            endif;
    } ?>
    <div class="centered">
        <input class="big-button" type="submit" value="Dodaj wszystko do RAPPAR">
    </div>
</form>
