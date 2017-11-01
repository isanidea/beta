<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    admin表
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_admin extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_admin';

    function __construct()
    {
        parent::__construct();
    }

    function get_tablename($uin=NULL)
    {
        $tablename = isset($uin) ? $uin : $this->_tableName;
        return $tablename;
    }
}