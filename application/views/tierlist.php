<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">Powrót do playlist</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#songsForm">Góra Listy</a>
    <select id="selectbox" class="optionsURL" onchange="javascript:location.href = this.value;">
        <option value="">Pokaż oceny:</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId)?>">Wszystkie oceny</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Adam")?>">Najlepsze: Adam</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Churchie")?>">Najlepsze: Kościelny</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Average")?>">Najlepsze: Średnia</option>
    </select>
</header>
<div id="songsForm" class="optionsHeaderSpace"></div>
<?php if(count($gradesToDisplay) == 1): ?>
    <h3>Nie znaleziono żadnych ocen tego recenzenta :/</h3>
<?php else:
    for($i = 15; $i >= 1; $i -= 0.25):
        if(in_array($i, $gradesToDisplay)): ?>
            <h4 class="gradeCategory">Ocena <?=$i?>:</h4>
            <?php foreach($songs as $song):
                if($Operation == "Adam" && $song->SongGradeAdam == $i): ?>
                    <div class="videoContainer">
                        <img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailLeft" />
                        <div class="dataContainer">
                            <h3 class="songTitle"><a href="<?=$song->SongURL?>"><?=$song->SongTitle?></a></h3>
                            <h4 class="dataContainer--gradeContainer">
                                Adam:
                                <input name="<?='OA-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
                                <span class="newScore" id="<?='NGBA-'.$song->SongId?>">
                                    ->
                                    <input class="gradeInputNew" type="text"
                                        id="<?='A-'.$song->SongId?>" name="<?='A-'.$song->SongId?>"
                                        value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
                                </span>
                            </h4>
                            <h4 class="dataContainer--gradeContainer">
                                Kościelny:
                                <input name="<?='OK-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeChurchie > 0 ? $song->SongGradeChurchie : 'Nieoceniona'?>" />
                                <span class="newScore" id="<?='NGBK-'.$song->SongId?>">
                                    ->
                                    <input class="gradeInputNew" type="text"
                                        id="<?='K-'.$song->SongId?>" name="<?='K-'.$song->SongId?>"
                                        value="<?=$song->SongGradeChurchie > 0 ? $song->SongGradeChurchie : 'Nieoceniona'?>" />
                                </span>
                            </h4>
                            <h5 class="dataContainer--gradeContainer">Średnia:
                                <input type="text" value="<?=$song->SongGradeAdam > 0 && $song->SongGradeChurchie > 0 ? ($song->SongGradeAdam + $song->SongGradeChurchie) / 2 : 'Nieoceniona'?>" disabled />
                                <span class="newScore" id="<?='NGBAv-'.$song->SongId?>">
                                    ->
                                    <input class="averageNew" type="text"
                                        id="<?=$song->SongId?>"
                                        value="<?=($song->SongGradeAdam > 0 && $song->SongGradeChurchie > 0) ? (($song->SongGradeAdam + $song->SongGradeChurchie) / 2) : 'Nieoceniona'?>" />
                                </span>
                            </h5>
                        </div>
                        <img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailRight" />
                    </div>
                <?php elseif($Operation == "Churchie" && $song->SongGradeChurchie == $i): ?>
                    <div class="videoContainer">
                        <img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailLeft" />
                        <div class="dataContainer">
                            <h3 class="songTitle"><a href="<?=$song->SongURL?>"><?=$song->SongTitle?></a></h3>
                            <h4 class="dataContainer--gradeContainer">
                                Kościelny:
                                <input name="<?='OK-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeChurchie > 0 ? $song->SongGradeChurchie : 'Nieoceniona'?>" />
                                <span class="newScore" id="<?='NGBK-'.$song->SongId?>">
                                    ->
                                    <input class="gradeInputNew" type="text"
                                        id="<?='K-'.$song->SongId?>" name="<?='K-'.$song->SongId?>"
                                        value="<?=$song->SongGradeChurchie > 0 ? $song->SongGradeChurchie : 'Nieoceniona'?>" />
                                </span>
                            </h4>
                            <h4 class="dataContainer--gradeContainer">
                                Adam:
                                <input name="<?='OA-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
                                <span class="newScore" id="<?='NGBA-'.$song->SongId?>">
                                    ->
                                    <input class="gradeInputNew" type="text"
                                        id="<?='A-'.$song->SongId?>" name="<?='A-'.$song->SongId?>"
                                        value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
                                </span>
                            </h4>
                            <h5 class="dataContainer--gradeContainer">Średnia:
                                <input type="text" value="<?=$song->SongGradeAdam > 0 && $song->SongGradeChurchie > 0 ? ($song->SongGradeAdam + $song->SongGradeChurchie) / 2 : 'Nieoceniona'?>" disabled />
                                <span class="newScore" id="<?='NGBAv-'.$song->SongId?>">
                                    ->
                                    <input class="averageNew" type="text"
                                        id="<?=$song->SongId?>"
                                        value="<?=($song->SongGradeAdam > 0 && $song->SongGradeChurchie > 0) ? (($song->SongGradeAdam + $song->SongGradeChurchie) / 2) : 'Nieoceniona'?>" />
                                </span>
                            </h5>
                        </div>
                        <img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailRight" />
                    </div>
                <?php elseif($Operation == "Average" && bcdiv(($song->SongGradeAdam+$song->SongGradeChurchie)/2, 1, 2) == $i ): ?>
                    <div class="videoContainer">
                        <img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailLeft" />
                        <div class="dataContainer">
                            <h3 class="songTitle"><a href="<?=$song->SongURL?>"><?=$song->SongTitle?></a></h3>
                            <h5 class="dataContainer--gradeContainer">Średnia:
                                <input type="text" value="<?=$song->SongGradeAdam > 0 && $song->SongGradeChurchie > 0 ? ($song->SongGradeAdam + $song->SongGradeChurchie) / 2 : 'Nieoceniona'?>" disabled />
                                <span class="newScore" id="<?='NGBAv-'.$song->SongId?>">
                                    ->
                                    <input class="averageNew" type="text"
                                        id="<?=$song->SongId?>"
                                        value="<?=($song->SongGradeAdam > 0 && $song->SongGradeChurchie > 0) ? (($song->SongGradeAdam + $song->SongGradeChurchie) / 2) : 'Nieoceniona'?>" />
                                </span>
                            </h5>
                            <h4 class="dataContainer--gradeContainer">
                                Adam:
                                <input name="<?='OA-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
                                <span class="newScore" id="<?='NGBA-'.$song->SongId?>">
                                    ->
                                    <input class="gradeInputNew" type="text"
                                        id="<?='A-'.$song->SongId?>" name="<?='A-'.$song->SongId?>"
                                        value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
                                </span>
                            </h4>
                            <h4 class="dataContainer--gradeContainer">
                                Kościelny:
                                <input name="<?='OK-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeChurchie > 0 ? $song->SongGradeChurchie : 'Nieoceniona'?>" />
                                <span class="newScore" id="<?='NGBK-'.$song->SongId?>">
                                    ->
                                    <input class="gradeInputNew" type="text"
                                        id="<?='K-'.$song->SongId?>" name="<?='K-'.$song->SongId?>"
                                        value="<?=$song->SongGradeChurchie > 0 ? $song->SongGradeChurchie : 'Nieoceniona'?>" />
                                </span>
                            </h4>
                        </div>
                        <img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailRight" />
                    </div>
                <?php endif;
            endforeach;
        endif;
    endfor;
endif; ?>
<div id="bottom"></div>
