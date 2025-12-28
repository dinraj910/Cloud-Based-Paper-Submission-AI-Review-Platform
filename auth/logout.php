<?php
session_start();
session_unset();
session_destroy();
header('Location: /research-portal/index.php');
exit;