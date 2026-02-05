<?php
session_start();
session_destroy();
// Go UP one level out of 'auth' to find index.html
header("Location: ../index.html");
exit();
?>