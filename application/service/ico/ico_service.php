<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * user  用户服务  0x2008
 */

class Ico_service extends MY_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("ico/Model_t_ico_info");
        $this->load->model("ico/Model_t_ico_log");
        $this->ico_errcode = array(
            "ICO_PARAM_ERR"           => 0x20080000,      // ico接口,参数错误
            "ICO_GET_ICO_INFO_ERR"    => 0x20080001,      // ico接口,获取ico详情失败
            "ICO_GET_ICO_LIST_ERR"    => 0x20080002,      // ico接口,获取ico列表失败
            "ICO_ICO_HAS_DEL"         => 0x20080003,      // ico接口,该ico信息已经被删除
            "ICO_GET_ICO_LOG_ERR"     => 0x20080004,      // ico接口,获取ico流水信息失败
            "ICO_GET_ICO_LIST_LOG_ERR"=> 0x20080005,      // ico接口,获取ico流水信息列表失败
            "ICO_STATE_CHECK_ERR"     => 0x20080006,      // ico接口,ICO状态校验失败
            "ICO_HAVE_NOT_ENOUCH_VOL" => 0x20080007,      // ico接口,ICO活动剩余量不足
            "ICO_JOIN_VOL_ERR"        => 0x20080008,      // ico接口,参加ico失败
            "ICO_UPDATE_INFO_ERR"     => 0x20080009,      // ico接口,更改ico_info失败
        );
        $this->ico_state = array(
            'DURING' => 1,         // 活动中
            'TO_BE_START' => 2,    // 待开始
            'DONE' => 3,           // 已结束
            'DEL' => 4,            // 已删除
        );
    }

    /**
     * @fun  校验ico状态
     */
    public function check_ico_state($ico_info,$state)
    {
        if ((int)$ico_info['f_ico_state'] !== (int)$state){
            cilog('debug',"ico状态校验成功! [ico_state:{$ico_info['f_ico_state']}] [check_state:{$state}]");
            return $this->ico_errcode['ICO_STATE_CHECK_ERR'];
        }
        cilog('debug',"ico状态校验成功! [ico_state:{$ico_info['f_ico_state']}] [check_state:{$state}]");
        return 0;
    }

    /**
     * @fun   获取ico项目详情
     */
    public function get_ico_info($conn,$ico_id)
    {
        $tablename = $this->Model_t_ico_info->get_tablename();
        $ico_info= $this->Model_t_ico_info->find_by_attributes(
            $conn,
            NULL,
            $tablename,
            array('f_ico_id' => $ico_id),
            $sort = NULL
        );
        if(!isset($ico_info['f_ico_id'])){
            cilog('error',"获取的ico项目详情为空! [id:{$ico_id}]");
            return $this->ico_errcode['ICO_GET_ICO_INFO_ERR'];
        }
        return $ico_info;
    }

    /**
     * @fun    获取ico项目列表
     */
    public function get_ico_list($conn,$page,$num,$aQuery=NULL)
    {
        $tablename = $this->Model_t_ico_info->get_tablename();
        $select = "*";
        $where = array();
        $sort = "f_start_time asc";

        if($aQuery !== NULL){
            foreach ($aQuery as $key => $value){
                $where[$key] = $value;
            }
        }

        $count = $this->Model_t_ico_info->count($conn,$tablename,$where);
        if((int)$count === 0){
            cilog('error',"找不到ico信息! [count:{$count}]");
            return $this->ico_errcode['ICO_GET_ICO_INFO_ERR'];
        }

        $ico_list = $this->Model_t_ico_info->find_all(
            $conn,
            $select,
            $tablename,
            $where,
            $num,
            $page,
            $sort
        );
        if (!$ico_list){
            cilog('error',"获取ico列表信息失败!");
            return $this->ico_errcode['ICO_GET_ICO_LIST_ERR'];
        }

        $aRsp = array(
            'page' => $page,
            'num' => $num,
            'totalNum' => $count,
            'rows' => $ico_list,
        );
        return $aRsp;
    }

    /**
     * @fun    获取ico参与记录列表
     */
    public function get_ico_log_list($conn,$page,$num,$uin,$aQuery=NULL)
    {
        $tablename = $this->Model_t_ico_log->get_tablename();
        $select = "*";
        $where = array('f_uin'=>$uin);
        $sort = "f_create_time desc";

        if($aQuery !== NULL){
            foreach ($aQuery as $key => $value){
                $where[$key] = $value;
            }
        }

        $count = $this->Model_t_ico_log->count($conn,$tablename,$where);
        if((int)$count === 0){
            cilog('error',"找不到ico流水! [count:{$count}]");
            return $this->ico_errcode['ICO_GET_ICO_LIST_LOG_ERR'];
        }

        $ico_list = $this->Model_t_ico_log->find_all(
            $conn,
            $select,
            $tablename,
            $where,
            $num,
            $page,
            $sort
        );
        if (!$ico_list){
            cilog('error',"获取ico流水列表信息失败!");
            return $this->ico_errcode['ICO_GET_ICO_LIST_LOG_ERR'];
        }

        $aRsp = array(
            'page' => $page,
            'num' => $num,
            'totalNum' => $count,
            'rows' => $ico_list,
        );
        return $aRsp;
    }


    /**
     * @fun update_ico_info_
     */

    public function update_ico_info_($conn,$attr,$ico_id){

        $tablename = $this->Model_t_ico_info->get_tablename();

        $res = $this->Model_t_ico_info->update_all($conn,$tablename,$attr, $where = array('f_ico_id'=>$ico_id));

        return $res;
    }

}