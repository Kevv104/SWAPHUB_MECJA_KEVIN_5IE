<?php

 use Firebase\JWT\JWT;
 use Firebase\JWT\Key;

 define('JWT_SECRET','c970bcc035afa6732f822ceaa9240d3c6167aa7b2fdfb671ccb1829cbe3f5a6e0ebf1b31892bcab6173171deb7253730f5ce4045e620dd9b1384f55194c');
 define('JWT_ALGO', 'HS256');
 define('JWT_TTL',600); //il token dura 10 min


?>