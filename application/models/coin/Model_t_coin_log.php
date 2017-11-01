<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    币种流水表
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_coin_log extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_coin_log';

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