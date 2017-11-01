<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 基础model类模型
 */
class MY_Model extends CI_Model
{
    public $_tableName = '';
    public $_dbgroup = '';

    public function __construct()
    {
        // $this->init_conn();
        parent::__construct();
    }

    /**
     * 连接db
     */
//    private function init_conn()
//    {
//        return $this->load->database($this->_dbgroup, true);
//    }

    function db_err_exit($key)
    {
        $con = $this->router->fetch_class();
        $func = $this->router->fetch_method();
        cilog('error',"[class:{$con}] [fun:{$func}] [DB ERR : {$key}]");
        exit();
    }

    // 检验是否存在该表名
    public function check_table_exist($conn,$tablename)
    {
        $query = $conn->table_exists($tablename);
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query;
    }

    // 检验是否存在该表名
    public function check_field_exist($conn,$array_field = array())
    {
        $flag = TRUE;
        $tablename = $this->get_tablename();

        if (!$array_field)
        {
            cilog("error","字段名列表为空!");
            $flag = FALSE;
            return $flag;
        }

        foreach ($array_field as $el)
        {
            $flag = $conn->field_exists($el, $tablename);
            if (!$flag)
            {
                cilog("error","{$el} 字段名不存在!");
                break;
            }
        }
        return $flag;
    }

    public function checkDbManage($conn,$rsp=true){
        $error = $conn->error();
        if ($error['code'] != 0){
            cilog('error',"errmsg:{$error['message']}");
            return FALSE;
        }else{
            return $rsp;
        }
    }

    /**
     * 获取空数据模型
     * 返回数据元数据
     */
    public function get_model($conn,$tablename){
        $fields = $conn->field_data($tablename);
        $data = array();
        foreach ($fields as $row){
            $data[$row->name] = $row->default;
        }
        return $data;
    }

    /**
     * 统计满足条件的总数
     *
     * @param array $where 统计条件
     * @return int 返回记录条数
     */
    public function count($conn,$tablename,$where = array())
    {
        $query = $conn->from($tablename)->where($where)->count_all_results();
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query;
    }

    /**
     * 根据属性获取一行记录
     * @param array $where
     * @return array 返回一维数组，未找到记录则返回空数组
     */
    public function find_by_attributes($conn,$select = NULL,$tablename,$where = array(), $sort = NULL)
    {
        $select = isset($select) ? $select : "*";
        $query = $conn->select($select)->from($tablename)->where($where)->order_by($sort)->limit(1)->get();
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");

        $error = $conn->error();
        if ($error['code'] != 0){
            cilog('error',"errmsg:{$error['message']}");
            return $error['code'];
        }

        return $query->row_array();
    }

    /**
     * 查询记录
     *
     * @param array $where 查询条件，可使用模糊查询，如array('name LIKE' => "pp%") array('stat >' => '1')
     * @param int $limit 返回记录条数
     * @param int $offset 偏移量
     * @param string|array $sort 排序, 当为数组的时候 如：array('id DESC', 'report_date ASC')可以通过第二个参数来控制是否escape
     * @return array 未找到记录返回空数组
     */
    public function find_all($conn,$select=NULL,$tablename,$where = array(), $limit = 10, $page = NULL, $sort = NULL)
    {
        $select = isset($select) ? $select : "*";
        $page = ($page >= 1) ? ceil($page) : 1;
        $offset = (abs($page - 1)) * $limit;
        $conn->select($select)->from($tablename)->where($where);
        if ($sort !== NULL) {
            if (is_array($sort)) {
                foreach ($sort as $value) {
                    $conn->order_by($value, '', false);
                }
            } else {
                $conn->order_by($sort);
            }
        }
        if ($limit > 0) {
            $conn->limit($limit, $offset);
        }
        $query = $conn->get();
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query->result_array();
    }

    /**
     * 根据SQL查询， 参数通过$param绑定
     * @param string $sql 查询语句，如SELECT * FROM some_table WHERE id = ? AND status = ? AND author = ?
     * @param array $param array(3, 'live', 'Rick')
     * @return array 未找到记录返回空数组，找到记录返回二维数组
     */
    public function query_sql($conn,$sql, $param = array())
    {
        $query = $conn->query($sql, $param);
        cilog('error',$sql);
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query->result_array();
    }

    /**
     * 删除记录
     *
     * @param array $where 删除条件
     * @param int $limit 删除行数
     * @return boolean true删除成功 false删除失败
     */
    public function delete_all($conn,$tablename,$where = array(), $limit = NULL)
    {
        $query = $conn->delete($tablename, $where, $limit);
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query;
    }

    /**
     * 更新表记录
     *
     * @param array $attributes
     * @param array $where
     * @return bollean true更新成功 false更新失败
     */
    public function update_all($conn,$tablename,$attributes, $where = array())
    {
        $conn->where($where)->update($tablename, $attributes);
        $error = $conn->error();
        if ($error['code'] !== 0) {
            cilog('error','更新数据失败errmsg:'.$error['message']);
        }
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $error['code'];
    }

    /**
     * 保存数据
     *
     * @param array $data 需要插入的表数据
     * @return boolean 插入成功返回ID，插入失败返回false
     */
    public function save($conn,$tablename,$data)
    {
        if ($conn->set($data)->insert($tablename)) {
            $sql = $conn->last_query();
            cilog('debug',"sql:{$sql}");
            return $conn->insert_id();
        }
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        $error = $conn->error();
        cilog('error','插入数据失败errmsg:'.$error['message']);
        return FALSE;
    }

    /**
     * Replace数据
     * @param array $data
     */
    public function replace($conn,$tablename,$data)
    {
        $query = $conn->replace($tablename, $data);
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query;
    }

    /**
     * @fun   where_in
     * @param $conn
     * @param $tablename
     * @param $key
     * @param $arr_data
     * @param $sort
     * @param $limit
     * @return mixed
     */
    function where_in_case($conn,$tablename,$key,$arr_data,$sort,$limit)
    {
        $conn->from($tablename)->where_in($key,$arr_data)->order_by($sort)->limit($limit);
        $query = $conn->get();
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query->result_array();
    }

    public function find_all_in($conn,$select=NULL,$tablename,$where = array(),$key,$arr_data ,$limit = 10, $page = NULL, $sort = NULL)
    {
        $select = isset($select) ? $select : "*";
        $page = ($page >= 1) ? ceil($page) : 1;
        $offset = (abs($page - 1)) * $limit;
        $conn->select($select)->from($tablename)->where($where)->where_in($key,$arr_data);
        if ($sort !== NULL) {
            if (is_array($sort)) {
                foreach ($sort as $value) {
                    $conn->order_by($value, '', false);
                }
            } else {
                $conn->order_by($sort);
            }
        }
        if ($limit > 0) {
            $conn->limit($limit, $offset);
        }
        $query = $conn->get();
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query->result_array();
    }

    public function count_in($conn,$tablename,$where = array(),$key,$arr_data)
    {
        $query = $conn->from($tablename)->where($where)->where_in($key,$arr_data)->count_all_results();
        $sql = $conn->last_query();
        cilog('debug',"sql:{$sql}");
        return $query;
    }
}