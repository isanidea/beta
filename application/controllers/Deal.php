<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class deal  交易模块
 */

require_once APPPATH . '/libraries/comm/captcha.php';
class Deal extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
         $this->conn = $this->load->database('trade_user',TRUE);
         $this->load->service('deal/deal_service');
    }

    // 交易中心页面
    public function pDealcent()
    {
        $this->init_log();
        $this->init_page();
        $this->load->service("coin/coin_service");

//        $trade_coin_id = get_post_valueI('coinid');
//        $trade_coin_id = ($trade_coin_id === 0) ? 10001 : $trade_coin_id;
//        $trade_coin_info = $this->coin_service->get_coin_info($this->conn, $trade_coin_id);
//        if(!is_array($trade_coin_info)){
//            show_404();
//        }

        // 获取比特币兑换美元韩元价格
        $coin_id = 10001;
        $coin_info = $this->coin_service->get_coin_info($this->conn, $coin_id);
        $price_usd = $coin_info['f_last_price'];
        $price_krw = $price_usd * $this->deal_service->get_krw_rate();

        // 填充数据到页面
        $this->load->view('deal/dealcent',array(
            'usd'=>number_format($price_usd,2),
            'krw'=>number_format($price_krw,2),
        ));
    }

    // 大单详情
    public function pBdealInfo()
    {
        $this->init_log();
        $this->init_page();

        $flag = $this->user_service->check_online($this->oSession);
        if ($flag != 0){
            // 页面未登录,停止后面的操作
            $this->go_loginout();
        }else{
            $this->load->view('deal/deal_info');
        }
    }

    /**
     * @fun     获取大单详情
     * @param   bdealid    大单id
     */
    public function get_entrust_info()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $bdealid = get_post_value('bdealid'); // 对外开放的id

        if($bdealid === 0){
            cilog('error',"参数错误,bdeal_id 为空");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $bdeal_info = $this->deal_service->get_bdeal_info($this->conn,$bdealid);
        if (!is_array($bdeal_info)){
            render_json(0);
        }

        $rsp = array(
            'create_time' => $bdeal_info['f_create_time'],
            'type' => $bdeal_info['f_type'],
            'coin_name' => $bdeal_info['f_coin_name'],
            'money' => $bdeal_info['f_total_money'],
            'vol' => $bdeal_info['f_total_vol'],
            'preDealVol' => $bdeal_info['f_pre_deal_vol'],
            'preDealMoney' => $bdeal_info['f_pre_deal_money'],
            'postDealVol' => $bdeal_info['f_post_deal_vol'],
            'postDealMoney' => $bdeal_info['f_post_deal_money'],
            'state' => $bdeal_info['f_state'],
            'dealinfo' => array(),
        );

        // $bdeal_id = substr($bdealid,6,8);
        $bdeal_id = $bdeal_info['f_bdeal_id'];
        $deal_list = $this->deal_service->get_deal_list($this->conn,$bdeal_id,100, 0);
        if (!is_array($deal_list)){
            render_json(0,'',$rsp);
        }
        $out_deal_list = $this->deal_service->export_deal_list($deal_list['rows']);
        $rsp['dealinfo'] = $out_deal_list;

        render_json(0,'',$rsp);
    }

    /**
     * @fun     获取大单列表信息
     * @param   status    单据状态
     * @param   page       页码
     * @param   num        每页展示最大数据量
     */
    public function get_user_entrust()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');
        $state = get_post_valueI('status');
        $coin_id = get_post_valueI('coinid');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        if(!$this->oValidator->isNum($page)){
            cilog('error',"page 参数错误, [page:{$page}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isNum($num)){
            cilog('error',"num 参数错误, [num:{$num}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!in_array($state,array(1,2,3))) {
            cilog('error',"state 参数错误, [state:{$state}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(($coin_id < 10000) && ($coin_id !== 0)){
            cilog('error',"coinid 参数错误, [coinid:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }elseif($coin_id === 0){
            $coin_id = NULL;
        }

        $num = ($num === 0) ? 10 : $num;

        $bdeal_list = $this->deal_service->get_bdeal_list($this->conn,$uin,$state,$coin_id,$page,$num);
        if(!is_array($bdeal_list['rows']))
        {
            render_json_list();
        }

        $rsp = $this->deal_service->export_bdeal_list($bdeal_list['rows']);
        render_json_list(0,'',$bdeal_list['total'],$rsp);
    }

    /**
     * @fun     发起一个委托单
     * @param   type    委托类型
     * @param   id      币种id
     * @param   num     交易数量
     * @param   tpw     交易密码
     * @param   price   成交价格
     */
    public function post_user_entrust()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $type    = get_post_valueI('type');
        $coin_id = get_post_valueI('id');
        $vol     = get_post_value('num');
        $deal_pw = get_post_value('tpw');
        $price   = get_post_value('price');


        // 不允许交易的币种id
        $coin_id_list = array(10012);
        if (!in_array($coin_id,$coin_id_list)){
            cilog("error","该币种目前禁止交易! [coin_id:{$coin_id}]");
            render_json($this->deal_service->deal_errcode['DEAL_COINID_CAN_NOT_TRADE']);
        }

        $type_list = array(
            $this->deal_service->type['BUY'],
            $this->deal_service->type['SELL']
        );
        if(!in_array($type,$type_list)){
            cilog('error',"参数错误,委托单类型为空");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($coin_id < 10000){
            cilog('error',"币种id参数错误 [id:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($deal_pw)){
            cilog('error',"密码格式不对 [pw:{$deal_pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if((float)$price <= 0.00000001){
            cilog('error',"参数错误,单价不能小于0.00000001 [vol:{$vol}] [price:{$price}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if((float)($vol * $price) <= 0.00000001){
            cilog('error',"参数错误,交易总额不超过0.00000001btc [vol:{$vol}] [price:{$price}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        cilog('debug',"参数信息 [type:{$type}] [id:{$coin_id}] [vol:{$vol}] [price:{$price}] [pw:{$deal_pw}]");


        // 验证交易密码
        $this->load->service('user/user_service');
        $uin_info = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($uin_info)) {
            render_json($this->user_service->user_errcode['USER_GET_USERINFO_ERR']);
        }

        $flag = $this->user_service->check_pw($uin_info['f_key'],$uin_info['f_deal_pw'],$deal_pw);
        if($flag !== 0){
            render_json($this->user_service->user_errcode['USER_PW_CHECK_ERR']);
        }

        // 检查用户状态 用户已删除、待激活状态不可交易
        $state_list = array(
            $this->user_service->user_state['CHECK_ONE'],
            $this->user_service->user_state['CHECK_TWO'],
            $this->user_service->user_state['CHECK_THREE'],
            $this->user_service->user_state['USER_REGISTER_SCU']
        );
        if(!in_array($uin_info['f_state'],$state_list)){
            cilog('error',"用户状态不合法! [state:{$uin_info['f_state']}]");
            render_json($this->user_service->user_errcode['USER_ERR_STATE']);
        }

        // 获取交易的币的详情
        $this->load->service('coin/coin_service');
        $coin_info = $this->coin_service->get_coin_info($this->conn,$coin_id);
        if(!is_array($coin_info)){
            cilog('error',"获取当前币种的详情失败,无法发起委托! [coinid:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 开始委托
        if($type === 1){
            // 买入
            $flag = $this->deal_service->submit_buy_bdeal($this->conn,$uin,$coin_info,$price,$vol);
        }elseif ($type === 2) {
            // 卖出
            $flag = $this->deal_service->submit_sell_bdeal($this->conn,$uin,$coin_info,$price,$vol);
        }else{
            cilog('error',"type类型错误,无法发起委托! [type:{$type}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if ($flag !== 0){
            // render_json($this->deal_service->deal_errcode['DEAL_BUY_BDEAL_ERR']);
            render_json($flag);
        }else{
            render_json(0);
        }
    }

    /**
     * @fun     取消委托
     * @param   id      大单id
     * @param   tradePw 交易密码
     */
    public function cancel_entrust()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $bdealid = get_post_valueI('id');
        $deal_pw = get_post_value('tradePw');

        if($bdealid === 0){
            cilog('error',"参数错误,bdeal_id 为空");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!$this->oValidator->isPw($deal_pw)){
            cilog('error',"密码格式不对 [pw:{$deal_pw}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 1 验证交易密码
        $uin_info = $this->user_service->get_user_info($this->conn,$uin);
        if(!is_array($uin_info)) {
            render_json($this->user_service->user_errcode['USER_GET_USERINFO_ERR']);
        }

        $flag = $this->user_service->check_pw($uin_info['f_key'],$uin_info['f_deal_pw'],$deal_pw);
        if($flag !== 0){
            render_json($this->user_service->user_errcode['USER_PW_CHECK_ERR']);
        }

        // 2 获取委托单信息
        $bdealinfo = $this->deal_service->get_bdeal_info($this->conn,$bdealid);
        if(!is_array($bdealinfo)){
            render_json($this->deal_service->deal_errcode['DEAL_GET_BDEAL_ERR']);
        }
        $type = $bdealinfo['f_type'];
        $coin_id = $bdealinfo['f_coin_id'];

        // 获取交易的币的详情
        $this->load->service('coin/coin_service');
        $coin_info = $this->coin_service->get_coin_info($this->conn,$coin_id);
        if(!is_array($coin_info)){
            cilog('error',"获取当前币种的详情失败,无法发起委托! [coinid:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        // 获取当前用户比特币财务信息
        $this->load->service('finance/finance_service');
        $market_finance_info = $this->finance_service->get_market_finance($this->conn,$uin);
        if(!is_array($market_finance_info)){
            cilog('error',"获取当前用户比特币财务信息失败,无法发起委托! [uin:{$uin}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if((int)$type === 1){
            // 买入
            $flag = $this->deal_service->cancel_buy_bdeal($this->conn,$uin_info,$bdealinfo,$market_finance_info);
        }elseif ((int)$type === 2){
            // 获取当前用户当前币种财务信息
            $this->load->service('finance/finance_service');
            $finance_info = $this->finance_service->get_finance_info($this->conn, $uin, $coin_id);
            if(!is_array($finance_info)){
                cilog('error',"获取当前用户比特币财务信息失败,无法发起委托! [uin:{$uin}]");
                render_json($this->conf_errcode['PARAM_ERR']);
            }
            $flag = $this->deal_service->cancel_sell_bdeal($this->conn,$uin_info,$bdealinfo,$market_finance_info,$finance_info,$coin_info);
        }else{
            cilog('error',"type参数错误! [type:{$type}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($flag !== 0){
            render_json($flag);
        }else{
            render_json(0);
        }
    }

    /**
     * 废弃
     * @fun     获取币种最新的10天成交记录
     * @param   coinId      币种id
     * @param   type        大单类型
     * @param   num         展示数量
     */
    public function get_last_entrust_bak()
    {
        $this->init_log();
        $this->init_api();

        $num = get_post_valueI('num');
        $type = get_post_valueI('type');
        $coid_id = get_post_valueI('coinId');

        $num = (($num > 0) && ($num <= 100) ) ? $num : 10;
        $arr_type = $this->deal_service->type;
        if(($type !== $arr_type['BUY']) && ($type !== $arr_type['SELL'])){
            $type = NULL;
        }

        if($coid_id < 10000){
            cilog('error',"参数错误,coin_id 为空");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $state = $this->deal_service->state['DEAL_DONE'];
        $query = array(
            'f_state' => $state['DEAL_DONE'],
            'f_type' => $type,
            'f_coin_id' =>$coid_id,
        );
        $sort = 'f_modify_time desc';
        $bdeal_list = $this->deal_service->search_bdeal_list($this->conn,1,$num,$query,$sort);
        if(!is_array($bdeal_list)){
            render_json_list();
        }

        $rsp = array();
        foreach ($bdeal_list['rows'] as $row){
            $a = array(
                // 'dealTime' => $row['f_modify_time'],
                'type' => rand(100,999).$row['f_type'],
                'money' => number_format($row['f_price'],3,'.',''),
                'num' => number_format($row['f_total_vol'],3,'.',''),
            );
            array_push($rsp,$a);
        }

        render_json_list(0,'',$bdeal_list['total'],$rsp);
    }

    /**
     * 获取币种最新的10天成交记录 修改版 20170926
     * @param   coinId      币种id
     * @param   num         展示数量
     */
    public function get_last_entrust()
    {
        $this->init_log();
        $this->init_api();

        $num = get_post_valueI('num');
        $coin_id = get_post_valueI('coinId');

        $num = (($num > 0) && ($num <= 100) ) ? $num : 10;

        if($coin_id < 10000){
            cilog('error',"参数错误,coin_id 为空");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $state = $this->deal_service->state['DEAL_DONE'];

        $query = array(
            'f_state' => $state['DEAL_DONE'],
            'f_coin_id' => $coin_id
        );

        $page = 1;
        $sort = 'f_create_time desc';
        $deal_list = $this->deal_service->search_deal_list($this->conn,$page,$num,$query,$sort);
        if(!is_array($deal_list)){
            render_json_list();
        }

        $rsp = array();
        foreach ($deal_list['rows'] as $row){
            $a = array(
                'dealTime' => $row['f_create_time'],
                'type' => rand(100,999).$row['f_type'],
                'money' => $row['f_money'],
                'num' => $row['f_num'],
            );
            array_push($rsp,$a);
        }
        render_json_list(0,'',$deal_list['total'],$rsp);
    }

    // 获取最新挂单记录
    // 废弃
    public function get_last_bdeal_bak()
    {
        $this->init_log();
        $this->init_api();

        $num = get_post_valueI('num');
        $type = get_post_valueI('type');
        $coid_id = get_post_valueI('coinId');

        $num = (($num > 0) && ($num <= 100) ) ? $num : 10;
        $arr_type = $this->config->item('type','conf/deal_define');

        if($coid_id < 10000){
            cilog('error',"参数错误,coin_id 为空");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!in_array($type,array(1,2))){
            cilog('error',"类型参数错误, [type:{$type}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $this->config->load('conf/deal_define', TRUE);
        $state = $this->config->item('state','conf/deal_define');
        $query = array(
            'f_state' => $state['DEAL_DURING'],
            'f_type' => $type,
            'f_coin_id' =>$coid_id,
        );
        $sort = 'f_modify_time desc';
        $bdeal_list = $this->deal_service->search_bdeal_list($this->conn,1,$num,$query,$sort);
        if(!is_array($bdeal_list)){
            render_json_list();
        }

        $rsp = array();
        foreach ($bdeal_list['rows'] as $row){
            $a = array(
                // 'dealTime' => $row['f_modify_time'],
                'type' => $row['f_type'],
                'money' => number_format($row['f_price'],3,'.',''),
                'num' => number_format($row['f_pre_deal_vol'],3,'.',''),
            );
            array_push($rsp,$a);
        }

        render_json_list(0,'',$bdeal_list['total'],$rsp);
    }

    /**
     * 获取最新的挂单价格,和价格统计
     *
     * 买单 价格高到低
     * 卖单 价格低到高
     *
     * num    获取单据数量
     * type   大单类型
     * coinid 币种id
     */
    public function get_last_bdeal()
    {
        $this->init_log();
        $this->init_api();

        $num = get_post_valueI('num');
        $type = get_post_valueI('type');
        $coin_id = get_post_valueI('coinId');

        $num = (($num > 0) && ($num <= 100) ) ? $num : 10;

        if($coin_id < 10000){
            cilog('error',"参数错误,coin_id 为空");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(!in_array($type,array(1,2))){
            cilog('error',"类型参数错误, [type:{$type}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }
        $state = $this->deal_service->state['DEAL_DURING'];

        if($type === $this->deal_service->type['BUY']){
            $sort = "desc";
        }elseif ($type === $this->deal_service->type['SELL']){
            $sort = "asc";
        }else{
            cilog('error',"参数错误,[type:{$type}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $list = $this->deal_service->get_bdeal_diff_price($this->conn,$state,$type,$coin_id,$sort,$num);
        if(!is_array($list)){
            render_json_list();
        }else{
            render_json_list(0,'',$list['num'],$list['rows']);
        }
    }

    /**
     * @fun     获取交易详情
     * @param   bdealid    大单id
     * @param   page       页码
     * @param   num        每页展示最大数据量
     */
    public function get_deal_list()
    {

        $this->init_log();
        $this->init_api();
        $this->check_login();

        $this->oSession = $this->get_session();
        $uin =  $this->oSession->uin;

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');
        $bdeal_id = get_post_valueI('bdealid');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $deal_list = $this->deal_service->get_deal_list($this->conn,$uin,$bdeal_id,$num, $page);
        if(!is_array($deal_list))
        {
            render_json_list();
        }

        render_json_list(0,'',$deal_list['total'],$deal_list['rows']);
    }

    /**
     * @fun     K线数据图
     *
     * symbol      币种id               必填
     * type        更新频率  单位秒       必填
     * size        记录数
     * since       实时数据标记
     */
    public function get_market_kline()
    {
        $this->init_log();
        $this->init_api();

        $coin_id = get_post_valueI("symbol");
        $type = get_post_valueI("type");
        $size = get_post_valueI('size');
        $since = get_post_valueI('since');

        if($coin_id < 10000){
            cilog('error',"参数错误,coin_id 不合法 [coin_id:{$coin_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }


        $type_list = [60,60*5,60*15,60*30,60*60,24*60*60,7*24*60*60];
        if(!in_array($type,$type_list)){
            cilog('error',"参数错误,type更新频率不合法 [type:{$type}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(($since + $size) === 0){
            cilog('error',"参数错误,size since不能同时为0");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(($since!==0) && ($size!==0)){
            cilog('error',"参数错误,size since不能同时不为0 [size:{$size}] [since:{$since}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if($size>1000){
            cilog('error',"参数错误, size参数错误 [size:{$size}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        if(($size !== 0) && ($size <= 1000)){
            // 初始化,获取一定条数的数据
            $rsp = array(
                'USDCNY' => "**",
                "marketName" => "coincoming",
                "moneyType" => "BTC",          // 市场id
                "symbol" => "",                // 币总简称
                "url" => "http://trade.coincoming.com/",
                "contractUnit" => "BTC",
                'data'=> array(),
            );
            $key = $key = $this->deal_service->deal_redis_key['KLINE'].$type."_".$coin_id;
            $data = $this->cache->redis->get($key);
            $rsp['data'] = unserialize($data);
            render_json(0,'',$rsp);
        }

        if ($since!==0){
            $rsp = "一个请求是怎样变成一个神奇的页面， 一个简单的请求又有怎样的多姿多彩，欢迎各位喜欢编程的爱好者加入FBISHARE,有意者请发送简历至hr@fbishare.com";
            render_json(0,'',$rsp);
        }
        render_json($this->conf_errcode['PARAM_ERR']);
    }



    /**
     * 撮合交易
     *
     * 常驻进程
     * 1. 查看交易开关是否打开  打开继续,否则退出
     * 2. 查看当前币种交易价格
     * 3. 卖出 状态中 价格大于等于币种价格  卖出价格 优先价格低的 价格相同,按照时间先后算
     * 4. 买入 状态中 价格大于等于币种价格  买入价格 优先价格高的 价格相同,按照时间先后算
     * 5. 当前币种价格为
     */
    public function to_be_done_deal($key,$coin_id)
    {
        $log_filename = "to_be_done_deal_";
        $fun_title = "开始撮合交易";
        $this->init_cron($key,$log_filename,$fun_title);
        $this->load->model('deal/Model_t_bdeal');

        // 1. 检查币种交易开关
        $redis_key = $this->deal_service->deal_redis_key['TO_BE_DEAL'].$coin_id;
        if($this->cache->redis->get($redis_key)){
            // 取到数据,表示该点正在执行
            cilog('error',"[coinid:{$coin_id}] 当前有正在执行的队列!直接退出,等待下次执行!",$log_filename);
            exit();
        }
        $this->cache->redis->save($redis_key,1,$this->deal_service->deal_redis_key['TIMEOUT']);
        cilog('debug',"[coinid:{$coin_id}] 开始交易,开启交易开关!",$log_filename);


        // 2. 获取币种信息
        $this->load->service("coin/coin_service");
        $coin_info = $this->coin_service->get_coin_info($this->conn, $coin_id);
        if(!isset($coin_info['f_last_price'])){
            // 获取币种信息失败
            cilog('error','获取币种信息失败,直接退出,等待下次执行!',$log_filename);
            exit();
        }
        cilog('debug',"获取币种信息成功!",$log_filename);
        cilog('debug',$coin_info,$log_filename);


        // 3. 获取最新的一条卖出记录 优先 价格低 时间早
        $bdeal_sell_info = $this->Model_t_bdeal->find_by_attributes(
            $conn = $this->conn,
            $select=NULL,
            $tablename = $this->Model_t_bdeal->get_tablename(),
            $where = array(
                'f_type' => $this->deal_service->type['SELL'],
                'f_state' => $this->deal_service->state['DEAL_DURING'],
                'f_coin_id' => $coin_id,
                'f_price >=' => $coin_info['f_last_price']
            ),
            $sort = 'f_price asc'
        );
        if(!$bdeal_sell_info){
            cilog('error',"获取最新的卖出记录失败!直接退出,等待下次执行!",$log_filename);
            $this->cache->redis->delete($redis_key);
            exit();
        }
        cilog('debug',"获取最新的卖出记录成功!",$log_filename);
        cilog('debug',$bdeal_sell_info,$log_filename);
        $vol = $bdeal_sell_info['f_pre_deal_vol'];
        $coin_id = $bdeal_sell_info['f_coin_id'];

        // 获取当前最新的买入记录 优先 价格高  时间早
        $bdeal_buy_info = $this->Model_t_bdeal->find_all(
            $conn = $this->conn,
            $select=NULL,
            $tablename = $this->Model_t_bdeal->get_tablename(),
            $where = array(
                'f_type' => $this->deal_service->type['BUY'],
                'f_state' => $this->deal_service->state['DEAL_DURING'],
                'f_coin_id' => $coin_id,
                'f_price >=' => $coin_info['f_last_price']
            ),
            $limit = 10,
            $page = 1,
            $sort = 'f_price asc'
        );

        if(!$bdeal_buy_info){
            cilog('error',"获取最新的买入记录失败!直接退出,等待下次执行!",$log_filename);
            $this->cache->redis->delete($redis_key);
            exit();
        }

        $this->load->model('finance/Model_t_finance_info');
        foreach ($bdeal_buy_info as $row){
            if($vol < $row['f_pre_deal_vol'])
            {
                cilog('debug',"当前买入的量足够 [买入:{$row['f_pre_deal_vol']}] [卖出:{$vol}]",$log_filename);
                $this->conn->trans_start();

                // 当前买入的量足够,扭转大单信息,调整财务信息
                $vol -= $row['f_pre_deal_vol'];

                // 修改卖出单状态
                $this->Model_t_bdeal->update_all(
                    $this->conn,
                    $tablename=$this->Model_t_bdeal->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_pre_deal_vol' => $bdeal_sell_info['f_pre_deal_vol'] - $row['f_pre_deal_vol'],
                        'f_post_deal_vol' => $bdeal_sell_info['f_post_deal_vol'] + $row['f_pre_deal_vol'],
                    ),
                    $where = array(
                        'f_bdeal_id' => $bdeal_sell_info['f_bdeal_id'],
                    )
                );

                //      修改卖出单用户财务信息 扣减当前币,增加市场币种
                $sell_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $coin_id,
                    ),
                    $sort = NULL
                );

                //      扣减当前币种的财务信息
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $sell_finance['f_total_vol'] - $row['f_pre_deal_vol'],
                        'f_freeze_vol' => $sell_finance['f_freeze_vol'] - $row['f_pre_deal_vol'],
                        'f_can_use_vol' => $sell_finance['f_can_use_vol'] - $row['f_pre_deal_vol'],
                    ),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdeal_sell_info['f_uin']."_".$coin_id;
                $this->cache->redis->delete($key);

                //      增加市场币种的财务信息
                $market_coin_id = get_coinid_by_marketid($coin_info['f_market_type']);
                $market_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    ),
                    $sort = NULL
                );
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $market_finance['f_total_vol'] + $row['f_pre_deal_vol']*$bdeal_sell_info['f_price'],
                        'f_freeze_vol' => $market_finance['f_freeze_vol'],
                        'f_can_use_vol' => $market_finance['f_can_use_vol'] + $row['f_pre_deal_vol']*$bdeal_sell_info['f_price'],
                    ),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdeal_sell_info['f_uin']."_".$market_coin_id;
                $this->cache->redis->delete($key);

                //      添加卖出单小单记录
                $this->deal_service->add_deal(
                    $conn=$this->conn,
                    $bdeal_sell_info['f_uin'],
                    $info=array(
                        'f_bdeal_id' => $bdeal_sell_info['f_bdeal_id'],
                        'f_type' => $bdeal_sell_info['f_type'],
                        'f_coin_id' => $bdeal_sell_info['f_coin_id'],
                        'f_num' => $row['f_pre_deal_vol'],
                        'f_money' => $row['f_price'],
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_commission' => $vol * ($coin_info['f_commission'] / 100),
                    )
                );

                // 修改买入单状态
                $this->Model_t_bdeal->update_all(
                    $this->conn,
                    $tablename=$this->Model_t_bdeal->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_state' => $this->deal_service->state['DEAL_DURING'],
                        'f_pre_deal_vol' => 0,   // 未成交数量
                        'f_post_deal_vol' => $row['f_post_deal_vol'] + $row['f_pre_deal_vol'], // 已成交
                    ),
                    $where = array(
                        'f_bdeal_id' => $row['f_bdeal_id'],
                    )
                );

                //      修改买入单用户财务信息 增加当前币,扣减市场币种
                $buy_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $coin_id,
                    ),
                    $sort = NULL
                );

                //      增加当前币种的财务信息
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $buy_finance['f_total_vol'] + $row['f_pre_deal_vol'],
                        'f_freeze_vol' => $buy_finance['f_freeze_vol'],
                        'f_can_use_vol' => $buy_finance['f_can_use_vol'] + $row['f_pre_deal_vol'],
                    ),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$row['f_uin']."_".$coin_id;
                $this->cache->redis->delete($key);

                //      扣减市场币种的财务信息
                $market_coin_id = get_coinid_by_marketid($coin_info['f_market_type']);
                $market_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    ),
                    $sort = NULL
                );
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $market_finance['f_total_vol'] - $row['f_pre_deal_vol']*$row['f_price'],
                        'f_freeze_vol' => $market_finance['f_freeze_vol'] - $row['f_pre_deal_vol']*$row['f_price'],
                        'f_can_use_vol' => $market_finance['f_can_use_vol'] - $row['f_pre_deal_vol']*$row['f_price'],
                    ),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$row['f_uin']."_".$market_coin_id;
                $this->cache->redis->delete($key);

                //      添加买入单小单记录
                $this->deal_service->add_deal(
                    $conn=$this->conn,
                    $row['f_uin'],
                    $info=array(
                        'f_bdeal_id' => $row['f_bdeal_id'],
                        'f_type' => $row['f_type'],
                        'f_coin_id' => $row['f_coin_id'],
                        'f_num' => $row['f_pre_deal_vol'],
                        'f_money' => $row['f_price'],
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_commission' => $vol * ($coin_info['f_commission'] / 100),
                    )
                );

                // 修改币种价格
                $this->load->model("coin/Model_t_coin");
                $this->Model_t_coin->update_all(
                    $conn = $this->conn,
                    $tablename = $this->Model_t_coin->get_tablename(),
                    $attributes = array(
                        'f_last_pric' => $row['f_price']
                    ),
                    $where = array(
                        'f_coin_id' =>$coin_id
                    )
                );
                $this->load->service("coin/coin_service");
                $key = $this->coin_service->coin_redis_key['COIN_INFO'].$coin_id;
                $this->cache->redis->delete($key);

                $this->conn->trans_complete();
                if ($this->conn->trans_status() === FALSE)
                {
                    // $conn->trans_rollback();
                    cilog('error',"撮合交易订单失败,开始回滚数据!",$log_filename);
                    cilog('error',"卖出单信息 [bdealid:{$bdeal_sell_info['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$bdeal_sell_info['f_price']}]",$log_filename);
                    cilog('error',"买入单信息 [bdealid:{$row['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$row['f_price']}]",$log_filename);
                    cilog('error',"退出后续程序,等待下次执行!",$log_filename);
                    break;
                }
                else
                {
                    // $conn->trans_commit();
                    cilog('debug',"撮合交易订单成功!",$log_filename);
                    cilog('debug',"卖出单信息 [bdealid:{$bdeal_sell_info['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$bdeal_sell_info['f_price']}]",$log_filename);
                    cilog('debug',"买入单信息 [bdealid:{$row['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$row['f_price']}]",$log_filename);
                    cilog('debug',"退出后续程序,等待下次执行!",$log_filename);
                    break;
                }
            }
            elseif ($vol > $row['f_pre_deal_vol'])
            {
                cilog('debug',"当前买入的量不足 [买入:{$row['f_pre_deal_vol']}] [卖出:{$vol}]",$log_filename);
                // 当前买入的量不足,先扣减该条买入,然后继续循环
                $this->conn->trans_start();

                // 当前买入的量不足,扭转大单信息,调整财务信息
                $vol -= $row['f_pre_deal_vol'];

                // 修改卖出单状态
                $this->Model_t_bdeal->update_all(
                    $this->conn,
                    $tablename=$this->Model_t_bdeal->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_state' => $this->deal_service->state['DEAL_DURING'],
                        'f_pre_deal_vol' => $bdeal_sell_info['f_pre_deal_vol'] - $vol,
                        'f_post_deal_vol' => $bdeal_sell_info['f_post_deal_vol'] + $vol,
                    ),
                    $where = array(
                        'f_bdeal_id' => $bdeal_sell_info['f_bdeal_id'],
                    )
                );

                //      修改卖出单用户财务信息 扣减当前币,增加市场币种
                $sell_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $coin_id,
                    ),
                    $sort = NULL
                );

                //      扣减当前币种的财务信息
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $sell_finance['f_total_vol'] - $vol,
                        'f_freeze_vol' => $sell_finance['f_freeze_vol'] - $vol,
                        'f_can_use_vol' => $sell_finance['f_can_use_vol'] - $vol,
                    ),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdeal_sell_info['f_uin']."_".$coin_id;
                $this->cache->redis->delete($key);

                //      增加市场币种的财务信息
                $market_coin_id = get_coinid_by_marketid($coin_info['f_market_type']);
                $market_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    ),
                    $sort = NULL
                );
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $market_finance['f_total_vol'] + $vol*$bdeal_sell_info['f_price'],
                        'f_freeze_vol' => $market_finance['f_freeze_vol'],
                        'f_can_use_vol' => $market_finance['f_can_use_vol'] + $vol*$bdeal_sell_info['f_price'],
                    ),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdeal_sell_info['f_uin']."_".$market_coin_id;
                $this->cache->redis->delete($key);

                //      添加卖出单小单记录
                $this->deal_service->add_deal(
                    $conn=$this->conn,
                    $bdeal_sell_info['f_uin'],
                    $info=array(
                        'f_bdeal_id' => $bdeal_sell_info['f_bdeal_id'],
                        'f_type' => $bdeal_sell_info['f_type'],
                        'f_coin_id' => $bdeal_sell_info['f_coin_id'],
                        'f_num' => $row['f_pre_deal_vol'],
                        'f_money' => $row['f_price'],
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_commission' => $vol * ($coin_info['f_commission'] / 100),
                    )
                );

                // 修改买入单状态
                $this->Model_t_bdeal->update_all(
                    $this->conn,
                    $tablename=$this->Model_t_bdeal->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_pre_deal_vol' => 0,   // 未成交数量
                        'f_post_deal_vol' => $row['f_post_deal_vol'] + $row['f_pre_deal_vol'], // 已成交
                    ),
                    $where = array(
                        'f_bdeal_id' => $row['f_bdeal_id'],
                    )
                );

                //      修改买入单用户财务信息 增加当前币,扣减市场币种
                $buy_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $coin_id,
                    ),
                    $sort = NULL
                );

                //      增加当前币种的财务信息
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $buy_finance['f_total_vol'] + $vol,
                        'f_freeze_vol' => $buy_finance['f_freeze_vol'],
                        'f_can_use_vol' => $buy_finance['f_can_use_vol'] + $vol,
                    ),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$row['f_uin']."_".$coin_id;
                $this->cache->redis->delete($key);

                //      扣减市场币种的财务信息
                $market_coin_id = get_coinid_by_marketid($coin_info['f_market_type']);
                $market_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    ),
                    $sort = NULL
                );
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $market_finance['f_total_vol'] - $vol*$row['f_price'],
                        'f_freeze_vol' => $market_finance['f_freeze_vol'] - $vol*$row['f_price'],
                        'f_can_use_vol' => $market_finance['f_can_use_vol'] - $vol*$row['f_price'],
                    ),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$row['f_uin']."_".$market_coin_id;
                $this->cache->redis->delete($key);

                //      添加买入单小单记录
                $this->deal_service->add_deal(
                    $conn=$this->conn,
                    $row['f_uin'],
                    $info=array(
                        'f_bdeal_id' => $row['f_bdeal_id'],
                        'f_type' => $row['f_type'],
                        'f_coin_id' => $row['f_coin_id'],
                        'f_num' => $row['f_pre_deal_vol'],
                        'f_money' => $row['f_price'],
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_commission' => $vol * ($coin_info['f_commission'] / 100),
                    )
                );

                // 修改币种价格
                $this->load->model("coin/Model_t_coin");
                $this->Model_t_coin->update_all(
                    $conn = $this->conn,
                    $tablename = $this->Model_t_coin->get_tablename(),
                    $attributes = array(
                        'f_last_pric' => $row['f_price']
                    ),
                    $where = array(
                        'f_coin_id' =>$coin_id
                    )
                );
                $this->load->service("coin/coin_service");
                $key = $this->coin_service->coin_redis_key['COIN_INFO'].$coin_id;
                $this->cache->redis->delete($key);

                $this->conn->trans_complete();
                if ($this->conn->trans_status() === FALSE)
                {
                    // $conn->trans_rollback();
                    cilog('error',"撮合交易订单失败,开始回滚数据!");
                    cilog('error',"卖出单信息 [bdealid:{$bdeal_sell_info['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$bdeal_sell_info['f_price']}]");
                    cilog('error',"买入单信息 [bdealid:{$row['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$row['f_price']}]");
                    // return $this->deal_service->deal_errcode['DEAL_TO_BE_DEAL_ERR'];
                    break;
                }
                else
                {
                    // $conn->trans_commit();
                    cilog('debug',"撮合交易订单成功!");
                    cilog('error',"卖出单信息 [bdealid:{$bdeal_sell_info['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$bdeal_sell_info['f_price']}]");
                    cilog('error',"买入单信息 [bdealid:{$row['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$row['f_price']}]");
                    continue;
                }
            }
            else{
                cilog('debug',"当前买入的量和卖出的量相等 [买入:{$row['f_pre_deal_vol']}] [卖出:{$vol}]",$log_filename);
                // $vol === $row['f_post_deal_vol']
//                $this->conn->trans_start();

                // 当前买入的量和卖出的量相等,扭转大单信息,调整财务信息
                cilog('debug',"修改卖出单相关信息!",$log_filename);
                $this->Model_t_bdeal->update_all(
                    $this->conn,
                    $tablename=$this->Model_t_bdeal->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_pre_deal_vol' => $bdeal_sell_info['f_pre_deal_vol'] - $vol,
                        'f_post_deal_vol' => $bdeal_sell_info['f_post_deal_vol'] + $vol,
                    ),
                    $where = array(
                        'f_bdeal_id' => $bdeal_sell_info['f_bdeal_id'],
                    )
                );
                cilog('debug',"修改卖出大单信息成功!",$log_filename);

                //      修改卖出单用户财务信息 扣减当前币,增加市场币种
                $sell_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $coin_id,
                    ),
                    $sort = NULL
                );

                //      扣减当前币种的财务信息
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $sell_finance['f_total_vol'] - $vol,
                        'f_freeze_vol' => $sell_finance['f_freeze_vol'] - $vol,
                        'f_can_use_vol' => $sell_finance['f_can_use_vol'] - $vol,
                    ),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdeal_sell_info['f_uin']."_".$coin_id;
                $this->cache->redis->delete($key);
                cilog('debug',"扣减卖出单的当前币种的财务信息成功!",$log_filename);

                //      增加市场币种的财务信息
                $market_coin_id = get_coinid_by_marketid($coin_info['f_market_type']);
                $market_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    ),
                    $sort = NULL
                );
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $market_finance['f_total_vol'] + $vol*$bdeal_sell_info['f_price'],
                        'f_freeze_vol' => $market_finance['f_freeze_vol'],
                        'f_can_use_vol' => $market_finance['f_can_use_vol'] + $vol*$bdeal_sell_info['f_price'],
                    ),
                    $where = array(
                        'f_uin' => $bdeal_sell_info['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdeal_sell_info['f_uin']."_".$market_coin_id;
                $this->cache->redis->delete($key);
                cilog('debug',"增加卖出单的市场币种的财务信息成功!",$log_filename);

                //      添加卖出单小单记录
                $this->deal_service->add_deal(
                    $conn=$this->conn,
                    $bdeal_sell_info['f_uin'],
                    $info=array(
                        'f_bdeal_id' => $bdeal_sell_info['f_bdeal_id'],
                        'f_type' => $bdeal_sell_info['f_type'],
                        'f_coin_id' => $bdeal_sell_info['f_coin_id'],
                        'f_num' => $row['f_pre_deal_vol'],
                        'f_money' => $row['f_price'],
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_commission' => $vol * ($coin_info['f_commission'] / 100),
                    )
                );
                cilog('debug',"创建小单记录成功!",$log_filename);

                // 修改买入单状态
                cilog('debug',"开始修改买入单相关信息!",$log_filename);
                $this->Model_t_bdeal->update_all(
                    $this->conn,
                    $tablename=$this->Model_t_bdeal->get_tablename(),
                    $attributes=array(
                        'f_modify_time' => timestamp2time(),
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_pre_deal_vol' => 0,   // 未成交数量
                        'f_post_deal_vol' => $row['f_post_deal_vol'] + $row['f_pre_deal_vol'], // 已成交
                    ),
                    $where = array(
                        'f_bdeal_id' => $row['f_bdeal_id'],
                    )
                );

                //      修改买入单用户财务信息 增加当前币,扣减市场币种
                $buy_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $coin_id,
                    ),
                    $sort = NULL
                );

                //      增加当前币种的财务信息
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $buy_finance['f_total_vol'] + $vol,
                        'f_freeze_vol' => $buy_finance['f_freeze_vol'],
                        'f_can_use_vol' => $buy_finance['f_can_use_vol'] + $vol,
                    ),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$row['f_uin']."_".$coin_id;
                $this->cache->redis->delete($key);
                cilog('debug',"增加当前币种财务信息成功!",$log_filename);

                //      扣减市场币种的财务信息
                $market_coin_id = get_coinid_by_marketid($coin_info['f_market_type']);
                $market_finance = $this->Model_t_finance_info->find_by_attributes(
                    $conn=$this->conn,
                    $select = NULL,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    ),
                    $sort = NULL
                );
                $this->Model_t_finance_info->update_all(
                    $conn=$this->conn,
                    $tablename = $this->Model_t_finance_info->get_tablename(),
                    $attributes = array(
                        'f_modify_time' => timestamp2time(),
                        'f_total_vol' => $market_finance['f_total_vol'] - $vol*$row['f_price'],
                        'f_freeze_vol' => $market_finance['f_freeze_vol'] - $vol*$row['f_price'],
                        'f_can_use_vol' => $market_finance['f_can_use_vol'] - $vol*$row['f_price'],
                    ),
                    $where = array(
                        'f_uin' => $row['f_uin'],
                        'f_coin_id' => $market_coin_id,
                    )
                );
                $this->load->service("finance/finance_service");
                $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$row['f_uin']."_".$market_coin_id;
                $this->cache->redis->delete($key);
                cilog('debug',"扣减市场币种财务信息成功!",$log_filename);

                //      添加买入单小单记录
                $this->deal_service->add_deal(
                    $conn=$this->conn,
                    $row['f_uin'],
                    $info=array(
                        'f_bdeal_id' => $row['f_bdeal_id'],
                        'f_type' => $row['f_type'],
                        'f_coin_id' => $row['f_coin_id'],
                        'f_num' => $row['f_pre_deal_vol'],
                        'f_money' => $row['f_price'],
                        'f_state' => $this->deal_service->state['DEAL_DONE'],
                        'f_commission' => $vol * ($coin_info['f_commission'] / 100),
                    )
                );
                cilog('debug',"添加小单记录成功!",$log_filename);

                // 修改币种价格
                $this->load->model("coin/Model_t_coin");
                $this->Model_t_coin->update_all(
                    $conn = $this->conn,
                    $tablename = $this->Model_t_coin->get_tablename(),
                    $attributes = array(
                        'f_last_pric' => $row['f_price'],
                    ),
                    $where = array(
                        'f_coin_id' =>$coin_id
                    )
                );
                $this->load->service("coin/coin_service");
                $key = $this->coin_service->coin_redis_key['COIN_INFO'].$coin_id;
                $this->cache->redis->delete($key);
                cilog('debug',"修改币种价格成功!",$log_filename);
                break;

//                $this->conn->trans_complete();
//                if ($this->conn->trans_status() === FALSE)
//                {
//                    // $conn->trans_rollback();
//                    cilog('error',"撮合交易订单失败,开始回滚数据!");
//                    cilog('error',"卖出单信息 [bdealid:{$bdeal_sell_info['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$bdeal_sell_info['f_price']}]");
//                    cilog('error',"买入单信息 [bdealid:{$row['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$row['f_price']}]");
//                    // return $this->deal_service->deal_errcode['DEAL_TO_BE_DEAL_ERR'];
//                    break;
//                }
//                else
//                {
//                    // $conn->trans_commit();
//                    cilog('debug',"撮合交易订单成功!");
//                    cilog('error',"卖出单信息 [bdealid:{$bdeal_sell_info['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$bdeal_sell_info['f_price']}]");
//                    cilog('error',"买入单信息 [bdealid:{$row['f_bdeal_id']}] [num:{$row['f_pre_deal_vol']}] [price:{$row['f_price']}]");
//                    break;
//                }
            }
        }
        $this->cache->redis->delete($redis_key);
        cilog('debug',"done",$log_filename);
    }

    /**
     * 获取K线数据,写入redis 定时任务
     *
     * [时间，开盘，最高，最低，收盘，成交量]
     *
     */
    public function get_kline_data_write2redis($key,$count_time,$num)
    {
        $log_filename = "get_kline_data_write2redis_";
        $fun_title = "获取K线数据";
        $this->init_cron($key,$log_filename,$fun_title);
        $this->load->model('deal/Model_t_deal');
        $time_now_timestamp = time2timestamp();

        cilog('debug',"开始获取K线数据 [数据总量:{$num}] [数据时间间隔:{$count_time}] [时间戳:{$time_now_timestamp}]",$log_filename);

        $this->load->model("coin/Model_t_coin");
        $coin_list = $this->Model_t_coin->find_all(
            $conn = $this->conn,
            $select='f_coin_id',
            $tablename = $this->Model_t_coin->get_tablename(),
            $where = array(
                'f_market_type >' => 1,
                'f_del_state' => 0,
            ),
            $limit = 100,
            $page = 1,
            $sort = "f_create_time desc"
        );

        foreach ($coin_list as $row){
            cilog('debug',"币种id:{$row['f_coin_id']}",$log_filename);
            $data = array();
            for($i=1;$i<=$num;$i++){
                $start = time2timestamp();
                $end = time2timestamp() + $count_time * $i;
                $count = $this->Model_t_deal->count(
                    $conn=$this->conn,
                    $tablename=$this->Model_t_deal->get_tablename(),
                    $where=array(
                        'UNIX_TIMESTAMP(f_create_time) >=' => $start,
                        'UNIX_TIMESTAMP(f_create_time) <=' => $end,
                        'f_coin_id' => $row['f_coin_id'],
                        'f_type' => $this->deal_service->type['SELL'],
                    )
                );
                cilog('error',"count:{$count}",$log_filename);

                if($count == 0){
                    continue;
                }

                $dealinfo = $this->Model_t_deal->find_by_attributes(
                    $conn=$this->conn,
                    $select = "f_money,max(f_money),min(f_money),sum(f_num)",
                    $tablename = $this->Model_t_deal->get_tablename(),
                    $where = array(
                        'UNIX_TIMESTAMP(f_create_time) >=' => $start,
                        'UNIX_TIMESTAMP(f_create_time) <=' => $end,
                        'f_coin_id' => $row['f_coin_id'],
                        'f_type' => $this->deal_service->type['SELL'],
                    ),
                    $sort = 'f_create_time desc'
                );
                $info = $this->Model_t_deal->find_by_attributes(
                    $conn=$this->conn,
                    $select = "f_money",
                    $tablename = $this->Model_t_deal->get_tablename(),
                    $where = array(
                        'UNIX_TIMESTAMP(f_create_time) >=' => $start,
                        'UNIX_TIMESTAMP(f_create_time) <=' => $end,
                        'f_coin_id' => $row['f_coin_id'],
                        'f_type' => $this->deal_service->type['SELL'],
                    ),
                    $sort = 'f_create_time asc'
                );
                $time_timestamp = $start * 1000;
                $open_price = $info['f_price'];
                $high_price = $dealinfo['max(f_money)'];
                $low_price = $dealinfo['min(f_money)'];
                $close_price = $dealinfo['f_money'];
                $deal_vol = $dealinfo['sum(f_num)'];
                $tmp = array($time_timestamp,$open_price,$high_price,$low_price,$close_price,$deal_vol);
                array_push($data,$tmp);
            }

            $key = $this->deal_service->deal_redis_key['KLINE'].$count_time."_".$row['f_coin_id'];
            $value = serialize($data);
            $this->cache->redis->save($key,$value,86400);
            cilog('debug',"获取K线数据成功! [coin_id:{$row['f_coin_id']}] [count_time:{$count_time}]",$log_filename);
        }

        cilog('debug',"全流程结算!",$log_filename);
    }


}