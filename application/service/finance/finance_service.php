<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * finance  用户服务 0x2005
 */
class Finance_service extends MY_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("finance/Model_t_finance_info");
        $this->load->model("finance/Model_t_finance_log");
        $this->finance_errcode = array(
            "FINANCE_PARAM_ERR"                  => 0x20050000,      // 财务接口,参数错误
            "FINANCE_ADD_DATA_ERR"               => 0x20050001,      // 财务接口,添加币种信息错误
            "FINANCE_GET_DATA_ERR"               => 0x20050002,      // 财务接口,获取财务信息错误
            "FINANCE_UPDATE_DATA_ERR"            => 0x20050003,      // 财务接口,更新币种信息错误
            "FINANCE_GET_LIST_DATA_ERR"          => 0x20050004,      // 财务接口,更新币种列表信息错误
            "FINANCE_GET_LOG_ERR"                => 0x20050005,      // 财务接口,获取财务流水信息错误
            "FINANCE_GET_LIST_LOG_ERR"           => 0x20050006,      // 财务接口,更新币种列表流水信息错误
            "FINANCE_HAVE_NO_ENOUCH_COIN"        => 0x20050007,      // 财务接口,余额不足
            "FINANCE_COIN_OUT_ERR"               => 0x20050008,      // 财务接口,提笔失败
            "FINANCE_LOG_NOT_SAME_UIN"           => 0x20050009,      // 财务接口,该流水是否属于该用户
            "FINANCE_STATE_ERR"                  => 0x2005000a,      // 财务接口,当前状态不是审核中,无法取消
            "FINANCE_CANCEL_COIN_OUT_ERR"        => 0x2005000b,      // 财务接口,取消提币失败
            "FINANCE_LOG_TYPE_ERR"               => 0x2005000c,      // 财务接口,流水类型不符合,无法完成预扣减
            "FINANCE_PRE_REDUCE_COIN_ERR"        => 0x2005000e,      // 财务接口,预扣减虚拟币失败
            "FINANCE_LOG_GET_ERR"                => 0x2005000f,      // 财务接口,获取流水信息错误
            "FINANCE_LOG_STATE_ERR"              => 0x20050010,      // 财务接口,获取流水状态信息错误,不为审核中
            "FINANCE_REDUCE_LOTTERY_COIN_ERR"    => 0x20050011,      // 财务接口,扣减抽奖金额失败
            "FINANCE_LOG_COIN_ID_ERR"            => 0x20050012,      // 财务接口,提现流水单coinid不合法
            "FINANCE_USER_STATE_ERR"             => 0x20050013,      // 财务接口,用户状态不合法,无法提币
        );
        $this->finance_redis_key = array(
            'TIMEOUT' => 3600,
            'FINANCE_INFO' => "finance_info_",    // 币种详情  finance_info_uin
        );
        $this->finance_type = array(
            // 0 默认 1 充币 2 提币 3 买入 4 卖出
            'COIN_IN' => 1,    // 充币
            'COIN_OUT' => 2,    // 提币
            'BUY' => 3,    // 买入
            'SELL' => 4,    // 卖出
        );
        $this->finance_state = array(
            // 0 默认 1 成功 2 失败 3 审核中 4 系统确认中
            'SCUESS' => 1,    // 成功
            'FAILED' => 2,    // 失败
            'DURING' => 3,    // 审核中
            'SYS_COF' => 4,    // 系统确认中
            'CANCEL' => 5,    // 取消
        );
    }

    /**
     * @fun       获取用户币种信息
     *
     * 1. 先从redis中获取uin+coin的财务信息
     * 2. 取不到数据,从db中获取信息
     */
    public function get_finance_info($conn, $uin, $coin_id)
    {
        return $this->Model_t_finance_info->get_finance_info($conn,$uin,$coin_id);
    }

    /**
     * @fun      获取用户财务信息列表
     *
     */
    public function get_uin_finance($conn, $uin, $num, $page)
    {
        $sort = 'f_create_time desc';
        $aQuery = array();
        $aRsp = $this->Model_t_finance_info->get_finance_list_by_uin($conn,$uin,$page,$num,$aQuery,$sort);
        return $aRsp;
    }

    /**
     * 获取市场币种 btc 财务信息
     */
    public function get_market_finance($conn,$uin)
    {
        $coin_id = 10001;
        $market_finance_info = $this->Model_t_finance_info->get_finance_info($conn,$uin,$coin_id);
        return $market_finance_info;
    }

    /**
     * 按币种和用户获取财务信息
     */
    public function get_finance($conn, $where, $num, $page)
    {
        $select = "*";
        $tablename = $this->Model_t_finance_info->get_tablename();
        $sort = 'f_create_time desc';

        $count = $this->Model_t_finance_info->count($conn, $tablename, $where);
        if ((int)$count === 0) {
            cilog('error', "找不到用户资产信息! [count:{$count}]");
            return $this->finance_errcode['FINANCE_GET_DATA_ERR'];
        }

        $finance_list = $this->Model_t_finance_info->find_all($conn, $select, $tablename, $where, $num, $page, $sort);
        if (!is_array($finance_list)) {
            cilog('error', "获取用户资产信息失败!");
            return $this->finance_errcode['FINANCE_GET_LIST_DATA_ERR'];
        }

        $aRsp = array(
            'total' => $count,
            'rows' => $finance_list,
        );
        return $aRsp;
    }

    /**
     * 按时间和coin_id查询提币，充币记录
     */
    public function _get_finance($conn, $where, $num, $page)
    {
        $select = "*";
        $tablename = $this->Model_t_finance_log->get_tablename();
        $sort = 'f_create_time desc';

        $count = $this->Model_t_finance_info->count($conn, $tablename, $where);
        if ((int)$count === 0) {
            cilog('error', "找不到用户资产信息! [count:{$count}]");
            return $this->finance_errcode['FINANCE_GET_DATA_ERR'];
        }

        $finance_list = $this->Model_t_finance_info->find_all($conn, $select, $tablename, $where, $num, $page, $sort);
        if (!is_array($finance_list)) {
            cilog('error', "获取用户资产信息失败!");
            return $this->finance_errcode['FINANCE_GET_LIST_DATA_ERR'];
        }

        $aRsp = array(
            'total' => $count,
            'rows' => $finance_list,
        );
        return $aRsp;
    }

    /**
     * @fun      更新用户币种财务信息
     * @param    $attributes    array   更改的数据
     *
     * 1. 更新币种财务信息
     * 2. 流水记录
     */
    public function update_finance_info($conn, $attributes, $uin, $coin_id)
    {
        $conn->trans_start();

        $where = array(
            'f_uin' => $uin,
            'f_coin_id' => $coin_id,
        );
        $attributes['f_modify_time'] = timestamp2time();
        $tablename = $this->Model_t_finance_info->get_tablename();
        $this->Model_t_coin->update_all($conn, $tablename, $attributes, $where);

        $conn->trans_complete();
        if ($conn->trans_status() === FALSE) {
            // $conn->trans_rollback();
            cilog('error', "更新币种数据失败,开始回滚数据!");
            return $this->finance_errcode['FINANCE_UPDATE_DATA_ERR'];
        } else {
            // $conn->trans_commit();
            $key = $this->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
            $this->cache->redis->delete($key);
            cilog('debug', "更新币种数据成功!");
            return 0;
        }
    }

    /**
     * @fun      充币   往虚拟币平台中充币
     *
     * 1. 在币种钱包中查询该用户的所有的交易记录
     * 2. 找到充值的记录和状态
     * 3. 如果该充值记录已经确认,更新财务信息;否则,财务信息保持不变
     * 4. 检查该流水是否存在,不存在,添加流水;存在,更新流水;
     */
    public function coin_in($conn,$txidinfo,$coin_id,$log_filename)
    {
        $uin = explode('_',$txidinfo['account'])[1];
        $txid_type = $txidinfo['category'];
        $txid_confirmations = $txidinfo['confirmations'];
        $txid_address = $txidinfo['address'];
        $txid_id = $txidinfo['txid'];
        $amount = abs($txidinfo['amount']);

        // 1 判断当前单据是否为接受类型
        if($txid_type != 'receive'){
            cilog('error',"当前单据状态不为接受! [type:{$txid_type}]",$log_filename);
            return -1;
        }

        // 2 判断当前单据的确认次数
        if ($txid_confirmations < 4){
            cilog('error',"该单据没有被确认! [conf:{$txid_confirmations}]",$log_filename);
            return -1;
        }

        // 3 查询地址是否存在于本地db中
        $aQuery = array(
            'f_coin_addr' => $txid_address
        );
        $finance_info = $this->finance_service->Model_t_finance_info->get_finance_list_by_uin($conn,$uin,1,1,$aQuery);
        if((int)$finance_info['total'] === 0){
            cilog('error',"该记录不在本地db中,直接退出 [uin:{$uin}] [address:{$txid_address}]",$log_filename);
            return -1;
        }

        // 4 查询当前交易是否已经写入记录
        $aQuery = array(
            'f_coin_key' => $txid_id,
        );
        $finance_log = $this->finance_service->Model_t_finance_log->get_finance_log_list_by_uin($conn,$uin,1,1,$aQuery);
        if((int)$finance_log['total'] !== 0){
            cilog('error',"当前交易已经写入了db中! 无需继续写入db! [uin:{$uin}] [txid:{$txid_id}]",$log_filename);
            return -1;
        }

        // 5 开始充值
        $conn->trans_start();
        // 添加财务流水
        $data = array(
            'f_type' => $this->finance_service->finance_type['COIN_IN'],
            'f_uin' =>  $uin,
            'f_coin_id' => $coin_id,
            'f_coin_addr' => $txid_address,
            'f_coin_key' => $txid_id,
            'f_vol' => $amount,
            'f_real_revice_vol' => $amount,
            'f_state' => $this->finance_service->finance_state['SCUESS'],
        );
        $this->finance_service->Model_t_finance_log->add_finance_log($conn,$finance_info['f_uin'],$data);

        // 更新财务信息
        $attributes = array(
            'f_can_use_vol' => $finance_info['f_can_use_vol'] + $amount,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$finance_info['f_uin'],$coin_id,$attributes);
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        $conn->trans_complete();
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"充值失败,开始回滚数据! [txid:{$txid_id}] [vol:{$amount}] [coinid:{$coin_id}] [uin:{$uin}]",$log_filename);
        }
        else
        {
            cilog('debug',"充值成功! [txid:{$txid_id}] [vol:{$amount}] [coinid:{$coin_id}] [uin:{$uin}]",$log_filename);
        }
    }

    /**
     * @fun      获取用户财务流水列表
     * $type     0 默认 1 充币 2 提币 3 买入 4 卖出
     */
    public function get_uin_finance_log($conn, $uin, $type, $coin_id, $num, $page)
    {
        $aQuery = array(
            'f_type' => $type,
            'f_coin_id' => $coin_id,
        );
        $sort = 'f_create_time desc';
        $fiannce_log_list = $this->Model_t_finance_log->get_finance_log_list_by_uin($conn,$uin,$page,$num,$aQuery,$sort);
        return $fiannce_log_list;
    }

    /**
     * @fun      获取财务流水
     */
    public function get_finance_log($conn, $where)
    {
        $select = "*";
        $tablename = $this->Model_t_finance_log->get_tablename();
        $finance_log = $this->Model_t_finance_log->find_by_attributes(
            $conn,
            $select,
            $tablename,
            $where,
            'f_id desc'
        );
        return $finance_log;
    }

    /**
     * @fun 提币审核 _check_finance_state
     */
    public function _check_finance_state($conn, $where,$uin)
    {

        $this->load->service('finance/finance_service');
        $order_id_info = $this->finance_service->get_finance_log($this->conn, $where);
        if (!is_array($order_id_info)) {
            render_json($order_id_info);
        }

        //用户跟单号是否匹配

        if ($order_id_info['f_uin'] != $uin) {
            cilog('error', "非法单，此用户跟单号不匹配");
            return $this->finance_errcode['FINANCE_LOG_NOT_SAME_UIN'];
        }

        //是否是审核状态

        if ($order_id_info['f_state'] != $this->finance_state['DURING']) {
            cilog('error', "该提币记录单状态不合法 [state:{$order_id_info['f_state']}]");
            return $this->finance_errcode['FINANCE_STATE_ERR'];
        }

        $finance_info = $this->finance_service->get_finance($conn,array('f_uin'=>$uin,'f_id'=>$order_id_info['f_id'],'f_coin_id'=>$order_id_info['f_coin_id']));
        //$conn, $where, $num, $page)

        $conn->trans_start();
        $this->Model_t_finance_info->update_all(
            $conn,
            $table_name = $this->Model_t_finance_info->get_tablename(),
            $attributes = array(
                'f_total_vol' =>$finance_info['f_total_vol']-$order_id_info['f_vol'],
                'f_can_use_vol' =>$finance_info['f_can_use_vol']-$order_id_info['f_vol'],
                'f_modify_time' => timestamp2time(),
            ),
            $where = array(
                'f_id' => $order_id_info['f_id'],
            )
        );
        $key = $this->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $order_id_info['f_coin_id'];
        $this->cache->redis->delete($key);

        $this->Model_t_finance_log->update_all(
            $conn,
            $table_name = $this->Model_t_finance_log->get_tablename(),
            $attributes = array(
                'f_state' => $this->finance_state['SCUESS'],
                'f_modify_time' => timestamp2time(),
            ),
            $where = array(
                'f_id' => $order_id_info['f_id'],
            )
        );

        $conn->trans_complete();
        if ($conn->trans_status() === FALSE) {
            cilog('error', "提币审核失败,开始回滚数据!");
            return $this->finance_errcode['FINANCE_COIN_OUT_ERR'];
        } else {
            cilog('debug', "提币审核成功!");
            return 0;
        }


    }


    /**
     * update
     */


    /**
     * 计算提现佣金  单位为比特币
     */
    public function get_atm_commission($coininfo,$num)
    {
        $coin_id = $coininfo['f_coin_id'];
        $sum     = $coininfo['f_last_price'] * $num;
        $rate    = $coininfo['f_atm_rate'] / 100;
        if($coin_id == 10001){
            $commission = $num * $rate;
        }else{
            $commission = $rate * $sum;
        }
        cilog('debug',"提现佣金计算成功! [coin_id:{$coin_id}] [commission:{$commission}] [rate:{$rate}]");
        return $commission;
    }

    /**
     * @fun   比特币提现
     *
     * 1. 校验比特币id 是否为10001
     * 3. 校验用户余额是否足够
     * 4. 冻结用户比特币数量
     * 5. 添加提币单据   默认审核中
     */
    public function btc_coin_out($conn,$coin_info,$finance_info,$user_info,$coin_out_num,$coin_out_addr)
    {
        $coin_id = $coin_info['f_coin_id'];
        $atm_commission = $this->get_atm_commission($coin_info,$coin_out_num);
        $need_amount = $coin_out_num + $atm_commission;
        $left_amount = $finance_info['f_can_use_vol'];
        $uin = $user_info['f_uin'];

        // 校验币种id是否为比特币
        if((int)$coin_id !== 10001){
            cilog('error',"币种id不为比特币 [coin_id:{$coin_id}]");
            return $this->finance_errcode['FINANCE_PARAM_ERR'];
        }

        // 校验用户余额是否足够
        if($need_amount > $left_amount){
            cilog('error',"比特币余额不足,无法提现! [left_amount:{$left_amount}] [need_amount:{$need_amount}]");
            $this->finance_errcode['FINANCE_HAVE_NO_ENOUCH_COIN'];
        }
        cilog('debug',$finance_info);

        // 冻结用户比特币 添加提币单
        $conn->trans_start();
        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] + $need_amount,
            'f_can_use_vol' => $finance_info['f_can_use_vol'] - $need_amount,
        );
        $this->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        $log_data = array(
            'f_uin'             => $uin,
            'f_type'            => $this->finance_type['COIN_OUT'],
            'f_coin_id'         => $coin_id,
            'f_coin_addr'       => $coin_out_addr,
            // 'f_coin_key'        => isset($data['f_coin_key']) ? $data['f_coin_key'] : '',
            'f_vol'             => $need_amount,
            'f_state'           => $this->finance_state['DURING'],
            'f_atm_rate_vol'    => $atm_commission,
            'f_real_revice_vol' => $coin_out_num,
        );
        $this->Model_t_finance_log->add_finance_log($conn,$uin,$log_data);
        $conn->trans_complete();
        $this->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE) {
            cilog('error', "提取比特币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [need:{$need_amount}] [revice:{$coin_out_num}] [commission:{$atm_commission}]");
            return $this->finance_errcode['FINANCE_COIN_OUT_ERR'];
        } else {
            cilog('debug', "提取比特币成功! [uin:{$uin}] [coin_id:{$coin_id}] [need:{$need_amount}] [revice:{$coin_out_num}] [commission:{$atm_commission}]");
            return 0;
        }
    }

    /**
     * @fun   取消比特币提现
     *
     * 1. 核对uin coin_id 是否与提现单一致
     * 2. 核对单据状态
     * 3. 修改单据状态
     * 4. 回退用户财务信息
     */
    public function cancel_btc_coin_out($conn,$finance_info,$user_info,$finance_log_info)
    {
        $coin_id = $finance_log_info['f_coin_id'];
        $log_id = $finance_log_info['f_id'];
        $log_state = $finance_log_info['f_state'];
        $coin_out_num = $finance_log_info['f_real_revice_vol'];
        $atm_commission = $finance_log_info['f_atm_rate_vol'];
        $need_amount = $coin_out_num + $atm_commission;
        $uin = $finance_log_info['f_uin'];

        if((string)$uin !== (string)$user_info['f_uin']){
            cilog('error',"该单据不属于当前用户! [log_uin:{$uin}] [user_uin:{$user_info['f_uin']}]");
            return $this->finance_errcode['FINANCE_LOG_NOT_SAME_UIN'];
        }

        if((int)$coin_id !== 10001){
            cilog('error',"该单据不是比特币! [log_coinid:{$coin_id}] [btc_coinid:10001]");
            return $this->finance_errcode['FINANCE_LOG_COIN_ID_ERR'];
        }

        if((int)$log_state !== $this->finance_state['DURING']){
            cilog('error',"单据状态不合法! [state:{$log_state}]");
            return $this->finance_errcode['FINANCE_STATE_ERR'];
        }

        $conn->trans_start();
        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] - $need_amount,
            'f_can_use_vol' => $finance_info['f_can_use_vol'] + $need_amount,
        );
        $this->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        $attributes = array(
            'f_state' => $this->finance_state['CANCEL'],
        );
        $this->Model_t_finance_log->update_finance_log_by_uin($conn,$uin,$log_id,$attributes);
        $conn->trans_complete();
        $this->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE) {
            cilog('error', "取消提取比特币失败,开始回滚数据! [uin:{$uin}] [log_id:{$log_id}] [need:{$need_amount}] [revice:{$coin_out_num}] [commission:{$atm_commission}]");
            return $this->finance_errcode['FINANCE_CANCEL_COIN_OUT_ERR'];
        } else {
            cilog('debug', "取消提取比特币成功! [uin:{$uin}] [log_id:{$log_id}] [need:{$need_amount}] [revice:{$coin_out_num}] [commission:{$atm_commission}]");
            return 0;
        }
    }

    /**
     * @fun 提币
     *
     * 1. 校验币种id不是10001
     * 2. 当前用户当前币种余额足够
     * 3. 比特币账户余额足够  用于扣减佣金
     * 4. 冻结比特币、当前币种的数量
     * 5. 添加提币单   默认审核中
     */
    public function coin_out_v($conn,$coin_info,$finance_info,$market_finance_info,$user_info,$coin_out_num,$coin_out_addr)
    {
        $coin_id = $coin_info['f_coin_id'];
        $atm_commission = $this->get_atm_commission($coin_info,$coin_out_num);
        $need_coin_vol = $coin_out_num;
        $need_btc_vol = $atm_commission;
        $left_amount = $finance_info['f_can_use_vol'];
        $btc_left_amount = $market_finance_info['f_can_use_vol'];
        $uin = $user_info['f_uin'];

        // 校验币种id是否为比特币
        if((int)$coin_id === 10001){
            cilog('error',"币种id为比特币 [coin_id:{$coin_id}]");
            return $this->finance_errcode['FINANCE_PARAM_ERR'];
        }

        // 校验用户当前币种余额是否足够
        if($need_coin_vol > $left_amount){
            cilog('error',"当前币种余额不足,无法提现! [left_amount:{$left_amount}] [need_amount:{$need_coin_vol}]");
            $this->finance_errcode['FINANCE_HAVE_NO_ENOUCH_COIN'];
        }

        // 校验用户当前币种余额是否足够
        if($need_btc_vol > $btc_left_amount){
            cilog('error',"当前用户比特币余额不足,无法提现! [left_amount:{$left_amount}] [need_amount:{$need_coin_vol}]");
            $this->finance_errcode['FINANCE_HAVE_NO_ENOUCH_COIN'];
        }

        // 冻结用户比特币 添加提币单
        $conn->trans_start();
        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] + $need_coin_vol,
            'f_can_use_vol' => $finance_info['f_can_use_vol'] - $need_coin_vol,
        );
        $this->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        $attributes = array(
            'f_freeze_vol'  => $market_finance_info['f_freeze_vol'] + $need_btc_vol,
            'f_can_use_vol' => $market_finance_info['f_can_use_vol'] - $need_btc_vol,
        );
        $this->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);

        $log_data = array(
            'f_uin'             => $uin,
            'f_type'            => $this->finance_type['COIN_OUT'],
            'f_coin_id'         => $coin_id,
            'f_coin_addr'       => $coin_out_addr,
            // 'f_coin_key'        => isset($data['f_coin_key']) ? $data['f_coin_key'] : '',
            'f_vol'             => $need_coin_vol,
            'f_state'           => $this->finance_state['DURING'],
            'f_atm_rate_vol'    => $need_btc_vol,  // 单位btc
            'f_real_revice_vol' => $need_coin_vol,
        );
        $this->Model_t_finance_log->add_finance_log($conn,$uin,$log_data);
        $conn->trans_complete();
        $this->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE) {
            cilog('error', "提取虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [need:{$need_coin_vol}] [revice:{$need_coin_vol}] [commission:{$need_btc_vol}]");
            return $this->finance_errcode['FINANCE_COIN_OUT_ERR'];
        } else {
            cilog('debug', "提取虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [need:{$need_coin_vol}] [revice:{$need_coin_vol}] [commission:{$need_btc_vol}]");
            return 0;
        }
    }

    /**
     * @fun   取消提币
     *
     * 1. 核对uin coin_id 是否与提现单一致
     * 2. 核对单据状态
     * 3. 修改单据状态
     * 4. 回退用户财务信息   btc 虚拟币
     */
    public function cancel_coin_out_v($conn,$finance_info,$market_finance_info,$user_info,$finance_log_info)
    {
        $coin_id = $finance_log_info['f_coin_id'];
        $log_id = $finance_log_info['f_id'];
        $log_state = $finance_log_info['f_state'];
        $coin_out_num = $finance_log_info['f_real_revice_vol'];
        $atm_commission = $finance_log_info['f_atm_rate_vol'];
        $need_coin_vol = $coin_out_num;
        $need_btc_vol = $atm_commission;
        $uin = $finance_log_info['f_uin'];

        if((int)$coin_id === 10001){
            cilog('error',"币种id为比特币 [coin_id:{$coin_id}]");
            return $this->finance_errcode['FINANCE_PARAM_ERR'];
        }

        if((string)$uin !== (string)$user_info['f_uin']){
            cilog('error',"该单据不属于当前用户! [log_uin:{$uin}] [user_uin:{$user_info['f_uin']}]");
            return $this->finance_errcode['FINANCE_LOG_NOT_SAME_UIN'];
        }

        if((int)$log_state !== $this->finance_state['DURING']){
            cilog('error',"单据状态不合法! [state:{$log_state}]");
            return $this->finance_errcode['FINANCE_STATE_ERR'];
        }

        $conn->trans_start();
        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] - $need_coin_vol,
            'f_can_use_vol' => $finance_info['f_can_use_vol'] + $need_coin_vol,
        );
        $this->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        $attributes = array(
            'f_freeze_vol'  => $market_finance_info['f_freeze_vol'] - $need_btc_vol,
            'f_can_use_vol' => $market_finance_info['f_can_use_vol'] + $need_btc_vol,
        );
        $this->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);

        $attributes = array(
            'f_state' => $this->finance_state['CANCEL'],
        );
        $this->Model_t_finance_log->update_finance_log_by_uin($conn,$uin,$log_id,$attributes);
        $conn->trans_complete();
        $this->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE) {
            cilog('error', "取消提取虚拟币失败,开始回滚数据! [uin:{$uin}] [log_id:{$log_id}] [revice:{$coin_out_num}] [commission:{$atm_commission}]");
            return $this->finance_errcode['FINANCE_CANCEL_COIN_OUT_ERR'];
        } else {
            cilog('debug', "取消提取虚拟币成功! [uin:{$uin}] [log_id:{$log_id}] [revice:{$coin_out_num}] [commission:{$atm_commission}]");
            return 0;
        }
    }
}