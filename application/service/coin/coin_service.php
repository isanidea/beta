<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * coin  币种服务   0x2003
 */

//require_once APPPATH.'service/base_comm/comm_define.php';
class Coin_service extends MY_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("coin/Model_t_coin");
        $this->load->model("coin/Model_t_coin_log");
        $this->coin_errcode = array(
            "COIN_PARAM_ERR" => 0x20030000,              // 币种接口,参数错误
            "COIN_ADD_DATA_ERR" => 0x20030001,           // 币种接口,添加币种信息错误
            "COIN_GET_DATA_ERR" => 0x20030002,           // 币种接口,获取币种信息错误
            "COIN_UPDATE_DATA_ERR" => 0x20030003,        // 币种接口,更新币种信息错误
            "COIN_GET_BASE_DEAL_ERR" => 0x20030004,      // 币种接口,获取币种基础信息和交易信息失败
            "COIN_HIDDEN" => 0x20030005,                 // 币种接口,该币下线,暂不可交易
            "COIN_STATE_ERROR" => 0x20030006,            // 币种接口,币种状态错误,已删除
        );
        $this->coin_redis_key = array(
            'TIMEOUT' => 3600,
            'COIN_INFO' => "coin_info_",    // 币种详情  coin_info_coin_id
        );
        $this->coin_market = array(
            'CNY' => 1,
            'BIT' => 2,
        );
        $this->deal_state = array(
            'SHOW' => 0,
            'HIDDEN' => 1,
        );
        $this->market_coin_id = array(
            '1' => 10000,
            '2' => 10001,
        );
    }

    /**
     * @fun    检查币种状态
     *
     * 1. 检查币种详情中的f_del_state  1 删除
     * 2. 0 未删除  返回币种详情
     * 3. 1 删除    返回空数组
     */
    public function check_coin_info($coin_info)
    {
        if ($coin_info['f_del_state'] == 1) {
            cilog('error', '该币种信息已经被删除 id:' . $coin_info['f_coin_id']);
            return $this->coin_errcode['COIN_STATE_ERROR'];
        } else {
            return 0;
        }
    }

    /**
     * @fun  获取币种详情
     *
     * 1. 从redis中获取数据
     * 2. 取到数据直接返回,取不到数据从db中获取数据
     * 3. db 通过 Model_t_coin 获取币种信息详情
     * 4. 返回数据
     */
    public function get_coin_info($conn, $coin_id)
    {
        $key = $this->coin_redis_key['COIN_INFO'] . $coin_id;
        $str_coin_info = $this->cache->redis->get($key);
        $coin_info = '';
        if (!$str_coin_info) {
            // 从db获取币种信息
            $select = "*";
            $tablename = $this->Model_t_coin->get_tablename();
            $where = array(
                'f_coin_id' => $coin_id
            );
            $sort = NULL;
            $coin_info = $this->Model_t_coin->find_by_attributes($conn, $select, $tablename, $where, $sort);
            if (!$coin_info) {
                cilog('error', "获取币种信息失败! coin_id:{$coin_id}");
                return $this->coin_errcode['COIN_GET_DATA_ERR'];
            }
            $str_coin_info = serialize($coin_info);
            $this->cache->redis->save($key, $str_coin_info, $this->coin_redis_key['TIMEOUT']);
        }
        
        return unserialize($str_coin_info);
    }

    /**
     * @fun   获取币种列表信息
     *
     */
    public function get_coin_list($conn, $page, $num)
    {
        $tablename = $this->Model_t_coin->get_tablename();
        $where = array(
            'f_del_state<>' => 1
        );
        $count = $this->Model_t_coin->count($conn, $tablename, $where);

        if ($count == 0) {
            return 0;
        }

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $select = "*";
        $sort = 'f_create_time desc';
        $coin_list = $this->Model_t_coin->find_all($conn, $select, $tablename, $where, $num, $page, $sort);
        if (!$coin_list) {
            cilog('error', "获取币种列表信息失败!");
            return $this->coin_errcode['COIN_GET_DATA_ERR'];
        }

        $rsp = array(
            'num' => $count,
            'rows' => $coin_list,
        );
        return $rsp;
    }
    /**
     * 原因：f_del_state 所有状态都要显示
     */
    public function get_coin_list_($conn,$page,$num){
        $tablename = $this->Model_t_coin->get_tablename();
        $where = array(
            
        );
        $count = $this->Model_t_coin->count($conn, $tablename, $where);

        if ($count == 0) {
            return 0;
        }

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $select = "*";
        $sort = 'f_create_time desc';
        $coin_list = $this->Model_t_coin->find_all($conn, $select, $tablename, $where, $num, $page, $sort);
        if (!$coin_list) {
            cilog('error', "获取币种列表信息失败!");
            return $this->coin_errcode['COIN_GET_DATA_ERR'];
        }

        $rsp = array(
            'num' => $count,
            'rows' => $coin_list,
        );
        return $rsp;
    }



    /**
     * @fun   更新币种详情
     * 1. 获取需要修改的币种信息的id
     * 2. 根据条件修改币种信息
     * 3. 清除redis中缓存的币种信息
     */
    public function update_coin_info($conn, $attributes, $where)
    {

        $tablename = $this->Model_t_coin->get_tablename();
        $attributes['f_modify_time'] = timestamp2time();
        $list_coin_id = $this->Model_t_coin->find_all($conn, 'f_coin_id', $tablename, $where, 0, 0, NULL);
        $res = $this->Model_t_coin->update_all($conn, $tablename, $attributes, $where);

        if ($res !== 0) {
            // $conn->trans_rollback();
            cilog('error', "更新币种数据失败,开始回滚数据!");
            return $this->coin_errcode['COIN_UPDATE_DATA_ERR'];
        } else {
            // $conn->trans_commit();
            cilog('debug', "更新币种数据成功!");
            foreach ($list_coin_id as $row) {
                $key = $this->coin_redis_key['COIN_INFO'] . $row['f_coin_id'];
                $this->cache->redis->delete($key);
            }
            return 0;
        }
    }

    /**
     * @fun 添加货币信息
     *
     */

    public function add_coin_list($conn,$data){

        $this->load->model("conf/Model_t_idmaker");

        $coin_id = $this->Model_t_idmaker->get_id_from_idmaker($conn,'COIN_ID');

        if($coin_id){
            $data['f_coin_id'] = $coin_id;
        }
        $table_name = $this->Model_t_coin->get_tablename();

        $res = $this->Model_t_coin->save($conn,$table_name,$data);

        if($res){
            return $res;
        }

        return false;
    }

    /**
     * @fun 简称属性的查询
     *
     */

    public function check_abbreviation($conn,$where){

        $table_name = $this->Model_t_coin->get_tablename();

        $res = $this->Model_t_coin->count($conn,$table_name,$where);

        if(is_int($res)){
            return $res;
        }
        return false;
    }














}