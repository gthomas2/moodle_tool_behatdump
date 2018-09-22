<?php


define('AJAX_SCRIPT', true);

require_once(__DIR__.'/../../../config.php');

header('Content-Type: application/json');

use tool_behatdump\controller;

controller::instance()->invoke();