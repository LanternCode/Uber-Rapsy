<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<h1>Zapisywanie ocen</h1>
<br><br>
<?php if ($searchQuery): ?>
    <a href="<?=base_url('search?SearchQuery='.$searchQuery)?>">Powrót do wyników wyszukiwania</a>
<?php elseif ($saveSource == "tierlist"): ?>
    <a href="<?=base_url('tierlist?playlistId='.$playlistId.'&filter='.$filter)?>">Powrót do tierlisty</a>
<?php else: ?>
    <a href="<?=base_url('playlist?playlistId='.$playlistId)?>">Powrót do playlisty</a>
<?php endif; ?>
<br><br><br>
<h2>Wynik Zapisu</h2>
<h3>Na playliście odnaleziono <?=
    ($c = $processedSongsCount - 1).' '.(
        // singular when last digit = 1 *and* not 11
        ($c % 10 === 1 && $c % 100 !== 11)
            ? 'utwór'
            // paucal when last digit 2–4 *and* not 12–14
            : (
                ($c % 10 >= 2 && $c % 10 <= 4 && ($c % 100 < 12 || $c % 100 > 14))
                    ? 'utwory'
                    // everything else
                    : 'utworów'
            )
    )
    ?>, z czego zaktualizowano <?=$processedAndUpdatedSongsCount?>.</h3>
<?php if (!empty($displayErrorMessage)): ?>
    <br><br>
    <h3>Wystąpił błąd w zapisywaniu ocen :/</h3>
    <h4><?=$displayErrorMessage?></h4>
    <h4>Jeśli po wykonaniu instrukcji ponowne podjęcie tej samej akcji nie zadziała, niezwłocznie skontaktuj się z administracją.</h4>
    <br><br>
<?php endif; ?>
<?=$resultMessage?>
<br><br>