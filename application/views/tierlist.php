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
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Repeat")?>">Ponowny Odsłuch</option>
        <option value="<?=base_url("playlist?ListId=" . $ListId . "&Reviewer=Unrated")?>">Nieoceniona</option>
    </select>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("playlist")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="hidden" name="ListId" value="<?=$ListId?>" />
        <input type="text" placeholder="Rajaner" name="Search" />
        <input type="submit" value="Szukaj" />
    </form>
</header>
<div id="songsForm" class="optionsHeaderSpace"></div>
<?php if(count($gradesToDisplay) == 1): ?>
    <h3>Nie znaleziono żadnych ocen tego recenzenta :/</h3>
<?php else:
    for($i = 15; $i >= 1; $i -= 0.25):
        if(in_array($i, $gradesToDisplay)): ?>
            <h4 class="gradeCategory">Ocena <?=$i?>:</h4>
            <?php foreach($songs as $song):
                if(($Operation == "Adam" && $song->SongGradeAdam == $i) || ($Operation == "Churchie" && $song->SongGradeChurchie == $i) ||
                    ($Operation == "Average" && bcdiv(($song->SongGradeAdam+$song->SongGradeChurchie)/2, 1, 2) == $i)): ?>
                    <div class="videoContainer">
                        <img src="<?=$song->SongThumbnailURL?>" alt="thumbnail" class="songThumbnailLeft" />
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
                            <label><input type="hidden" name="<?="songRehearsal-".$i+4?>" value="<?=$song->SongRehearsal?>"><input type="checkbox" <?=$song->SongRehearsal ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Do ponownego odsłuchu</label>
                            <label><input type="hidden" name="<?="songBelow-".$i+13?>" value="<?=$song->SongBelow?>"><input type="checkbox" <?=$song->SongBelow ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> < 7</label>
                            <label><input type="hidden" name="<?="songDistinction-".$i+5?>" value="<?=$song->SongDistinction?>"><input type="checkbox" <?=$song->SongDistinction ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Wyróżnienie</label>
                            <label><input type="hidden" name="<?="songMemorial-".$i+6?>" value="<?=$song->SongMemorial?>"><input type="checkbox" <?=$song->SongMemorial ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> 10*</label>
                            <label><input type="hidden" name="<?="songUber-".$i+12?>" value="<?=$song->SongUber?>"><input type="checkbox" <?=$song->SongUber ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Uber</label>
                            <label><input type="hidden" name="<?="songTop-".$i+10?>" value="<?=$song->SongTop?>"><input type="checkbox" <?=$song->SongTop ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> X15</label>
                            <label><input type="hidden" name="<?="songXD-".$i+7?>" value="<?=$song->SongXD?>"><input type="checkbox" <?=$song->SongXD ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> XD</label>
                            <label><input type="hidden" name="<?="songDiscomfort-".$i+9?>" value="<?=$song->SongDiscomfort?>"><input type="checkbox" <?=$song->SongDiscomfort ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Strefa Dyskomfortu</label>
                            <label><input type="hidden" name="<?="songNotRap-".$i+8?>" value="<?=$song->SongNotRap?>"><input type="checkbox" <?=$song->SongNotRap ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> To nie rapsik</label>
                            <label><input type="hidden" name="<?="songNoGrade-".$i+11?>" value="<?=$song->SongNoGrade?>"><input type="checkbox" <?=$song->SongNoGrade ? "checked" : ""?> onclick="this.previousSibling.value=1-this.previousSibling.value"> Nie oceniam</label>
                        </div>
                    </div>
                <?php endif;
            endforeach;
        endif;
    endfor;
endif; ?>
<div id="bottom"></div>
