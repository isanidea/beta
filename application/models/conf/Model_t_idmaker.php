<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    id生成器
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_idmaker extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_idmaker';
    private $_redis_key_idmaker = "idmaker_type_";
    private $_redis_expired_time = 24*60*60;

    function __construct()
    {
        parent::__construct();
        $this->IDAKER_TYPE_ID_ERR = 0x20000004;
        // $this->conn = $this->load->database($this->_dbgroup,true);
    }

    // 根据type_id 获取type_num
    public function get_id_from_idmaker($conn,$id_type)
    {
        $tablename = $this->_tableName;
        $id_type_num = $this->get_type_id($id_type);
        $where = array('f_type_id' => $id_type_num);
        $key = $this->_redis_key_idmaker.$id_type;
        $value = $this->cache->redis->get($key);
        if (!$value)
        {
            // redis中没有数据,从db中获取数据
            $select = NULL;
            $result = $this->find_by_attributes($conn,$select,$tablename,$where,NULL);
            $value = $result['f_type_num'];
        }

        // 更新db和redis中数据
        $value_up = $value + 1;
        $attributes = array('f_type_num' => $value_up);
        $this->update_all($conn,$tablename,$attributes, $where);
        $this->cache->redis->save($key,$value_up,$this->_redis_expired_time);

        return $value;
    }

    // 根据key获取type_id
    public function get_type_id ($key)
    {
        $id_arr_conf = array(
            'UIN_ID' => 1,
            'COIN_ID' => 2,
            'BDEAL_ID' => 3,
            'DEAL_ID' => 4,
            'CMS_TYPE_ID' => 5,
            'CMS_CASE_ID' => 6,
            'ICO_INFO_ID' =>7,
            "ADMIN_ID" =>8
        );

        if (!isset($id_arr_conf[$key])){
            cilog('error',"从idmaker获取id类型 key:{$key}");
            $id_arr_conf[$key] = 0;
            // return $this->IDAKER_TYPE_ID_ERR;
        }

        return $id_arr_conf[$key];
    }
}