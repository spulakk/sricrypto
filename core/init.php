<?php

ob_start();
session_start();

require_once __DIR__ . './general.php';
require_once __DIR__ . './dial/CoinStatDial.php';
require_once __DIR__ . './database/Table.php';
require_once __DIR__ . './manager/ApiManager.php';
require_once __DIR__ . './manager/CoinManager.php';
require_once __DIR__ . './manager/CacheManager.php';