<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<form id="toplist" method="post" action="<?=base_url("songsToplist/saveGrades")?>">
    <?php foreach($songs as $song): ?>
        <input type="hidden" name="songId" value="<?=$song->SongId?>">
        <div class="song-container songBackground">
            <div class="song-header songBackground">
                <div class="songBackground">
                    <h2 class="song-title songBackground"><?=$song->SongTitle?></h2>
                    <p class="song-authors songBackground">Autorzy Utworu (Rok Wydania)</p>
                </div>
                <div class="song-awards songBackground">
                    <p class="songBackground">NUTA ROKU 2022</p>
                    <p class="songBackground">TOP 100 UBER RAPSY</p>
                    <p class="songBackground">WYRÓŻNIENI ARTYŚCI</p>
                </div>
            </div>
            <div class="song-content songBackground">
                <img src="<?=$song->SongThumbnailURL?>" alt="Song Thumbnail" class="song-thumbnail" />
                <div class="song-grades">
                    <p>Moja Ocena: <?=$ratings->songGrade ?? 0?></p>
                    <p>Ocena Adama: <?=$song->SongGradeAdam?></p>
                    <p>Ocena Kościelnego: <?=$song->SongGradeChurchie?></p>
                    <p>Średnia Społeczności: <?=$averages?></p>
                </div>
            </div>
            <div class="song-slider songBackground">
                <input type="range" min="1" max="10" step="0.5" value="<?=$ratings->songGrade ?? 0?>" name="songGrade">
                <div class="slider-labels">
                    <!-- n from 0..9, so label=1 => n=0, label=10 => n=9 -->
                    <span style="left: calc((0/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    1
                  </span>
                    <span style="left: calc((1/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    2
                  </span>
                    <span style="left: calc((2/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    3
                  </span>
                    <span style="left: calc((3/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    4
                  </span>
                    <span style="left: calc((4/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    5
                  </span>
                    <span style="left: calc((5/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    6
                  </span>
                    <span style="left: calc((6/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    7
                  </span>
                    <span style="left: calc((7/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    8
                  </span>
                    <span style="left: calc((8/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    9
                  </span>
                    <span style="left: calc((9/9) * (100% - var(--thumb-size)) + var(--thumb-center) - var(--offset-adjust));">
                    10
                  </span>
                </div>
            </div>
        </div>
    <?php endforeach;?>
</form>
