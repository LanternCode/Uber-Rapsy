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
    <form class="optionsURL" method="get" action="<?=base_url("playlist")?>">
        <label>Szukaj nuty</label>
        <input type="hidden" name="ListId" value="<?=$ListId?>" />
        <input type="text" placeholder="Rajaner" name="Search" />
        <input type="submit" value="Szukaj" />
    </form>
    <?php if(isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer"): ?>
        <input type="submit" class="optionsURL" value="Zapisz oceny" form="songsForm"/>
        <a class="optionsURL" href="<?=base_url("downloadSongs?ListId=" . $ListId)?>">Załaduj nowe nuty</a>
    <?php endif; ?>
</header>
<div class="optionsHeaderSpace"></div>
<form id="songsForm" method="post" action="<?=base_url('updateGrades')?>">
	<?php if(count($songs) > 0): ?>
		<?php foreach($songs as $song):?>
			<div class="videoContainer">
				<img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailLeft" />
				<div class="dataContainer">
					<h3 class="songTitle"><a href="https://<?=$song->SongURL?>" target="_blank"><?=$song->SongTitle?></a></h3>
                    <?php //For reviewers show scores, buttons to edit them and the select list to move the song
                    if(isset($_SESSION['userRole']) && $_SESSION['userRole'] == "reviewer"): ?>
						<h4 class="dataContainer--gradeContainer">
							Adam:
							<button type="button" class="btnGrade" onclick="lowerGrade('A', <?=$song->SongId?>)">-</button>
								<input name="<?='OA-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
							<button type="button" class="btnGrade" onclick="raiseGrade('A', <?=$song->SongId?>)">+</button>
							<span class="newScore" id="<?='NGBA-'.$song->SongId?>">
								->
								<input class="gradeInputNew" type="text"
									id="<?='A-'.$song->SongId?>" name="<?='A-'.$song->SongId?>"
									value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
							</span>
						</h4>
						<h4 class="dataContainer--gradeContainer">
							Kościelny:
							<button type="button" class="btnGrade" onclick="lowerGrade('K', <?=$song->SongId?>)">-</button>
								<input name="<?='OK-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeChurchie > 0 ? $song->SongGradeChurchie : 'Nieoceniona'?>" />
							<button type="button" class="btnGrade" onclick="raiseGrade('K', <?=$song->SongId?>)">+</button>
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
						<?php  //only 1 list means there is nowhere to move the song to
						if(count($lists) > 1): ?>
							<h5 class="dataContainer--gradeContainer">Przenieś do:
								<select id="" class="" name="<?="playlistId-".$song->SongId?>">
									<option value="0" default>Nie przenoś</option>
									<?php foreach($lists as $list):
										//Do not show the same list in the options
										if($list->ListId !== $ListId):?>
											<option value="<?=$list->ListId?>"><?=$list->ListName?></option>
										<?php endif;
									endforeach; ?>
								</select>
							</h5>
						<?php else: ?>
                            <select style="display:none;" name="<?="playlistId-".$song->SongId?>">
                                <option value="0">Nie przenoś</option>
                            </select>
                        <?php endif;
                    //For guest users only show the scores of the song
                    else: ?>
                        <h4 class="dataContainer--gradeContainer">
                            Adam:
                            <input name="<?='OA-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeAdam > 0 ? $song->SongGradeAdam : 'Nieoceniona'?>" />
                        </h4>
                        <h4 class="dataContainer--gradeContainer">
                            Kościelny:
                            <input name="<?='OK-'.$song->SongId?>" class="gradeInput" type="text" value="<?=$song->SongGradeChurchie > 0 ? $song->SongGradeChurchie : 'Nieoceniona'?>" />
                        </h4>
                        <h5 class="dataContainer--gradeContainer">Średnia:
                            <input type="text" value="<?=$song->SongGradeAdam > 0 && $song->SongGradeChurchie > 0 ? ($song->SongGradeAdam + $song->SongGradeChurchie) / 2 : 'Nieoceniona'?>" disabled />
                        </h5>
                    <?php endif; ?>
				</div>
				<img src="<?=$song->SongThumbnailURL?>" width="250" height="140" alt="thumbnail" class="songThumbnailRight" />
			</div>
		<?php endforeach;?>
	<?php else: ?>
		<h3>Ta playlista jest pusta mordo, nowy sezon już wkrótce!</h3>
	<?php endif; ?>
    <input type="hidden" name="playlistId" value="<?=$ListId?>"/>
</form>
<span id="bottom"></span>
<script type="text/javascript" src="<?=base_url( 'scripts/playlist.js' )?>"></script>
