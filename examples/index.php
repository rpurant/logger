<?php

require_once '../vendor/autoload.php';

use Spier\Logger\Logger;

$logger = new Logger('C:\\xampp\\htdocs\\Logger\\logs\\');

$logger->logAppError('Test error logged');
