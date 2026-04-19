<?php
// server should keep session data for 24 hours
ini_set('session.gc_maxlifetime', 86400);

// each client should remember their session id for 24 hours
session_set_cookie_params(86400);

session_start(); // ready to go!


?>