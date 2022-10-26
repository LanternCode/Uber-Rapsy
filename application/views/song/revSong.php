<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?=isset($successMessage) && $successMessage == 1 ? "Recenzja została zapisana!<br><br>" : ""?>
<?= $errorMessage ?? "" ?>
<?php if($existingReview != false){
    $rD = $existingReview['reviewDate'];
    $rT = $existingReview['reviewText'];
    $rM = $existingReview['reviewMusic'];
    $rI = $existingReview['reviewImpact'];
    $rH = $existingReview['reviewRh'];
    $rC = $existingReview['reviewComp'];
    $rR = $existingReview['reviewReflection'];
    $rU = $existingReview['reviewUber'];
    $rP = $existingReview['reviewPartner'];
    $rV = $existingReview['reviewRev'];

    if($errorMessage == "") {
        $total = $rT + $rM + $rI + $rH + $rC + $rR + $rU + $rP;
        $percentage = floor($total / 90 * 100);
    }
}
?>
<label class="reviewBox">Tytuł: <?=$song->SongTitle?></label>
<label class="reviewBox">Autor: <?='?'//$song->SongAuthor?></label><br>
<form method="POST" action="<?=base_url('song/rev?id='.$song->SongId)?>">
    <article class="reviewBox">
        <label>Data Oceny:</label>
        <input id="createdAt" type="text" name="reviewDate" value="<?=$rD ?? ''?>">
        <button onclick="createdAt.value = new Date().toISOString().slice(0, 10); return false;">Dzisiaj</button>
    </article>
    <article class="reviewBox">
        <label>Tekst:</label>
        <input class="gradeInputReview" type="text" name="reviewText" value="<?=$rT ?? ''?>">/20<br>
    </article>
    <article class="reviewBox">
        <label>Muzyka:</label>
        <input class="gradeInputReview" type="text" name="reviewMusic" value="<?=$rM ?? ''?>">/20<br>
    </article>
    <article class="reviewBox">
        <label>Popularność:</label>
        <input class="gradeInputReview" type="text" name="reviewImpact" value="<?=$rI ?? ''?>">/5<br>
    </article>
    <article class="reviewBox">
        <label>Słuchalność:</label>
        <input class="gradeInputReview" type="text" name="reviewRh" value="<?=$rH ?? ''?>">/5<br>
    </article>
    <article class="reviewBox">
        <label>Kompozycja:</label>
        <input class="gradeInputReview" type="text" name="reviewComp" value="<?=$rC ?? ''?>">/10<br>
    </article>
    <article class="reviewBox">
        <label>Refleksyjność:</label>
        <input class="gradeInputReview" type="text" name="reviewReflection" value="<?=$rR ?? ''?>">/10<br>
    </article>
    <article class="reviewBox">
        <label>Ocena Uber:</label>
        <input class="gradeInputReview" type="text" name="reviewUber" value="<?=$rU ?? ''?>">/10<br>
    </article>
    <article class="reviewBox">
        <label>Ocena Partnera:</label>
        <input class="gradeInputReview" type="text" name="reviewPartner" value="<?=$rP ?? ''?>">/10<br>
    </article>
    <article class="reviewBox">
        <label>Razem:</label>
        <input class="gradeInputReview" type="text" name="reviewTotal" value="<?=$total ?? ''?>" disabled>/90 (<?=$percentage ?? ''?>%)<br>
    </article>
    <article class="reviewBox">
        <label>Recenzja:</label><br>
        <textarea name="reviewRev" rows="10" cols="100"><?=$rV ?? ''?></textarea><br>
    </article>
    <input type="submit" value="Zapisz Recenzję!">
</form>