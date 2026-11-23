<?php
declare(strict_types=1);

/* Gli endpoint non espongono una pagina iniziale: rimandano all'accesso. */
header('Location: /login.php');
exit;
