<?php defined('BASEPATH') OR exit('No direct script access allowed');
$query_builder = TRUE;
$mysqlDriver = function_exists('mysqli_connect')?'mysqli':'mysql';
$db['trade_user'] = array(
    'dsn'	=> '',
    'hostname' => '127.0.0.1',
    'username' => 'trade',
    'password' => '41e23b923cd5372cd75499c86883000e',
    'database' => 'trade_user',
    'dbdriver' => $mysqlDriver,
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);