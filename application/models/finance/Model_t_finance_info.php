<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    用户资金明细表
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_finance_info extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_finance_info';

    /**
     * Model_t_finance_info constructor.
     */
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

        $this->finance_redis_key = array(
            'TIMEOUT' => 3600,
            'FINANCE_INFO' => "finance_info_",    // 币种详情  finance_info_uin
        );
    }

    // 获取表名
    function get_tablename($uin=NULL)
    {
        $tablename = $this->_tableName;
        return $tablename;
    }

    // 通过uin coin_id 获取用户财务信息
    function get_finance_info($conn,$uin,$coin_id)
    {
        $key = $this->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
        $str_finance_info = $this->cache->redis->get($key);
        if (!$str_finance_info) {
            $select = NULL;
            $tablename = $this->get_tablename();
            $where = array(
                'f_coin_id' => $coin_id,
                'f_uin' => $uin,
            );
            $arr_finance_info = $this->find_by_attributes($conn, $select, $tablename, $where, NULL);
            if (!$arr_finance_info) {
                cilog('error', "获取用户财务信息失败! [uin:{$uin}]");
                $this->db_err_exit($this->m_errcode['M_FINANCE_GET_FINANCE_INFO_ERR']);
            }
            $str_finance_info = serialize($arr_finance_info);
            $this->cache->redis->save($key, $str_finance_info, $this->finance_redis_key['TIMEOUT']);
        }
        cilog('debug',"获取用户财务信息成功! [uin:{$uin}]");
        return unserialize($str_finance_info);
    }

    // 清除 uin coinid 财务信息缓存
    function clean_coin_finance_cache($uin,$coin_id)
    {
        $key = $this->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
        $this->cache->redis->delete($key);
        $key_btc = $this->finance_redis_key['FINANCE_INFO'] . $uin . "_" . '10001';
        $this->cache->redis->delete($key_btc);
        cilog('debug',"清除用户财务信息成功! [uin:{$uin}] [coin_id:{$coin_id}]");
    }

    // 通过uin 获取财务信息
    function get_finance_list_by_uin($conn,$uin,$page=NULL,$num=NULL,$aQuery=array(),$sort=NULL)
    {
        if(!isset($uin)){
            cilog('error',"uin不存在,通过uin获取财务信息列表失败! [uin:{$uin}]");
            return $this->m_errcode['M_FINANCE_PARAM_ERR'];
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
            cilog('error',"通过uin获取财务信息列表失败! [uin:{$uin}] [tablename:{$tablename}] [num:{$count}]");
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
            cilog('debug',"通过uin获取财务信息列表成功! [uin:{$uin}] [tablename:{$tablename}] [num:{$count}]");
        }
        return $rsp;
    }

    // 更新财务信息
    function update_finance_info($conn,$uin,$coin_id,$attributes)
    {
        if(!isset($uin)){
            cilog('error',"用户uin不存在!");
            return $this->m_errcode['M_FINANCE_PARAM_ERR'];
        }

        $tablename = $this->get_tablename($uin);
        $attributes['f_modify_time'] = timestamp2time();
        $where = array(
            'f_uin' => $uin,
            'f_coin_id' => $coin_id
        );
        $flag = $this->update_all($conn,$tablename,$attributes,$where);
        if($flag === 0){
            cilog('debug',"修改用户财务信息成功! [uin:{$uin}] [coin_id:{$coin_id}]");
        }else{
            cilog('error',"修改用户财务信息失败! [uin:{$uin}] [coin_id:{$coin_id}] [errcode:{$flag}]");
        }
        $this->clean_coin_finance_cache($uin,$coin_id);
        return $flag;
    }

    // 添加财务信息
    function add_finance_info($conn,$uin,$data)
    {
        if(!isset($uin)){
            cilog('error',"用户uin不存在!");
            return $this->m_errcode['M_FINANCE_PARAM_ERR'];
        }

        $tablename = $this->get_tablename($uin);
        $finance_info = array(
            'f_uin'         => $uin,
            'f_coin_id'     => isset($data['f_coin_id']) ? $data['f_coin_id'] : 0,
            'f_coin_abbr'   => isset($data['f_coin_abbr']) ? $data['f_coin_abbr'] : '',
            'f_coin_addr'   => isset($data['f_coin_addr']) ? $data['f_coin_addr'] : '',
            'f_total_vol'   => isset($data['f_total_vol']) ? $data['f_total_vol'] : 0,
            'f_freeze_vol'  => isset($data['f_freeze_vol']) ? $data['f_freeze_vol'] : 0,
            'f_can_use_vol' => isset($data['f_can_use_vol']) ? $data['f_can_use_vol'] : 0,
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $flag = $this->save($conn,$tablename,$finance_info);
        if($flag === FALSE){
            cilog('error',"添加用户财务信息失败! [uin:{$uin}] [coin_id:{$data['f_coin_id']}] [errcode:{$flag}]");
        }else{
            cilog('debug',"添加用户财务信息成功! [uin:{$uin}] [coin_id:{$data['f_coin_id']}]");
        }
        return $flag;
    }

    /**
     * 预扣用户财务信息
     */
    function pre_reduce_finance($conn,$finance_info,$amount)
    {
        if($finance_info['f_can_use_vol'] < $amount){
            cilog('error',"用户的可用余额不足,无法预扣! [can_use_vol:{$finance_info['f_can_use_vol']}] [need:{$amount}]");
            return $this->m_errcode['M_FINANCE_CAN_USE_MONEY_NOT_ENOUCH'];
        }

        cilog('debug',"开始预扣减用户余额! [uin:{$finance_info['f_uin']}] [coin_id:{$finance_info['f_coin_id']}] [amount:{$amount}]");

        $this->update_all(
            $conn,
            $tablename=$this->get_tablename(),
            $attributes=array(
                'f_modify_time' => timestamp2time(),
                'f_can_use_vol' => $finance_info['f_can_use_vol'] - $amount,
                'f_freeze_vol' => $finance_info['f_freeze_vol'] + $amount,
            ),
            $where = array(
                'f_id' => $finance_info['f_id']
            )
        );
        $this->load->service("finance/finance_service");
        $uin = $finance_info['f_uin'];
        $coin_id = $finance_info['f_coin_id'];
        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
        $this->cache->redis->delete($key);
    }

    /**
     * 回退用户预扣财务信息
     */
    function rollback_pre_reduce_finance($conn,$finance_info,$amount)
    {
        if($finance_info['f_freeze_vol'] < $amount){
            cilog('error',"用户的冻结金额不足,无法回退预扣! [f_freeze_vol:{$finance_info['f_freeze_vol']}] [need:{$amount}]");
            return $this->m_errcode['M_FINANCE_FREEZE_MONEY_NOT_ENOUCH'];
        }

        cilog('debug',"开始回退用户预扣财务信息! [uin:{$finance_info['f_uin']}] [coin_id:{$finance_info['f_coin_id']}] [amount:{$amount}]");

        $this->update_all(
            $conn,
            $tablename=$this->get_tablename(),
            $attributes=array(
                'f_modify_time' => timestamp2time(),
                'f_can_use_vol' => $finance_info['f_can_use_vol'] + $amount,
                'f_freeze_vol' => $finance_info['f_freeze_vol'] - $amount,
            ),
            $where = array(
                'f_id' => $finance_info['f_id']
            )
        );
        $this->load->service("finance/finance_service");
        $uin = $finance_info['f_uin'];
        $coin_id = $finance_info['f_coin_id'];
        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
        $this->cache->redis->delete($key);
    }

    /**
     * 实扣
     */
    function real_reduce_finance($conn,$finance_info,$amount)
    {
        if($finance_info['f_can_use_vol'] < $amount){
            cilog('error',"用户的可用金额不足,无法实扣! [f_can_use_vol:{$finance_info['f_can_use_vol']}] [need:{$amount}]");
            return $this->m_errcode['M_FINANCE_CAN_USE_MONEY_NOT_ENOUCH'];
        }

        if($finance_info['f_freeze_vol'] < $amount){
            cilog('error',"用户的冻结金额不足,无法实扣! [f_freeze_vol:{$finance_info['f_freeze_vol']}] [need:{$amount}]");
            return $this->m_errcode['M_FINANCE_FREEZE_MONEY_NOT_ENOUCH'];
        }

        if($finance_info['f_total_vol'] < $amount){
            cilog('error',"用户的总金额不足,无法实扣! [f_total_vol:{$finance_info['f_total_vol']}] [need:{$amount}]");
            return $this->m_errcode['M_FINANCE_TOTAL_MONEY_NOT_ENOUCH'];
        }

        cilog('debug',"开始实扣财务信息! [uin:{$finance_info['f_uin']}] [coin_id:{$finance_info['f_coin_id']}] [amount:{$amount}]");

        $this->update_all(
            $conn,
            $tablename=$this->get_tablename(),
            $attributes=array(
                'f_modify_time' => timestamp2time(),
                'f_can_use_vol' => $finance_info['f_can_use_vol'] - $amount,
                'f_freeze_vol' => $finance_info['f_freeze_vol'] - $amount,
                'f_total_vol' => $finance_info['f_total_vol'] - $amount,
            ),
            $where = array(
                'f_id' => $finance_info['f_id']
            )
        );

        $this->load->service("finance/finance_service");
        $uin = $finance_info['f_uin'];
        $coin_id = $finance_info['f_coin_id'];
        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
        $this->cache->redis->delete($key);
    }

    /**
     * 实际增加财务信息
     *
     * $need_reduce_freeze_vol     需要扣减预扣的冻结币种数量
     */
    function real_add_finance($conn,$finance_info,$amount,$need_reduce_freeze_vol)
    {
        if($need_reduce_freeze_vol < 0){
            cilog('error',"需要扣减的冻结币种数量数据不合法! [need_freeze:{$need_reduce_freeze_vol}]");
            return $this->m_errcode['M_FINANCE_PARAM_ERR'];
        }else{
            if($need_reduce_freeze_vol !== $amount){
                cilog('error',"用户冻结金额和需要增加的金额不一致");
                return $this->m_errcode['M_FINANCE_TOTAL_MONEY_NOT_ENOUCH'];
            }
        }

        cilog('debug',"开始实扣财务信息! [uin:{$finance_info['f_uin']}] [coin_id:{$finance_info['f_coin_id']}] [amount:{$amount}]");

        $this->update_all(
            $conn,
            $tablename=$this->get_tablename(),
            $attributes=array(
                'f_modify_time' => timestamp2time(),
                'f_can_use_vol' => $finance_info['f_can_use_vol'] + $amount,
                'f_freeze_vol' => $finance_info['f_freeze_vol'] - $amount,
                'f_total_vol' => $finance_info['f_total_vol'] +  $amount,
            ),
            $where = array(
                'f_id' => $finance_info['f_id']
            )
        );

        $this->load->service("finance/finance_service");
        $uin = $finance_info['f_uin'];
        $coin_id = $finance_info['f_coin_id'];
        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
        $this->cache->redis->delete($key);
    }

    function get_finance_without_cache($conn,$uin,$coin_id)
    {
        $select = NULL;
        $tablename = $this->get_tablename();
        $where = array(
            'f_coin_id' => $coin_id,
            'f_uin' => $uin,
        );
        $arr_finance_info = $this->find_by_attributes($conn, $select, $tablename, $where, NULL);
        if (!$arr_finance_info) {
            cilog('error', "获取用户财务信息失败! [uin:{$uin}]");
            $this->db_err_exit($this->m_errcode['M_FINANCE_GET_FINANCE_INFO_ERR']);
        }
        return $arr_finance_info;
    }
}