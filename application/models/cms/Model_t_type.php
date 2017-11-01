<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    类型详情
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_type extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_type';

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