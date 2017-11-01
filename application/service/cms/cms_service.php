<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * cms  新闻服务
 */

class Cms_service extends MY_Service
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("cms/Model_t_case");
        $this->load->model("cms/Model_t_type");
        $this->cms_errcode = array(
            "CMS_PARAM_ERR"           => 0x20020000,      // cms接口,参数错误
            "CMS_GET_DATA_ERR"        => 0x20020001,      // cms接口,获取新闻详情错误
            "CMS_GET_DATA_NUM_ERR"    => 0x20020002,      // cms接口,获取新闻总条数
            "CMS_GET_DATA_LIST_ERR"   => 0x20020003,      // cms接口,获取新闻列表信息失败
            "CMS_GET_TYPE_ERR"        => 0x20020004,      // cms接口,获取类型列表信息失败
            "CMS_GET_TYPE_NUM_ERR"    => 0x20020005,      // cms接口,获取类型列表信息失败
            "CMS_GET_TYPE_LIST_ERR"   => 0x20020006,      // cms接口,获取类型列表信息失败
            "CMS_UPDATE_CASE_INFO_ERR"=> 0x20020007,      // cms接口,修改case内容失败
        );
        $this->cms_redis_key = array(
            'TIMEOUT'             => 3600,
            'TOP_CASE_INFO'       => "top_case_info_"
        );
    }

    public function export_case($caseinfo)
    {
        if ($caseinfo['f_del_state'] == 1){
            return '';
        }

        return array(
            'id' => isset($caseinfo['f_case_id'])?$caseinfo['f_case_id']:0,
            'title' => isset($caseinfo['f_title'])?$caseinfo['f_title']:'',
            'desc' => isset($caseinfo['f_desc'])?$caseinfo['f_desc']:'',
            // 'intTypeId' => isset($caseinfo['f_type_id'])?$caseinfo['f_type_id']:0,
            // 'strPicSrc' => isset($caseinfo['f_pic_main'])?$caseinfo['f_pic_main']:'',
            'content' => isset($caseinfo['f_content']) ? $caseinfo['f_content'] : '' ,
            'addtime' => isset($caseinfo['f_create_time'])?$caseinfo['f_create_time']:'',
        );
    }
    
    public function export_case_2($caseinfo){

        return array(
            'title'=>isset($caseinfo['f_title'])?$caseinfo['f_title']:'',
            'desc'=>isset($caseinfo['f_desc'])?$caseinfo['f_desc']:'',
            'content'=>isset($caseinfo['f_content'])?$caseinfo['f_content']:'',
            'id'=>isset($caseinfo['f_case_id'])?$caseinfo['f_case_id']:0,
            'typeId'=>isset($caseinfo['f_type_id'])?$caseinfo['f_type_id']:0,
            'state'=>isset($caseinfo['f_del_state'])?$caseinfo['f_del_state']:0,
            'power'=>isset($caseinfo['f_power'])?$caseinfo['f_power']:9999,
            'addtime'=>isset($caseinfo['f_create_time'])?$caseinfo['f_create_time']:''
        );
    }

    public function export_type($typeInfo)
    {
        if ($typeInfo['f_del_state'] == 1){
            return array();
        }

        return array(
            'intTypeId' => isset($typeInfo['f_type_id'])?$typeInfo['f_type_id']:0,
            'strTitle' => isset($typeInfo['f_name'])?$typeInfo['f_name']:'',
            'strDesc' => isset($typeInfo['f_desc'])?$typeInfo['f_desc']:'',
            'strCreateTime' => isset($typeInfo['f_create_time'])?$typeInfo['f_create_time']:'',
        );
    }

    public function get_case_info($conn,$case_id)
    {
        $tablename = $this->Model_t_case->get_tablename();
        $where = array('f_case_id' => $case_id);
        $count = $this->Model_t_case->count($conn,$tablename,$where);
        if((int)$count !== 1){
            cilog('error',"文章id不唯一! [caseid:{$case_id}] [count:{$count}]");
            return $this->cms_errcode['CMS_PARAM_ERR'];
        }

        $select = '*';
        $sort = NULL;
        $cms_info = $this->Model_t_case->find_by_attributes($conn,$select,$tablename,$where,$sort);
        if (!$cms_info){
            cilog('error',"获取新闻信息失败! [caseid:{$case_id}]");
            return $this->cms_errcode['CMS_GET_DATA_ERR'];
        }
        return $cms_info;
    }

    public function get_case_list($conn,$type_id,$page,$num,$aQuery=NULL)
    {
        $tablename = $this->Model_t_case->get_tablename();
        $select = "f_case_id,f_title,f_desc,f_author,f_type_id,f_del_state,f_create_time,f_power";
        $where = array('f_type_id' => $type_id);
        $sort = "f_create_time desc";

        if($aQuery !== NULL){
            foreach($aQuery as $key => $value){
                $where[$key] = $value;
            }
        }

        $count = $this->Model_t_case->count($conn,$tablename,$where);
        if((int)$count === 0){
            cilog('error',"找不到文章信息! [type_id:{$type_id}] [count:{$count}]");
            return $this->cms_errcode['CMS_GET_DATA_NUM_ERR'];
        }

        $case_list = $this->Model_t_case->find_all($conn,$select,$tablename,$where, $num, $page, $sort);
        if (!$case_list){
            cilog('error',"获取新闻列表信息失败!");
            return $this->cms_errcode['CMS_GET_DATA_LIST_ERR'];
        }

        $aRsp = array(
            'page' => $page,
            'num' => $num,
            'totalNum' => $count,
            'rows' => $case_list,
        );
        return $aRsp;
    }

    public function get_type_info($conn,$type_id)
    {
        $tablename = $this->Model_t_type->get_tablename();
        $where = array('f_type_id' => $type_id);
        $count = $this->Model_t_type->count($conn,$tablename,$where);
        if((int)$count !== 1){
            cilog('error',"类型id不唯一! [typeid:{$type_id}] [count:{$count}]");
            return $this->Model_t_type['CMS_PARAM_ERR'];
        }

        $select = '*';
        $sort = NULL;
        $cms_info = $this->Model_t_type->find_by_attributes($conn,$select,$tablename,$where,$sort);
        if (!$cms_info){
            cilog('error',"获取类型信息失败! [typeid:{$type_id}]");
            return $this->cms_errcode['CMS_GET_TYPE_ERR'];
        }
        return $cms_info;
    }

    public function get_type_list($conn,$page,$num,$aQuery=NULL)
    {
        $tablename = $this->Model_t_type->get_tablename();
        $select = "f_type_id,f_name,f_desc,f_del_state,f_create_time";
        $where = array();
        $sort = "f_create_time desc";

        $count = $this->Model_t_type->count($conn,$tablename,$where);
        if((int)$count === 0){
            cilog('error',"找不到类型信息! [count:{$count}]");
            return $this->cms_errcode['CMS_GET_TYPE_NUM_ERR'];
        }

        $case_list = $this->Model_t_type->find_all($conn,$select,$tablename,$where, $num, $page, $sort);
        if (!$case_list){
            cilog('error',"获取类型列表信息失败!");
            return $this->cms_errcode['CMS_GET_TYPE_LIST_ERR'];
        }

        $aRsp = array(
            'page' => $page,
            'num' => $num,
            'totalNum' => $count,
            'rows' => $case_list,
        );
        return $aRsp;
    }

    public function update_cms_info($conn,$case_id,$attributes){

        $table_name = $this->Model_t_case->get_tablename();

        $where = array('f_case_id'=>$case_id);

        $flag = $this->Model_t_case->update_all(
            $conn,
            $table_name,
            $attributes,
            $where);

        return $flag;
    }

    public function add_case($conn,$data){
        $this->load->model("conf/Model_t_idmaker");

        $data['f_case_id'] = $this->Model_t_idmaker->get_id_from_idmaker($conn,'CMS_CASE_ID');

        $table_name = $this->Model_t_case->get_tablename();

        $flag = $this->Model_t_case->save($conn,$table_name,$data);

        return $flag;


    }

    public function get_top_case($conn,$type_id)
    {
        $redis_key = $this->cms_redis_key['TOP_CASE_INFO'].$type_id;
        $redis_value = $this->cache->redis->get($redis_key);
        if (!$redis_value){
            // 从db获取
            $arr_case_info = $this->Model_t_case->find_by_attributes(
                $conn,
                $select = 'f_case_id,f_title',
                $tablename=$this->Model_t_case->get_tablename(),
                $where = array('f_type_id' => $type_id,'f_power' => 1),
                $sort = NULL
            );
            $redis_value = serialize($arr_case_info);
            $this->cache->redis->save($redis_key,$redis_value,86400);
        }
        return unserialize($redis_value);
    }

    public function del_top_case_redis_value($type_id)
    {
        $redis_key = $this->cms_redis_key['TOP_CASE_INFO'].$type_id;
        $this->cache->redis->delete($redis_key);
    }
}