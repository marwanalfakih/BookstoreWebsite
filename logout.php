<?php
$past = time() - 3600;
foreach ($_COOKIE as $key => $value) {
    setcookie($key, $value, $past);
}
echo '<meta http-equiv="Refresh" content=0;url="index.php">';
