<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    委托单信息(大单)
 *
 */

class Model_t_bdeal extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_bdeal';

    function __construct()
    {
        parent::__construct();
        $this->m_errcode = array(
            'M_BDEAL_PARAM_ERR'      => 0x90010001,
        );
    }

    function get_tablename($uin=NULL)
    {
        $tablename = $this->_tableName;
        return $tablename;
    }

    function get_distinct_price($conn,$state,$type,$coin_id,$sort,$num)
    {
        $sql = "select distinct f_price from t_bdeal where f_state={$state} and f_type={$type} and f_coin_id={$coin_id} order by f_price {$sort} limit {$num}";
        return $this->query_sql($conn,$sql);
    }

    function create_bdeal($conn,$userinfo,$coininfo,$bdealid,$price,$vol,$type)
    {
        $this->state = array(
            'DEAL_DURING' => 1,
            'DEAL_DONE' => 2,
            'DEAL_CANCEL' => 3,
        );

        $tablename = $this->get_tablename();

        $bdeal_info = array(
            'f_bdeal_id' => $bdealid,
            'f_uin' => $userinfo['f_uin'],
            'f_type' => $type,
            'f_coin_id' => $coininfo['f_coin_id'],
            'f_coin_name' => $coininfo['f_abbreviation'],
            'f_total_vol' => $vol,
            'f_price' => $price,
            'f_total_money' => $price * $vol,
            'f_pre_deal_vol' => $vol,
            'f_state' => $this->state['DEAL_DURING'],
            'f_export_id' => todealid($bdealid,$userinfo['f_uin']),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $this->save($conn,$tablename,$bdeal_info);
    }

    function add_bdeal($conn,$uin,$coin_id,$data)
    {
        if((!isset($uin)) OR (!isset($coin_id))){
            cilog('error',"用户uin或者coin_id不存在!");
            return $this->m_errcode['M_BDEAL_PARAM_ERR'];
        }

        $tablename = $this->get_tablename($uin);
        $finance_info = array(
            'f_bdeal_id'           => isset($data['f_bdeal_id']) ? $data['f_bdeal_id'] : 0,
            'f_export_id'          => isset($data['f_export_id']) ? $data['f_export_id'] : '',
            'f_uin'                => $uin,
            'f_type'               => isset($data['f_type']) ? $data['f_type'] : 0,
            'f_coin_id'            => $coin_id,
            'f_coin_name'          => isset($data['f_coin_name']) ? $data['f_coin_name'] : '',
            'f_total_vol'          => isset($data['f_total_vol']) ? $data['f_total_vol'] : 0,
            'f_total_money'        => isset($data['f_total_money']) ? $data['f_total_money'] : 0,
            'f_post_deal_vol'      => isset($data['f_post_deal_vol']) ? $data['f_post_deal_vol'] : 0,
            'f_post_deal_money'    => isset($data['f_post_deal_money']) ? $data['f_post_deal_money'] : 0,
            'f_pre_deal_vol'       => isset($data['f_pre_deal_vol']) ? $data['f_pre_deal_vol'] : 0,
            'f_pre_deal_money'     => isset($data['f_pre_deal_money']) ? $data['f_pre_deal_money'] : 0,
            'f_state'              => isset($data['f_state']) ? $data['f_state'] : 0,
            'f_price'              => isset($data['f_price']) ? $data['f_price'] : 0,
            'f_post_time'       => get_microtime(),
            'f_create_time'        => timestamp2time(),
            'f_modify_time'        => timestamp2time(),
        );
        $flag = $this->save($conn,$tablename,$finance_info);
        if($flag === FALSE){
            cilog('error',"添加大单信息失败! [uin:{$uin}] [coin_id:{$coin_id}] [errcode:{$flag}]");
        }else{
            cilog('debug',"添加大单成功! [uin:{$uin}] [coin_id:{$coin_id}]");
        }
        return $flag;
    }

    function get_bdeal_info_by_export_id($conn,$export_id)
    {
        $tablename = $this->get_tablename();
        $where = array(
            'f_export_id' => $export_id
        );
        $data = $this->find_by_attributes(
            $conn,
            $select = NULL,
            $tablename,
            $where,
            $sort = NULL
        );
        return $data;
    }

    function get_bdeal_list_by_uin($conn,$uin,$page=NULL,$num=NULL,$aQuery=array(),$sort=NULL)
    {
        if(!isset($uin)){
            cilog('error',"uin不存在,通过uin获取财务信息列表失败! [uin:{$uin}]");
            return $this->m_errcode['M_BDEAL_PARAM_ERR'];
        }

        $rsp = array(
            'total' => 0,
            'rows' => array(),
        );
        $tablename = $this->get_tablename();
        $where = array('f_uin' => $uin);

        if($aQuery !== array()){
            foreach($aQuery as $key => $value){
                $where[$key] = $value;
            }
        }

        if($sort !== NULL){
            $sort = "f_create_time desc";
        }

        $page = isset($page) ? $page : 1;
        $num = isset($num) ? $num : 10;

        $count = $this->count($conn,$tablename,$where);

        if((int)$count === 0) {
            cilog('error',"通过uin获取大单列表失败! [uin:{$uin}] [tablename:{$tablename}] [num:{$count}]");
        }else{
            $rsp['total'] = $count;
            $rsp['rows'] = $this->find_all(
                $conn,
                $select=NULL,
                $tablename,
                $where,
                $limit = $num,
                $page = $page,
                $sort
            );
            cilog('debug',"通过uin获取大单列表成功! [uin:{$uin}] [tablename:{$tablename}] [num:{$count}]");
        }
        return $rsp;
    }

    function update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes)
    {
        if(!isset($uin)){
            cilog('error',"用户uin不存在!");
            return $this->m_errcode['M_BDEAL_PARAM_ERR'];
        }

        $tablename = $this->get_tablename($uin);
        $attributes['f_modify_time'] = timestamp2time();
        $where = array(
            'f_bdeal_id' => $bdealid
        );
        $flag = $this->update_all($conn,$tablename,$attributes,$where);
        if($flag === 0){
            cilog('debug',"修改用户大单信息成功! [uin:{$uin}] [bdealid:{$bdealid}]");
        }else{
            cilog('error',"修改用户大单信息失败! [uin:{$uin}] [bdealid:{$bdealid}] [errcode:{$flag}]");
        }
        return $flag;
    }

    function get_bdeal_list_by_coin_id($conn,$coin_id,$page=NULL,$num=NULL,$aQuery=array(),$sort=NULL)
    {
        if(!isset($coin_id)){
            cilog('error',"coin_id不存在,通过coinid获取大单列表失败! [coin_id:{$coin_id}]");
            return $this->m_errcode['M_BDEAL_PARAM_ERR'];
        }

        $rsp = array(
            'total' => 0,
            'rows' => array(),
        );
        $tablename = $this->get_tablename($coin_id);
        $where = array('f_coin_id' => $coin_id);

        if($aQuery !== array()){
            foreach($aQuery as $key => $value){
                $where[$key] = $value;
            }
        }

        if($sort === NULL){
            $sort = "f_create_time desc";
        }

        $page = isset($page) ? $page : 1;
        $num = isset($num) ? $num : 10;

        $count = $this->count($conn,$tablename,$where);

        if((int)$count === 0) {
            cilog('error',"通过coin_id获取大单列表失败! [coin_id:{$coin_id}] [tablename:{$tablename}] [num:{$count}]");
        }else{
            $rsp['total'] = $count;
            $rsp['rows'] = $this->find_all(
                $conn,
                $select=NULL,
                $tablename,
                $where,
                $limit = $num,
                $page = $page,
                $sort
            );
            cilog('debug',"通过uin获取大单列表成功! [coin_id:{$coin_id}] [tablename:{$tablename}] [num:{$count}]");
        }
        return $rsp;
    }

    function get_bdeal_info_by_bdealid($conn,$bdealid,$aQuery=array())
    {
        $where = array(
            'f_bdeal_id' => $bdealid
        );

        if($aQuery !== array()){
            foreach($aQuery as $key => $value){
                $where[$key] = $value;
            }
        }

        $tablename = $this->get_tablename();

        $data = $this->find_by_attributes(
            $conn,
            $select = NULL,
            $tablename,
            $where,
            $sort = NULL
        );
        return $data;
    }
}