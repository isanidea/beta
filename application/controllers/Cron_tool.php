<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class 定时任务
 */

class Cron_tool extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user',TRUE);
        $this->load->model("coin/Model_t_coin");
        $this->load->model("deal/Model_t_bdeal");
        $this->load->model("deal/Model_t_deal");
        $this->load->model("finance/Model_t_finance_info");
        $this->load->service('deal/deal_service');
        $this->load->service("finance/finance_service");
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
    public function to_be_done_deal($key,$coin_id,$num)
    {
        $log_filename = "to_be_done_deal_";
        $fun_title = "开始撮合交易";
        $this->init_cron($key,$log_filename,$fun_title);
        $this->load->model('deal/Model_t_bdeal');
        $redis_key = $this->deal_service->deal_redis_key['TO_BE_DEAL'].$coin_id;

        // 1. 检查币种交易开关
        if($this->cache->redis->get($redis_key)){
            // 取到数据,表示该点正在执行
            cilog('error',"[coinid:{$coin_id}] 当前有正在执行的队列!直接退出,等待下次执行!",$log_filename);
            exit();
        }
        $this->cache->redis->save($redis_key,1,$this->deal_service->deal_redis_key['TIMEOUT']);
        cilog('debug',"[coinid:{$coin_id}] 开始交易,开启交易开关!",$log_filename);

        for ($i=1;$i<=$num;$i++){
            usleep(50000);
            // 2. 获取币种信息
            $this->load->service("coin/coin_service");
            $coin_info = $this->coin_service->get_coin_info($this->conn, $coin_id);
            if(!isset($coin_info['f_last_price'])){
                // 获取币种信息失败
                cilog('error','获取币种信息失败,直接退出,等待下次执行!',$log_filename);
                $this->cache->redis->delete($redis_key);
                continue;
            }
            cilog('debug',"获取币种信息成功!",$log_filename);
            // cilog('debug',$coin_info,$log_filename);

            // 3 获取交易信息
            // 获取最新的一条卖出记录 优先 价格低 时间早
            $this->load->model("deal/Model_t_bdeal");
            $bdeal_sell_info = $this->Model_t_bdeal->find_by_attributes(
                $conn = $this->conn,
                $select=NULL,
                $tablename = $this->Model_t_bdeal->get_tablename(),
                $where = array(
                    'f_type' => $this->deal_service->type['SELL'],
                    'f_state' => $this->deal_service->state['DEAL_DURING'],
                    'f_coin_id' => $coin_id,
                    // 'f_price >=' => $coin_info['f_last_price']
                ),
                $sort = 'f_price asc'
            );
            if(!$bdeal_sell_info){
                cilog('error','获取卖出大单记录为空,直接退出,等待下次执行!',$log_filename);
                $this->cache->redis->delete($redis_key);
                continue;
            }
            cilog('debug',"获取卖出大单记录成功!",$log_filename);
            // cilog('debug',$bdeal_sell_info,$log_filename);

            // 获取当前最新的买入记录 优先 价格高  时间早
            $bdeal_buy_info = $this->Model_t_bdeal->find_by_attributes(
                $conn = $this->conn,
                $select=NULL,
                $tablename = $this->Model_t_bdeal->get_tablename(),
                $where = array(
                    'f_type' => $this->deal_service->type['BUY'],
                    'f_state' => $this->deal_service->state['DEAL_DURING'],
                    'f_coin_id' => $coin_id,
                    'f_price >=' => $bdeal_sell_info['f_price']
                ),
                $sort = 'f_price desc'
            );
            if(!$bdeal_buy_info){
                cilog('error','获取买入大单记录为空,直接退出,等待下次执行!',$log_filename);
                $this->cache->redis->delete($redis_key);
                continue;
            }
            cilog('debug',"获取买入大单记录成功!",$log_filename);
            //cilog('debug',$bdeal_buy_info,$log_filename);

            // 修改大单状态 更新财务信息 添加小单记录
            if($bdeal_sell_info['f_pre_deal_vol'] > $bdeal_buy_info['f_pre_deal_vol'])
            {
                cilog('debug',"卖出量大于买入的量",$log_filename);
                $deal_mount = $bdeal_sell_info['f_pre_deal_vol']; // 本次成交的量
                $deal_privce = $bdeal_sell_info['f_price'];       // 本次成交的价格

                $this->conn->trans_start();
                cilog('debug',"开始针对买入单操作",$log_filename);   // 已成交  增加币 扣减市场币 添加小单记录
                $this->buy_deal_finance(
                    $this->conn,
                    $bdeal_buy_info,
                    $this->deal_service->state['DEAL_DONE'],
                    $deal_mount,
                    $deal_privce,
                    $coin_info,
                    $log_filename);

                cilog('debug',"开始针对卖出单操作",$log_filename);   // 待成交  扣减币 增加市场币 添加小单记录
                $this->sell_deal_finance(
                    $this->conn,
                    $bdeal_sell_info,
                    $this->deal_service->state['DEAL_DURING'],
                    $deal_mount,
                    $deal_privce,
                    $coin_info,
                    $log_filename);

                cilog('debug',"开始修改币种价格",$log_filename);
                $this->Model_t_coin->update_all(
                    $conn = $this->conn,
                    $tablename = $this->Model_t_coin->get_tablename(),
                    $attributes = array(
                        'f_last_price' => $deal_privce,
                        'f_modify_time' => timestamp2time(),
                        'f_rate_change_24' => ($deal_privce >= $coin_info['f_last_price']) ? ($deal_privce-$coin_info['f_last_price'])/$coin_info['f_last_price'] : "-".abs(($deal_privce-$coin_info['f_last_price'])/$coin_info['f_last_price'])
                    ),
                    $where = array(
                        'f_coin_id' =>$coin_id
                    )
                );

                $this->conn->trans_complete();
                if ($this->conn->trans_status() === FALSE)
                {
                    cilog('error',"撮合交易订单失败,开始回滚数据!");
                }
                else
                {
                    // $conn->trans_commit();
                    cilog('debug',"撮合交易订单成功!");
                    $key = $this->coin_service->coin_redis_key['COIN_INFO'].$coin_id;
                    $this->cache->redis->delete($key);
                }

            }
            elseif($bdeal_sell_info['f_pre_deal_vol'] < $bdeal_buy_info['f_pre_deal_vol'])
            {
                cilog('debug',"卖出量小于买入的量",$log_filename);
                $deal_mount = $bdeal_sell_info['f_pre_deal_vol']; // 本次成交的量
                $deal_privce = $bdeal_sell_info['f_price'];       // 本次成交的价格

                $this->conn->trans_start();
                cilog('debug',"开始针对买入单操作",$log_filename);   // 待成交  增加币 扣减市场币 添加小单记录
                $this->buy_deal_finance(
                    $this->conn,
                    $bdeal_buy_info,
                    $this->deal_service->state['DEAL_DURING'],
                    $deal_mount,
                    $deal_privce,
                    $coin_info,
                    $log_filename);

                cilog('debug',"开始针对卖出单操作",$log_filename);   // 已成交  扣减币 增加市场币 添加小单记录
                $this->sell_deal_finance(
                    $this->conn,
                    $bdeal_sell_info,
                    $this->deal_service->state['DEAL_DONE'],
                    $deal_mount,
                    $deal_privce,
                    $coin_info,
                    $log_filename);

                cilog('debug',"开始修改币种价格",$log_filename);
                $this->Model_t_coin->update_all(
                    $conn = $this->conn,
                    $tablename = $this->Model_t_coin->get_tablename(),
                    $attributes = array(
                        'f_last_price' => $deal_privce,
                        'f_modify_time' => timestamp2time(),
                        'f_rate_change_24' => ($deal_privce >= $coin_info['f_last_price']) ? ($deal_privce-$coin_info['f_last_price'])/$coin_info['f_last_price'] : "-".abs(($deal_privce-$coin_info['f_last_price'])/$coin_info['f_last_price'])
                    ),
                    $where = array(
                        'f_coin_id' =>$coin_id
                    )
                );

                $this->conn->trans_complete();
                if ($this->conn->trans_status() === FALSE)
                {
                    cilog('error',"撮合交易订单失败,开始回滚数据!");
                }
                else
                {
                    // $conn->trans_commit();
                    cilog('debug',"撮合交易订单成功!");
                    $key = $this->coin_service->coin_redis_key['COIN_INFO'].$coin_id;
                    $this->cache->redis->delete($key);
                }
            }
            else
            {
                cilog('debug',"卖出量等于买入的量",$log_filename);
                $deal_mount = $bdeal_sell_info['f_pre_deal_vol']; // 本次成交的量
                $deal_privce = $bdeal_sell_info['f_price'];       // 本次成交的价格

                $this->conn->trans_start();
                cilog('debug',"开始针对买入单操作",$log_filename);   // 已成交  增加币 扣减市场币 添加小单记录
                $this->buy_deal_finance(
                    $this->conn,
                    $bdeal_buy_info,
                    $this->deal_service->state['DEAL_DONE'],
                    $deal_mount,
                    $deal_privce,
                    $coin_info,
                    $log_filename);

                cilog('debug',"开始针对卖出单操作",$log_filename);   // 已成交  扣减币 增加市场币 添加小单记录
                $this->sell_deal_finance(
                    $this->conn,
                    $bdeal_sell_info,
                    $this->deal_service->state['DEAL_DONE'],
                    $deal_mount,
                    $deal_privce,
                    $coin_info,
                    $log_filename);

                cilog('debug',"开始修改币种价格",$log_filename);
                $this->Model_t_coin->update_all(
                    $conn = $this->conn,
                    $tablename = $this->Model_t_coin->get_tablename(),
                    $attributes = array(
                        'f_last_price' => $deal_privce,
                        'f_modify_time' => timestamp2time(),
                        'f_rate_change_24' => ($deal_privce >= $coin_info['f_last_price']) ? ($deal_privce-$coin_info['f_last_price'])/$coin_info['f_last_price'] : "-".abs(($deal_privce-$coin_info['f_last_price'])/$coin_info['f_last_price'])
                    ),
                    $where = array(
                        'f_coin_id' =>$coin_id
                    )
                );
                $key = $this->coin_service->coin_redis_key['COIN_INFO'].$coin_id;
                $this->cache->redis->delete($key);

                $this->conn->trans_complete();
                if ($this->conn->trans_status() === FALSE)
                {
                    cilog('error',"撮合交易订单失败,开始回滚数据!");
                }
                else
                {
                    // $conn->trans_commit();
                    cilog('debug',"撮合交易订单成功!");
                }
            }
            $this->cache->redis->delete($redis_key);
            // usleep(1000*10);
        }
        // $this->cache->redis->delete($redis_key);
        cilog('debug',"流程结束!",$log_filename);
        exit();
    }

    /**
     * 买入成交财务变化
     */
    public function buy_deal_finance($conn,$bdealinfo,$state,$vol,$price,$coininfo,$log_filename)
    {
        cilog("debug","买入的大单成交,更新财务信息! [id:{$bdealinfo['f_bdeal_id']}] [vol:{$vol}] [price:{$price}] [coinid:{$coininfo['f_coin_id']}]",$log_filename);
        $coin_id = $coininfo['f_coin_id'];
        $uin = $bdealinfo['f_uin'];
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        $market_coin_id = get_coinid_by_marketid($coininfo['f_market_type']);
        $finance_coin = $this->finance_service->get_finance_info($conn,$uin,$coin_id);
        $finance_coin_market = $this->finance_service->get_finance_info($conn,$uin,$market_coin_id);

        // 实际增加当前币种,
        $flag = $this->Model_t_finance_info->update_all(
            $conn,
            $tablename=$this->Model_t_finance_info->get_tablename(),
            $attributes = array(
                'f_modify_time' => timestamp2time(),
                'f_can_use_vol' => $finance_coin['f_can_use_vol'] + $vol,
            ),
            $where = array(
                'f_uin' => $bdealinfo['f_uin'],
                'f_coin_id' => $coin_id,
            )
        );
        if($flag !==0 ){
            cilog('error','更新币种信息失败',$log_filename);
        }
        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdealinfo['f_uin']."_".$coin_id;
        $this->cache->redis->delete($key);

        // 实际扣减比特币 回退剩余的比特币
        if(($bdealinfo['f_pre_deal_vol'] - $vol) <= 0){
            $rolback_btc = $bdealinfo['f_pre_deal_money'] - $vol*$price;
        }else{
            $rolback_btc = 0;
        }
        $buy_need = $vol * $price;
        $flag = $this->Model_t_finance_info->update_all(
            $conn,
            $tablename=$this->Model_t_finance_info->get_tablename(),
            $attributes = array(
                'f_modify_time' => timestamp2time(),
                'f_freeze_vol' => $finance_coin_market['f_freeze_vol'] - $buy_need,
                'f_can_use_vol' => $finance_coin_market['f_can_use_vol'] + $rolback_btc,
            ),
            $where = array(
                'f_uin' => $bdealinfo['f_uin'],
                'f_coin_id' => $market_coin_id,
            )
        );
        if($flag !==0 ){
            cilog('error','更新市场币种信息失败',$log_filename);
        }
        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdealinfo['f_uin']."_".$market_coin_id;
        $this->cache->redis->delete($key);

        // 添加小单记录
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'DEAL_ID');
        $tablename = $this->Model_t_deal->get_tablename();

        $deal_info = array(
            'f_deal_id' => $deal_id,
            'f_uin' => $uin,
            'f_bdeal_id' => $bdealinfo['f_bdeal_id'],
            'f_type' => $bdealinfo['f_type'],
            'f_coin_id' => $bdealinfo['f_coin_id'],
            'f_coin_name' => $bdealinfo['f_coin_name'],
            'f_num' => $vol,     // 成交数量
            'f_money' => $price, // 成交价格
            'f_state' => $this->deal_service->state['DEAL_DONE'],
            'f_commission' => 0, // 成交手续费for平台
            'f_order_id' => $this->deal_service->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $flag = $this->Model_t_deal->save($conn,$tablename,$deal_info);
        if ($flag === FALSE) {
            cilog('error', "添加小单失败",$log_filename);
        }

        // 更新大单状态
        $flag = $this->Model_t_bdeal->update_all(
            $conn,
            $tablename=$this->Model_t_bdeal->get_tablename(),
            $attributes=array(
                'f_modify_time' => timestamp2time(),
                'f_state' => $state,
                'f_pre_deal_vol' => $vol - $bdealinfo['f_pre_deal_vol'],
                'f_pre_deal_money' => $bdealinfo['f_pre_deal_money'] - $vol*$price,
                'f_post_deal_vol' => $bdealinfo['f_post_deal_vol'] + $vol,
                'f_post_deal_money' => $bdealinfo['f_post_deal_money'] + $vol * $price,
            ),
            $where = array(
                'f_bdeal_id' => $bdealinfo['f_bdeal_id']
            )
        );
        if($flag === FALSE){
            cilog('error','更新大单信息失败',$log_filename);
        }
    }

    /**
     * 卖出成交财务变化
     */
    public function sell_deal_finance($conn,$bdealinfo,$state,$vol,$price,$coininfo,$log_filename)
    {
        cilog("debug","卖出的大单成交,更新财务信息! [id:{$bdealinfo['f_bdeal_id']}] [vol:{$vol}] [price:{$price}] [coinid:{$coininfo['f_coin_id']}]",$log_filename);

        $coin_id = $coininfo['f_coin_id'];
        $uin = $bdealinfo['f_uin'];
        $this->finance_service->Model_t_finance_info->clean_coin_finance_cache($uin,$coin_id);
        $market_coin_id = get_coinid_by_marketid($coininfo['f_market_type']);
        $finance_coin = $this->finance_service->get_finance_info($conn,$uin,$coin_id);
        $finance_coin_market = $this->finance_service->get_finance_info($conn,$uin,$market_coin_id);

        // 扣减币
        $this->Model_t_finance_info->update_all(
            $conn,
            $tablename=$this->Model_t_finance_info->get_tablename(),
            $attributes = array(
                'f_modify_time' => timestamp2time(),
                'f_freeze_vol' => $finance_coin['f_freeze_vol'] - $vol,
            ),
            $where = array(
                'f_uin' => $bdealinfo['f_uin'],
                'f_coin_id' => $coin_id,
            )
        );
        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdealinfo['f_uin']."_".$coin_id;
        $this->cache->redis->delete($key);

        // 增加市场币
        $need_sell_btc = $vol * $price * $coininfo['f_commission'] / 100;
        $this->Model_t_finance_info->update_all(
            $conn,
            $tablename=$this->Model_t_finance_info->get_tablename(),
            $attributes = array(
                'f_modify_time' => timestamp2time(),
                'f_freeze_vol' => $finance_coin_market['f_freeze_vol'],
                'f_can_use_vol' => $finance_coin_market['f_can_use_vol'] + ($vol * $price) - $need_sell_btc,
            ),
            $where = array(
                'f_uin' => $bdealinfo['f_uin'],
                'f_coin_id' => $market_coin_id,
            )
        );
        $key = $this->finance_service->finance_redis_key['FINANCE_INFO'].$bdealinfo['f_uin']."_".$market_coin_id;
        $this->cache->redis->delete($key);

        // 添加小单记录
        $this->load->model("conf/Model_t_idmaker");
        $deal_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'DEAL_ID');
        $tablename = $this->Model_t_deal->get_tablename();

        $deal_info = array(
            'f_deal_id' => $deal_id,
            'f_uin' => $uin,
            'f_bdeal_id' => $bdealinfo['f_bdeal_id'],
            'f_type' => $bdealinfo['f_type'],
            'f_coin_id' => $bdealinfo['f_coin_id'],
            'f_coin_name' => $bdealinfo['f_coin_name'],
            'f_num' => $vol,     // 成交数量
            'f_money' => $price, // 成交价格
            'f_state' => $this->deal_service->state['DEAL_DONE'],
            'f_commission' => $need_sell_btc, // 成交手续费for平台
            'f_order_id' => $this->deal_service->todealid($deal_id,$uin),
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        $flag = $this->Model_t_deal->save($conn,$tablename,$deal_info);
        if ($flag === FALSE) {
            cilog('error', "添加小单失败",$log_filename);
        }

        // 更新大单状态
        $this->Model_t_bdeal->update_all(
            $conn,
            $tablename=$this->Model_t_bdeal->get_tablename(),
            $attributes=array(
                'f_modify_time' => timestamp2time(),
                'f_state' => $state,
                'f_pre_deal_vol' => $bdealinfo['f_pre_deal_vol'] - $vol,
                'f_post_deal_vol' => $bdealinfo['f_post_deal_vol'] + $vol,
            ),
            $where = array(
                'f_bdeal_id' => $bdealinfo['f_bdeal_id']
            )
        );
    }
}