<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *  All methods of this class are derived from the bitcoin API
 * https://en.bitcoin.it/wiki/Original_Bitcoin_client/API_Calls_list
 *
 * 钱包服务  0x2007
 */
require_once APPPATH.'libraries/easybitcoin.php';
class Bitcoin_service extends MY_Service {

    public function __construct(){
        parent::__construct();
        $this->key = "FBI2017.wallet";
        $this->timeout = 20; // 20秒
        $this->wallet_errcode = array(
            'BITCOIN_CREATE_ADDRESS_ERR'   => 0x20070000,     // 创建钱包地址失败
            'BITCOIN_ADDRESS_ERR'          => 0x20070001,     // 钱包地址无效
            'BITCOIN_HAVE_NO_ENOUCH_MONEY' => 0x20070002,     // 钱包余额不足
            'BITCOIN_COIN_TURN_ERR'        => 0x20070003,     // 转币失败
        );
    }

    /**
     * 给站内用户生成一个地址，表结构大致如下　
     * --------------------------------
     * id   |     address |     username
     * ---------------------------------
     * @param $label 用户->address 一对多的关系
     * @return array
     */
    public function create_address($wallet_conn,$label)
    {
        // 这步骤是获取这个标签的钱包地址，如果已经存在则返回，
        $address = $wallet_conn->getaddressesbyaccount($label);
        if(!is_array($address)){
            $address = $wallet_conn->getnewaddress($label);
            if(!$address){
                cilog('error','生成钱包地址失败!');
                return $this->wallet_errcode['BITCOIN_CREATE_ADDRESS_ERR'] ;
            }
        }else{
            $address = $address [0];
        }
        cilog('debug',"生成钱包地址成功! [address:{$address}]");
        return array('address' => $address) ;
    }

    /**
     * 将币转出，平台会有转出币钱包场景，在这之前需要判断是否是站内互转
     * 在这之前需要判断是否是一个有效的钱包地址
     * 该步骤通常以后台任务队列运行
     */
    public function turnout($wallet_conn,$address,$number)
    {
        // 判断是否是一个有效的钱包地址
        $isAddr = $wallet_conn->validateaddress($address);
        if(!$isAddr){
            cilog('error',"钱包地址无效! [address:{$address}]");
            return $this->wallet_errcode['BITCOIN_ADDRESS_ERR'] ;
        }

        // 获取钱包的余额
        $info = $wallet_conn->getinfo();
        if($info['balance'] < $number ){
            cilog('error',"钱包余额不足! [address:{$address}] [balance:{$info['balance']}] [num:{$number}]");
            return $this->wallet_errcode['BITCOIN_HAVE_NO_ENOUCH_MONEY'] ;
        }

        // 开始转币接口
        $wallet_conn->walletlock();//强制上锁
        $wallet_conn->walletpassphrase($this->key,$this->timeout);
        $id=$wallet_conn->sendtoaddress($address, $number );
        if(!isset($id)){
            cilog('error',"系统转币失败!");
            return $this->wallet_errcode['BITCOIN_HAVE_NO_ENOUCH_MONEY'] ;
        }
        $wallet_conn->walletlock();
        return array('id'=>$id);
    }

    /**
     * 向多人转币
     * $arr_data = array('address'=>mount);
     */
    public function turn_many_out($wallet_conn,$arr_data)
    {
        // 校验地址是否合法
        $num = 0;
        foreach ($arr_data as $key => $value){
            $isAddr = $wallet_conn->validateaddress($key);
            if(!$isAddr){
                cilog('error',"钱包地址无效! [address:{$key}]");
                return $this->wallet_errcode['BITCOIN_ADDRESS_ERR'] ;
            }
            $num += $value;
        }

        // 校验转币总金额是否足额
        $info = $wallet_conn->getinfo();
        if($info['balance'] < $num ){
            cilog('error',"钱包余额不足! [balance:{$info['balance']}] [num:{$num}]");
            return $this->wallet_errcode['BITCOIN_HAVE_NO_ENOUCH_MONEY'] ;
        }

        // 开始转币接口
        $wallet_conn->walletlock();//强制上锁
        $wallet_conn->walletpassphrase($this->key,$this->timeout);
        $id=$wallet_conn->sendmany($address, $number );
        if(!isset($id)){
            cilog('error',"系统转币失败!");
            return $this->wallet_errcode['BITCOIN_HAVE_NO_ENOUCH_MONEY'] ;
        }
        $wallet_conn->walletlock();
        return array('id'=>$id);
    }

    /**
     * 交易查询接口.
     */
    public function trade()
    {
        // 该方法　有３个参数，第一个是指查询标签的意思* 查找所有用户 123456 ，查找123456的交易情况
        $list =  $this->client->listtransactions("*",100,0);
        foreach($list as $key => $val){
            if($val ['category'] == 'receive'){
                // 接受的交易
                $this->handleReceive($val);
            }elseif($val ['category'] == 'send'){
                // 发送的交易
                $this->handleSend($val);
            }
        }
    }

    // 这里是系统用户向平台充币的处理，
    // 由于充币事先是不知道的，这里需要动态生成订单
    // txid 是交易的唯一编号 注意启动事务
    private function handleReceive($item)
    {
        $address = DB::table('address')->where(['address' => $item['address']])->get();
        if(!$address){
            return ;
        }
        $row = DB::table('trade')->where(['txid' => $item['txid'] , 'status' => 0 , 'type' => 'receive'])->get();
        if($row){
            // 如果有订单，判断确认次数
            if($item['confirmations'] >= 2){
                $row->save(['status' => 1 , 'confirmations' => $item['confirmations']]);
                // 给用户加钱
                $user = $address->user()->increment('coin', $item['amount']);
            }
        }else{
            $arr = [
                'confirmations' => $item['confirmations'],
                'user_id' => $address->user->id ,
                'amount' => $item['amount'],
                'txid' => $item['txid'],
            ];
            if($item['confirmations'] >= 2){
                $arr ['status'] = 1 ;
                $user = $address->user()->increment('coin', $item['amount']);
            }else{
                $arr ['status'] = 0;
            }
            DB::table('trade')->create($arr);
        }
    }

    // 处理转出情况，转出是用户发起的，不需要动态插表
    private function handleSend($item)
    {
        $row = DB::where(['address' => $item['address']])->get();
        if(!$row){
            return ;
        }
        if($item['confirmations'] >= 2){
            $row->save(['status' => 1,'txid' => $item['txid'] , 'confirmations' => $item['confirmations']]);
            // 可能需要处理用户币的冻结情况，
        }
    }

}
