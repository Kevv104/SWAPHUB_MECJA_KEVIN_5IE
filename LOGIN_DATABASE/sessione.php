<?php
// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 600);

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(600);

session_start(); // ready to go!


?>