<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    交易单信息(小单)
 *
 */

class Model_t_deal extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_deal';

    function __construct()
    {
        parent::__construct();
    }

    function get_tablename($uin=NULL)
    {
        $tablename = isset($uin) ? $uin : $this->_tableName;
        return $tablename;
    }

    function get_distinct_price($conn,$state,$type,$coin_id,$sort,$num)
    {
        $sql = "select distinct f_money from t_deal where f_state={$state} and f_type={$type} and f_coin_id={$coin_id} order by f_create_time {$sort} limit {$num}";
        return $this->query_sql($conn,$sql);
    }
}