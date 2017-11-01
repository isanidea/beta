<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    中奖明细列表
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_lottery_bingo_list extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_lottery_bingo_list';

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