<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * deal  交易服务
 */

//require_once APPPATH.'service/base_comm/comm_define.php';
class Deal_service extends MY_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("deal/Model_t_deal");
        $this->load->model("deal/Model_t_bdeal");
        $this->deal_errcode = array(
            "DEAL_PARAM_ERR"               => 0x20040000,      // deal接口,参数错误
            "DEAL_ADD_BDEAL_ERR"           => 0x20040001,      // deal接口,添加大单失败
            "DEAL_GET_BDEAL_ERR"           => 0x20040002,      // deal接口,获取大单信息失败
            "DEAL_UPDATE_BDEAL_ERR"        => 0x20040003,      // deal接口,更新大单信息失败
            "DEAL_ADD_DEAL_ERR"            => 0x20040004,      // deal接口,添加小单信息失败
            "DEAL_GET_DEAL_ERR"            => 0x20040005,      // deal接口,获取大单信息失败
            "DEAL_UPDATE_DEAL_ERR"         => 0x20040006,      // deal接口,更新大单信息失败
            "DEAL_GET_BDEAL_NUM_ERR"       => 0x20040007,      // deal接口,获取大单总数错误
            "DEAL_GET_DEAL_NUM_ERR"        => 0x20040008,      // deal接口,获取小单总数错误
            "DEAL_USER_NOT_ENOUGH_COIN"    => 0x20040009,      // deal接口,用户余额不足
            "DEAL_BDEAL_STATE_NOT_DURING"  => 0x2004000a,      // deal接口,委托单状态不是交易中
            "DEAL_BDEAL_UIN_NOT_SANME"     => 0x2004000b,      // deal接口,委托单状态不属于该用户
            "DEAL_BUY_BDEAL_ERR"           => 0x2004000c,      // deal接口,买入委托出错
            "DEAL_SELL_BDEAL_ERR"          => 0x2004000d,      // deal接口,卖出委托出错
            "DEAL_CANCEL_BDEAL_ERR"        => 0x2004000e,      // deal接口,取消大单失败
            "DEAL_COINID_CAN_NOT_TRADE"    => 0x2004000f,      // deal接口,该币种禁止交易
            "DEAL_BDEAL_TYPE_ERR"          => 0x20040010,      // deal接口,大单类型错误
            "DEAL_BDEAL_SELL_PRICE_ERR"    => 0x20040011,      // deal接口,卖单挂单价不得超过10%浮动
        );
        $this->deal_redis_key = array(
            'TIMEOUT'             => 3600,
            'TO_BE_DEAL'          =>'to_be_deal_list_',
            'KLINE'               =>'kline_',
            'BDEAL_GOING_TO_DEAL' => "bdeal_going_to_deal_",     // bdeal_going_to_deal_coinid_type
            'BDEAL_BUY_BURING_LIST'  => "bdeal_buy_list_during_" // 买一单列表
        );
        // 订单状态
        $this->state = array(
            'DEAL_DURING' => 1,
            'DEAL_DONE' => 2,
            'DEAL_CANCEL' => 3,
        );
        // 订单类型
        $this->type = array(
            'BUY' => 1,
            'SELL' => 2,
        );
    }

    public function export_bdeal_list($bdeal_info_list)
    {
        $arr = array();
        foreach ($bdeal_info_list as $row){
            $a = array(
                'create_time' => $row['f_create_time'],
                'type' => $row['f_type'],
                'coin_name' => $row['f_coin_name'],
                'money' => $row['f_price'],
                'vol' => $row['f_total_vol'],
                'preDealVol' => $row['f_pre_deal_vol'],
                'postDealVol' => $row['f_post_deal_vol'],
                'state' => $row['f_state'],
                'bdealId' => $row['f_export_id'],
            );
            array_push($arr,$a);
        }
        return $arr;
    }

    public function export_deal_list($deal_info_list)
    {
        $arr = array();
        foreach ($deal_info_list as $row){
            $a = array(
                'create_time' => $row['f_create_time'],
                'type' => $row['f_type'],
                'coin_name' => $row['f_coin_name'],
                'money' => $row['f_money'],
                'vol' => round($row['f_num'],3),
                'state' => $row['f_state'],
            );
            array_push($arr,$a);
        }
        return $arr;
    }


    // 生成对外的单id
    public function todealid($dealid,$uin)
    {
        $last_deal_id = get_str_num($dealid,8);
        $last_uin = get_str_num($uin,2);
        $pretime = date("ymd",time());
        $data =  $pretime .$last_deal_id. $last_uin;
        return $data;
    }

    // 计算需要的交易佣金
    public function get_trade_commission($coininfo,$deal_num,$deal_price)
    {
        $coin_id = $coininfo['f_coin_id'];
        $sum     = $deal_price * $deal_num;
        $rate    = $coininfo['f_commission'] / 100;
        if($coin_id == 10001){
            $commission = $deal_num * $rate;
        }else{
            $commission = $rate * $sum;
        }
        cilog('debug',"提现佣金计算成功! [coin_id:{$coin_id}] [commission:{$commission}] [rate:{$rate}]");
        return $commission;
    }

    // 校验用户提交委托是否足额
    public function check_bdeal_money($buy_need_money,$finance_vol)
    {
        if ($buy_need_money > $finance_vol){
            cilog('error',"用户余额不足,无法购买,请充值");
            return $this->deal_errcode['DEAL_USER_NOT_ENOUGH_COIN'];
        }else{
            return 0;
        }
    }

    // 校验委托单状态是否匹配
    public function check_bdeal_state($bdealinfo,$state)
    {
        if($bdealinfo['f_state'] == $state){
            return 0;
        }else{
            cilog('error',"委托单状态校验错误! [bdealid:{$bdealinfo['f_bdeal_id']}] [db_state:{$bdealinfo['f_state']}] [state:{$state}]");
            return $this->deal_errcode['DEAL_BDEAL_STATE_NOT_DURING'];
        }
    }

    // 校验委托单和用户是否匹配
    public function check_bdeal_uin_match($bdealinfo,$uin)
    {
        if($bdealinfo['f_uin'] == $uin){
            return 0;
        }else{
            cilog('error',"该委托单与用户信息不符合! [bdealid:{$bdealinfo['f_bdeal_id']}] [deal_uin:{$bdealinfo['f_uin']}] [uin:{$uin}]");
            return $this->deal_errcode['DEAL_BDEAL_UIN_NOT_SANME'];
        }
    }

    // 添加一个委托单
    public function add_bdeal($conn,$uin,$type,$info)
    {
        $conn->trans_start();
        $this->load->model("conf/Model_t_idmaker");
        $bdeal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'BDEAL_ID');
        $bdeal_state = $this->state;
        $coin_id = isset($info['f_coin_id']) ? $info['f_coin_id'] : 0;

        $bdeal_info = array(
            'f_bdeal_id'        => $bdeal_id,
            'f_uin'             => $uin,
            'f_type'            => $type,
            'f_coin_id'         => $info['f_coin_id'],
            'f_coin_name'       => $info['f_coin_name'],
            'f_total_money'     => $info['f_total_money'],
            'f_total_vol'       => $info['f_total_vol'],
            'f_post_deal_vol'   => $info['f_total_vol'],
            'f_post_deal_money' => $info['f_total_money'],
            'f_state'           => $bdeal_state['DEAL_DURING'],
            'f_export_id'       => $this->todealid($bdeal_id,$uin),
        );

        $this->Model_t_bdeal->add_bdeal($conn,$uin,$coin_id,$bdeal_info);
        $conn->trans_complete();
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"添加大单失败,开始回滚数据! [uin:{$uin}] [type:{$type}]");
            return $this->deal_errcode['DEAL_ADD_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"添加大单成功! [uin:{$uin}] [type:{$type}]");
            return 0;
        }
    }

    // 获取委托单信息
    public function get_bdeal_info($conn,$bdealid)
    {
        $bdeal_info = $this->Model_t_bdeal->get_bdeal_info_by_export_id($conn,$bdealid);
        if (!is_array($bdeal_info)){
            cilog('error',"获取大单信息失败! bdealid:{$bdealid}");
            return $this->deal_errcode['DEAL_GET_BDEAL_ERR'];
        }else{
            return $bdeal_info;
        }
    }

    // 获取用户委托单列表信息
    public function get_bdeal_list($conn,$uin,$state,$coin_id=NULL,$page,$num)
    {
        if($coin_id===NULL){
            $aQuery = array(
                'f_state' => $state,
            );
        }else{
            $aQuery = array(
                'f_state' => $state,
                'f_coin_id' => $coin_id,
            );
        }
        $sort = 'f_create_time desc';

        $rsp = $this->Model_t_bdeal->get_bdeal_list_by_uin($conn,$uin,$page,$num,$aQuery,$sort);
        return $rsp;
    }

    // 查询用户委托单列表信息
    public function search_bdeal_list($conn,$page,$num,$query,$sort)
    {
        $tablename = $this->Model_t_bdeal->get_tablename();
        $where = array();
        foreach ($query as $key=>$value){
            if($value === NULL){
                continue;
            }
            $where[$key] = $value;
        }

        $count = $this->Model_t_bdeal->count($conn,$tablename,$where);
        if($count == 0){
            cilog('error',"找不到该用户的委托单信息");
            return $this->deal_errcode['DEAL_GET_BDEAL_NUM_ERR'];
        }

        $bdeal_list = $this->Model_t_bdeal->find_all($conn,NULL,$tablename,$where, $num, $page, $sort);
        $rsp['total'] = $count;
        $rsp['rows'] = $bdeal_list;
        return $rsp;
    }

    //按照条件进行订单查询
    public function find_deal_list($conn,$where,$num,$page){

        $table_name = $this->Model_t_bdeal->get_tablename();

        $select ="*";

        $sort = "f_create_time desc";

        $count = $this->Model_t_bdeal->count($conn,$table_name,$where);

        if($count === 0){
            cilog('error',"订单查询数量\$where有错误");
            return $this->deal_errcode['DEAL_GET_BDEAL_ERR'];
        }

        $res = $this->Model_t_bdeal->find_all($conn,$select,$table_name,$where,$num,$page,$sort);

        if (!$res){
            cilog('error',"查询订单信息失败!");
            return $this->deal_errcode['DEAL_GET_BDEAL_ERR'];
        }

        $aRsp = array(
            'page' => $page,
            'num' => $num,
            'totalNum' => $count,
            'rows' => $res,
        );
        return $aRsp;

    }

    // 更新大单数据
    public function update_bdeal_info($conn,$bdealid,$arr_where,$arr_att)
    {
        $tablename = $this->Model_t_bdeal->get_tablename();
        $where = array(
            'f_bdeal_id' => $bdealid,
        );
        foreach ($arr_where as $key => $value){
            if($value === NULL){
                continue;
            }
            $where[$key] = $value;
        }

        $attributes = array();
        foreach ($arr_att as $key => $value){
            if($value === NULL){
                continue;
            }
            $attributes[$key] = $value;
        }

        $conn->trans_start();
        $this->Model_t_bdeal->update_all($conn,$tablename,$attributes, $where);
        $conn->trans_complete();
        if ($conn->trans_status() === FALSE)
        {
            // $conn->trans_rollback();
            cilog('error',"更新大单信息失败,开始回滚数据!");
            return $this->deal_errcode['DEAL_UPDATE_DATA_ERR'];
        }
        else
        {
            // $conn->trans_commit();
            cilog('debug',"更新大单信息成功!");
            return 0;
        }
    }

    /**
     * 买入虚拟币   预扣市场币
     *
     */
    public function buy_bdeal($conn,$userinfo,$coininfo,$market_finance_info,$price,$vol)
    {
        cilog('debug',"买入币种 [uin:{$userinfo['f_uin']}] [coin_id:{$coininfo['f_coin_id']}] [price:{$price}] [vol:{$vol}]");

        $this->load->service("finance/finance_service");
        $uin = $userinfo['f_uin'];
        $coin_id = $coininfo['f_coin_id'];
        $left_amount = $market_finance_info['f_can_use_vol'];
        $need = $vol * $price;

        if((int)$coin_id === 10001){
            cilog('error','参数错误,比特币不参加买入!');
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }

        // 判断用户余额是否足够
        if ($left_amount < $need){
            cilog('error',"用户btc余额不足,无法买入! [need:{$need}] [left:{$left_amount}]");
            return $this->deal_errcode['DEAL_USER_NOT_ENOUGH_COIN'];
        }

        $conn->trans_start();
        // 开始预扣财务中的基本币种信息
        $attributes = array(
            'f_freeze_vol'  => $market_finance_info['f_freeze_vol'] + $need,
            'f_can_use_vol' => $market_finance_info['f_can_use_vol'] - $need,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);

        // 开始创建买入委托单
        $this->load->model("conf/Model_t_idmaker");
        $bdeal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'BDEAL_ID');
        $bdealinfo = array(
            'f_bdeal_id'        => $bdeal_id,
            'f_uin'             => $uin,
            'f_type'            => $this->type['BUY'],
            'f_coin_id'         => $coininfo['f_coin_id'],
            'f_coin_name'       => $coininfo['f_abbreviation'],
            'f_total_money'     => $need,
            'f_total_vol'       => $vol,
            'f_pre_deal_vol'    => $vol,
            'f_pre_deal_money'  => $need,
            'f_price'           => $price,
            'f_state'           => $this->state['DEAL_DURING'],
            'f_export_id'       => $this->todealid($bdeal_id,$uin),
        );
        $this->Model_t_bdeal->add_bdeal($conn,$uin,$coin_id,$bdealinfo);
        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"买入虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"买入虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return 0;
        }
    }

    /**
     * 卖出虚拟币   扣减虚拟币财务
     */
    public function sell_bdeal($conn,$userinfo,$coininfo,$market_finance_info,$finance_info,$price,$vol)
    {
        cilog('debug',"卖出币种 [uin:{$userinfo['f_uin']}] [coin_id:{$coininfo['f_coin_id']} [price:{$price}] [vol:{$vol}]");

        $this->load->service("finance/finance_service");
        $uin = $userinfo['f_uin'];
        $coin_id = $coininfo['f_coin_id'];
        $left_coin_amount = $finance_info['f_can_use_vol'];
        $btc_left_coin_amount = $market_finance_info['f_can_use_vol'];
        $commission = $this->get_trade_commission($coininfo,$vol,$price);

        if((int)$coin_id === 10001){
            cilog('error','参数错误,比特币不参加卖出!');
            return $this->coin_service->coin_errcode['DEAL_PARAM_ERR'];
        }

        // 校验当前币余额是否足额
        if ($left_coin_amount < $vol){
            cilog('error',"用户余额不足,无法卖出! [sell:{$vol}] [left:{$left_coin_amount}]");
            return $this->deal_errcode['DEAL_USER_NOT_ENOUGH_COIN'];
        }

        // 校验用户btc是否足额
        if ($btc_left_coin_amount < $commission){
            cilog('error',"用户btc余额不足,无法卖出! [need:{$commission}] [left:{$btc_left_coin_amount}]");
            return $this->deal_errcode['DEAL_USER_NOT_ENOUGH_COIN'];
        }

        $conn->trans_start();
        $attributes = array(
            'f_freeze_vol'  => $market_finance_info['f_freeze_vol'] + $commission,
            'f_can_use_vol' => $market_finance_info['f_can_use_vol'] - $commission,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);

        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] + $vol,
            'f_can_use_vol' => $finance_info['f_can_use_vol'] - $vol,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);


        // 开始创建卖出委托单
        $this->load->model("conf/Model_t_idmaker");
        $bdeal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'BDEAL_ID');
        $bdealinfo = array(
            'f_bdeal_id'        => $bdeal_id,
            'f_uin'             => $uin,
            'f_type'            => $this->type['SELL'],
            'f_coin_id'         => $coin_id,
            'f_coin_name'       => $coininfo['f_abbreviation'],
            'f_total_money'     => $price * $vol,
            'f_total_vol'       => $vol,
            'f_pre_deal_vol'    => $vol,
            'f_pre_deal_money'  => $price * $vol,
            'f_commission'      => $commission,
            'f_price'           => $price,
            'f_state'           => $this->state['DEAL_DURING'],
            'f_export_id'       => $this->todealid($bdeal_id,$uin),
        );
        $this->Model_t_bdeal->add_bdeal($conn,$uin,$coin_id,$bdealinfo);
        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"卖出虚拟币失败,开始回滚数据! [uin:{$uin}] [coinid:{$coin_id}] [price:{$price}] [vol:{$vol}] [commission:{$commission}]");
            return $this->deal_errcode['DEAL_SELL_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"卖出虚拟币成功! [uin:{$uin}] [coinid:{$coin_id}] [price:{$price}] [vol:{$vol}] [commission:{$commission}]");
            return 0;
        }
    }


    /**
     * fun 交易单
     */

    // 添加一个交易单
    public function add_deal($conn,$uin,$info)
    {
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'BDEAL_ID');
        $tablename = $this->Model_t_bdeal->get_tablename();

        $deal_info = array(
            'f_deal_id' => $deal_id,
            'f_uin' => $uin,
            'f_bdeal_id' => $info['f_bdeal_id'],
            'f_type' => $info['f_type'],
            'f_coin_id' => $info['f_coin_id'],
            'f_num' => $info['f_num'],
            'f_money' => $info['f_money'],
            'f_state' => $info['f_state'],
            'f_commission' => $info['f_commission'],
            'f_order_id' => $this->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $flag = $this->Model_t_deal->save($conn,$tablename,$deal_info);
        if ($flag === FALSE){
            cilog('error',"添加小单失败,开始回滚数据!");
            return $this->deal_errcode['DEAL_ADD_DEAL_ERR'];
        }else{
            cilog('debug',"添加小单成功!");
            return 0;
        }
    }

    // 获取交易单列表信息
    public function get_deal_list($conn,$bdeal_id,$num, $page)
    {
        $tablename = $this->Model_t_deal->get_tablename();
        $where = array(
            'f_bdeal_id' => $bdeal_id,
        );
        $rsp = array(
            'total' => 0,
            'rows' => array(),
        );
        $sort = 'f_create_time desc';
        $count = $this->Model_t_deal->count($conn,$tablename,$where);
        if($count == 0){
            cilog('error',"找不到该用户的委托单信息");
            return $this->deal_errcode['DEAL_GET_DEAL_NUM_ERR'];
        }

        $deal_list = $this->Model_t_deal->find_all($conn,NULL,$tablename,$where, $num, $page, $sort);
        $rsp['total'] = $count;
        $rsp['rows'] = $deal_list;
        return $rsp;
    }

    // 查询用户小单列表信息
    public function search_deal_list($conn,$page,$num,$query,$sort)
    {
        $tablename = $this->Model_t_deal->get_tablename();
        $where = array();
        foreach ($query as $key=>$value){
            if($value === NULL){
                continue;
            }
            $where[$key] = $value;
        }

        $count = $this->Model_t_deal->count($conn,$tablename,$where);
        if($count == 0){
            cilog('error',"找不到该用户的委托单信息");
            return $this->deal_errcode['DEAL_GET_DEAL_NUM_ERR'];
        }

        $deal_list = $this->Model_t_deal->find_all($conn,NULL,$tablename,$where, $num, $page, $sort);
        $rsp['total'] = $count;
        $rsp['rows'] = $deal_list;
        return $rsp;
    }

    // 获取价格不同的挂单
    public function get_bdeal_diff_price($conn,$state,$type,$coin_id,$sort,$num)
    {
        $rsp = array(
            'num' => 0,
            'rows' => array(),
        );
        $tablename = $this->Model_t_bdeal->get_tablename();
        $list = $this->Model_t_bdeal->get_distinct_price($conn,$state,$type,$coin_id,$sort,$num);
        if (!$list){
            cilog('error',"当前找不到待成交的大单!具体条件如下:");
            return $rsp;
        }

        $rsp['num'] = count($list);
        foreach ($list as $row){
            $arr_where['f_price'] = $row['f_price'];
            $arr_where['f_state'] = $state;
            $arr_where['f_type'] = $type;
            $arr_where['f_coin_id'] = $coin_id;
            $a = $this->Model_t_bdeal->find_by_attributes(
                $conn,
                $select = 'sum(f_pre_deal_vol)',
                $tablename,
                $where = $arr_where,
                $sort = NULL
            );
            $tmp = array(
                'type' => $type,
                'money' => $row['f_price'],
                'num' => $a['sum(f_pre_deal_vol)'],
            );
            array_push($rsp['rows'],$tmp);
        }
        return $rsp;
    }

    // 获取价格不同的成交单
    public function get_deal_diff_price($conn,$state,$type,$coin_id,$sort,$num)
    {
        $rsp = array(
            'num' => 0,
            'rows' => array(),
        );
        $tablename = $this->Model_t_deal->get_tablename();
        $list = $this->Model_t_deal->get_distinct_price($conn,$state,$type,$coin_id,$sort,$num);
        if (!$list){
            cilog('error',"当前找不到待成交的大单!");
            return $rsp;
        }

        $rsp['num'] = count($list);
        foreach ($list as $row){
            $arr_where['f_money'] = $row['f_money'];
            $a = $this->Model_t_deal->find_by_attributes(
                $conn,
                $select = 'sum(f_num)',
                $tablename,
                $where = $arr_where,
                $sort = NULL
            );
            $tmp = array(
                'type' => $type,
                'money' => number_format($row['f_money'],3,'.',''),
                'num' => number_format($a['sum(f_num)'],3,'.',''),
            );
            array_push($rsp['rows'],$tmp);
        }
        return $rsp;
    }

    // 获取需要成交的挂单信息,写入redis
    public function write_bdeal_list_during_2_redis($conn,$type,$coininfo,$num=10)
    {
        $state = $this->state['DEAL_DURING'];
        $key = $this->deal_redis_key['BDEAL_GOING_TO_DEAL'].$coininfo['f_coin_id']."_".$type;
        // 从db中获取
        if($type == $this->type['BUY']){
            $sort = "f_price desc";
        }elseif($type == $this->type['SELL']){
            $sort = "f_price asc";
        }

        $arr_deal_list = $this->Model_t_bdeal->find_all(
            $conn,
            $select=NULL,
            $tablename=$this->Model_t_bdeal->get_tablename(),
            $where = array(
                'f_coin_id' => $coininfo['f_coin_id'],
                'f_type' => $type,
                'f_state' => $state
            ),
            $limit = $num,
            $page = 1,
            $sort = $sort
        );
        $str_deal_list = serialize($arr_deal_list);
        $this->cache->redis->save($key,$str_deal_list,86400);
    }

    // 从redis中获取待成交的列表
    public function get_bdeal_list_during_2_redis($conn,$type,$coininfo,$num=10)
    {
        $state = $this->state['DEAL_DURING'];
        $key = $this->deal_redis_key['BDEAL_GOING_TO_DEAL'].$coininfo['f_coin_id']."_".$type;
        $str_deal_list = $this->cache->redis->get($key);
        if(!$str_deal_list){
            // 从db中获取
            if($type == $this->type['BUY']){
                $sort = "f_price desc";
            }elseif($type == $this->type['SELL']){
                $sort = "f_price asc";
            }

            $arr_deal_list = $this->Model_t_bdeal->find_all(
                $conn,
                $select=NULL,
                $tablename=$this->Model_t_bdeal->get_tablename(),
                $where = array(
                    'f_coin_id' => $coininfo['f_coin_id'],
                    'f_type' => $type,
                    'f_state' => $state
                ),
                $limit = $num,
                $page = 1,
                $sort = $sort
            );
            $str_deal_list = serialize($arr_deal_list);
            $this->cache->redis->save($key,$str_deal_list,86400);
            return $arr_deal_list;
        }
        return unserialize($str_deal_list);
    }

    /**
     * 获取韩元与美元的兑换率
     */
    public function get_krw_rate()
    {
        $key = "krw_price_rate";
        $value = $this->cache->redis->get($key);
        if(!$value){
            require_once APPPATH . '/libraries/comm/http.php';
            $http = new Http();
            $url = "http://service.fx168.com/financeQQ/Forex/GetAjaxData.aspx?page=fx168&getType=1&code=KRD";
            $rsp = $http->get($url);
            $value = $rsp;
            $this->cache->redis->save($key,$rsp,86400);
        }
        return $value;
    }

    /**
     * @fun   创建买入大单
     */
    public function add_buy_bdeal($conn,$bdeal_id,$uin,$coin_info,$buy_price,$buy_num)
    {
        $conn->trans_start();
        $coin_id = $coin_info['f_coin_id'];
        $coin_name = $coin_info['f_abbreviation'];

        $need = $buy_num * $buy_price;
        $bdeal_info = array(
            'f_bdeal_id'        => $bdeal_id,
            'f_uin'             => $uin,
            'f_type'            => $this->type['BUY'],
            'f_coin_id'         => $coin_id,
            'f_coin_name'       => $coin_name,
            'f_total_money'     => $need,
            'f_total_vol'       => $buy_num,
            'f_pre_deal_money'  => $need,
            'f_pre_deal_vol'    => $buy_num,
            'f_price'           => $buy_price,
            'f_state'           => $this->state['DEAL_DURING'],
            'f_export_id'       => $this->todealid($bdeal_id,$uin),
        );
        $this->Model_t_bdeal->add_bdeal($conn,$uin,$coin_id,$bdeal_info);

        $btc_finance_info = $this->finance_service->get_market_finance($conn,$uin);
        if($need > $btc_finance_info['f_can_use_vol']){
            cilog('error',"挂单失败,用户余额不足! [need:$need] [left:{$btc_finance_info['f_can_use_vol']}]");
            return $this->finance_service->finance_errcode['FINANCE_HAVE_NO_ENOUCH_COIN'];
        }
        $attributes = array(
            'f_freeze_vol'  => $btc_finance_info['f_freeze_vol'] + $need,
            'f_can_use_vol' => $btc_finance_info['f_can_use_vol'] - $need,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);
        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"挂单,买入虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"挂单,买入虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return 0;
        }
    }

    /**
     * @fun   创建卖出大单
     */
    public function add_sell_bdeal($conn,$bdeal_id,$uin,$coin_info,$sell_price,$sell_num)
    {
        $conn->trans_start();
        $coin_id = $coin_info['f_coin_id'];
        $coin_name = $coin_info['f_abbreviation'];
        $need = $sell_price * $sell_num;
        $bdeal_info = array(
            'f_bdeal_id'        => $bdeal_id,
            'f_uin'             => $uin,
            'f_type'            => $this->type['SELL'],
            'f_coin_id'         => $coin_id,
            'f_coin_name'       => $coin_name,
            'f_total_money'     => $need,
            'f_total_vol'       => $sell_num,
            'f_pre_deal_money'  => $need,
            'f_pre_deal_vol'    => $sell_num,
            'f_price'           => $sell_price,
            'f_state'           => $this->state['DEAL_DURING'],
            'f_export_id'       => $this->todealid($bdeal_id,$uin),
        );
        $this->Model_t_bdeal->add_bdeal($conn,$uin,$coin_id,$bdeal_info);

        $finance_info = $this->finance_service->get_market_finance($conn,$uin);
        if($sell_num > $finance_info['f_can_use_vol']){
            cilog('error',"挂单失败,用户余额不足! [need:$need] [left:{$finance_info['f_can_use_vol']}]");
            return $this->finance_service->finance_errcode['FINANCE_HAVE_NO_ENOUCH_COIN'];
        }
        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] + $sell_num,
            'f_can_use_vol' => $finance_info['f_can_use_vol'] - $sell_num,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);
        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"挂单,买入虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"挂单,买入虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return 0;
        }
    }

    /**
     * @fun   买单 小单全部成交
     */
    public function fill_buy_bdeal_all_done($conn,$uin,$coin_info,$bdealinfo,$deal_price)
    {
        $conn->trans_start();
        $coin_id = $coin_info['f_coin_id'];
        $bdealid = $bdealinfo['f_bdeal_id'];
        $deal_num = $bdealinfo['f_pre_deal_vol'];
        if($bdealinfo['f_state'] == $this->state['DEAL_DONE']){
            cilog('debug',"当前大单状态为已完成,无需处理! [bdealid:{$bdealid}] [state:{$bdealinfo['f_state']}]");
            return 0;
        }

        // 添加小单
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'DEAL_ID');
        if(!$deal_id){
            cilog('error',"获取小单id错误!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        $tablename = $this->Model_t_deal->get_tablename();
        $deal_info = array(
            'f_deal_id'     => $deal_id,
            'f_uin'         => $uin,
            'f_bdeal_id'    => $bdealid,
            'f_type'        => $this->type['BUY'],
            'f_coin_id'     => $coin_id,
            'f_num'         => $deal_num,
            'f_money'       => $deal_num * $deal_price,
            'f_state'       => $this->state['DEAL_DONE'],
            'f_commission'  => 0,
            'f_order_id'    => $this->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $this->Model_t_deal->save($conn,$tablename,$deal_info);

        // 修改大单状态为交易完成
        $attributes = array(
            'f_state'           => $this->state['DEAL_DONE'],
            'f_pre_deal_vol'    => 0,
            'f_pre_deal_money'  => $bdealinfo['f_pre_deal_money'] - ($deal_num * $deal_price),
            'f_post_deal_vol'   => $bdealinfo['f_post_deal_vol'] + $deal_num,
            'f_post_deal_money' => $bdealinfo['f_post_deal_money'] + $deal_num * $deal_price,
        );
        $this->Model_t_bdeal->update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes);

        // 实扣比特币冻结的财务信息
        $btc_finance_info = $this->finance_service->get_market_finance($conn,$uin);
        $attributes = array(
            'f_freeze_vol'  => $btc_finance_info['f_freeze_vol'] - $deal_info['f_money'],
            'f_can_use_vol' => $btc_finance_info['f_can_use_vol'] + $bdealinfo['f_pre_deal_money'] - ($deal_num * $deal_price),
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 增加购买成功的该虚拟币信息
        $finance_info = $this->finance_service->get_market_finance($conn,$uin,$coin_id);
        $attributes = array(
            'f_can_use_vol'  => $finance_info['f_can_use_vol'] + $deal_num,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 更新币种信息
        $this->load->service('coin/coin_service');
        $where = array('f_coin_id' => $coin_id);
        $attributes = array(
            'f_last_price' => $deal_price,
        );
        $this->coin_service->update_coin_info($conn, $attributes, $where);

        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"成交小单,买单全部成交虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"成交小单,买单全部成交虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}]");
            return 0;
        }
    }

    /**
     * 买入单,部分成交
     */
    public function fill_buy_bdeal_all_part($conn,$uin,$coin_info,$bdealinfo,$deal_price,$deal_num)
    {
        $conn->trans_start();
        $coin_id = $coin_info['f_coin_id'];
        $bdealid = $bdealinfo['f_bdeal_id'];
        if($bdealinfo['f_state'] == $this->state['DEAL_DONE']){
            cilog('debug',"当前大单状态为已完成,无需处理! [bdealid:{$bdealid}] [state:{$bdealinfo['f_state']}]");
            return 0;
        }

        // 添加小单
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'DEAL_ID');
        if(!$deal_id){
            cilog('error',"获取小单id错误!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        if(!$deal_id){
            cilog('error',"获取小单id错误!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        $tablename = $this->Model_t_deal->get_tablename();
        $deal_info = array(
            'f_deal_id'     => $deal_id,
            'f_uin'         => $uin,
            'f_bdeal_id'    => $bdealid,
            'f_type'        => $this->type['SELL'],
            'f_coin_id'     => $coin_id,
            'f_num'         => $deal_num,
            'f_money'       => $deal_num * $deal_price,
            'f_state'       => $this->state['DEAL_DONE'],
            'f_commission'  => 0,
            'f_order_id'    => $this->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $this->Model_t_deal->save($conn,$tablename,$deal_info);

        // 修改大单状态为交易中
        $attributes = array(
            'f_state'           => $this->state['DEAL_DURING'],
            'f_pre_deal_vol'    => $bdealinfo['f_pre_deal_vol'] - $deal_num,
            'f_pre_deal_money'  => $bdealinfo['f_pre_deal_money'] - ($deal_num * $deal_price),
            'f_post_deal_vol'   => $bdealinfo['f_post_deal_vol'] + $deal_num,
            'f_post_deal_money' => $bdealinfo['f_post_deal_money'] + $deal_num * $deal_price,
        );
        $this->Model_t_bdeal->update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes);

        // 实扣比特币冻结的财务信息
        $btc_finance_info = $this->finance_service->get_market_finance($conn,$uin);
        $attributes = array(
            'f_freeze_vol'  => $btc_finance_info['f_freeze_vol'] - $deal_info['f_money'],
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 增加购买成功的该虚拟币信息
        $finance_info = $this->finance_service->get_market_finance($conn,$uin,$coin_id);
        $attributes = array(
            'f_can_use_vol'  => $finance_info['f_can_use_vol'] + $deal_num,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 更新币种信息
        $this->load->service('coin/coin_service');
        $where = array('f_coin_id' => $coin_id);
        $attributes = array(
            'f_last_price' => $deal_price,
        );
        $this->coin_service->update_coin_info($conn, $attributes, $where);

        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"成交小单,买单部分成交虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"成交小单,买单部分成交虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}]");
            return 0;
        }
    }

    /**
     * @fun   卖单 小单全部成交
     */
    public function fill_sell_deal_done($conn,$uin,$coin_info,$bdealinfo,$deal_price)
    {
        $conn->trans_start();
        $coin_id = $coin_info['f_coin_id'];
        $bdealid = $bdealinfo['f_bdeal_id'];
        $deal_num = $bdealinfo['f_pre_deal_vol'];
        if($bdealinfo['f_state'] == $this->state['DEAL_DONE']){
            cilog('debug',"当前大单状态为已完成,无需处理! [bdealid:{$bdealid}] [state:{$bdealinfo['f_state']}]");
            return 0;
        }

        // 添加小单
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'DEAL_ID');
        if(!$deal_id){
            cilog('error',"获取小单id错误!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        $tablename = $this->Model_t_deal->get_tablename();
        $deal_info = array(
            'f_deal_id'     => $deal_id,
            'f_uin'         => $uin,
            'f_bdeal_id'    => $bdealid,
            'f_type'        => $this->type['SELL'],
            'f_coin_id'     => $coin_id,
            'f_num'         => $deal_num,
            'f_money'       => $deal_num * $deal_price,
            'f_state'       => $this->state['DEAL_DONE'],
            'f_commission'  => $deal_num * $deal_price * $coin_info['f_commission'] / 100,
            'f_order_id'    => $this->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $this->Model_t_deal->save($conn,$tablename,$deal_info);

        // 修改大单状态为交易完成
        $attributes = array(
            'f_state'           => $this->state['DEAL_DONE'],
            'f_pre_deal_vol'    => 0,
            'f_pre_deal_money'  => $bdealinfo['f_pre_deal_money'] - ($deal_num * $deal_price),
            'f_post_deal_vol'   => $bdealinfo['f_post_deal_vol'] + $deal_num,
            'f_post_deal_money' => $bdealinfo['f_post_deal_money'] + $deal_num * $deal_price,
        );
        $this->Model_t_bdeal->update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes);

        // 实扣冻结虚拟币财务信息
        $finance_info = $this->finance_service->get_market_finance($conn,$uin,$coin_id);
        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] - $deal_num,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 实际增加比特币财务信息
        $btc_finance_info = $this->finance_service->get_market_finance($conn,$uin);
        $attributes = array(
            'f_can_use_vol' => $btc_finance_info['f_can_use_vol'],
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 更新币种信息
        $this->load->service('coin/coin_service');
        $where = array('f_coin_id' => $coin_id);
        $attributes = array(
            'f_last_price' => $deal_price,
        );
        $this->coin_service->update_coin_info($conn, $attributes, $where);

        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"成交小单,卖单全部成交虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"成交小单,卖单全部成交虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}]");
            return 0;
        }
    }

    /**
     * @fun   卖单 小单部分成交
     */
    public function fill_sell_deal_part($conn,$uin,$coin_info,$bdealinfo,$deal_price,$deal_num)
    {
        $conn->trans_start();
        $coin_id = $coin_info['f_coin_id'];
        $bdealid = $bdealinfo['f_bdeal_id'];
        if($bdealinfo['f_state'] == $this->state['DEAL_DONE']){
            cilog('debug',"当前大单状态为已完成,无需处理! [bdealid:{$bdealid}] [state:{$bdealinfo['f_state']}]");
            return 0;
        }

        // 添加小单
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'DEAL_ID');
        if(!$deal_id){
            cilog('error',"获取小单id错误!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        $tablename = $this->Model_t_deal->get_tablename();
        $deal_info = array(
            'f_deal_id'     => $deal_id,
            'f_uin'         => $uin,
            'f_bdeal_id'    => $bdealid,
            'f_type'        => $this->type['SELL'],
            'f_coin_id'     => $coin_id,
            'f_num'         => $deal_num,
            'f_money'       => $deal_num * $deal_price,
            'f_state'       => $this->state['DEAL_DURING'],
            'f_commission'  => $deal_num * $deal_price * $coin_info['f_commission'] / 100,
            'f_order_id'    => $this->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $this->Model_t_deal->save($conn,$tablename,$deal_info);

        // 修改大单状态为交易完成
        $attributes = array(
            'f_state'           => $this->state['DEAL_DURING'],
            'f_pre_deal_vol'    => $bdealinfo['f_pre_deal_vol'] - $deal_num,
            'f_pre_deal_money'  => $bdealinfo['f_pre_deal_money'] - ($deal_num * $deal_price),
            'f_post_deal_vol'   => $bdealinfo['f_post_deal_vol'] + $deal_num,
            'f_post_deal_money' => $bdealinfo['f_post_deal_money'] + $deal_num * $deal_price,
        );
        $this->Model_t_bdeal->update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes);

        // 实扣冻结虚拟币财务信息
        $finance_info = $this->finance_service->get_market_finance($conn,$uin,$coin_id);
        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] - $deal_num,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 实际增加比特币财务信息
        $btc_finance_info = $this->finance_service->get_market_finance($conn,$uin);
        $attributes = array(
            'f_can_use_vol' => $btc_finance_info['f_can_use_vol'],
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 更新币种信息
        $this->load->service('coin/coin_service');
        $where = array('f_coin_id' => $coin_id);
        $attributes = array(
            'f_last_price' => $deal_price,
        );
        $this->coin_service->update_coin_info($conn, $attributes, $where);

        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"成交小单,卖单部分成交虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"成交小单,卖单部分成交虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}]");
            return 0;
        }
    }


    /**
     * 买入委托
     */
    public function buy_order($conn,$uin,$coin_info,$buy_price,$buy_num)
    {
        $coin_id = $coin_info['f_coin_id'];
        $aQuery = array(
            'f_type' => $this->type['SELL'],
            'f_state' => $this->state['DEAL_DURING']
        );
        $sort = 'f_price asc';
        $sell_one_list = $this->Model_t_bdeal->get_bdeal_list_by_coin_id($conn,$coin_id,1,10,$aQuery,$sort);
        cilog('debug',"[ 1 ] 获取卖一单信息  卖单 价格低到高,获取卖一单信息成功!");

        $this->load->model("conf/Model_t_idmaker");
        $this->load->service("finance/finance_service");
        $bdeal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'BDEAL_ID');
        if(!$bdeal_id){
            cilog('error',"获取大单id错误!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }

        $flag = $this->add_buy_bdeal($conn,$bdeal_id,$uin,$coin_info,$buy_price,$buy_num);
        if($flag !== 0){
            cilog('error',"创建大单失败!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        cilog('debug',"[ 2 ] 获取大单id成功,同时生成大单! [bdealid:{$bdeal_id}]");


        if((int)$sell_one_list['total'] === 0){
            cilog('debug',"当前没有买入单信息,直接挂单");
            return 0;
        }

        $sell_one_vol = 0;
        foreach ($sell_one_list['rows'] as $row){
            $row['f_pre_deal_vol'] = isset($row['f_pre_deal_vol']) ? $row['f_pre_deal_vol'] : 0;
            $sell_one_vol += $row['f_pre_deal_vol'];
        }

        $sell_one_price = $sell_one_list['rows'][0]['f_price'];
        if ($buy_price < $sell_one_price){
            cilog('debug',"买单价格小于卖一单价格,无法成交,直接挂单! [buy_price:{$buy_price}] [sell_price:{$sell_one_price}]");
            return 0;
        }

        $deal_price = $sell_one_price;
        $bdealinfo = $this->Model_t_bdeal->get_bdeal_info_by_bdealid($conn,$bdeal_id,$aQuery=array());
        cilog('debug',"[ 3 ] 卖单价大于或者等于卖一价,开始交易! [buy_price:{$buy_price}] [sell_price:{$sell_one_price}] [bdealid:{$bdeal_id}] [deal_price:{$deal_price}]");

        if($buy_num <= $sell_one_vol){
            cilog('debug',"当前买单数量小于或等于卖一单数量,当前买单完全成交!");
            cilog('debug',"当前买单全部完成交易 [bdealid:{$bdealinfo['f_bdeal_id']}] [deal_num:{$bdealinfo['f_pre_deal_vol']}]");
            $flag = $this->fill_buy_bdeal_all_done($conn,$uin,$coin_info,$bdealinfo,$deal_price);
            if($flag !==0 ){
                cilog('error',"买单失败!");
                return $flag;
            }
            // 卖单部分提交
            foreach ($sell_one_list['rows'] as $row){
                if($buy_num > $row['f_pre_deal_vol']){
                    cilog('debug',"当前卖单全部完成交易 [bdealid:{$row['f_bdeal_id']}] [deal_num:{$row['f_pre_deal_vol']}]");
                    $flag = $this->fill_sell_deal_done($conn,$uin,$coin_info,$row,$deal_price);
                    if($flag !==0 ){
                        cilog('error',"买单失败!");
                        return $flag;
                    }
                    $buy_num -= $row['f_pre_deal_vol'];
                }else{
                    cilog('debug',"当前卖单部分完成交易 [bdealid:{$row['f_bdeal_id']}] [deal_num:{$row['f_pre_deal_vol']}]");
                    $flag = $this->fill_sell_deal_part($conn,$uin,$coin_info,$row,$deal_price,$buy_num);
                    return $flag;
                }
            }
        }else{
            cilog('debug',"当前买单数量大于卖一单数量,当前卖单完全成交, 买单部分成交!");
            foreach ($sell_one_list['rows'] as $row){
                cilog('debug',"当前卖单全部完成交易 [bdealid:{$row['f_bdeal_id']}] [deal_num:{$row['f_pre_deal_vol']}]");
                $flag = $this->fill_sell_deal_done($conn,$uin,$coin_info,$row,$deal_price);
                if($flag !==0 ){
                    cilog('error',"买单失败!");
                    return $flag;
                }
            }
            cilog('debug',"当前买单部分完成交易 [bdealid:{$bdealinfo['f_bdeal_id']}] [deal_num:{$bdealinfo['f_pre_deal_vol']}]");
            $flag = $this->fill_buy_bdeal_all_part($conn,$uin,$coin_info,$bdealinfo,$deal_price,$sell_one_vol);
            if($flag !==0 ){
                return $flag;
            }
        }
    }

    /**
     * 卖出委托
     */
    public function sell_order($conn,$uin,$coin_info,$sell_price,$sell_num)
    {
        // 1. 获取买一单信息  买单 价格高到低
        $coin_id = $coin_info['f_coin_id'];
        $aQuery = array(
            'f_type' => $this->type['BUY'],
            'f_state' => $this->state['DEAL_DURING']
        );
        $sort = 'f_price desc';
        $buy_one_list = $this->Model_t_bdeal->get_bdeal_list_by_coin_id($conn,$coin_id,1,10,$aQuery,$sort);
        cilog('debug',"[ 1 ] 获取买一单信息  买单 价格高到低,获取卖一单信息成功!");

        $this->load->model("conf/Model_t_idmaker");
        $this->load->service("finance/finance_service");
        $bdeal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'BDEAL_ID');
        if(!$bdeal_id){
            cilog('error',"获取大单id错误!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        $flag = $this->add_sell_bdeal($conn,$bdeal_id,$uin,$coin_info,$sell_price,$sell_num);
        if($flag !== 0){
            cilog('error',"创建大单失败!");
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        cilog('debug',"[ 2 ] 创建大单成功,[bdealid:{$bdeal_id}]");

        if((int)$buy_one_list['total'] === 0){
            cilog('debug',"当前没有买入单信息,直接挂单");
            return 0;
        }

        $buy_one_vol = 0;
        foreach ($buy_one_list['rows'] as $row){
            $row['f_pre_deal_vol'] = isset($row['f_pre_deal_vol']) ? $row['f_pre_deal_vol'] : 0;
            $buy_one_vol += $row['f_pre_deal_vol'];
        }

        $buy_one_price = $buy_one_list['rows'][0]['f_price'];
        if ($sell_price > $buy_one_price){
            cilog('debug',"卖单价格大于买一单价格,无法成交,直接挂单! [sell_price:{$sell_price}] [buy_price:{$buy_one_price}]");
            return 0;
        }
        $deal_price = $buy_one_price;
        $bdealinfo = $this->Model_t_bdeal->get_bdeal_info_by_bdealid($conn,$bdeal_id,$aQuery=array());
        cilog('debug',"[ 3 ] 卖单价小于买一价,开始交易! [buy_price:{$buy_one_price}] [sell_price:{$sell_price}] [bdealid:{$bdeal_id}] [deal_price:{$deal_price}]");

        if($sell_num > $buy_one_vol){
            cilog('debug',"买单全部完成 卖单部分完成");
            foreach ($buy_one_list['rows'] as $row){
                cilog('debug',"当前买单全部完成交易 [bdealid:{$row['f_bdeal_id']}] [deal_num:{$row['f_pre_deal_vol']}]");
                $flag = $this->fill_buy_bdeal_all_done($conn,$uin,$coin_info,$row,$deal_price);
                if($flag !==0 ){
                    cilog('error',"卖单失败!");
                    return $flag;
                }
            }
            cilog('debug',"当前卖单部分完成交易 [bdealid:{$bdealinfo['f_bdeal_id']}] [deal_num:{$buy_one_vol}]");
            $flag = $this->fill_sell_deal_part($conn,$uin,$coin_info,$bdealinfo,$deal_price,$buy_one_vol);
            if($flag !==0 ){
                return $flag;
            }
        }else{
            cilog('debug',"当前卖单全部完成交易 [bdealid:{$bdealinfo['f_bdeal_id']}] [deal_num:{$bdealinfo['f_pre_deal_vol']}]");
            $flag = $this->fill_sell_deal_done($conn,$uin,$coin_info,$bdealinfo,$deal_price);
            if($flag !==0 ){
                return $flag;
            }
            foreach ($buy_one_list['rows'] as $row){
                if($sell_num > $row['f_pre_deal_vol']){
                    cilog('debug',"当前卖单全部完成交易 [bdealid:{$row['f_bdeal_id']}] [deal_num:{$row['f_pre_deal_vol']}]");
                    $flag = $this->fill_sell_deal_done($conn,$uin,$coin_info,$row,$deal_price);
                    if($flag !==0 ){
                        cilog('error',"卖单失败!");
                        return $flag;
                    }
                    $sell_num -= $row['f_pre_deal_vol'];
                }else{
                    cilog('debug',"当前卖单部分完成交易 [bdealid:{$row['f_bdeal_id']}] [deal_num:{$sell_num}]");
                    return $this->fill_sell_deal_part($conn,$uin,$coin_info,$row,$deal_price,$sell_num);
                }
            }
        }
    }

    /**
     * 取消买单   回退比特币
     */
    public function cancel_buy_bdeal($conn,$userinfo,$bdealinfo,$market_finance_info)
    {
        cilog('error',"开启取消大单记录 [uin:{$userinfo['f_uin']}] [bdealid:{$bdealinfo['f_bdeal_id']}]");

        $this->load->service("finance/finance_service");
        $uin = $bdealinfo['f_uin'];
        $coin_id = $bdealinfo['f_coin_id'];
        $left_coin_money = $bdealinfo['f_pre_deal_vol'] * $bdealinfo['f_price'];
        $bdealid = $bdealinfo['f_bdeal_id'];

        // 判断大单状态
        if($bdealinfo['f_state'] == $this->state['DEAL_CANCEL']){
            cilog('error',"该大单状态不合法! [state:{$bdealinfo['f_state']}]");
            return $this->deal_errcode['DEAL_BDEAL_STATE_NOT_DURING'];
        }

        // 判断大单是否属于该用户
        if($bdealinfo['f_uin'] != $userinfo['f_uin']){
            cilog('error',"该大单不属于该用户! [uin:{$bdealinfo['f_uin']}]");
            return $this->deal_errcode['DEAL_BDEAL_UIN_NOT_SANME'];
        }

        // 验证当前单据状态为买入
        if($bdealinfo['f_type'] != $this->type['BUY']){
            cilog('error',"该大单不为卖出类型! [type:{$bdealinfo['f_type']}]");
            return $this->deal_errcode['DEAL_BDEAL_TYPE_ERR'];
        }

        if((int)$coin_id === 10001){
            cilog('error','参数错误,比特币不参加卖出!');
            return $this->coin_service->coin_errcode['DEAL_PARAM_ERR'];
        }

        // 开始取消操作
        $conn->trans_start();
        // 回退比特币财务信息
        $attributes = array(
            'f_freeze_vol'  => $market_finance_info['f_freeze_vol'] - $left_coin_money,
            'f_can_use_vol' => $market_finance_info['f_can_use_vol'] + $left_coin_money,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);

        // 扭转大单状态
        $attributes = array(
            'f_state' => $this->state['DEAL_CANCEL'],
        );
        $this->Model_t_bdeal->update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes);
        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"取消委托失败,开始回滚数据! [uin:{$uin}] [coinid:{$coin_id}] [bdealid:{$bdealid}]");
            return $this->deal_errcode['DEAL_CANCEL_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"取消委托成功! [uin:{$uin}] [coinid:{$coin_id}] [bdealid:{$bdealid}]");
            return 0;
        }
    }

    /**
     * @fun  取消卖单   回退比特币  回退当前币种
     */
    public function cancel_sell_bdeal($conn,$userinfo,$bdealinfo,$market_finance_info,$finance_info,$coininfo)
    {
        $this->load->service("finance/finance_service");
        $uin = $bdealinfo['f_uin'];
        $coin_id = $bdealinfo['f_coin_id'];
        $left_coin_vol = $bdealinfo['f_pre_deal_vol'];
        $price = $bdealinfo['f_price'];
        $left_commission = $this->get_trade_commission($coininfo,$left_coin_vol,$price);
        $bdealid = $bdealinfo['f_bdeal_id'];

        // 判断大单状态
        if($bdealinfo['f_state'] == $this->state['DEAL_CANCEL']){
            cilog('error',"该大单状态不合法! [state:{$bdealinfo['f_state']}]");
            return $this->deal_errcode['DEAL_BDEAL_STATE_NOT_DURING'];
        }

        // 判断大单是否属于该用户
        if($bdealinfo['f_uin'] != $userinfo['f_uin']){
            cilog('error',"该大单不属于该用户! [uin:{$bdealinfo['f_uin']}]");
            return $this->deal_errcode['DEAL_BDEAL_UIN_NOT_SANME'];
        }

        // 验证当前单据状态为卖出
        if($bdealinfo['f_type'] != $this->type['SELL']){
            cilog('error',"该大单不为卖出类型! [type:{$bdealinfo['f_type']}]");
            return $this->deal_errcode['DEAL_BDEAL_TYPE_ERR'];
        }

        if((int)$coin_id === 10001){
            cilog('error','参数错误,比特币不参加卖出!');
            return $this->coin_service->coin_errcode['DEAL_PARAM_ERR'];
        }

        // 开始取消操作
        $conn->trans_start();

        // 回退当前币种
        $attributes = array(
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] - $left_coin_vol,
            'f_can_use_vol' => $finance_info['f_can_use_vol'] + $left_coin_vol,
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 扭转大单状态
        $attributes = array(
            'f_state' => $this->state['DEAL_CANCEL'],
        );
        $this->Model_t_bdeal->update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes);
        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"取消委托失败,开始回滚数据! [uin:{$uin}] [coinid:{$coin_id}] [bdealid:{$bdealid}]");
            return $this->deal_errcode['DEAL_CANCEL_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"取消委托成功! [uin:{$uin}] [coinid:{$coin_id}] [bdealid:{$bdealid}]");
            return 0;
        }
    }

    /**
     * 提交买入委托申请
     */
    public function submit_buy_bdeal($conn,$uin,$coin_info,$buy_price,$buy_num)
    {
        $conn->trans_start();
        $coin_id = $coin_info['f_coin_id'];
        $coin_name = $coin_info['f_abbreviation'];

        $need = $buy_num * $buy_price;
        $this->load->model("conf/Model_t_idmaker");
        $this->load->service("finance/finance_service");
        $bdeal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'BDEAL_ID');
        $bdeal_info = array(
            'f_bdeal_id'        => $bdeal_id,
            'f_uin'             => $uin,
            'f_type'            => $this->type['BUY'],
            'f_coin_id'         => $coin_id,
            'f_coin_name'       => $coin_name,
            'f_total_money'     => $need,
            'f_total_vol'       => $buy_num,
            'f_pre_deal_money'  => $need,
            'f_pre_deal_vol'    => $buy_num,
            'f_price'           => $buy_price,
            'f_state'           => $this->state['DEAL_DURING'],
            'f_export_id'       => $this->todealid($bdeal_id,$uin),
        );
        $this->Model_t_bdeal->add_bdeal($conn,$uin,$coin_id,$bdeal_info);

        $btc_finance_info = $this->finance_service->Model_t_finance_info->get_finance_without_cache($conn,$uin,10001);
        if($need > $btc_finance_info['f_can_use_vol']){
            cilog('error',"挂单失败,用户余额不足! [need:$need] [left:{$btc_finance_info['f_can_use_vol']}]");
            return $this->finance_service->finance_errcode['FINANCE_HAVE_NO_ENOUCH_COIN'];
        }
        $attributes = array(
            'f_freeze_vol'  => math_add($btc_finance_info['f_freeze_vol'],$need),
            'f_can_use_vol' => math_sub($btc_finance_info['f_can_use_vol'],$need),
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);
        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"提交挂单申请失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"提交挂单申请成功! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return 0;
        }

    }

    /**
     * 提交卖出申请
     */
    public function submit_sell_bdeal($conn,$uin,$coin_info,$sell_price,$sell_num)
    {
        $conn->trans_start();
        $coin_id = $coin_info['f_coin_id'];
        $coin_name = $coin_info['f_abbreviation'];
        $need = $sell_price * $sell_num;

        $this->load->model("conf/Model_t_idmaker");
        $this->load->service("finance/finance_service");
        $bdeal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'BDEAL_ID');
        $bdeal_info = array(
            'f_bdeal_id'        => $bdeal_id,
            'f_uin'             => $uin,
            'f_type'            => $this->type['SELL'],
            'f_coin_id'         => $coin_id,
            'f_coin_name'       => $coin_name,
            'f_total_money'     => $need,
            'f_total_vol'       => $sell_num,
            'f_pre_deal_money'  => $need,
            'f_pre_deal_vol'    => $sell_num,
            'f_price'           => $sell_price,
            'f_post_time'       => get_microtime(),
            'f_state'           => $this->state['DEAL_DURING'],
            'f_export_id'       => $this->todealid($bdeal_id,$uin),
        );
        $this->Model_t_bdeal->add_bdeal($conn,$uin,$coin_id,$bdeal_info);

        $finance_info = $this->finance_service->Model_t_finance_info->get_finance_without_cache($conn,$uin,$coin_id);
        if($sell_num > $finance_info['f_can_use_vol']){
            cilog('error',"挂单失败,用户余额不足! [need:$need] [left:{$finance_info['f_can_use_vol']}]");
            return $this->finance_service->finance_errcode['FINANCE_HAVE_NO_ENOUCH_COIN'];
        }
        $attributes = array(
            'f_freeze_vol'  => math_add($finance_info['f_freeze_vol'],$sell_num),
            'f_can_use_vol' => math_sub($finance_info['f_can_use_vol'],$sell_num),
        );
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);
        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"挂单,买入虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"挂单,买入虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [bdealid:{$bdeal_id}]");
            return 0;
        }
    }
}