<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">Powrót do playlist</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#topoftherap">Góra Listy</a>
    <?php if(($isOwner || $isReviewer) && count($songs) > 0): ?>
        <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
    <?php endif; ?>
    <select class="optionsURL redirectsBox" onchange="javascript:location.href = this.value;">
        <option value="">Pokaż oceny:</option>
        <option value="<?=base_url("playlist?listId=".$listId)?>">Wszystkie Oceny</option>
        <option value="<?=base_url("tierlist?listId=".$listId."&filter=Adam")?>">Najlepsze: Adam</option>
        <option value="<?=base_url("tierlist?listId=".$listId."&filter=Churchie")?>">Najlepsze: Kościelny</option>
        <option value="<?=base_url("tierlist?listId=".$listId."&filter=Owner")?>">Najlepsze: Właściciel</option>
        <option value="<?=base_url("tierlist?listId=".$listId."&filter=Average")?>">Najlepsze: Średnia</option>
    </select>
</header>
<br id="topoftherap"><br><br>
<div class="averagesBar">
    <div class="averagesBar--left">
        <h2 class="blackBar">Przeglądasz tierlistę <?=$filter == "Adam" ? "Adama" : ($filter == "Churchie" ? "Kościelnego" : ($filter == "Owner" ? "właściciela" : "średniej ocen"))?> plejki <?=$playlist->ListName ?? "o nieznanej nazwie"?>!</h2>
        <h3 class="blackBar">Liczba ocenionych nut: <?=count($songs)?></h3>
    </div>
</div>
<form id="songsForm" method="post" action="<?=base_url('updateSongRatings')?>">
    <?php if(count($songs) < 1): ?>
        <h3>Nie znaleziono żadnych ocen <?=$filter == "Adam" ? "Adama" : ($filter == "Churchie" ? "Kościelnego" : ($filter == "Owner" ? "właściciela playlisty" : "żeby liczyć średnią ocen"))?> :/</h3>
    <?php else:
        $lastGrade = 0;
        $i = 0;
        foreach ($songs as $song):
            if($song->$propName != $lastGrade):
                $lastGrade = $song->$propName; ?>
                <h4 class="gradeCategory">Ocena <?=$lastGrade?>:</h4>
            <?php endif; ?>
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
                                       value="<?=$song->SongGradeAdam ?? 'Nieoceniona'?>" <?=$isReviewer ? "" : "disabled" ?>/>
                            </h4>
                            <h4 class="dataContainer--gradeContainer">
                                <label>Kościelny: <?=$song->SongGradeChurchie ?? 'Nieoceniona'?> -></label>
                                <input name="nwGradeC-<?=$i+2?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                       value="<?=$song->SongGradeChurchie ?? 'Nieoceniona'?>" <?=$isReviewer ? "" : "disabled" ?>/>
                            </h4>
                            <h4 class="dataContainer--gradeContainer">
                                <label>Moja Ocena: <?=$song->SongGradeOwner ?? 'Nieoceniona'?> -></label>
                                <input name="nwGradeM-<?=$i+27?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                       value="<?=$song->SongGradeOwner ?? 'Nieoceniona'?>" <?=$isOwner ? "" : "disabled" ?>/>
                            </h4>
                            <h5 class="dataContainer--gradeContainer">
                                <label>Średnia:</label>
                                <input class="gradeInput" type="text" value="<?=$song->Average ?? "Nieoceniona"?>" disabled />
                            </h5>
                            <?php  //only 1 list means there is nowhere to move or copy the song to
                            if(count($lists) > 1 && $isReviewer): ?>
                                <h5 class="dataContainer--gradeContainer">
                                    <label>Przenieś do:</label>
                                    <select name="<?="nwPlistId-".$i+3?>" class="selectBox">
                                        <option value="0">Nie przenoś</option>
                                        <?php foreach($lists as $list):
                                            if($list->ListId !== $listId):?>
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
                                            if($list->ListId !== $listId):?>
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
                            <textarea placeholder="Komentarz do utworu..." class="commentBox" name="songComment-<?=$i+22?>" <?=$isOwner ? "" : "disabled" ?>><?=$song->SongComment?></textarea>
                        </div>
                    </div>
                    <label <?=$playlist->btnRehearsal ? '' : 'hidden'?>><input type="checkbox" name="<?="songRehearsal-".$i+4?>" class="buttonBox" <?=$song->SongRehearsal ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Do ponownego odsłuchu</label>
                    <label <?=$playlist->btnBelowFour ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelFour-".$i+17?>" class="buttonBox" <?=$song->SongBelFour ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 4</label>
                    <label <?=$playlist->btnBelowSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelow-".$i+13?>" class="buttonBox" <?=$song->SongBelow ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 7</label>
                    <label <?=$playlist->btnBelowHalfSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfSeven-".$i+23?>" class="buttonBox" <?=$song->SongBelHalfSeven ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 7.5</label>
                    <label <?=$playlist->btnBelowEight ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelEight-".$i+16?>" class="buttonBox" <?=$song->SongBelEight ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 8</label>
                    <label <?=$playlist->btnBelowHalfEight ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfEight-".$i+24?>" class="buttonBox" <?=$song->SongBelHalfEight ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 8.5</label>
                    <label <?=$playlist->btnBelowNine ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelNine-".$i+15?>" class="buttonBox" <?=$song->SongBelNine ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 9</label>
                    <label <?=$playlist->btnBelowHalfNine ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfNine-".$i+25?>" class="buttonBox" <?=$song->SongBelHalfNine ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 9.5</label>
                    <label <?=$playlist->btnBelowTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelTen-".$i+14?>" class="buttonBox" <?=$song->SongBelTen ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> < 10</label>
                    <label <?=$playlist->btnDistinction ? '' : 'hidden'?>><input type="checkbox" name="<?="songDistinction-".$i+5?>" class="buttonBox" <?=$song->SongDistinction ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Wyróżnienie</label>
                    <label <?=$playlist->btnDuoTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songDuoTen-".$i+18?>" class="buttonBox" <?=$song->SongDuoTen ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> "10"</label>
                    <label <?=$playlist->btnMemorial ? '' : 'hidden'?>><input type="checkbox" name="<?="songMemorial-".$i+6?>" class="buttonBox" <?=$song->SongMemorial ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> 10*</label>
                    <label <?=$playlist->btnUber ? '' : 'hidden'?>><input type="checkbox" name="<?="songUber-".$i+12?>" class="buttonBox" <?=$song->SongUber ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Uber</label>
                    <label <?=$playlist->btnTop ? '' : 'hidden'?>><input type="checkbox" name="<?="songTop-".$i+10?>" class="buttonBox" <?=$song->SongTop ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> X15</label>
                    <label <?=$playlist->btnXD ? '' : 'hidden'?>><input type="checkbox" name="<?="songXD-".$i+7?>" class="buttonBox" <?=$song->SongXD ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> XD</label>
                    <label <?=$playlist->btnDiscomfort ? '' : 'hidden'?>><input type="checkbox" name="<?="songDiscomfort-".$i+9?>" class="buttonBox" <?=$song->SongDiscomfort ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Strefa Dyskomfortu</label>
                    <label <?=$playlist->btnDepA ? '' : 'hidden'?>><input type="checkbox" name="<?="songDepA-".$i+26?>" class="buttonBox" <?=$song->SongDepA ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Dep. Abroad</label>
                    <label <?=$playlist->btnNotRap ? '' : 'hidden'?>><input type="checkbox" name="<?="songNotRap-".$i+8?>" class="buttonBox" <?=$song->SongNotRap ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> To nie rapsik</label>
                    <label <?=$playlist->btnNoGrade ? '' : 'hidden'?>><input type="checkbox" name="<?="songNoGrade-".$i+11?>" class="buttonBox" <?=$song->SongNoGrade ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> Nie oceniam</label>
                    <label <?=$playlist->btnVeto ? '' : 'hidden'?>><input type="checkbox" name="<?="songVeto-".$i+19?>" class="buttonBox" <?=$song->SongVeto ? "checked" : ""?> <?=$isOwner ? "" : "disabled" ?>> VETO</label>
                    <input type="hidden" name="songUpdated-<?=$i+21?>" value="0">
                </div>
            </div>
            <?php
            $i += 28;
        endforeach;
    endif; ?>
    <input type="hidden" name="playlistId" value="<?=$listId?>"/>
</form>
<div id="bottom"></div>
<script type="text/javascript" src="<?=base_url( 'scripts/playlist.js' )?>"></script>