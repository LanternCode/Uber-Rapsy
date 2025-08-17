<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<br><br>
Wygenerowano nowy token odświeżający:
<br><br>
<h2><?=$accessToken['refresh_token']?></h2>
<br><br>
1. Utwórz plik o nazwie refresh_token.txt<br>
2. W pierwszej linijce pliku umieść powyższy token i zapisz<br>
3. Wstaw plik do ścieżki RAPPAR/application/api<br>
4. Ponownie wykonaj akcję która zaprowadziła Cię do tej strony<br><br>