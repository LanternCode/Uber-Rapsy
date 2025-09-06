<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="#">Nawigacja jest wyłączona w trakcie zatwierdzania utworów. Możesz kontynuować lub zakończyć proces klikając obok. Sesja jest ważna 24 godziny.</a>
    <a class="optionsURL" href="<?=base_url('importSongs?abandon=1')?>">Wyjdź bez dodawania utworów i zakończ sesję.</a>
</header>
<main>
    <br><br><br>
    <h2>Dodajesz następujące utwory do RAPPAR</h2><br>
    <h3>Jeśli utwór znajduje się już w bazie danych, nie zostanie ponownie dodany, nawet jeśli widnieje na liście poniżej.<br>
        Utwory importowane z YouTube nie zawierają informacji o autorze (poza nazwą kanału, który je przesłał).
        <br>Możesz uzupełnić te informacje teraz, wpisując je w pola podane poniżej lub edytując istniejące dane.
        <br>Gdy wszystko będzie gotowe i upewnisz się, że znalezione utwory są poprawne, kliknij przycisk na dole listy, aby zatwierdzić zmiany.</h3>
    <form method="post" action="<?=base_url('confirmImporting')?>">
        <?php $i = 0;
        foreach ($songItems as $song): ?>
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
        <?php $i += 1;
        endforeach; ?>
        <div class="centered">
            <input class="big-button" type="submit" value="Dodaj wszystko do RAPPAR">
        </div>
    </form>
</main>