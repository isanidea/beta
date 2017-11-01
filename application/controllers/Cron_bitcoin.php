<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/easybitcoin.php';

class Cron_bitcoin extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model("finance/Model_t_finance_info");
        $this->load->model("finance/Model_t_finance_log");
        $this->load->service("user/user_service");
        $this->load->service("finance/finance_service");
        $this->conn = $this->load->database('trade_user',TRUE);
        $this->data = array(
//            '10006' => array(
//                'username' => 'FBI@2017.user',
//                'password' => 'FBI@2017.passwd',
//                'host' => '47.91.242.194',
//                'port' => 9332,
//            ),
            '10001' => array(
                'username' => 'FBI@2017.user',
                'password' => 'FBI2017.wallet',
                'host' => '47.52.78.107',
                'port' => 8332,
            )
        );
        $this->log_filename = "bitcoin_trade_";
        $this->time_out_data = 20;
    }

    private function init_conn($conf)
    {
        return new Bitcoin($conf['username'],$conf['password'],$conf['host'],$conf['port']);
    }

    /**
     * 检查用户有没有币种账号,如果没有添加币种账号
     */
    public function get_address($key)
    {
        $log_filename = "get_address_";
        $fun_title = "开始检查用户是否有币种收货的地址!";
        $this->init_cron($key, $log_filename, $fun_title);

        $redis_key = "get_address_cron_option";
        $value = $this->cache->redis->get($redis_key);
        if($value){
            cilog('error',"程序执行中,直接退出等待下次执行! [option:{$value}]",$log_filename);
            exit();
        }

        $this->cache->redis->save($redis_key,"on",3600);

        foreach ($this->data as $key => $value){
            // 获取用户信息
            $coin_id = $key;
            $fbi_conf = $value;
            $count = $this->Model_t_finance_info->count(
                $conn=$this->conn,
                $tablename = $this->Model_t_finance_info->get_tablename(),
                $where=array(
                    'f_coin_addr' => '',
                    'f_coin_id' => $coin_id
                )
            );
            if($count <= 0){
                cilog('error',"所有用户该币种下都有地址信息! [addr:{$coin_id}]",$log_filename);
                $this->cache->redis->delete($redis_key);
                return -1;
            }

            cilog('debug',"本次共有{$count}个用户需要添加收币地址信息!",$log_filename);
            $list = $this->Model_t_finance_info->find_all(
                $conn=$this->conn,
                $select='f_id,f_uin,f_coin_id',
                $tablename = $this->Model_t_finance_info->get_tablename(),
                $where=array(
                    'f_coin_addr' => "",
                    'f_coin_id' => $coin_id
                ),
                $limit = 0,
                $page = 1,
                $sort = 'f_create_time asc'
            );

            $coin_conn = $this->init_conn($fbi_conf);
            foreach ($list as $row){
                cilog('debug',"当前处理用户 [uin:{$row['f_uin']}] [coin_id:{$row['f_coin_id']}]",$log_filename);
                $account = $row['f_uin']."_".$row['f_coin_id'];
                $address = $coin_conn->getaddressesbyaccount($account);
                if($address === []){
                    $address = $coin_conn->getnewaddress($account);
                    if($address){
                        cilog('debug',"开始添加币种地址!",$log_filename);
                        $this->Model_t_finance_info->update_all(
                            $conn=$this->conn,
                            $tablename = $this->Model_t_finance_info->get_tablename(),
                            $attributes = array(
                                'f_coin_addr' => $address,
                                'f_modify_time' => timestamp2time(),
                            ),
                            $where = array(
                                'f_id' => $row['f_id']
                            )
                        );
                        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$coin_id;
                        $this->cache->redis->delete($key);
                    }else{
                        cilog('error',"添加收币地址失败!",$log_filename);
                    }
                }else{
                    cilog('debug',"该用户已经有了收币地址!",$log_filename);
                    cilog('debug',$address,$log_filename);
                }
            }
        }
        cilog('debug',"全流程结束!",$log_filename);
        $this->cache->redis->delete($redis_key);
        return -1;
    }

    /**
     * 废弃
     */
    public function get_address_v2()
    {
        $log_filename = "trade_v2_";
        foreach ($this->data as $key => $value){
            // 获取用户信息
            $coin_id = $key;
            $fbi_conf = $value;
            $count = $this->Model_t_finance_info->count(
                $conn=$this->conn,
                $tablename = $this->Model_t_finance_info->get_tablename(),
                $where=array(
                    // 'f_coin_addr <>' => '',
                    'f_coin_id' => $coin_id
                )
            );
            if($count <= 0){
                cilog('error',"所有用户该币种下都有地址信息! [coin_id:{$coin_id}]",$log_filename);
                render_json(0,'',TRUE);
            }

            cilog('debug',"本次共有{$count}个用户需要添加收币地址信息!",$log_filename);
            $list = $this->Model_t_finance_info->find_all(
                $conn=$this->conn,
                $select='f_id,f_uin,f_coin_id',
                $tablename = $this->Model_t_finance_info->get_tablename(),
                $where=array(
                    // 'f_coin_addr <>' => "",
                    'f_coin_id' => $coin_id
                ),
                $limit = 0,
                $page = 1,
                $sort = 'f_create_time asc'
            );

            $coin_conn = $this->init_conn($fbi_conf);
            foreach ($list as $row){
                cilog('debug',"当前处理用户 [uin:{$row['f_uin']}] [coin_id:{$row['f_coin_id']}]",$log_filename);
                $account = $row['f_uin']."_".$row['f_coin_id'];
                $address = $coin_conn->getnewaddress($account);
                if($address) {
                    cilog('debug',"开始添加币种地址!",$log_filename);
                    $this->Model_t_finance_info->update_all(
                        $conn=$this->conn,
                        $tablename = $this->Model_t_finance_info->get_tablename(),
                        $attributes = array(
                            'f_coin_addr' => $address,
                            'f_modify_time' => timestamp2time(),
                        ),
                        $where = array(
                            'f_id' => $row['f_id']
                        )
                    );
                    $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$coin_id;
                    $this->cache->redis->delete($key);
                }else{
                cilog('error',"添加收币地址失败!",$log_filename);
                }
            }
        }
        cilog('debug',"全流程结束!",$log_filename);
        render_json();
    }

    /**
     * 遍历最近的交易信息
     */
    public function trade()
    {
        // 比特币
        $fbi_conf = $this->data['10001'];
        $coin_id = 10001;
        $coin_conn = $this->init_conn($fbi_conf);
        $list =  $coin_conn->listtransactions("*",1000,0);
        // render_json(0,'',$list);
        foreach($list as $key => $val){
            if($val ['category'] == 'receive'){
                // 接受的交易  充值到平台
                $this->handleReceive($val,$this->conn,$coin_id);
            }elseif($val ['category'] == 'send'){
                // 发送的交易  转账出去
                // $this->handleSend($val,$this->conn,$coin_id);
            }
        }
    }

    /**
     * 这里是系统用户向平台充币的处理,由于充币事先是不知道的
     * 这里需要动态生成订单
     * txid 是交易的唯一编号
     */
    public function handleReceive($item,$conn,$coin_id){
        // 查询该地址信息是否存在于本地db中
        $finance_info = $this->Model_t_finance_info->find_by_attributes(
            $conn,
            $select = NULL,
            $tablename = $this->Model_t_finance_info->get_tablename(),
            $where = array(
                'f_coin_addr' => $item['address']
            ),
            $sort = NULL
        );

        if(!$finance_info){
            cilog('error',"该记录不在本地db中,直接退出",$this->log_filename);
            return -1;
        }

        // 判断交易单号是否在表中
        $count = $this->Model_t_finance_log->count(
            $conn,
            $tablename=$this->Model_t_finance_log->get_tablename(),
            $where = array(
                'f_coin_key' => $item['txid']
            )
        );
        if($count == 0){
            $conn->trans_start();
            cilog('error',"该记录不在本地db中,需要写入本地记录",$this->log_filename);
            $this->Model_t_finance_log->save(
                $conn,
                $tablename=$this->Model_t_finance_log->get_tablename(),
                $data = array(
                    'f_type' => $this->finance_service->finance_type['COIN_IN'],
                    'f_uin' =>  $finance_info['f_uin'],
                    'f_coin_id' => $coin_id,
                    'f_coin_addr' => $item['address'],
                    'f_coin_key' => $item['txid'],
                    'f_vol' => $item['amount'],
                    'f_state' => $this->finance_service->finance_state['SYS_COF'],
                    'f_create_time' => timestamp2time(),
                    'f_modify_time' => timestamp2time(),
                )
            );
            $this->Model_t_finance_info->update_all(
                $conn,
                $tablename = $this->Model_t_finance_info->get_tablename(),
                $attributes = array(
                    'f_total_vol' => $finance_info['f_total_vol'] + $item['amount'],
                    'f_can_use_vol' => $finance_info['f_can_use_vol'] + $item['amount'],
                    'f_modify_time' => timestamp2time()
                ),
                $where = array(
                    'f_uin' => $finance_info['f_uin'],
                    'f_coin_id' => $coin_id,
                )
            );
            $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $finance_info['f_uin'] . "_" . $coin_id;
            $this->cache->redis->delete($key);
            cilog('debug',"添加充币记录到本地db! [txid:{$item['txid']}] [vol:{$item['amount']}] [coinid:{$coin_id}]",$this->log_filename);
            $conn->trans_complete();
            if ($conn->trans_status() === FALSE)
            {
                cilog('error',"充值失败,开始回滚数据!");
            }
            else
            {
                cilog('debug',"充值成功!");
            }
        }
    }

    /**
     * 转币给其他用户
     */
    public function handleSend()
    {
        cilog('debug',"开始",$this->log_filename);
        $list = $this->Model_t_finance_log->find_all(
            $conn=$this->conn,
            $select=NULL,
            $tablename=$this->Model_t_finance_log->get_tablename(),
            $where = array(
                'f_type' => $this->finance_service->finance_type['COIN_OUT'],
                'f_state' => $this->finance_service->finance_state['SYS_COF'],
                'f_coin_id' => '10003'
            ),
            $limit = 100,
            $page = 1,
            $sort = 'f_create_time asc'
        );
        cilog('debug',$list,$this->log_filename);

        $coin_conn = $this->init_conn($this->data['10003']);
        foreach ($list as $row){
            // $from_account = $row['f_uin']."_".$row['f_coin_id'];
            $to_addr = $row['f_coin_addr'];
            $amount = $row['f_vol'];
            $uin = $row['f_uin'];
            $coin_id=$row['f_coin_id'];
            $flag = $this->sys_send_to_user($coin_conn,$to_addr,$amount,$this->data['10003']['password']);
            if(strlen($flag) < 6){
                cilog('error',"转币失败 [err:{$flag}]",$this->log_filename);
                continue;
            }
            cilog('error',"转币成功！",$this->log_filename);
            // 转币成功 改变单据状态
            $this->Model_t_finance_log->update_all(
                $conn=$this->conn,
                $tablename=$this->Model_t_finance_log->get_tablename(),
                $attributes=array(
                    'f_modify_time' => timestamp2time(), 
                    'f_state' => $this->finance_service->finance_state['SCUESS'], 
                    'f_coin_key' => $flag
                ), 
                $where = array('f_id' => $row['f_id'])
            );

            $finance_info = $this->finance_service->get_finance_info($this->conn, $uin, $coin_id);

            $this->Model_t_finance_info->update_all(
                $conn=$this->conn,
                $tablename=$this->Model_t_finance_info->get_tablename(),
                $attributes=array(
                    'f_modify_time' => timestamp2time(), 
                    'f_total_vol' => $finance_info['f_total_vol'] + $amount, 
                    'f_can_use_vol' => $finance_info['f_can_use_vol'] + $amount
                ), 
                $where = array(
                    'f_uin' => $uin,
                    'f_coin_id' => $coin_id
                )
            );
            $key = $this->finance_service->finance_redis_key['FINANCE_INFO'] . $uin . "_" . $coin_id;
            $this->cache->redis->delete($key);
        }
        cilog('debug',"done",$this->log_filename);
    }

    public function send_coin($coin_conn,$from_account,$to_addr,$amount,$pw){
        cilog('debug',"开始转币 [from:{$from_account}] [to:{$to_addr}] [amount:{$amount}]",$this->log_filename);

        $isAddr = $coin_conn->validateaddress($to_addr);
        if(!$isAddr["isvalid"]){
            cilog('error',"该地址错误! addr:{$address}",$log_filename);
            return -1;
        } 
        $coin_conn->walletpassphrase($pw,$this->time_out_data);
        $flag = $coin_conn->sendfrom($from_account,$to_addr,$amount);
        $coin_conn->walletlock();
        return $flag;
    }

    public function sys_send_to_user($coin_conn,$to_addr,$amount,$pw)
    {
        cilog('debug',"开始系统转币 [to:{$to_addr}] [amount:{$amount}] [pw:{$pw}]",$this->log_filename);
        $isAddr = $coin_conn->validateaddress($to_addr);
        cilog('error',$isAddr,$this->log_filename);
        if(!$isAddr["isvalid"]){
            cilog('error',"该地址错误! addr:{$to_addr}",$this->log_filename);
            cilog('error',$isAddr,$this->log_filename);
            return -1;
        }

        $info = $coin_conn->getinfo();
        cilog('error',"钱包信息!",$this->log_filename);
        cilog('error',$info,$this->log_filename);

        $coin_conn->walletlock();
        $coin_conn->walletpassphrase($pw,$this->time_out_data);
        $flag = $coin_conn->sendtoaddress($to_addr,round($amount));
        cilog('debug',"转币结果如下",$this->log_filename);
        cilog('error',$flag,$this->log_filename);
        $coin_conn->walletlock();

        return $flag;
    }

    public function query_txid()
    {
        $fbi_conf = array(
            'username' => 'FBI@2017.user',
            'password' => 'FBI2017.wallet',
            'host' => '47.52.78.107',
            'port' => 8332,
        );
        $coin_id = 10001;
        $coin_conn = $this->init_conn($fbi_conf);

        $txid = get_post_value('id');

        $data = $coin_conn->gettransaction($txid);
        // $data = $coin_conn->validateaddress($txid);
        render_json(0,'',$data);
    }

    public function test()
    {
        $data = $this->Model_t_finance_info->find_by_attributes(
            $conn=$this->conn,
            $select = NULL,
            $tablename = $this->Model_t_finance_info->get_tablename(),
            $where = array(
                'f_id' => 3
            ),
            $sort = NULL
        );
        render_json(0,'',$data);
    }

    public function newaddr()
    {
        $this->init_log();
        $this->init_api();

        $label = 'test';
        $address = $this->bitcoin->getaddressesbyaccount($label);
        // render_json(0,'123',$address);
        if(!is_array($address)){
            $address = $this->bitcoin->getnewaddress($label);
            if(!$address){
                // return false ;
                render_json(0,'',false);
            }
        }else{
            render_json(0,'',$address);
        }
    }

    public function check_wallet()
    {
        $this->init_log();
        $this->init_api();

        $address = get_post_value('addr');

        $isAddr = $this->bitcoin->validateaddress($address);
        if(!$isAddr["isvalid"]){
            cilog('error',"该地址错误! addr:{$address}");
            render_json(0,'',FALSE);
        }else{
            cilog('debug',"该地址正确! addr:{$address}");
            render_json(0,'',TRUE);
        }
    }

    public function trade1()
    {
        $list =  $this->bitcoin->listtransactions("*",1,0);
        foreach($list as $key => $val){
            if($val ['category'] == 'receive'){
                // 接受的交易
                echo "接受的交易".$key.":".$val."</br>";
            }elseif($val ['category'] == 'send'){
                // 发送的交易
                $this->handleSend($val);
                echo "发送的交易".$key.":".$val."</br>";
            }
        }
        render_json(0,'',$list);
    }

    public function get_new_address()
    {
        $this->bitcoin = $this->init_conn($this->data['10001']);
        // $account = "fbishare1"; // 1BfnGxXbtCeAGfrvz49fDCGVvig4c3jfDz
        $account = "10006_10161";
        // $list = $this->bitcoin->getaddressesbyaccount($account);
        // $list = $this->bitcoin->getnewaddress($account);
        $list = $this->bitcoin->listtransactions("*",10,0);
        // $list = $this->bitcoin->getaccountaddress($account);
        // $list = $this->bitcoin->getaccount("LT2TeHzDyX9KqEBS471yKisGstMzJQ69hd");
        $walletinfo = $this->bitcoin->getwalletinfo();
        $b_info = $this->bitcoin->listaccounts();
        render_json(0,'',array(
            'walletinfo'=>$walletinfo,
            'trade_list'=>$list,
            'balance_info' =>$b_info
        ));
    }
}