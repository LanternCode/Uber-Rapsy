<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">Powrót do playlist</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#topoftherap">Góra Listy</a>
    <?php if(isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer" && count($songs) > 0): ?>
        <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
    <?php endif; ?>
    <select class="optionsURL redirectsBox" onchange="javascript:location.href = this.value;">
        <option value="">Pokaż oceny:</option>
        <option value="<?=base_url("playlist?ListId=".$ListId)?>">Wszystkie Oceny</option>
        <option value="<?=base_url("playlist?ListId=".$ListId."&Filter=Adam")?>">Najlepsze: Adam</option>
        <option value="<?=base_url("playlist?ListId=".$ListId."&Filter=Churchie")?>">Najlepsze: Kościelny</option>
        <option value="<?=base_url("playlist?ListId=".$ListId."&Filter=Average")?>">Najlepsze: Średnia</option>
    </select>
</header>
<br id="topoftherap"><br><br>
<div class="averagesBar">
    <div class="averagesBar--left">
        <h2 class="blackBar">Przeglądasz tierlistę <?=$Filter == "Adam" ? "Adama" : ($Filter == "Average" ? "średniej ocen" : "Kościelnego")?> plejki <?=$ListName ?? "o nieznanej nazwie"?>!</h2>
        <h3 class="blackBar">Liczba ocenionych nut: <?=count($songs)?></h3>
    </div>
</div>
<form id="songsForm" method="post" action="<?=base_url('updateSelection')?>">
    <?php if(count($gradesToDisplay) == 1): ?>
        <h3>Nie znaleziono żadnych ocen <?=$Filter == "Adam" ? "Adama" : ($Filter == "Average" ? "Średniej" : "Kościelnego")?> :/</h3>
    <?php else: ?>
        <?php $i = 0;
        for($j = 15; $j >= 1; $j -= 0.25):
            if(in_array($j, $gradesToDisplay)): ?>
                <h4 class="gradeCategory">Ocena <?=$j?>:</h4>
                <?php foreach($songs as $song):
                    if(($Filter == "Adam" && $song->SongGradeAdam == $j) || ($Filter == "Churchie" && $song->SongGradeChurchie == $j) ||
                        ($Filter == "Average" && bcdiv(($song->SongGradeAdam+$song->SongGradeChurchie)/2, 1, 2) == $j)): ?>
                        <div class="videoContainerBox">
                            <img src="<?=$song->SongThumbnailURL?>" alt="thumbnail" class="songThumbnailLeft" />
                            <div class="dataContainerBox">
                                <input type="hidden" name="songId-<?=$i?>" value="<?=$song->SongId?>"/>
                                <h3 class="songTitle"><a href="https://youtu.be/<?=$song->SongURL?>" target="_blank"><?=$song->SongTitle?></a></h3>
                                <div class="dataContainerBox--split">
                                    <div class="dataContainerBox--split__left">
                                        <h4 class="dataContainer--gradeContainer">
                                            <label>Adam: <?=$song->SongGradeAdam ?? 'Nieoceniona'?> -></label>
                                            <input name="nwGradeA-<?=$i+1?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                                   value="<?=$song->SongGradeAdam ?? 'Nieoceniona'?>" <?=$Reviewer ? "" : "disabled" ?>/>
                                        </h4>
                                        <h4 class="dataContainer--gradeContainer">
                                            <label>Kościelny: <?=$song->SongGradeChurchie ?? 'Nieoceniona'?> -></label>
                                            <input name="nwGradeC-<?=$i+2?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                                   value="<?=$song->SongGradeChurchie ?? 'Nieoceniona'?>" <?=$Reviewer ? "" : "disabled" ?>/>
                                        </h4>
                                        <h5 class="dataContainer--gradeContainer">
                                            <label>Średnia:</label>
                                            <input class="gradeInput" type="text" value="<?=is_numeric($song->SongGradeAdam) && is_numeric($song->SongGradeChurchie) ? (($song->SongGradeAdam + $song->SongGradeChurchie) / 2) : "Nieoceniona"?>" disabled />
                                        </h5>
                                        <?php  //only 1 list means there is nowhere to move or copy the song to
                                        if(count($lists) > 1 && $Reviewer): ?>
                                            <h5 class="dataContainer--gradeContainer">
                                                <label>Przenieś do:</label>
                                                <select name="<?="nwPlistId-".$i+3?>" class="selectBox">
                                                    <option value="0">Nie przenoś</option>
                                                    <?php foreach($lists as $list):
                                                        if($list->ListId !== $ListId):?>
                                                            <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                                        <?php endif;
                                                    endforeach; ?>
                                                </select>
                                            </h5>
                                            <h5 class="dataContainer--gradeContainer">
                                                <label>Kopiuj do:</label>
                                                <select name="<?="copyPlistId-".$i+20?>" class="selectBox">
                                                    <option value="0">Nie kopiuj</option>
                                                    <?php foreach($lists as $list):
                                                        if($list->ListId !== $ListId):?>
                                                            <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                                        <?php endif;
                                                    endforeach; ?>
                                                </select>
                                            </h5>
                                        <?php else: ?>
                                            <select style="display:none;" name="<?="nwPlistId-".$i+3?>">
                                                <option value="0">Nie przenoś</option>
                                            </select>
                                            <select style="display:none;" name="<?="copyPlistId-".$i+20?>">
                                                <option value="0">Nie kopiuj</option>
                                            </select>
                                        <?php endif;?>
                                    </div>
                                    <div class="dataContainerBox--split__right">
                                        <textarea placeholder="Komentarz do utworu..." class="commentBox" name="songComment-<?=$i+22?>"><?=$song->SongComment?></textarea>
                                    </div>
                                </div>
                                <label <?=$playlist->btnRehearsal ? '' : 'hidden'?>><input type="checkbox" name="<?="songRehearsal-".$i+4?>" class="buttonBox" <?=$song->SongRehearsal ? "checked" : ""?>> Do ponownego odsłuchu</label>
                                <label <?=$playlist->btnBelowFour ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelFour-".$i+17?>" class="buttonBox" <?=$song->SongBelFour ? "checked" : ""?>> < 4</label>
                                <label <?=$playlist->btnBelowSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelow-".$i+13?>" class="buttonBox" <?=$song->SongBelow ? "checked" : ""?>> < 7</label>
                                <label <?=$playlist->btnBelowHalfSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfSeven-".$i+23?>" class="buttonBox" <?=$song->SongBelHalfSeven ? "checked" : ""?>> < 7.5</label>
                                <label <?=$playlist->btnBelowEight ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelEight-".$i+16?>" class="buttonBox" <?=$song->SongBelEight ? "checked" : ""?>> < 8</label>
                                <label <?=$playlist->btnBelowHalfEight ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfEight-".$i+24?>" class="buttonBox" <?=$song->SongBelHalfEight ? "checked" : ""?>> < 8.5</label>
                                <label <?=$playlist->btnBelowNine ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelNine-".$i+15?>" class="buttonBox" <?=$song->SongBelNine ? "checked" : ""?>> < 9</label>
                                <label <?=$playlist->btnBelowHalfNine ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfNine-".$i+25?>" class="buttonBox" <?=$song->SongBelHalfNine ? "checked" : ""?>> < 9.5</label>
                                <label <?=$playlist->btnBelowTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelTen-".$i+14?>" class="buttonBox" <?=$song->SongBelTen ? "checked" : ""?>> < 10</label>
                                <label <?=$playlist->btnDistinction ? '' : 'hidden'?>><input type="checkbox" name="<?="songDistinction-".$i+5?>" class="buttonBox" <?=$song->SongDistinction ? "checked" : ""?>> Wyróżnienie</label>
                                <label <?=$playlist->btnDuoTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songDuoTen-".$i+18?>" class="buttonBox" <?=$song->SongDuoTen ? "checked" : ""?>> "10"</label>
                                <label <?=$playlist->btnMemorial ? '' : 'hidden'?>><input type="checkbox" name="<?="songMemorial-".$i+6?>" class="buttonBox" <?=$song->SongMemorial ? "checked" : ""?>> 10*</label>
                                <label <?=$playlist->btnUber ? '' : 'hidden'?>><input type="checkbox" name="<?="songUber-".$i+12?>" class="buttonBox" <?=$song->SongUber ? "checked" : ""?>> Uber</label>
                                <label <?=$playlist->btnTop ? '' : 'hidden'?>><input type="checkbox" name="<?="songTop-".$i+10?>" class="buttonBox" <?=$song->SongTop ? "checked" : ""?>> X15</label>
                                <label <?=$playlist->btnXD ? '' : 'hidden'?>><input type="checkbox" name="<?="songXD-".$i+7?>" class="buttonBox" <?=$song->SongXD ? "checked" : ""?>> XD</label>
                                <label <?=$playlist->btnDiscomfort ? '' : 'hidden'?>><input type="checkbox" name="<?="songDiscomfort-".$i+9?>" class="buttonBox" <?=$song->SongDiscomfort ? "checked" : ""?>> Strefa Dyskomfortu</label>
                                <label <?=$playlist->btnDepA ? '' : 'hidden'?>><input type="checkbox" name="<?="songDepA-".$i+26?>" class="buttonBox" <?=$song->SongDepA ? "checked" : ""?>> Dep. Abroad</label>
                                <label <?=$playlist->btnNotRap ? '' : 'hidden'?>><input type="checkbox" name="<?="songNotRap-".$i+8?>" class="buttonBox" <?=$song->SongNotRap ? "checked" : ""?>> To nie rapsik</label>
                                <label <?=$playlist->btnNoGrade ? '' : 'hidden'?>><input type="checkbox" name="<?="songNoGrade-".$i+11?>" class="buttonBox" <?=$song->SongNoGrade ? "checked" : ""?>> Nie oceniam</label>
                                <label <?=$playlist->btnVeto ? '' : 'hidden'?>><input type="checkbox" name="<?="songVeto-".$i+19?>" class="buttonBox" <?=$song->SongVeto ? "checked" : ""?>> VETO</label>
                                <input type="hidden" name="songUpdated-<?=$i+21?>" value="0">
                            </div>
                        </div>
                    <?php
                    $i += 27;
                    endif;
                endforeach;
            endif;
        endfor;
    endif; ?>
    <input type="hidden" name="playlistId" value="<?=$ListId?>"/>
</form>
<div id="bottom"></div>
<script type="text/javascript" src="<?=base_url( 'scripts/playlist.js' )?>"></script>