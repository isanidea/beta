<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @desc    配置信息流水
 *
 */

require_once APPPATH.'core/MY_Model.php';

class Model_t_conf extends MY_Model
{
    public $_dbgroup   = '';
    public $_tableName = 't_conf';

    function __construct()
    {
        parent::__construct();
        $this->conf_redis_base_key = "conf_info_";
    }

    // 写入一条配置
    function insert_conf($conn)
    {
        $tablename = $this->_tableName;
        $banner_pic_info = array(
            array('url' => 'http://img.test.com/35/7435A96A4513A5203BA0E2CAC29F60DC.jpeg','click' => '#'),
            array('url' => 'http://img.test.com/35/7435A96A4513A5203BA0E2CAC29F60DC.jpeg','click' => '#'),
            array('url' => 'http://img.test.com/35/7435A96A4513A5203BA0E2CAC29F60DC.jpeg','click' => '#'),
            array('url' => 'http://img.test.com/35/7435A96A4513A5203BA0E2CAC29F60DC.jpeg','click' => '#'),
        );
        $strdata = serialize($banner_pic_info);
        $data = array(
            'f_mark' => '测试配置信息',
            'f_key' => 'banner_pic_info',
            'f_value' => $strdata,
            'f_create_time' => timestamp2time(),
            'f_modify_time' => timestamp2time(),
        );
        return $this->save($conn,$tablename,$data);
    }

    // 按照关键字查找配置信息
    function get_conf_info($conn,$key)
    {
        $redis_key = $this->conf_redis_base_key.$key;
        $vaue_from_redis = $this->cache->redis->get($redis_key);
        if(!$vaue_from_redis) {
            $tablename = $this->_tableName;
            $conf_info=$this->find_by_attributes(
                $conn,
                $select = 'f_value',
                $tablename,
                $where = array('f_key' => $key),
                $sort = NULL
            );
            $this->cache->redis->save($redis_key,$conf_info['f_value'],86400);
            $vaue_from_redis = $conf_info['f_value'];
        }
        return unserialize($vaue_from_redis);
    }

    // 清空缓存
    function del_redis_key($key)
    {
        $redis_key = $this->conf_redis_base_key.$key;
        $this->cache->redis->delete($redis_key);
    }
}