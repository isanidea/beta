<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class 定时任务
 *
 * /usr/local/php7/bin/php index.php cron/cron_matchmaking_trading/trade rasine 10012
 */

class Cron_matchmaking_trading extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->get_db_conn();
        $this->load->model("coin/Model_t_coin");
        $this->load->model("deal/Model_t_bdeal");
        $this->load->model("deal/Model_t_deal");
        $this->load->model("finance/Model_t_finance_info");
        $this->load->service('deal/deal_service');
        $this->load->service("finance/finance_service");
        $this->load->service("coin/coin_service");
        $this->trade_more_key = "trade_more_bdealid";
        $this->log_filename = "matchmaking_trading_";
        $this->redis_key = $this->deal_service->deal_redis_key['TO_BE_DEAL']."10012";
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
    public function trade($key,$coin_id,$num)
    {
        $log_filename = $this->log_filename;
        $fun_title = "开始撮合交易!";
        $this->init_cron($key,$log_filename,$fun_title);
        $redis_key = $this->deal_service->deal_redis_key['TO_BE_DEAL'].$coin_id;

        // 1. 检查币种交易开关
        if($this->cache->redis->get($redis_key)){
            // 取到数据,表示该点正在执行
            cilog('error',"[coinid:{$coin_id}] 当前有正在执行的队列!直接退出,等待下次执行!",$this->log_filename);
            exit();
        }
        $this->cache->redis->save($redis_key,1,$this->deal_service->deal_redis_key['TIMEOUT']);
        cilog('debug',"[coinid:{$coin_id}] 开始交易,开启交易开关!",$this->log_filename);

        for ($i=1;$i<=$num;$i++)
        {
            usleep(1000000);
            cilog('debug',"开始买单维度交易!",$this->log_filename);
            // $this->trade_by_buyer($coin_id);
            $this->trade_by_buyer_simple($coin_id);

            cilog('debug',"开始卖单维度交易!",$this->log_filename);
            // $this->trade_by_sell($coin_id);
            $this->trade_by_sell_simple($coin_id);

            cilog('debug',"处理完成一次交易,等待100毫秒继续处理,当前序号:{$i}",$this->log_filename);
        }
        $this->cache->redis->delete($redis_key);
        cilog('debug',"done",$this->log_filename);
    }

    /**
     * 多次交易
     */
    public function trade_more($key,$coin_id,$num)
    {
        $log_filename = $this->log_filename;
        $fun_title = "开始撮合交易!";
        $this->init_cron($key,$log_filename,$fun_title);
        $redis_key = $this->deal_service->deal_redis_key['TO_BE_DEAL'].$coin_id;

        // 1. 检查币种交易开关
        if($this->cache->redis->get($redis_key)){
            // 取到数据,表示该点正在执行
            cilog('error',"[coinid:{$coin_id}] 当前有正在执行的队列!直接退出,等待下次执行!",$this->log_filename);
            exit();
        }
        $this->cache->redis->save($redis_key,1,$this->deal_service->deal_redis_key['TIMEOUT']);
        cilog('debug',"[coinid:{$coin_id}] 开始交易,开启交易开关!",$this->log_filename);

        for ($i=1;$i<=$num;$i++)
        {
            usleep(1000000);
            cilog('debug',"等待100毫秒继续处理,当前序号:{$i}",$this->log_filename);
            $value = $this->cache->redis->get($this->trade_more_key);
            if(!$value){
                // 开始单个正常交易
//                cilog('debug',"开始买单维度交易!",$this->log_filename);
//                $aQuery = array(
//                    'f_type' => $this->deal_service->type['BUY'],
//                    'f_state' => $this->deal_service->state['DEAL_DURING'],
//                );
//                $sort = "f_price desc";
//                $buy_bdeal_list = $this->deal_service->Model_t_bdeal->get_bdeal_list_by_coin_id($this->conn,$coin_id,1,1,$aQuery,$sort);
//                if((int)$buy_bdeal_list['total'] === 0){
//                    cilog('error',"当前db中找不到合适的买单信息,退出,等待下次执行!",$this->log_filename);
//                    continue;
//                }
//
//                $trade_info = array(
//                    'bdealid' => $buy_bdeal_list['rows'][0]['f_bdeal_id'],
//                    'type' => $this->deal_service->type['BUY']
//                );
                $aQuery = array(
                    'f_type' => $this->deal_service->type['SELL'],
                    'f_state' => $this->deal_service->state['DEAL_DURING'],
                );
                $sort = "f_price asc";
                $sell_bdeal_list = $this->deal_service->Model_t_bdeal->get_bdeal_list_by_coin_id($this->conn,$coin_id,1,1,$aQuery,$sort);
                if((int)$sell_bdeal_list['total'] === 0){
                    cilog('error',"当前db中找不到合适的卖单信息,退出,等待下次执行!",$this->log_filename);
                    continue;
                }
                $trade_info = array(
                    'bdealid' => $sell_bdeal_list['rows'][0]['f_bdeal_id'],
                    'type' => $this->deal_service->type['SELL']
                );
            }else{
                $trade_info = unserialize($value);
            }

            $bdealinfo = $this->deal_service->Model_t_bdeal->get_bdeal_info_by_bdealid($this->conn,$trade_info['bdealid'],$aQuery=array());
            if(!$bdealinfo){
                cilog('error',"获取大单信息失败! [bdealid:{$trade_info['bdealid']}]",$this->log_filename);
                continue;
            }
            cilog('debug',$bdealinfo,$this->log_filename);
            $type = $trade_info['type'];

            cilog('debug',"开始交易,当前交易 [bdealid:{$trade_info['bdealid']}] [type:{$type}]",$this->log_filename);
            if((int)$type === $this->deal_service->type['SELL']){
                $this->trade_by_seller($this->conn,$coin_id,$bdealinfo);
            }elseif ((int)$type === $this->deal_service->type['BUY']){
                $this->trade_by_buyer($this->conn,$coin_id,$bdealinfo);
            }else{
                cilog('error',"参数错误! type:{$type}",$this->log_filename);
            }
        }
        $this->cache->redis->delete($redis_key);
        cilog('debug',"done",$this->log_filename);
    }

    private function trade_by_buyer($conn,$coin_id,$buy_bdeal_info)
    {
        // 查找卖一单
        $buy_price = $buy_bdeal_info['f_price'];
        $sell_list = $this->get_sell_one_list($coin_id,$buy_price);

        if(!$sell_list){
            cilog('error',"当前找不到合适的卖单数据,退出,等待下次执行!",$this->log_filename);
            return -1;
        }

        $sell_bdeal_info = $sell_list[0];
        cilog('debug'," db找到的卖一单信息数据如下",$this->log_filename);
        cilog('debug',$sell_bdeal_info,$this->log_filename);
        cilog('debug',"[trade_by_buyer]开始交易! sell [bdealid:{$sell_bdeal_info['f_bdeal_id']}] [price:{$sell_bdeal_info['f_price']}] [vol:{$sell_bdeal_info['f_pre_deal_vol']}]",$this->log_filename);
        cilog('debug',"开始交易! buy  [bdealid:{$buy_bdeal_info['f_bdeal_id']}] [price:{$buy_bdeal_info['f_price']}] [vol:{$buy_bdeal_info['f_pre_deal_vol']}]",$this->log_filename);
        cilog('debug',"deal_price:{$sell_bdeal_info['f_price']}",$this->log_filename);

        if($sell_bdeal_info['f_pre_deal_vol'] * $sell_bdeal_info['f_pre_deal_vol'] <= 0){
            cilog('debug',"参数错误! [sell_num:{$sell_bdeal_info['f_pre_deal_vol']}] [buy_num:{$buy_bdeal_info['f_pre_deal_vol']}]",$this->log_filename);
            return -1;
        }

        $deal_price = $sell_bdeal_info['f_price'];
        $buy_deal_num = get_base_num($buy_bdeal_info['f_pre_deal_vol']);
        $sell_deal_num = get_base_num($sell_bdeal_info['f_pre_deal_vol']);
        cilog('debug',"[trade_by_buyer] 当前单量 [buy:{$buy_deal_num}] [sell:{$sell_deal_num}]",$this->log_filename);
        if((string)$buy_deal_num > (string)$sell_deal_num) {
            $deal_num = $sell_deal_num;
            cilog('debug',"当前成交量为 num:{$deal_num}",$this->log_filename);
            $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DURING']);
            $this->fill_sell_bdeal($conn,$sell_bdeal_info['f_uin'],$coin_id,$sell_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);

            cilog('debug',"该情况下买单没有完成交易,写入redis记录下来,下次优先交易!",$this->log_filename);
            $redis_value = array(
                'bdealid' => $buy_bdeal_info['f_bdeal_id'],
                'type' => $this->deal_service->type['BUY'],
            );
            $data = serialize($redis_value);
            $this->cache->redis->save($this->trade_more_key,$data,3600);
        }elseif((string)$buy_deal_num === (string)$sell_deal_num){
            $deal_num = $sell_deal_num;
            cilog('debug',"当前成交量为 num:{$deal_num}",$this->log_filename);
            $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
            $this->fill_sell_bdeal($conn,$sell_bdeal_info['f_uin'],$coin_id,$sell_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);

            cilog('debug',"当前买卖双方均完全成交!",$this->log_filename);
            $this->cache->redis->delete($this->trade_more_key);
        }elseif((string)$buy_deal_num < (string)$sell_deal_num) {
            $deal_num = $buy_deal_num;
            cilog('debug',"当前成交量为 num:{$deal_num}",$this->log_filename);
            $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
            $this->fill_sell_bdeal($conn,$sell_bdeal_info['f_uin'],$coin_id,$sell_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DURING']);

            cilog('debug',"该情况下卖单没有完成交易,写入redis记录下来,下次优先交易!",$this->log_filename);
            $redis_value = array(
                'bdealid' => $sell_bdeal_info['f_bdeal_id'],
                'type' => $this->deal_service->type['SELL'],
            );
            $data = serialize($redis_value);
            $this->cache->redis->save($this->trade_more_key,$data,3600);
        }else{
            cilog('debug',"[trade_by_buyer] 找不到对应关系!",$this->log_filename);
        }
    }

    private function trade_by_seller($conn,$coin_id,$sell_bdeal_info)
    {
        // 查找买一单
        $sell_price = $sell_bdeal_info['f_price'];
        $buy_list = $this->get_buy_one_list($coin_id,$sell_price);

        if(!$buy_list){
            cilog('error',"当前找不到合适的卖单数据,退出,等待下次执行!",$this->log_filename);
            // $this->err_exit();
            return -1;
        }

        $buy_bdeal_info = $buy_list[0];
        cilog('debug',"db找到的买一单列表数据如下",$this->log_filename);
        cilog('debug',$buy_bdeal_info,$this->log_filename);

        cilog('debug',"[trade_by_seller] 开始交易! sell [bdealid:{$sell_bdeal_info['f_bdeal_id']}] [price:{$sell_bdeal_info['f_price']}] [vol:{$sell_bdeal_info['f_pre_deal_vol']}]",$this->log_filename);
        cilog('debug',"[trade_by_seller] 开始交易! buy  [bdealid:{$buy_bdeal_info['f_bdeal_id']}] [price:{$buy_bdeal_info['f_price']}] [vol:{$buy_bdeal_info['f_pre_deal_vol']}]",$this->log_filename);
        cilog('debug',"deal_price:{$buy_bdeal_info['f_price']}",$this->log_filename);

        if($buy_bdeal_info['f_pre_deal_vol'] * $sell_bdeal_info['f_pre_deal_vol'] <=0 ){
            cilog('debug',"参数错误! [sell_num:{$sell_bdeal_info['f_pre_deal_vol']}] [buy_num:{$buy_bdeal_info['f_pre_deal_vol']}]",$this->log_filename);
            // $this->err_exit();
            return -1;
        }

        $deal_price = $buy_bdeal_info['f_price'];
        $buy_deal_num = get_base_num($buy_bdeal_info['f_pre_deal_vol']);
        $sell_deal_num = get_base_num($sell_bdeal_info['f_pre_deal_vol']);
        cilog('debug',"[trade_by_seller] 当前单量 [buy:{$buy_deal_num}] [sell:{$sell_deal_num}]",$this->log_filename);
        if((string)$buy_deal_num > (string)$sell_deal_num) {
            $deal_num = $sell_deal_num;
            cilog('debug',"当前成交量 num:{$deal_num}",$this->log_filename);
            $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DURING']);
            $this->fill_sell_bdeal($conn,$sell_bdeal_info['f_uin'],$coin_id,$sell_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);

            cilog('debug',"该情况下买单没有完成交易,写入redis记录下来,下次优先交易!",$this->log_filename);
            $redis_value = array(
                'bdealid' => $buy_bdeal_info['f_bdeal_id'],
                'type' => $this->deal_service->type['BUY'],
            );
            $data = serialize($redis_value);
            $this->cache->redis->save($this->trade_more_key,$data,3600);
        }elseif((string)$buy_deal_num === (string)$sell_deal_num){
            $deal_num = $sell_deal_num;
            cilog('debug',"当前成交量 num:{$deal_num}",$this->log_filename);
            $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
            $this->fill_sell_bdeal($conn,$sell_bdeal_info['f_uin'],$coin_id,$sell_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);

            cilog('debug',"当前买卖双方均完全成交!",$this->log_filename);
            $this->cache->redis->delete($this->trade_more_key);
        }elseif((string)$buy_deal_num < (string)$sell_deal_num){
            $deal_num = $buy_deal_num;
            cilog('debug',"当前成交量 num:{$deal_num}",$this->log_filename);
            $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
            $this->fill_sell_bdeal($conn,$sell_bdeal_info['f_uin'],$coin_id,$sell_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DURING']);

            cilog('debug',"该情况下买单没有完成交易,写入redis记录下来,下次优先交易!",$this->log_filename);
            $redis_value = array(
                'bdealid' => $sell_bdeal_info['f_bdeal_id'],
                'type' => $this->deal_service->type['SELL'],
            );
            $data = serialize($redis_value);
            $this->cache->redis->save($this->trade_more_key,$data,3600);
        }else{
            cilog('debug',"[trade_by_seller] 找不到对应关系!",$this->log_filename);
        }

    }


    /**
     * 以买单维度交易
     *
     * 1. 获取当前db中所有的待成交的买单列表
     * 2. 找到当前卖一单数据
     * 3. 买单价格>=卖一价   卖单价成交  否则不交易
     */
    private function trade_by_buyer_simple($coin_id)
    {
        $aQuery = array(
            'f_type' => $this->deal_service->type['BUY'],
            'f_state' => $this->deal_service->state['DEAL_DURING'],
        );
        $sort = "f_price desc";
        $buy_bdeal_list = $this->deal_service->Model_t_bdeal->get_bdeal_list_by_coin_id($this->conn,$coin_id,1,1,$aQuery,$sort);
        if((int)$buy_bdeal_list['total'] === 0){
            cilog('error',"当前db中找不到合适的买单信息,退出,等待下次执行!",$this->log_filename);
        }else{
            $buy_price = $buy_bdeal_list['rows'][0]['f_price'];
            $buy_num = $buy_bdeal_list['rows'][0]['f_pre_deal_vol'];
            $uin = $buy_bdeal_list['rows'][0]['f_uin'];
            $buy_bdeal_info = $buy_bdeal_list['rows'][0];
            cilog('debug',"当前需要成交的大单信息",$this->log_filename);
            cilog('debug',$buy_bdeal_info,$this->log_filename);

            // 查找卖一价的数据  按照卖单价成交
            $sell_list = $this->deal_service->Model_t_bdeal->find_all(
                $conn = $this->conn,
                $select=NULL,
                $tablename = $this->deal_service->Model_t_bdeal->get_tablename(),
                $where = array(
                    'f_type' => $this->deal_service->type['SELL'],
                    'f_state' => $this->deal_service->state['DEAL_DURING'],
                    'f_coin_id' => $coin_id,
                    'f_price <=' => $buy_price,
                ),
                $limit = 10,
                $page = 1,
                $sort = 'f_price asc'
            );

            if(!$sell_list){
                cilog('error',"当前找不到合适的卖单数据,退出,等待下次执行!",$this->log_filename);
            }else{
                $sell_bdeal_bdealinfo = $sell_list[0];
                cilog('debug',"db找到的卖一单列表数据如下",$this->log_filename);
                cilog('debug',$sell_bdeal_bdealinfo,$this->log_filename);

                cilog('debug',"开始交易! sell [bdealid:{$sell_bdeal_bdealinfo['f_bdeal_id']}] [price:{$sell_bdeal_bdealinfo['f_price']}] [vol:{$sell_bdeal_bdealinfo['f_pre_deal_vol']}]",$this->log_filename);
                cilog('debug',"开始交易! buy  [bdealid:{$buy_bdeal_info['f_bdeal_id']}] [price:{$buy_bdeal_info['f_price']}] [vol:{$buy_bdeal_info['f_pre_deal_vol']}]",$this->log_filename);
                cilog('debug',"deal_price:{$sell_bdeal_bdealinfo['f_price']}",$this->log_filename);

                if($buy_bdeal_info['f_pre_deal_vol'] * $sell_bdeal_bdealinfo['f_pre_deal_vol'] <= 0){
                    cilog('debug',"参数错误! [sell_num:{$sell_bdeal_bdealinfo['f_pre_deal_vol']}] [buy_num:{$buy_bdeal_info['f_pre_deal_vol']}]",$this->log_filename);
                    $this->err_exit();
                }

                $deal_price = $sell_bdeal_bdealinfo['f_price'];
                $buy_deal_num = get_base_num($buy_bdeal_info['f_pre_deal_vol']);
                $sell_deal_num = get_base_num($sell_bdeal_bdealinfo['f_pre_deal_vol']);
                cilog('debug',"当前单量 [buy:{$buy_deal_num} [sell:{$sell_deal_num}]",$this->log_filename);
                if($buy_deal_num > $sell_deal_num)
                {
                    $deal_num = $sell_deal_num;
                    cilog('debug',"当前成交量为 num:{$deal_num}",$this->log_filename);
                    $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DURING']);
                    $this->fill_sell_bdeal($conn,$sell_bdeal_bdealinfo['f_uin'],$coin_id,$sell_bdeal_bdealinfo,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
                }elseif($buy_deal_num = $sell_deal_num){
                    $deal_num = $sell_deal_num;
                    cilog('debug',"当前成交量为 num:{$deal_num}",$this->log_filename);
                    $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
                    $this->fill_sell_bdeal($conn,$sell_bdeal_bdealinfo['f_uin'],$coin_id,$sell_bdeal_bdealinfo,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
                }else{
                    $deal_num = $buy_deal_num;
                    cilog('debug',"当前成交量为 num:{$deal_num}",$this->log_filename);
                    $this->fill_buy_bdeal($conn,$buy_bdeal_info['f_uin'],$coin_id,$buy_bdeal_info,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
                    $this->fill_sell_bdeal($conn,$sell_bdeal_bdealinfo['f_uin'],$coin_id,$sell_bdeal_bdealinfo,$deal_price,$deal_num,$this->deal_service->state['DEAL_DURING']);
                }
            }
        }
    }

    /**
     * 以卖单维度交易
     *
     * 1. 获取当前db中所有的待成交的卖单列表
     * 2. 找到当前买一单数据
     * 3. 买一价>=卖单价  买单价成交  否则不交易
     */
    private function trade_by_sell_simple($coin_id)
    {
        $aQuery = array(
            'f_type' => $this->deal_service->type['SELL'],
            'f_state' => $this->deal_service->state['DEAL_DURING'],
        );
        $sort = "f_price asc";
        $sell_bdeal_list = $this->deal_service->Model_t_bdeal->get_bdeal_list_by_coin_id($this->conn,$coin_id,1,1,$aQuery,$sort);
        if((int)$sell_bdeal_list['total'] === 0){
            cilog('error',"当前db中找不到合适的卖单信息,退出,等待下次执行!",$this->log_filename);
        }else{
            $sell_price = $sell_bdeal_list['rows'][0]['f_price'];
            $sell_num = $sell_bdeal_list['rows'][0]['f_pre_deal_vol'];
            $uin = $sell_bdeal_list['rows'][0]['f_uin'];
            $sell_bdeal = $sell_bdeal_list['rows'][0];
            cilog('debug',"当前需要成交的大单信息",$this->log_filename);
            cilog('debug',$sell_bdeal,$this->log_filename);

            // 查找买一价的数据  按照买单价成交
            $buy_list = $this->deal_service->Model_t_bdeal->find_all(
                $conn = $this->conn,
                $select=NULL,
                $tablename = $this->deal_service->Model_t_bdeal->get_tablename(),
                $where = array(
                    'f_type' => $this->deal_service->type['BUY'],
                    'f_state' => $this->deal_service->state['DEAL_DURING'],
                    'f_coin_id' => $coin_id,
                    'f_price >=' => $sell_price,
                ),
                $limit = 10,
                $page = 1,
                $sort = 'f_price desc'
            );

            if(!$buy_list){
                cilog('error',"当前找不到合适的买单数据,退出,等待下次执行!",$this->log_filename);
            }else{
                $buy_bdeal = $buy_list[0];
                cilog('debug',"db找到的买一单列表数据如下",$this->log_filename);
                cilog('debug',$buy_bdeal,$this->log_filename);

                cilog('debug',"开始交易! sell [bdealid:{$sell_bdeal['f_bdeal_id']}] [price:{$sell_bdeal['f_price']}] [vol:{$sell_bdeal['f_pre_deal_vol']}]",$this->log_filename);
                cilog('debug',"开始交易! buy  [bdealid:{$buy_bdeal['f_bdeal_id']}] [price:{$buy_bdeal['f_price']}] [vol:{$buy_bdeal['f_pre_deal_vol']}]",$this->log_filename);
                cilog('debug',"deal_price:{$buy_bdeal['f_price']}",$this->log_filename);

                if($buy_bdeal['f_pre_deal_vol'] * $sell_bdeal['f_pre_deal_vol'] <= 0){
                    cilog('debug',"参数错误! [sell_num:{$sell_bdeal['f_pre_deal_vol']}] [buy_num:{$buy_bdeal['f_pre_deal_vol']}]",$this->log_filename);
                    $this->err_exit();
                }

                $deal_price = $buy_bdeal['f_price'];
                $buy_deal_num = get_base_num($buy_bdeal['f_pre_deal_vol']);
                $sell_deal_num = get_base_num($sell_bdeal['f_pre_deal_vol']);
                cilog('debug',"当前单量 [buy:{$buy_deal_num} [sell:{$sell_deal_num}]",$this->log_filename);
                if($buy_deal_num > $sell_deal_num)
                {
                    $deal_num = $sell_deal_num;
                    cilog('debug',"当前成交量 num:{$deal_num}",$this->log_filename);
                    $this->fill_buy_bdeal($conn,$buy_bdeal['f_uin'],$coin_id,$buy_bdeal,$deal_price,$deal_num,$this->deal_service->state['DEAL_DURING']);
                    $this->fill_sell_bdeal($conn,$sell_bdeal['f_uin'],$coin_id,$sell_bdeal,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
                }elseif($buy_deal_num === $sell_deal_num){
                    $deal_num = $sell_deal_num;
                    cilog('debug',"当前成交量 num:{$deal_num}",$this->log_filename);
                    $this->fill_buy_bdeal($conn,$buy_bdeal['f_uin'],$coin_id,$buy_bdeal,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
                    $this->fill_sell_bdeal($conn,$sell_bdeal['f_uin'],$coin_id,$sell_bdeal,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
                }else{
                    $deal_num = $buy_deal_num;
                    cilog('debug',"当前成交量 num:{$deal_num}",$this->log_filename);
                    $this->fill_buy_bdeal($conn,$buy_bdeal['f_uin'],$coin_id,$buy_bdeal,$deal_price,$deal_num,$this->deal_service->state['DEAL_DONE']);
                    $this->fill_sell_bdeal($conn,$sell_bdeal['f_uin'],$coin_id,$sell_bdeal,$deal_price,$deal_num,$this->deal_service->state['DEAL_DURING']);
                }
            }
        }
    }

    /**
     * 买单成交
     *
     * 1 当前币种可用账户 + 成交量
     * 2 比特币币种冻结量 - 成交量*成交价
     * 3 比特币可用账号 + 返还比特币(仅买单完全成交下)
     */
    private function fill_buy_bdeal($conn,$uin,$coin_id,$bdealinfo,$deal_price,$deal_num,$state)
    {
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        $coin_info = $this->coin_service->get_coin_info($conn, $coin_id);
        if(!is_array($coin_info)){
            cilog('error',"获取币种信息失败!",$this->log_filename);
            $this->err_exit();
        }

        if($deal_num <= 0){
            cilog('error',"成交数量错误! [deal_buy_num:{$deal_num}]");
            $this->err_exit();
        }

        $conn->trans_start();
        $bdealid = $bdealinfo['f_bdeal_id'];
        cilog('debug',"正在处理买单的成交操作 [bdeal_id:{$bdealid}] [uin:{$uin}] [deal_prece:{$deal_price}] [deal_num:{$deal_num}]",$this->log_filename);

        // 创建小单
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'DEAL_ID');
        if(!$deal_id){
            cilog('error',"获取小单id错误!",$this->log_filename);
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        $tablename = $this->Model_t_deal->get_tablename();
        $deal_info = array(
            'f_deal_id'     => $deal_id,
            'f_uin'         => $uin,
            'f_bdeal_id'    => $bdealid,
            'f_coin_name'   => $coin_info['f_abbreviation'],
            'f_type'        => $this->deal_service->type['BUY'],
            'f_coin_id'     => $coin_id,
            'f_num'         => $deal_num,
            'f_money'       => $deal_price,
            'f_state'       => $this->deal_service->state['DEAL_DONE'],
            'f_commission'  => 0,
            'f_order_id'    => $this->deal_service->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $this->Model_t_deal->save($conn,$tablename,$deal_info);
        cilog('debug',"创建买单小单成功! [deal:{$deal_id}] [bdeal:{$bdealid}]",$this->log_filename);

        // 获取大单信息
        $bdealinfo_now = $this->deal_service->Model_t_bdeal->get_bdeal_info_by_bdealid($conn,$bdealid,$aQuery=array());
        cilog('debug',"开始修改大单信息,大单基础信息如下:",$this->log_filename);
        cilog('debug',$bdealinfo_now,$this->log_filename);

        // 修改大单状态
        $attributes = array(
            'f_state'           => $state,
            'f_pre_deal_vol'    => $bdealinfo_now['f_pre_deal_vol'] - $deal_num,
            'f_pre_deal_money'  => $bdealinfo_now['f_pre_deal_money'] - ($deal_num * $deal_price),
            'f_post_deal_vol'   => $bdealinfo_now['f_post_deal_vol'] + $deal_num,
            'f_post_deal_money' => $bdealinfo_now['f_post_deal_money'] + $deal_num * $deal_price,
        );

        $this->Model_t_bdeal->update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes);
        cilog('debug',"state:{$bdealinfo_now['f_state']} -> $state");
        cilog('debug',"f_pre_deal_vol:{$bdealinfo_now['f_pre_deal_vol']} -> {$attributes['f_pre_deal_vol']}",$this->log_filename);
        cilog('debug',"f_pre_deal_money:{$bdealinfo_now['f_pre_deal_money']} -> {$attributes['f_pre_deal_money']}",$this->log_filename);
        cilog('debug',"f_post_deal_vol:{$bdealinfo_now['f_post_deal_vol']} -> {$attributes['f_post_deal_vol']}",$this->log_filename);
        cilog('debug',"f_post_deal_money:{$bdealinfo_now['f_post_deal_money']} -> {$attributes['f_post_deal_money']}",$this->log_filename);


        // 比特币币种冻结量 - 成交量*成交价
        // 比特币可用账号 + 返还比特币(仅买单完全成交下)
        $btc_finance_info = $this->finance_service->Model_t_finance_info->get_finance_without_cache($conn,$uin,10001);
        cilog('debug',"当前比特币信息如下: [uin:{$uin}]",$this->log_filename);
        cilog('debug',$btc_finance_info,$this->log_filename);
        $attributes = array(
            'f_freeze_vol'  => $btc_finance_info['f_freeze_vol'] - $this->get_total_btc($deal_price,$deal_num),
            'f_can_use_vol' => $btc_finance_info['f_can_use_vol'] + $this->get_back_btc($deal_price,$bdealinfo_now['f_price'],$deal_num,$state),
        );
        cilog('debug',"比特币账户 可用由:{$btc_finance_info['f_can_use_vol']} => {$attributes['f_can_use_vol']}",$this->log_filename);
        cilog('debug',"比特币账户 冻结为:{$btc_finance_info['f_freeze_vol']} => {$attributes['f_freeze_vol']}",$this->log_filename);
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);

         // 当前币种可用账户 + 成交量
        $finance_info = $this->finance_service->Model_t_finance_info->get_finance_without_cache($conn,$uin,$coin_id);
        cilog('debug',"当前虚拟币信息如下: [coin_id:{$coin_id}] [uin:{$uin}]",$this->log_filename);
        cilog('debug',$finance_info,$this->log_filename);
        $attributes = array(
            'f_can_use_vol'  => $finance_info['f_can_use_vol'] + $deal_num,
            'f_freeze_vol' => $finance_info['f_freeze_vol']
        );
        cilog('debug',"当前虚拟币账户 可用由:{$finance_info['f_can_use_vol']} => {$attributes['f_can_use_vol']}",$this->log_filename);
        cilog('debug',"当前虚拟币账户 冻结由:{$finance_info['f_freeze_vol']} => {$attributes['f_freeze_vol']}",$this->log_filename);
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 更新币种信息
        $this->load->service('coin/coin_service');
        $where = array('f_coin_id' => $coin_id);
        $attributes = array(
            'f_last_price' => $deal_price,
            'f_open_price' => $deal_price,
        );
        $this->coin_service->update_coin_info($conn, $attributes, $where);

        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"买单成交虚拟币失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}] [state:{$state}]",$this->log_filename);
            // return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"买单成交虚拟币成功! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}] [state:{$state}]",$this->log_filename);
            // return 0;
        }
    }

    /**
     * fun  卖单成交
     *
     * 1 币种冻结量 - 成交量
     * 2 比特币可用量 + 成交量*成交价 - 交易佣金
     */
    private function fill_sell_bdeal($conn,$uin,$coin_id,$bdealinfo,$deal_price,$deal_num,$state)
    {
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        $coin_info = $this->coin_service->get_coin_info($conn, $coin_id);
        if(!is_array($coin_info)){
            cilog('error',"获取币种信息失败!",$this->log_filename);
            $this->err_exit();
        }

        if($deal_num <= 0){
            cilog('error',"成交数量错误! [deal_sell_num:{$deal_num}]");
            $this->err_exit();
        }

        $conn->trans_start();
        $bdealid = $bdealinfo['f_bdeal_id'];
        cilog('debug',"正在处理买单的成交操作 [bdeal_id:{$bdealid}] [uin:{$uin}] [deal_prece:{$deal_price}] [deal_num:{$deal_num}]",$this->log_filename);

        // 添加小单
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'DEAL_ID');
        if(!$deal_id){
            cilog('error',"获取小单id错误!",$this->log_filename);
            return $this->deal_errcode['DEAL_PARAM_ERR'];
        }
        $tablename = $this->Model_t_deal->get_tablename();
        $commission = $deal_num * $deal_price * $coin_info['f_commission'] / 100;
        $deal_info = array(
            'f_deal_id'     => $deal_id,
            'f_uin'         => $uin,
            'f_bdeal_id'    => $bdealid,
            'f_type'        => $this->deal_service->type['SELL'],
            'f_coin_id'     => $coin_id,
            'f_coin_name'   => $coin_info['f_abbreviation'],
            'f_num'         => $deal_num,
            'f_money'       => $deal_price,
            'f_state'       => $this->deal_service->state['DEAL_DONE'],
            'f_commission'  => $commission,
            'f_order_id'    => $this->deal_service->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $this->Model_t_deal->save($conn,$tablename,$deal_info);
        cilog('debug',"创建卖单小单成功! [deal:{$deal_id}] [bdeal:{$bdealid}]",$this->log_filename);

        // 获取大单信息
        $bdealinfo_now = $this->deal_service->Model_t_bdeal->get_bdeal_info_by_bdealid($conn,$bdealid,$aQuery=array());
        cilog('debug',"开始修改大单信息,大单基础信息如下:",$this->log_filename);
        cilog('debug',$bdealinfo_now,$this->log_filename);

        // 修改大单状态
        $attributes = array(
            'f_state'           => $state,
            'f_pre_deal_vol'    => $bdealinfo_now['f_pre_deal_vol'] - $deal_num,
            'f_pre_deal_money'  => $bdealinfo_now['f_pre_deal_money'] - ($deal_num * $deal_price),
            'f_post_deal_vol'   => $bdealinfo_now['f_post_deal_vol'] + $deal_num,
            'f_post_deal_money' => $bdealinfo_now['f_post_deal_money'] + $deal_num * $deal_price,
        );
        $this->Model_t_bdeal->update_bdeal_info_by_bdealid($conn,$uin,$bdealid,$attributes);

        // 比特币可用量 + 成交量*成交价 - 交易佣金
        $btc_finance_info = $this->finance_service->Model_t_finance_info->get_finance_without_cache($conn,$uin,10001);
        cilog('debug',"当前比特币信息如下: [coin:{$uin}",$this->log_filename);
        cilog('debug',$btc_finance_info);
        $attributes = array(
            'f_can_use_vol' => $btc_finance_info['f_can_use_vol'] + $this->get_total_btc($deal_price,$deal_num) - $this->get_commission($coin_info,$deal_price,$deal_num),
            'f_freeze_vol' => $btc_finance_info['f_freeze_vol'],
        );
        cilog('debug',"比特币账户 可用由:{$btc_finance_info['f_can_use_vol']} => {$attributes['f_freeze_vol']}",$this->log_filename);
        cilog('debug',"比特币账户 冻结为:{$btc_finance_info['f_freeze_vol']} => {$attributes['f_freeze_vol']}",$this->log_filename);
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,10001,$attributes);

        // 币种冻结量 - 成交量
        $finance_info = $this->finance_service->Model_t_finance_info->get_finance_without_cache($conn,$uin,$coin_id);
        cilog('debug',"当前虚拟币信息如下: [coin:{$uin}]",$this->log_filename);
        cilog('debug',$finance_info);
        $attributes = array(
            'f_can_use_vol' => $finance_info['f_can_use_vol'],
            'f_freeze_vol'  => $finance_info['f_freeze_vol'] - $deal_num,
        );
        cilog('debug',"当前虚拟币账户 可用由:{$finance_info['f_can_use_vol']} => {$attributes['f_can_use_vol']}",$this->log_filename);
        cilog('debug',"当前虚拟币账户 冻结为:{$finance_info['f_freeze_vol']} => {$attributes['f_freeze_vol']}",$this->log_filename);
        $this->finance_service->Model_t_finance_info->update_finance_info($conn,$uin,$coin_id,$attributes);

        // 更新币种信息
        $this->load->service('coin/coin_service');
        $where = array('f_coin_id' => $coin_id);
        $attributes = array(
            'f_last_price' => $deal_price,
            'f_close_price' => $deal_price,
        );
        $this->coin_service->update_coin_info($conn, $attributes, $where);

        $conn->trans_complete();
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        if ($conn->trans_status() === FALSE)
        {
            cilog('error',"卖单成交失败,开始回滚数据! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}] [commission:{$commission}] [state:{$state}]",$this->log_filename);
            // return $this->deal_errcode['DEAL_BUY_BDEAL_ERR'];
        }
        else
        {
            cilog('debug',"卖单成交成功! [uin:{$uin}] [coin_id:{$coin_id}] [deal_num:{$deal_num}] [deal_price:{$deal_price}] [commission:{$commission}] [state:{$state}]",$this->log_filename);
            // return 0;
        }
    }

    // 计算佣金
    private function get_commission($coin_info,$price,$num)
    {
        $data = ($coin_info['f_commission']/100) * $price * $num;
        cilog('debug',"获取当前交易佣金为:{$data} [price:{$price}] [num:{$num}] [rate:{$coin_info['f_commission']}]",$this->log_filename);
        return $data;
    }

    // 计算买家退单时的退单btc  单位btc
    private function get_back_btc($sell_price,$buy_price,$deal_num,$state)
    {
        $back_btc = 0;
        if($state == $this->deal_service->state['DEAL_DONE']){
            $back_btc = abs($sell_price - $buy_price) * $deal_num;
        }
        cilog('debug',"获取买家成交后多余的btc总额为:{$back_btc}",$this->log_filename);
        return $back_btc;
    }

    // 获取交易的比特币总额
    private function get_total_btc($deal_price,$deal_num){
        $data = $deal_num * $deal_price;
        cilog('debug',"获取当前的交易总额为:{$data} [price:{$deal_price}] [num:{$deal_num}]",$this->log_filename);
        return $data;
    }

    private function err_exit()
    {
        $this->cache->redis->delete($this->redis_key);
        exit();
    }

    // 获取卖一列表
    private function get_sell_one_list($coin_id,$buy_price,$num=NULL)
    {
        $num = isset($num) ? $num : 10;
        $sell_list = $this->deal_service->Model_t_bdeal->find_all(
            $conn = $this->conn,
            $select=NULL,
            $tablename = $this->deal_service->Model_t_bdeal->get_tablename(),
            $where = array(
                'f_type' => $this->deal_service->type['SELL'],
                'f_state' => $this->deal_service->state['DEAL_DURING'],
                'f_coin_id' => $coin_id,
                'f_price <=' => $buy_price,
            ),
            $limit = $num,
            $page = 1,
            $sort = 'f_price asc'
        );
        return $sell_list;
    }

    // 获取买一列表 价格 顺序
    private function get_buy_one_list($coin_id,$sell_price,$num=NULL)
    {
        $num = isset($num) ? $num : 10;
        $buy_list = $this->deal_service->Model_t_bdeal->find_all(
            $conn = $this->conn,
            $select=NULL,
            $tablename = $this->deal_service->Model_t_bdeal->get_tablename(),
            $where = array(
                'f_type' => $this->deal_service->type['BUY'],
                'f_state' => $this->deal_service->state['DEAL_DURING'],
                'f_coin_id' => $coin_id,
                'f_price >=' => $sell_price
            ),
            $limit = $num,
            $page = 1,
            $sort = 'f_price desc'
        );
        return $buy_list;
    }
}