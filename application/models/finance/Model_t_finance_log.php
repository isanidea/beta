<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    用户资金流水明细表  提币  充币
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_finance_log extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_finance_log';

    function __construct()
    {
        parent::__construct();
        $this->m_errcode = array(
            'M_FINANCE_PARAM_ERR'                  => 0x90000001,
            'M_FINANCE_CAN_USE_MONEY_NOT_ENOUCH'   => 0x90000002,
            'M_FINANCE_FREEZE_MONEY_NOT_ENOUCH'    => 0x90000003,
            'M_FINANCE_TOTAL_MONEY_NOT_ENOUCH'     => 0x90000004,
            'M_FINANCE_REDUCE_FREEZE_NOT_ENOUCH'   => 0x90000005,
            'M_FINANCE_GET_FINANCE_INFO_ERR'       => 0x90000006,     // 获取用户财务信息失败
        );
    }

    function get_tablename($uin=NULL)
    {
        $tablename = $this->_tableName;
        return $tablename;
    }

    // 添加财务信息
    function add_finance_log($conn,$uin,$data)
    {
        if(!isset($uin)){
            cilog('error',"用户uin不存在!");
            return $this->m_errcode['M_FINANCE_PARAM_ERR'];
        }

        $tablename = $this->get_tablename($uin);
        $finance_log = array(
            'f_uin'             => $uin,
            'f_type'            => isset($data['f_type']) ? $data['f_type'] : 0,
            'f_coin_id'         => isset($data['f_coin_id']) ? $data['f_coin_id'] : 0,
            'f_coin_addr'       => isset($data['f_coin_addr']) ? $data['f_coin_addr'] : '',
            'f_coin_key'        => isset($data['f_coin_key']) ? $data['f_coin_key'] : '',
            'f_vol'             => isset($data['f_vol']) ? $data['f_vol'] : 0,
            'f_state'           => isset($data['f_state']) ? $data['f_state'] : 0,
            'f_atm_rate_vol'    => isset($data['f_atm_rate_vol']) ? $data['f_atm_rate_vol'] : 0,
            'f_real_revice_vol' => isset($data['f_real_revice_vol']) ? $data['f_real_revice_vol'] : 0,
            'f_modify_time'     => timestamp2time(),
            'f_create_time'     => timestamp2time(),
        );
        $flag = $this->save($conn,$tablename,$finance_log);
        if($flag === FALSE){
            cilog('error',"添加用户财务流水信息失败! [uin:{$uin}] [coin_id:{$data['f_coin_id']}] [amount:{$data['f_vol']}] [errcode:{$flag}]");
        }else{
            cilog('debug',"添加用户财务流水信息成功! [uin:{$uin}] [coin_id:{$data['f_coin_id']}] [amount:{$data['f_vol']}]");
        }
        return $flag;
    }

    // 获取用户财务流水
    function get_finance_log_by_id($conn,$id)
    {
        $tablename = $this->get_tablename();
        $where = array('f_id' => $id);
        $finance_log = $this->find_by_attributes(
            $conn,
            $select=NULL,
            $tablename,
            $where,
            'f_id desc'
        );
        return $finance_log;
    }

    // 获取财务流水信息,通过uin type查询
    function get_finance_log_list_by_uin($conn,$uin,$page=NULL,$num=NULL,$aQuery=array(),$sort=NULL)
    {
        if(!isset($uin)){
            cilog('error',"uin不存在,通过uin获取财务流水列表失败! [uin:{$uin}]");
            return $this->m_errcode['M_FINANCE_PARAM_ERR'];
        }

        $rsp = array(
            'total' => 0,
            'rows' => array(),
        );
        $tablename = $this->get_tablename($uin);
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
            cilog('error',"通过uin获取财务流水列表失败! [uin:{$uin}] [tablename:{$tablename}] [num:{$count}]");
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
            cilog('debug',"通过uin获取财务流水列表成功! [uin:{$uin}] [tablename:{$tablename}] [num:{$count}]");
        }
        return $rsp;
    }

    // 修改流水单信息
    function update_finance_log_by_uin($conn,$uin,$log_id,$attributes)
    {
        if(!isset($uin)){
            cilog('error',"用户uin不存在!");
            return $this->m_errcode['M_FINANCE_PARAM_ERR'];
        }

        $tablename = $this->get_tablename($uin);
        $attributes['f_modify_time'] = timestamp2time();
        $where = array(
            'f_uin' => $uin,
            'f_id' => $log_id
        );
        $flag = $this->update_all($conn,$tablename,$attributes,$where);
        if($flag === 0){
            cilog('debug',"修改用户财务流水信息成功! [uin:{$uin}] [log_id:{$log_id}]");
        }else{
            cilog('error',"修改用户财务流水信息失败! [uin:{$uin}] [log_id:{$log_id}] [errcode:{$flag}]");
        }
        return $flag;
    }
}