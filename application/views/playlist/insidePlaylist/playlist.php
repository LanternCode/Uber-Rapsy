<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<header class="optionsHeader">
    <a class="optionsURL" href="<?=base_url()?>">Powrót do playlist</a>
    <a class="optionsURL" href="#bottom">Dół Listy</a>
    <a class="optionsURL" href="#topoftherap">Góra Listy</a>
    <?php if (count($songs) > 0 && $isPlaylistOwner): ?>
        <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
    <?php endif; ?>
    <select class="optionsURL redirectsBox" onchange="javascript:location.href = this.value;">
        <option value="">Pokaż oceny:</option>
        <option value="<?=base_url("playlist?playlistId=".$listId)?>">Wszystkie Oceny</option>
        <?php if ($rapparManagedPlaylist): ?>
            <option value="<?=base_url("tierlist?playlistId=".$listId."&filter=Adam")?>">Najlepsze: Adam</option>
            <option value="<?=base_url("tierlist?playlistId=".$listId."&filter=Churchie")?>">Najlepsze: Kościelny</option>
        <?php endif; ?>
        <option value="<?=base_url("tierlist?playlistId=".$listId."&filter=Average")?>">Najlepsze: Średnia (malejąco)</option>
        <option value="<?=base_url("playlist?playlistId=".$listId."&filter=Unrated")?>">Nieocenione</option>
        <?php foreach ($checkboxPropertiesDetails as $propDetails):
            if ($playlist->{$propDetails[1]}): ?>
                <option value="<?=base_url("playlist?playlistId=".$listId."&filter=Checkbox&prop=".$propDetails[0])?>"><?=$propDetails[2]?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
    <form class="optionsURL optionsRight" method="get" action="<?=base_url("playlist")?>">
        <label class="optionsSearchLabel">Szukaj nuty</label>
        <input type="hidden" name="playlistId" value="<?=$listId?>" />
        <input type="text" placeholder="Rajaner" name="searchQuery" />
        <input type="submit" value="Szukaj" />
    </form>
</header>
<br id="topoftherap"><br><br>
<div class="averagesBar">
    <div class="averagesBar--left">
        <h2 class="blackBar">Przeglądasz playlistę <?=$playlist->ListName?>!</h2>
        <h3 class="blackBar">Liczba nut: <?=count($songs)?></h3>
        <?php if ($rapparManagedPlaylist): ?>
            <h3 class="blackBar" title="Średnia ocen jest obliczana tylko, gdy obu recenzentów oceniło utwór. Utwory, na których zaznaczono przycisk '10' i wpisano tylko oceny 10, nie są brane pod uwagę.">Średnie ocen (?)</h3>
        <?php else: ?>
            <h3 class="blackBar" title="Utwory, na których zaznaczono przycisk '10' i wpisano tylko oceny 10, nie są brane pod uwagę.">Średnia ocen (?)</h3>
        <?php endif; ?>
        <h4 class="blackBar">Średnia Ocen Playlisty: <?=number_format($avgOverall, 2)?> (<?=$ratedOverall?>)</h4>
        <?php if ($rapparManagedPlaylist): ?>
            <h4 title="Średnia ocen na podstawie ocenionych utworów Kościelnego" class="blackBar">Średnia Ocen (Kościelny): <?=number_format($avgChurchie, 2)?> (<?=$ratedChurchie?>)</h4>
            <h4 title="Średnia ocen na podstawie ocenionych utworów Adama" class="blackBar">Średnia Ocen (Adam): <?=number_format($avgAdam, 2)?> (<?=$ratedAdam?>)</h4>
        <?php endif; ?>
    </div>
    <div class="averagesBar--right">
        <?php if ($isPlaylistOwner): ?>
            <img src="./styles/icons/bigger_cog.png" class="hamburger optionsRight settings_cog menuIcon">
        <?php endif; ?>
    </div>
</div>
<form id="songsForm" method="post" action="<?=base_url('updateSongRatings')?>">
    <?php if ($isPlaylistOwner): ?>
        <ul class="menu">
            <img src="./styles/icons/bigger_cog.png" class="hamburger closing_cog menuIcon"><br><br><br>
            <?php if (false): ?>
                <li class="optionsURL">Zintegrowana: <?=$playlist->ListIntegrated ? "<a target='_blank' href='https://www.youtube.com/playlist?list=$playlist->ListUrl'>Tak</a>" : "Nie"?></li><br>
            <?php endif; ?>
            <li class="optionsURL menuURL"><a class="blackBar" href="<?=base_url("downloadSongs?playlistId=".$listId)?>">Załaduj nowe nuty</a></li><br>
            <li class="optionsURL menuURL"><a class="blackBar" href="<?=base_url('playlist/details?playlistId='.$listId.'&src=plist')?>">Statystyki i Ustawienia</a></li>
        </ul>
	<?php endif; ?>
    <?php if (count($songs) > 0):
        $i = 0; ?>
		<?php foreach ($songs as $song): ?>
            <div class="videoContainerBox">
				<img src="<?=$song->SongThumbnailURL?>" alt="thumbnail" class="songThumbnailLeft">
				<div class="dataContainerBox">
                    <input type="hidden" name="playlistSongId-<?=$i?>" value="<?=$song->id?>"/>
                    <h3 class="songTitle"><a href="https://youtu.be/<?=$song->SongURL?>" target="_blank"><?=htmlspecialchars($song->SongTitle, ENT_QUOTES, 'UTF-8')?></a>
                        <?php if ($song->SongTemplateActive): ?>
                             (<a target='_blank' href="<?=base_url('song/reviewSong?songId='.$song->SongId)?>">+</a>)
                        <?php endif; ?>
                    </h3>
                    <div class="dataContainerBox--split">
                        <div class="dataContainerBox--split__left">
                            <?php if ($rapparManagedPlaylist): ?>
                                <h4 class="dataContainer--gradeContainer">
                                    <label>Adam: <?=$song->SongGradeAdam ?? 'Nieoceniona'?> -></label>
                                    <input name="newGradeAdam-<?=$i+1?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                           value="<?=$song->SongGradeAdam ?? 'Nieoceniona'?>" <?=$isPlaylistOwner ? "" : "disabled" ?>/>
                                </h4>
                                <h4 class="dataContainer--gradeContainer">
                                    <label>Kościelny: <?=$song->SongGradeChurchie ?? 'Nieoceniona'?> -></label>
                                    <input name="newGradeChurchie-<?=$i+2?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                           value="<?=$song->SongGradeChurchie ?? 'Nieoceniona'?>" <?=$isPlaylistOwner ? "" : "disabled" ?>/>
                                </h4>
                                <h5 class="dataContainer--gradeContainer">
                                    <label>Średnia:</label>
                                    <input class="gradeInput" type="text" value="<?=$song->Average ?? "Nieoceniona"?>" disabled />
                                </h5>
                            <?php else: ?>
                                <h4 class="dataContainer--gradeContainer">
                                    <label>Ocena: <?=$song->SongGradeOwner ?? 'Nieoceniona'?> -></label>
                                    <input name="myNewGrade-<?=$i+27?>" class="gradeInput" type="number" step="0.5" min="0" max="15"
                                           value="<?=$song->SongGradeOwner ?? 'Nieoceniona'?>" <?=$isPlaylistOwner ? "" : "disabled" ?>/>
                                </h4>
                            <?php endif; ?>
                            <?php  //only 1 list means there is nowhere to move or copy the song to
                            if (count($userOwnedPlaylists) > 1 && $isPlaylistOwner): ?>
                                <h5 class="dataContainer--gradeContainer">
                                    <label>Przenieś do:</label>
                                    <select name="<?="newPlaylistId-".$i+3?>" class="selectBox">
                                        <option value="0">Nie przenoś</option>
                                        <?php foreach ($userOwnedPlaylists as $list):
                                            //Do not show the current list in the options
                                            if ($list->ListId !== $listId):?>
                                                <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                            <?php endif;
                                        endforeach; ?>
                                    </select>
                                </h5>
                                <h5 class="dataContainer--gradeContainer">
                                    <label>Kopiuj do:</label>
                                    <select name="<?="copyPlaylistId-".$i+20?>" class="selectBox">
                                        <option value="0">Nie kopiuj</option>
                                        <?php foreach ($userOwnedPlaylists as $list):
                                            //Do not show the current list in the options
                                            if ($list->ListId !== $listId):?>
                                                <option value="<?=$list->ListId?>"><?=$list->ListName?></option>
                                            <?php endif;
                                        endforeach; ?>
                                    </select>
                                </h5>
                            <?php else: ?>
                                <select style="display:none;" name="<?="newPlaylistId-".$i+3?>">
                                    <option value="0">Nie przenoś</option>
                                </select>
                                <select style="display:none;" name="<?="copyPlaylistId-".$i+20?>">
                                    <option value="0">Nie kopiuj</option>
                                </select>
                            <?php endif;?>
                        </div>
                        <div class="dataContainerBox--split__right">
                            <textarea placeholder="Komentarz do utworu..." class="commentBox" name="songComment-<?=$i+22?>" <?=$isPlaylistOwner ? "" : "disabled" ?>><?=$song->SongComment?></textarea>
                        </div>
                    </div>
                    <label <?=$playlist->btnRehearsal ? '' : 'hidden'?>><input type="checkbox" name="<?="songRehearsal-".$i+4?>" class="buttonBox" <?=$song->SongRehearsal ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Do ponownego odsłuchu</label>
                    <label <?=$playlist->btnBelowFour ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelFour-".$i+17?>" class="buttonBox" <?=$song->SongBelFour ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 4</label>
                    <label <?=$playlist->btnBelowSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelow-".$i+13?>" class="buttonBox" <?=$song->SongBelow ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 7</label>
                    <label <?=$playlist->btnBelowHalfSeven ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfSeven-".$i+23?>" class="buttonBox" <?=$song->SongBelHalfSeven ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 7.5</label>
                    <label <?=$playlist->btnBelowEight ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelEight-".$i+16?>" class="buttonBox" <?=$song->SongBelEight ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 8</label>
                    <label <?=$playlist->btnBelowHalfEight ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfEight-".$i+24?>" class="buttonBox" <?=$song->SongBelHalfEight ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 8.5</label>
                    <label <?=$playlist->btnBelowNine ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelNine-".$i+15?>" class="buttonBox" <?=$song->SongBelNine ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 9</label>
                    <label <?=$playlist->btnBelowHalfNine ? '' : 'hidden'?>><input type="checkbox" name="<?="SongBelHalfNine-".$i+25?>" class="buttonBox" <?=$song->SongBelHalfNine ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 9.5</label>
                    <label <?=$playlist->btnBelowTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songBelTen-".$i+14?>" class="buttonBox" <?=$song->SongBelTen ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> < 10</label>
                    <label <?=$playlist->btnDistinction ? '' : 'hidden'?>><input type="checkbox" name="<?="songDistinction-".$i+5?>" class="buttonBox" <?=$song->SongDistinction ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Wyróżnienie</label>
                    <label <?=$playlist->btnDuoTen ? '' : 'hidden'?>><input type="checkbox" name="<?="songDuoTen-".$i+18?>" class="buttonBox" <?=$song->SongDuoTen ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> "10"</label>
                    <label <?=$playlist->btnMemorial ? '' : 'hidden'?>><input type="checkbox" name="<?="songMemorial-".$i+6?>" class="buttonBox" <?=$song->SongMemorial ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> 10*</label>
                    <label <?=$playlist->btnUber ? '' : 'hidden'?>><input type="checkbox" name="<?="songUber-".$i+12?>" class="buttonBox" <?=$song->SongUber ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Uber</label>
                    <label <?=$playlist->btnTop ? '' : 'hidden'?>><input type="checkbox" name="<?="songTop-".$i+10?>" class="buttonBox" <?=$song->SongTop ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> X15</label>
                    <label <?=$playlist->btnXD ? '' : 'hidden'?>><input type="checkbox" name="<?="songXD-".$i+7?>" class="buttonBox" <?=$song->SongXD ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> XD</label>
                    <label <?=$playlist->btnDiscomfort ? '' : 'hidden'?>><input type="checkbox" name="<?="songDiscomfort-".$i+9?>" class="buttonBox" <?=$song->SongDiscomfort ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Strefa Dyskomfortu</label>
                    <label <?=$playlist->btnDepA ? '' : 'hidden'?>><input type="checkbox" name="<?="songDepA-".$i+26?>" class="buttonBox" <?=$song->SongDepA ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Dep. Abroad</label>
                    <label <?=$playlist->btnNotRap ? '' : 'hidden'?>><input type="checkbox" name="<?="songNotRap-".$i+8?>" class="buttonBox" <?=$song->SongNotRap ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> To nie rapsik</label>
                    <label <?=$playlist->btnNoGrade ? '' : 'hidden'?>><input type="checkbox" name="<?="songNoGrade-".$i+11?>" class="buttonBox" <?=$song->SongNoGrade ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> Nie oceniam</label>
                    <label <?=$playlist->btnVeto ? '' : 'hidden'?>><input type="checkbox" name="<?="songVeto-".$i+19?>" class="buttonBox" <?=$song->SongVeto ? "checked" : ""?> <?=$isPlaylistOwner ? "" : "disabled" ?>> VETO</label>
                    <input type="hidden" name="songUpdated-<?=$i+21?>" value="0">
                </div>
			</div>
		<?php
        $i += 28;
        endforeach;?>
	<?php elseif (strlen($searchQuery) > 0 && count($songs) == 0): ?>
        <h3>Nie znaleziono żadnych wyników wyszukiwania!</h3>
	<?php else: ?>
		<h3>Ta playlista jest pusta mordo, nowy sezon już wkrótce!</h3>
	<?php endif; ?>
    <input type="hidden" name="playlistId" value="<?=$listId?>"/>
</form>
<span id="bottom"></span>
<script type="text/javascript" src="<?=base_url('scripts/playlist.js')?>"></script>
<script type="text/javascript" src="<?=base_url('scripts/nav.js')?>"></script>