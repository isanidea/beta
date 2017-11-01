<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/easybitcoin.php';

class Cron_gts extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model("finance/Model_t_finance_info");
        $this->load->model("finance/Model_t_finance_log");
        $this->load->service("user/user_service");
        $this->load->service("finance/finance_service");
        $this->conn = $this->load->database('trade_user',TRUE);
        $this->conf = array(
            'username' => 'FBI@2017.user',
            'password' => 'FBI2017.wallet',
            'host' => '120.78.145.211',
            'port' => 31105,
        );
        $this->log_filename = "gts_wallet_";
        $this->time_out_data = 20;
        $this->base_account = "";
        $this->wallet_conn = $this->init_conn($this->conf);
    }

    private function init_conn($conf)
    {
        return new Bitcoin($conf['username'],$conf['password'],$conf['host'],$conf['port']);
    }

    // 创建地址
    private function create_addr($account)
    {
        $address = $this->wallet_conn->getaddressesbyaccount($account);
        if($address === []){
            $address = $this->wallet_conn->getnewaddress($account);
            if($address){
                cilog('debug',"开始添加币种地址! [account:{$account}] [addr:{$address}]",$this->log_filename);
            }else{
                cilog('error',"添加收币地址失败!",$this->log_filename);
            }
        }else{
            cilog('debug',"该用户已经有了收币地址!",$this->log_filename);
            cilog('debug',$address,$this->log_filename);
        }
        return $address;
    }

    // 检查基本账户余额
    private function check_base_balance($amount)
    {
        $balance = $this->wallet_conn->getbalance($this->base_account);
        if(($balance >= $amount) && ($balance > 0)){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    // 从某个账户转账到某个地址
    private function sys_send_to_user($from_account,$to_addr,$amount)
    {
        cilog('debug',"开始转币 [to:{$to_addr}] [amount:{$amount}]",$this->log_filename);
        // 1. 验证收币地址是否为真
        if(!$this->is_address($to_addr)){
            return -1;
        }

        // 2. 验证基础账户是否有余额
        if(!$this->check_base_balance($amount)){
            return -1;
        }

        // 3. 开始转币 sendfrom <fromaccount> <tobitcoinaddress> <amount> [minconf=1] [comment] [comment-to]
        try {
            $this->wallet_conn->walletlock();
            $this->wallet_conn->walletpassphrase($this->conf['password'],$this->time_out_data);
            $txid = $this->wallet_conn->sendfrom($from_account,$to_addr,$amount);
            $this->wallet_conn->walletlock();
        } catch (Exception $e) {
            cilog('error',$e,$this->log_filename);
        }

        if($txid){
            cilog('debug',$txid,$this->log_filename);
        }else{
            cilog('error',"转币失败!",$this->log_filename);
        }
        return $txid;
    }

    // 转账给多人
    private function sys_send_many($from_account,$arr_to_addr)
    {
        if(!is_array($arr_to_addr)){
            cilog('error',"地址列表不存在!");
            return -1;
        }

        $amount = 0;
        foreach ($arr_to_addr as $key => $value){
            if(!$this->is_address($key)){
                return -1;
            }
            $amount = $amount + $value;
        }
        if(!$this->check_base_balance($amount)){
            return -1;
        }

        try {
            $this->wallet_conn->walletlock();
            $this->wallet_conn->walletpassphrase($this->conf['password'],$this->time_out_data);
            $txid = $this->wallet_conn->sendmany($from_account,$arr_to_addr);
            $this->wallet_conn->walletlock();
        } catch (Exception $e) {
            cilog('error',$e,$this->log_filename);
        }

        if($txid){
            cilog('debug',$txid,$this->log_filename);
        }else{
            cilog('error',"转币失败!",$this->log_filename);
        }
        return $txid;
    }

    // 检查收币地址是否为真
    private function is_address($address)
    {
        $rsp = $this->wallet_conn->validateaddress($address);
        if($rsp['isvalid']){
            cilog('debug',"收币地址为真! [address:{$address}]",$this->log_filename);
        }else{
            cilog('error',"收币地址错误! [address:{$address}]",$this->log_filename);
        }
        return TRUE;
    }


    /**
     * 以下为正式业务
     */

    public function test()
    {
//        $trade_list = $this->wallet_conn->listtransactions("*",1000,0);
//        render_json(0,'',$trade_list);
        $data_account = array(
            'production_10618_10011',
            'production_10645_10011',
            'production_10725_10011',
            'production_10795_10011',
            'production_11001_10011',
            'production_11187_10011',
        );
        $rsp = array();
        foreach ($data_account as $account){
            $data = $this->wallet_conn->getbalance($account);
            echo $account.":".$data."<br>";
        }
        render_json();
    }

//    public function get_account()
//    {
//        // $addr_list = $this->wallet_conn->getaddressesbyaccount($account);
//        $listaddressgroupings = $this->wallet_conn->listaddressgroupings();
//        // var_dump($listaddressgroupings);
//        render_json(0,'',$listaddressgroupings);
//    }

//    public function create_account_addr()
//    {
//        $account = array('1','2','3','4','5');
//        foreach ($account as $row){
//            $this->create_addr($row);
//        }
//    }

    private function get_trade_list()
    {
        cilog('debug',"======== 开始获取前200个钱包记录列表! ========",$this->log_filename);
        $coin_id = 10011;
        $trade_list = $this->wallet_conn->listtransactions("*",200,0);
        foreach($trade_list as $key => $val)
        {
            if($val ['category'] == 'receive'){
                // 接受的交易  充值到平台
                $this->handleReceive($this->conn,$val,$coin_id);
            }elseif($val ['category'] == 'send'){
                // 发送的交易  转账出去
                $this->handleSend($val);
            }else{
                cilog('debug',"type:{$val['category']}",$this->log_filename);
                continue;
            }
        }
        cilog('debug',"done",$this->log_filename);
    }

    private function handleReceive($conn,$val,$coin_id)
    {
        cilog('debug',"[fun:handleReceive] ====> 接受虚拟币 [txid:{$val['txid']}]",$this->log_filename);
        // $this->finance_service->coin_in($conn,$val,$coin_id,$this->log_filename);
        if($val['category'] != 'receive'){
            cilog('error',"当前单据状态不为接受!",$this->log_filename);
            return -1;
        }elseif ($val['confirmations'] < 4){
            cilog('error',"该单据没有被确认! [conf:{$val['confirmations']}]",$this->log_filename);
            return -1;
        }

        // 查询该地址信息是否存在于本地db中
        $finance_info = $this->finance_service->Model_t_finance_info->find_by_attributes(
            $conn,
            $select = NULL,
            $tablename = $this->finance_service->Model_t_finance_info->get_tablename(),
            $where = array(
                'f_coin_addr' => $val['address']
            ),
            $sort = NULL
        );

        if(!$finance_info){
            cilog('error',"该记录不在本地db中,直接退出",$this->log_filename);
            return -1;
        }

        // 查询当前交易是否已经写入记录
        $where = array(
            'f_coin_key' => $val['txid'],
        );
        $count = $this->finance_service->Model_t_finance_log->count(
            $conn=$this->conn,
            $tablename=$this->finance_service->Model_t_finance_log->get_tablename(),
            $where
        );
        if((int)$count !== 0){
            cilog('error',"当前交易已经写入了db中! 无需继续写入db!",$this->log_filename);
            return -1;
        }

        cilog('debug',"该记录不在本地db中,需要写入本地记录",$this->log_filename);

        // 开始充值
        $conn->trans_start();
        $amount = abs($val['amount']);
        // 添加财务流水
        $data = array(
            'f_type' => $this->finance_service->finance_type['COIN_IN'],
            'f_uin' =>  $finance_info['f_uin'],
            'f_coin_id' => $coin_id,
            'f_coin_addr' => $val['address'],
            'f_coin_key' => $val['txid'],
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
        $conn->trans_complete();
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"充值失败,开始回滚数据! [txid:{$val['txid']}] [vol:{$val['amount']}] [coinid:{$coin_id}]",$this->log_filename);
        }
        else
        {
            cilog('debug',"充值成功! [txid:{$val['txid']}] [vol:{$val['amount']}] [coinid:{$coin_id}]",$this->log_filename);
        }
    }

    private function handleSend($val)
    {
        cilog('debug',"[fun:handleSend] ====> 发送虚拟币 [txid:{$val['txid']}]",$this->log_filename);
        if($val['category'] != 'send'){
            cilog('debug',"当前单据状态不为发送!",$this->log_filename);
            return -1;
        }elseif ($val['confirmations'] < 4){
            cilog('debug',"该单据没有被确认! [conf:{$val['confirmations']}]",$this->log_filename);
            return -1;
        }
        cilog('debug','send deal:'.$val['txid'],$this->log_filename);
        // 开始转币
    }

    private function create_coin_uin_fiancne_info($coinid,$uin,$coin_desc)
    {
        $where = array(
            'f_uin' => $uin,
            'f_coin_id' => $coinid,
        );
        $tablename = 't_finance_info';

        $count = $this->finance_service->Model_t_finance_info->count($this->conn,$tablename,$where);
        if((int)$count !== 0){
            cilog('error',"该信息已经存在,无需继续创建! [uin:{$uin}] [coinid:{$coinid}] [count:{$count}]",$this->log_filename);
            return -1;
        }

        $flag = $this->finance_service->Model_t_finance_info->save(
            $conn=$this->conn,
            $tablename,
            $data=array(
                'f_uin' => $uin,
                'f_coin_id' => $coinid,
                'f_coin_abbr' => $coin_desc,
                'f_coin_addr' => '',
                'f_total_vol' => 0,
                'f_freeze_vol' => 0,
                'f_can_use_vol' => 0,
                'f_create_time' => timestamp2time(),
                'f_modify_time' => timestamp2time(),
            )
        );

        if($flag === FALSE){
            cilog('error',"创建财务信息失败! [uin:{$uin}] [coinid:{$coinid}]",$this->log_filename);
        }else{
            cilog('debug',"创建财务信息成功! [uin:{$uin}] [coinid:{$coinid}]",$this->log_filename);
        }
        return $flag;
    }

    private function add_coin_address($coinid,$uin)
    {
        $where = array(
            'f_uin' => $uin,
            'f_coin_id' => $coinid,
        );
        $tablename = 't_finance_info';

        $finance_info = $this->user_service->Model_t_uin->find_by_attributes(
            $conn = $this->conn,
            $select = NULL,
            $tablename,
            $where,
            $sort = NULL
        );

        if(!is_array($finance_info)){
            cilog('error',"获取用户财务信息失败! [uin:{$uin}] [coinid:{$coinid}]",$this->log_filename);
            return -1;
        }

        if($finance_info['f_coin_addr']){
            cilog('debug',"用户财务信息中有收币地址信息! [uin:{$uin}] [coinid:{$coinid}] [addr:{$finance_info['f_coin_addr']}]",$this->log_filename);
            return -1;
        }

        // 开始添加币种地址信息
        $account = ENVIRONMENT."_".$uin."_".$coinid;
        $address = $this->create_addr($account);
        $attributes = array(
            'f_coin_addr' => $address,
            'f_modify_time' => timestamp2time(),
        );
        $flag = $this->finance_service->Model_t_finance_info->update_all(
            $conn=$this->conn,
            $tablename,
            $attributes,
            $where = $where
        );
        if($flag === 0){
            cilog('debug',"添加财务信息中有收币地址信息成功! [uin:{$uin}] [coinid:{$coinid}] [account:{$account}] [address:{$address}]",$this->log_filename);
        }else{
            cilog('debug',"添加财务信息中有收币地址信息失败! [uin:{$uin}] [coinid:{$coinid}] [account:{$account}] [address:{$address}]",$this->log_filename);
        }
        return $flag;
    }

    public function add_finance_info($key)
    {
        $this->init_cron($key,$this->log_filename,"add_finance_info");
        cilog('debug',"======== 开始添加财务信息! ========",$this->log_filename);
        $tablename = 't_uin';
        $list = $this->user_service->Model_t_uin->find_all(
            $conn=$this->conn,
            $select='f_uin',
            $tablename,
            $where = array(),
            $limit = 10000,
            $page = 1,
            $sort = 'f_uin desc'
        );
        $coin_id = 10011;
        $coin_desc = "GTS";

        foreach ($list as $row){
            cilog('debug'," ====> {$row['f_uin']}",$this->log_filename);
            $this->create_coin_uin_fiancne_info($coin_id,$row['f_uin'],$coin_desc);
            $this->add_coin_address($coin_id,$row['f_uin']);
        }
        $this->get_trade_list();
        cilog('debug',"done!",$this->log_filename);
    }
}

//class Txid{
//    var $account = "";
//    var $address = "";
//    var $category = "";
//    var $fee = 0;
//    var $confirmations = 0;
//    var $blockhash = '';
//    var $blockindex = 0;
//    var $blocktime = 0;
//    var $txid = '';
//    var $time = 0;
//    var $timereceived = 0;
//    var $comment = '';
//    var $message = '';
//}


