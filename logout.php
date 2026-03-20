<?php
session_start();
session_unset(); // clear session variables
session_destroy(); // destroy the session
header('Location: ./'); // or wherever your login page is
exit();