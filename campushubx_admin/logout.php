<?php
include("includes/auth.php");
session_start();
session_destroy();
header("Location: login.php");
?>