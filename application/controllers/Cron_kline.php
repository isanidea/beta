<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class 定时任务 生成K线
 *
 * 1min、5min、15min、30min、1hour、1day、1week
 * 60、60*5、60*15、60*30、60*60、24*60*60、7*24*60*60
 *
 * /usr/local/php7/bin/php index.php cron_kline get_kline_data_write2redis rasine 60 1000
 */
class Cron_kline extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user', TRUE);
        $this->load->model('deal/Model_t_deal');
        $this->load->model("coin/Model_t_coin");
        $this->load->service("deal/deal_service");
        $this->load->service("coin/coin_service");
        $this->log_type = 'info';
        $this->log_filename = "kline";
    }

    /**
     * 获取k线数据写入redis中
     *
     * key          密钥
     * count_time   间隔时间
     * num          数据量
     */
    public function get_kline_data_write2redis($key, $count_time, $num)
    {
        $log_filename = "get_kline_data_write2redis_";
        $fun_title = "获取K线数据";
        $this->init_cron($key, $log_filename, $fun_title);
        $time_now_timestamp = time2timestamp();

        cilog($this->log_type, "开始获取K线数据 [数据总量:{$num}] [数据时间间隔:{$count_time}] [时间戳:{$time_now_timestamp}]", $log_filename);

//        $coin_list = $this->Model_t_coin->find_all(
//            $conn = $this->conn,
//            $select = 'f_coin_id',
//            $tablename = $this->Model_t_coin->get_tablename(),
//            $where = array(
//                'f_market_type >' => 1,
//                'f_del_state' => 0,
//            ),
//            $limit = 100,
//            $page = 1,
//            $sort = "f_create_time desc"
//        );
        $coin_list = array(
            array('f_coin_id' => 10012)
        );

        foreach ($coin_list as $row) {
            cilog($this->log_type, "币种id:{$row['f_coin_id']}", $log_filename);

            if ($row['f_coin_id'] == 10001) {
                continue;
            }

            $data = array();

            if ($count_time == 60) {
                $data = $this->get_kline_last($row['f_coin_id']);
            } else {
                for ($i = 1; $i <= $num; $i++) {
                    $start = time2timestamp() - $count_time * $i;
                    $end = time2timestamp() - $count_time * ($i - 1);
                    $count = $this->Model_t_deal->count(
                        $conn = $this->conn,
                        $tablename = $this->Model_t_deal->get_tablename(),
                        $where = array(
                            'UNIX_TIMESTAMP(f_create_time) >=' => $start,
                            'UNIX_TIMESTAMP(f_create_time) <=' => $end,
                            'f_coin_id' => $row['f_coin_id'],
                            // 'f_type' => $this->deal_service->type['SELL'],
                        )
                    );
                    // cilog('error',"count:{$count}",$log_filename);

                    if ($count === 0) {
                        continue;
                    }

                    $dealinfo = $this->Model_t_deal->find_by_attributes(
                        $conn = $this->conn,
                        $select = "f_money,MAX(f_money),MIN(f_money),SUM(f_num)",
                        $tablename = $this->Model_t_deal->get_tablename(),
                        $where = array(
                            'UNIX_TIMESTAMP(f_create_time) >=' => $start,
                            'UNIX_TIMESTAMP(f_create_time) <=' => $end,
                            'f_coin_id' => $row['f_coin_id'],
                            // 'f_type' => $this->deal_service->type['SELL'],
                        ),
                        $sort = 'f_create_time desc'
                    );
                    $info = $this->Model_t_deal->find_by_attributes(
                        $conn = $this->conn,
                        $select = "f_money",
                        $tablename = $this->Model_t_deal->get_tablename(),
                        $where = array(
                            'UNIX_TIMESTAMP(f_create_time) >=' => $start,
                            'UNIX_TIMESTAMP(f_create_time) <=' => $end,
                            'f_coin_id' => $row['f_coin_id'],
                            // 'f_type' => $this->deal_service->type['SELL'],
                        ),
                        $sort = 'f_create_time ASC'
                    );

                    $time_timestamp = $start * 1000;
                    $open_price = isset($info['f_money']) ? number_format($info['f_money'], 3) : 0;
                    $high_price = isset($dealinfo['MAX(f_money)']) ? number_format($dealinfo['MAX(f_money)'], 3) : $open_price;
                    $low_price = isset($dealinfo['MIN(f_money)']) ? number_format($dealinfo['MIN(f_money)'], 3) : $open_price;
                    $close_price = isset($dealinfo['f_money']) ? number_format($dealinfo['f_money'], 3) : $open_price;
                    $deal_vol = isset($dealinfo['SUM(f_num)']) ? number_format($dealinfo['SUM(f_num)'], 3) : $open_price;
                    $tmp = array($time_timestamp, (float)$open_price, (float)$high_price, (float)$low_price, (float)$close_price, (float)$deal_vol);
                    array_push($data, $tmp);
                    // cilog('error',$tmp,$log_filename);
                }
            }
            $key = $this->deal_service->deal_redis_key['KLINE'] . $count_time . "_" . $row['f_coin_id'];
            $value = serialize($data);
            $this->cache->redis->save($key, $value, 86400 * 8);
            cilog($this->log_type, "获取K线数据成功! [coin_id:{$row['f_coin_id']}] [count_time:{$count_time}]", $log_filename);
        }
        cilog($this->log_type, "全流程结算!", $log_filename);
        exit();
    }


    /**
     * 比特币的数据拉取okcoin数据
     *
     * 数据每隔30分钟跑一次
     *
     * /usr/local/php7/bin/php index.php cron_kline get_bitcoin_data_from_okcoin rasine
     */
    public function get_bitcoin_data_from_okcoin($key)
    {
        $log_filename = "get_bitcoin_data_from_okcoin_";
        $fun_title = "比特币的数据拉取okcoin数据";
        $this->init_cron($key, $log_filename, $fun_title);

        require_once APPPATH . '/libraries/comm/http.php';
        $http = new Http();
        $coin_id = 10001;
        $coin_info_url = "https://www.okcoin.com/v2/markets/market-tickers";

        $url_list = array(
            '60' => "https://www.okex.com/api/klineData.do?marketFrom=34&type=0&limit=1000&coinVol=1",
            '300' => "https://www.okex.com/api/klineData.do?marketFrom=34&type=1&limit=1000&coinVol=1",
            '900' => "https://www.okex.com/api/klineData.do?marketFrom=34&type=2&limit=1000&coinVol=1",
            '1800' => "https://www.okex.com/api/klineData.do?marketFrom=34&type=9&limit=1000&coinVol=1",
            '3600' => "https://www.okex.com/api/klineData.do?marketFrom=34&type=10&limit=1000&coinVol=1",
            '86400' => "https://www.okex.com/api/klineData.do?marketFrom=34&type=3&limit=1000&coinVol=1",
            '604800' => "https://www.okex.com/api/klineData.do?marketFrom=34&type=4&limit=1000&coinVol=1",
        );

        foreach ($url_list as $key => $value) {
            $data = $http->get($value);
            if ($data !== FALSE) {
                $data = json_decode($data);
                $redis_key = $this->deal_service->deal_redis_key['KLINE'] . $key . "_" . $coin_id;
                $this->cache->redis->save($redis_key, serialize($data), 86400);
            }
        }

        // 获取比特币的最新数据  btc ltc eth etc bcc
        $data = $http->get($coin_info_url);
        $coin_info = json_decode($data);
        $coin_info = objtoarr($coin_info);
        $bitcoin_data = $coin_info['data'][0];

        $this->Model_t_coin->update_all(
            $conn = $this->conn,
            $tablename = $this->Model_t_coin->get_tablename(),
            $attributes = array(
                "f_modify_time" => timestamp2time(),
                'f_last_price' => str_replace(',', '', $bitcoin_data['buy']),
                'f_high_price_24' => str_replace(',', '', $bitcoin_data['dayHigh']),
                'f_low_price_24' => str_replace(',', '', $bitcoin_data['dayLow']),
                'f_deal_vol_24' => str_replace(',', '', $bitcoin_data['volume']),
                'f_open_price' => str_replace(',', '', $bitcoin_data['open']),
                'f_close_price' => str_replace(',', '', $bitcoin_data['sell']),
                'f_rate_change_24' => strstr($bitcoin_data['changePercentage'], "%", TRUE),
            ),
            $where = array(
                'f_coin_id' => $coin_id
            )
        );
        cilog('debug', $attributes, $log_filename);
        $key = $this->coin_service->coin_redis_key['COIN_INFO'] . $coin_id;
        $this->cache->redis->delete($key);
        exit();
    }


    /**
     * 当时间间隔为60秒时，获取当前最新的500条成交记录
     *
     *  [时间，开盘，最高，最低，收盘，成交量]
     */
    private function get_kline_last($coin_id)
    {
        $deal_list = $this->Model_t_deal->find_all(
            $conn = $this->conn,
            $select = 'f_create_time,f_money,f_num,f_type',
            $tablename = $this->Model_t_deal->get_tablename(),
            $where = array(
                'f_coin_id' => $coin_id,
            ),
            $limit = 300,
            $page = 1,
            $sort = 'f_create_time desc'
        );

        $a=array();
        foreach($deal_list as $row){
            $timedata = time2timestamp(isset($row['f_create_time']) ? $row['f_create_time'] : 0) * 1000;
            $open_price = isset($row['f_money']) ? number_format($row['f_money'],3,',','') : 0;
            $high_price = isset($row['f_money']) ? number_format($row['f_money'],3,',','') : 0;
            $low_price = isset($row['f_money']) ? number_format($row['f_money'],3,',','') : 0;
            $close_price = isset($row['f_money']) ? number_format($row['f_money'],3,',','') : 0;
            $vol = isset($row['f_num']) ? number_format($row['f_num'],3,',','') : 0;
            array_push($a,array($timedata, (float)$open_price, (float)$high_price, (float)$low_price, (float)$close_price, (float)$vol));
        }
        return $a;
    }

    /**
     * 获取莱特币数据
     */
    public function get_ltc_data_from_okcoin($key)
    {
        $log_filename = "get_ltc_data_from_okcoin_";
        $fun_title = "莱特币的数据拉取okcoin数据";
        $this->init_cron($key, $log_filename, $fun_title);

        require_once APPPATH . '/libraries/comm/http.php';
        $http = new Http();
        $coin_id = 10006;
        $coin_info_url = "https://www.okex.com/v2/markets/ltc_btc/ticker";
        // 获取基础数据
        $data = $http->get($coin_info_url);
        $coin_info = json_decode($data);
        $coin_info = objtoarr($coin_info);
        $coin_data = $coin_info['data'];
        $this->Model_t_coin->update_all(
            $conn = $this->conn,
            $tablename = $this->Model_t_coin->get_tablename(),
            $attributes = array(
                "f_modify_time" => timestamp2time(),
                'f_last_price' => str_replace(',', '', $coin_data['buy']),
                'f_high_price_24' => str_replace(',', '', $coin_data['dayHigh']),
                'f_low_price_24' => str_replace(',', '', $coin_data['dayLow']),
                'f_deal_vol_24' => str_replace(',', '', $coin_data['volume']),
                'f_open_price' => str_replace(',', '', $coin_data['open']),
                'f_close_price' => str_replace(',', '', $coin_data['sell']),
                'f_rate_change_24' => strstr($coin_data['changePercentage'], "%", TRUE),
            ),
            $where = array(
                'f_coin_id' => $coin_id
            )
        );
        cilog('debug', $attributes, $log_filename);
        $key = $this->coin_service->coin_redis_key['COIN_INFO'] . $coin_id;
        $this->cache->redis->delete($key);

        // 获取k线数据
        $url_list = array(
            '60' => "https://www.okex.com/v2/markets/ltc_btc/kline?since=0&marketFrom=ltc_btc&type=1min&limit=1000&coinVol=0",
            '300' => "https://www.okex.com/v2/markets/ltc_btc/kline?since=0&marketFrom=ltc_btc&type=5min&limit=1000&coinVol=0",
            '900' => "https://www.okex.com/v2/markets/ltc_btc/kline?since=0&marketFrom=ltc_btc&type=15min&limit=1000&coinVol=0",
            '1800' => "https://www.okex.com/v2/markets/ltc_btc/kline?since=0&marketFrom=ltc_btc&type=30min&limit=1000&coinVol=0",
            '3600' => "https://www.okex.com/v2/markets/ltc_btc/kline?since=0&marketFrom=ltc_btc&type=1hour&limit=1000&coinVol=0",
            '86400' => "https://www.okex.com/v2/markets/ltc_btc/kline?since=0&marketFrom=ltc_btc&type=day&limit=1000&coinVol=0",
            '604800' => "https://www.okex.com/v2/markets/ltc_btc/kline?since=0&marketFrom=ltc_btc&type=week&limit=1000&coinVol=0",
        );
        foreach ($url_list as $key => $value) {
            $data = $http->get($value);
            if ($data !== FALSE) {
                $data = json_decode($data);
                $data = objtoarr($data);
                $kline_data = $data['data'];
                $arr_kline_data = array();
                foreach ($kline_data as $row){
                    // 时间，开盘，最高，最低，收盘，成交量
                    $timedata = (float)$row['createdDate'];
                    $open = (float)$row['open'];
                    $high = (float)$row['high'];
                    $low = (float)$row['low'];
                    $close = (float)$row['close'];
                    $vol= (float)$row['volume'];
                    $tmp = array($timedata,$open,$high,$low,$close,$vol);
                    array_push($arr_kline_data,$tmp);
                }
                $redis_key = $this->deal_service->deal_redis_key['KLINE'] . $key . "_" . $coin_id;
                $this->cache->redis->save($redis_key, serialize($arr_kline_data), 86400);
            }
        }
        cilog('debug',"获取莱特币数据成功",$log_filename);
    }

    /**
     * 获取以太币数据
     */
    public function get_eth_data_from_okcoin($key)
    {
        $log_filename = "get_eth_data_from_okcoin_";
        $fun_title = "以太币的数据拉取okcoin数据";
        $this->init_cron($key, $log_filename, $fun_title);

        require_once APPPATH . '/libraries/comm/http.php';
        $http = new Http();
        $coin_id = 10002;
        $coin_info_url = "https://www.okex.com/v2/markets/eth_btc/ticker";
        // 获取基础数据
        $data = $http->get($coin_info_url);
        $coin_info = json_decode($data);
        $coin_info = objtoarr($coin_info);
        $coin_data = $coin_info['data'];
        $this->Model_t_coin->update_all(
            $conn = $this->conn,
            $tablename = $this->Model_t_coin->get_tablename(),
            $attributes = array(
                "f_modify_time" => timestamp2time(),
                'f_last_price' => str_replace(',', '', $coin_data['buy']),
                'f_high_price_24' => str_replace(',', '', $coin_data['dayHigh']),
                'f_low_price_24' => str_replace(',', '', $coin_data['dayLow']),
                'f_deal_vol_24' => str_replace(',', '', $coin_data['volume']),
                'f_open_price' => str_replace(',', '', $coin_data['open']),
                'f_close_price' => str_replace(',', '', $coin_data['sell']),
                'f_rate_change_24' => strstr($coin_data['changePercentage'], "%", TRUE),
            ),
            $where = array(
                'f_coin_id' => $coin_id
            )
        );
        cilog('debug', $attributes, $log_filename);
        $key = $this->coin_service->coin_redis_key['COIN_INFO'] . $coin_id;
        $this->cache->redis->delete($key);

        // 获取k线数据
        $url_list = array(
            '60' => "https://www.okex.com/v2/markets/eth_btc/kline?since=0&marketFrom=eth_btc&type=1min&limit=1000&coinVol=0",
            '300' => "https://www.okex.com/v2/markets/eth_btc/kline?since=0&marketFrom=eth_btc&type=5min&limit=1000&coinVol=0",
            '900' => "https://www.okex.com/v2/markets/eth_btc/kline?since=0&marketFrom=eth_btc&type=15min&limit=1000&coinVol=0",
            '1800' => "https://www.okex.com/v2/markets/eth_btc/kline?since=0&marketFrom=eth_btc&type=30min&limit=1000&coinVol=0",
            '3600' => "https://www.okex.com/v2/markets/eth_btc/kline?since=0&marketFrom=eth_btc&type=1hour&limit=1000&coinVol=0",
            '86400' => "https://www.okex.com/v2/markets/eth_btc/kline?since=0&marketFrom=eth_btc&type=day&limit=1000&coinVol=0",
            '604800' => "https://www.okex.com/v2/markets/eth_btc/kline?since=0&marketFrom=eth_btc&type=week&limit=1000&coinVol=0",
        );
        foreach ($url_list as $key => $value) {
            $data = $http->get($value);
            if ($data !== FALSE) {
                $data = json_decode($data);
                $data = objtoarr($data);
                $kline_data = $data['data'];
                $arr_kline_data = array();
                foreach ($kline_data as $row){
                    // 时间，开盘，最高，最低，收盘，成交量
                    $timedata = (float)$row['createdDate'];
                    $open = (float)$row['open'];
                    $high = (float)$row['high'];
                    $low = (float)$row['low'];
                    $close = (float)$row['close'];
                    $vol= (float)$row['volume'];
                    $tmp = array($timedata,$open,$high,$low,$close,$vol);
                    array_push($arr_kline_data,$tmp);
                }
                $redis_key = $this->deal_service->deal_redis_key['KLINE'] . $key . "_" . $coin_id;
                $this->cache->redis->save($redis_key, serialize($arr_kline_data), 86400);
            }
        }
        cilog('debug',"获取以太币数据成功",$log_filename);
    }

    /**
     * 获取bcc数据
     */
    public function get_bcc_data_from_okcoin($key)
    {
        $log_filename = "get_bcc_data_from_okcoin_";
        $fun_title = "bcc的数据拉取okcoin数据";
        $this->init_cron($key, $log_filename, $fun_title);

        require_once APPPATH . '/libraries/comm/http.php';
        $http = new Http();
        $coin_id = 10007;
        $coin_info_url = "https://www.okex.com/v2/markets/bcc_btc/ticker";
        // 获取基础数据
        $data = $http->get($coin_info_url);
        $coin_info = json_decode($data);
        $coin_info = objtoarr($coin_info);
        $coin_data = $coin_info['data'];
        $this->Model_t_coin->update_all(
            $conn = $this->conn,
            $tablename = $this->Model_t_coin->get_tablename(),
            $attributes = array(
                "f_modify_time" => timestamp2time(),
                'f_last_price' => str_replace(',', '', $coin_data['buy']),
                'f_high_price_24' => str_replace(',', '', $coin_data['dayHigh']),
                'f_low_price_24' => str_replace(',', '', $coin_data['dayLow']),
                'f_deal_vol_24' => str_replace(',', '', $coin_data['volume']),
                'f_open_price' => str_replace(',', '', $coin_data['open']),
                'f_close_price' => str_replace(',', '', $coin_data['sell']),
                'f_rate_change_24' => strstr($coin_data['changePercentage'], "%", TRUE),
            ),
            $where = array(
                'f_coin_id' => $coin_id
            )
        );
        cilog('debug', $attributes, $log_filename);
        $key = $this->coin_service->coin_redis_key['COIN_INFO'] . $coin_id;
        $this->cache->redis->delete($key);

        // 获取k线数据
        $url_list = array(
            '60' => "https://www.okex.com/v2/markets/bcc_btc/kline?since=0&marketFrom=bcc_btc&type=1min&limit=1000&coinVol=0",
            '300' => "https://www.okex.com/v2/markets/bcc_btc/kline?since=0&marketFrom=bcc_btc&type=5min&limit=1000&coinVol=0",
            '900' => "https://www.okex.com/v2/markets/bcc_btc/kline?since=0&marketFrom=bcc_btc&type=15min&limit=1000&coinVol=0",
            '1800' => "https://www.okex.com/v2/markets/bcc_btc/kline?since=0&marketFrom=bcc_btc&type=30min&limit=1000&coinVol=0",
            '3600' => "https://www.okex.com/v2/markets/bcc_btc/kline?since=0&marketFrom=bcc_btc&type=1hour&limit=1000&coinVol=0",
            '86400' => "https://www.okex.com/v2/markets/bcc_btc/kline?since=0&marketFrom=bcc_btc&type=day&limit=1000&coinVol=0",
            '604800' => "https://www.okex.com/v2/markets/bcc_btc/kline?since=0&marketFrom=bcc_btc&type=week&limit=1000&coinVol=0",
        );
        foreach ($url_list as $key => $value) {
            $data = $http->get($value);
            if ($data !== FALSE) {
                $data = json_decode($data);
                $data = objtoarr($data);
                $kline_data = $data['data'];
                $arr_kline_data = array();
                foreach ($kline_data as $row){
                    // 时间，开盘，最高，最低，收盘，成交量
                    $timedata = (float)$row['createdDate'];
                    $open = (float)$row['open'];
                    $high = (float)$row['high'];
                    $low = (float)$row['low'];
                    $close = (float)$row['close'];
                    $vol= (float)$row['volume'];
                    $tmp = array($timedata,$open,$high,$low,$close,$vol);
                    array_push($arr_kline_data,$tmp);
                }
                $redis_key = $this->deal_service->deal_redis_key['KLINE'] . $key . "_" . $coin_id;
                $this->cache->redis->save($redis_key, serialize($arr_kline_data), 86400);
            }
        }
        cilog('debug',"获取bbc数据成功",$log_filename);
    }


    /**
     * 获取 etc 数据
     */
    public function get_etc_data_from_okcoin($key)
    {
        $log_filename = "get_etc_data_from_okcoin_";
        $fun_title = "etc的数据拉取okcoin数据";
        $this->init_cron($key, $log_filename, $fun_title);

        require_once APPPATH . '/libraries/comm/http.php';
        $http = new Http();
        $coin_id = 10009;
        $coin_info_url = "https://www.okex.com/v2/markets/etc_btc/ticker";
        // 获取基础数据
        $data = $http->get($coin_info_url);
        $coin_info = json_decode($data);
        $coin_info = objtoarr($coin_info);
        $coin_data = $coin_info['data'];
        $this->Model_t_coin->update_all(
            $conn = $this->conn,
            $tablename = $this->Model_t_coin->get_tablename(),
            $attributes = array(
                "f_modify_time" => timestamp2time(),
                'f_last_price' => str_replace(',', '', $coin_data['buy']),
                'f_high_price_24' => str_replace(',', '', $coin_data['dayHigh']),
                'f_low_price_24' => str_replace(',', '', $coin_data['dayLow']),
                'f_deal_vol_24' => str_replace(',', '', $coin_data['volume']),
                'f_open_price' => str_replace(',', '', $coin_data['open']),
                'f_close_price' => str_replace(',', '', $coin_data['sell']),
                'f_rate_change_24' => strstr($coin_data['changePercentage'], "%", TRUE),
            ),
            $where = array(
                'f_coin_id' => $coin_id
            )
        );
        cilog('debug', $attributes, $log_filename);
        $key = $this->coin_service->coin_redis_key['COIN_INFO'] . $coin_id;
        $this->cache->redis->delete($key);

        // 获取k线数据
        $url_list = array(
            '60' => "https://www.okex.com/v2/markets/etc_btc/kline?since=0&marketFrom=etc_btc&type=1min&limit=1000&coinVol=0",
            '300' => "https://www.okex.com/v2/markets/etc_btc/kline?since=0&marketFrom=etc_btc&type=5min&limit=1000&coinVol=0",
            '900' => "https://www.okex.com/v2/markets/etc_btc/kline?since=0&marketFrom=etc_btc&type=15min&limit=1000&coinVol=0",
            '1800' => "https://www.okex.com/v2/markets/etc_btc/kline?since=0&marketFrom=etc_btc&type=30min&limit=1000&coinVol=0",
            '3600' => "https://www.okex.com/v2/markets/etc_btc/kline?since=0&marketFrom=etc_btc&type=1hour&limit=1000&coinVol=0",
            '86400' => "https://www.okex.com/v2/markets/etc_btc/kline?since=0&marketFrom=etc_btc&type=day&limit=1000&coinVol=0",
            '604800' => "https://www.okex.com/v2/markets/etc_btc/kline?since=0&marketFrom=etc_btc&type=week&limit=1000&coinVol=0",
        );
        foreach ($url_list as $key => $value) {
            $data = $http->get($value);
            if ($data !== FALSE) {
                $data = json_decode($data);
                $data = objtoarr($data);
                $kline_data = $data['data'];
                $arr_kline_data = array();
                foreach ($kline_data as $row){
                    // 时间，开盘，最高，最低，收盘，成交量
                    $timedata = (float)$row['createdDate'];
                    $open = (float)$row['open'];
                    $high = (float)$row['high'];
                    $low = (float)$row['low'];
                    $close = (float)$row['close'];
                    $vol= (float)$row['volume'];
                    $tmp = array($timedata,$open,$high,$low,$close,$vol);
                    array_push($arr_kline_data,$tmp);
                }
                $redis_key = $this->deal_service->deal_redis_key['KLINE'] . $key . "_" . $coin_id;
                $this->cache->redis->save($redis_key, serialize($arr_kline_data), 86400);
            }
        }
        cilog('debug',"获取以太经典数据成功",$log_filename);
    }

    /**
     * 一次获取多份数据
     */
    public function get_data_from_okcoin($key)
    {
        $this->get_ltc_data_from_okcoin($key);
        $this->get_eth_data_from_okcoin($key);
        $this->get_bcc_data_from_okcoin($key);
        $this->get_etc_data_from_okcoin($key);
        $this->get_bitcoin_data_from_okcoin($key);
    }
}