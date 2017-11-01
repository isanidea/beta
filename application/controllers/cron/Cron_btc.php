<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/easybitcoin.php';

class Cron_btc extends MY_Controller {

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
            'host' => '47.52.78.107',
            'port' => 8332,
        );
        $this->log_filename = "btc_wallet_";
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
        $trade_list = $this->wallet_conn->listtransactions("*",1000,0);
        render_json(0,'',$trade_list);

    }
}