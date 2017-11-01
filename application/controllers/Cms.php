<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class cms  新闻 公告 问题模块
 */

require_once APPPATH . '/libraries/comm/captcha.php';
class Cms extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->load->database('trade_user',TRUE);
        $this->load->service('cms/cms_service');
    }

    // 公告页面-list
    public function pNotice()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('notice/notice');
    }

    // 公告详情页面
    public function pDetail()
    {
        $this->init_log();
        $this->init_page();
        $case_id = get_post_valueI('id');
        $cmsinfo = $this->pri_get_case_info($case_id);
        $this->load->view('notice/detail',$cmsinfo);
    }

    // 问题中心-list
    public function pProblem()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('notice/problem_list');
    }

    // 问题详情
    public function pPro_detail()
    {
        $this->init_log();
        $this->init_page();
        $case_id = get_post_valueI('id');
        $cmsinfo = $this->pri_get_case_info($case_id);
        $this->load->view('notice/problem_detail',$cmsinfo);
    }

    // 新闻中心-list
    public function pNews()
    {
        $this->init_log();
        $this->init_page();
        $this->load->view('notice/news_list');
    }

    // 新闻详情
    public function pNews_detail()
    {
        $this->init_log();
        $this->init_page();
        $case_id = get_post_valueI('id');
        $cmsinfo = $this->pri_get_case_info($case_id);
        $this->load->view('notice/news_detail',$cmsinfo);
    }

    // 私有变量,供直出使用,获取详情
    private function pri_get_case_info($case_id)
    {
        $this->init_log();

        if ($case_id < 10000){
            cilog('error',"case_id错误 [id:{$case_id}]");
            // render_json($this->conf_errcode['PARAM_ERR']);
            show_404();
        }

        cilog('debug',"scuess!");
        $cmsinfo = $this->cms_service->get_case_info($this->conn,$case_id);
        if(!is_array($cmsinfo)){
            // render_json($cmsinfo,'');
            show_404();
        }

        if ($cmsinfo['f_del_state'] == 1){
            // render_json();
            show_404();
        }

        $rsp = array(
            // 'id' => isset($cmsinfo['f_case_id'])?$cmsinfo['f_case_id']:0,
            'title' => isset($cmsinfo['f_title'])?$cmsinfo['f_title']:'',
            // 'desc' => isset($cmsinfo['f_desc'])?$cmsinfo['f_desc']:'',
            // 'intTypeId' => isset($caseinfo['f_type_id'])?$caseinfo['f_type_id']:0,
            // 'strPicSrc' => isset($caseinfo['f_pic_main'])?$caseinfo['f_pic_main']:'',
            // 'desc' => isset($cmsinfo['f_desc']) ? filter_value($cmsinfo['f_desc'],1) : '' ,
            'content' => isset($cmsinfo['f_content']) ? filter_value($cmsinfo['f_content'],1) : '' ,
            'addtime' => isset($cmsinfo['f_create_time'])?substr($cmsinfo['f_create_time'],0,10):'',
        );
        return $rsp;
    }

    /**
     * @fun    获取cms详情
     * @param  id       caseid
     */
    public function get_cms_info()
    {
        $this->init_log();
        $this->init_api();

        $case_id = get_post_valueI('id');

        if ($case_id < 10000){
            cilog('error',"case_id错误 [id:{$case_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $cmsinfo = $this->cms_service->get_case_info($this->conn,$case_id);
        if(!is_array($cmsinfo)){
            render_json($cmsinfo,'');
        }

        if ($cmsinfo['f_del_state'] == 1){
            render_json();
        }
        $rsp = array(
            'id' => isset($cmsinfo['f_case_id'])?$cmsinfo['f_case_id']:0,
            'title' => isset($cmsinfo['f_title'])?$cmsinfo['f_title']:'',
            'desc' => isset($cmsinfo['f_desc'])?$cmsinfo['f_desc']:'',
            // 'intTypeId' => isset($caseinfo['f_type_id'])?$caseinfo['f_type_id']:0,
            // 'strPicSrc' => isset($caseinfo['f_pic_main'])?$caseinfo['f_pic_main']:'',
            'desc' => isset($cmsinfo['f_desc']) ? filter_value($cmsinfo['f_desc'],1) : '' ,
            'content' => isset($cmsinfo['f_content']) ? filter_value($cmsinfo['f_content'],1) : '' ,
            'addtime' => isset($cmsinfo['f_create_time'])?substr($cmsinfo['f_create_time'],0,10):'',
        );
        render_json(0,'',$rsp);
    }

    /**
     * @fun     获取cms列表信息
     * @param   typeId     类型id
     * @param   page       页码
     * @param   num        每页展示最大数据量
     */
    public function get_cms_list()
    {
        $this->init_log();
        $this->init_api();

        $type_id = get_post_valueI('typeId');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        if ($type_id < 1000){
            cilog('error',"type_id 参数错误 [id:{$type_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $aQuery = array(
            'f_del_state' => 0,
        );
        $cms_list = $this->cms_service->get_case_list($this->conn,$type_id,$page,$num,$aQuery);
        if(!is_array($cms_list)){
            render_json_list(0,'',0,'');
        }

        $a = array();

        foreach ($cms_list['rows'] as $row){
            if ($row['f_del_state'] == 1){
                continue;
            }
            $case_list_row = array(
                'id' => isset($row['f_case_id'])?$row['f_case_id']:0,
                'title' => isset($row['f_title'])?$row['f_title']:'',
                'desc' => isset($row['f_desc'])?$row['f_desc']:'',
                // 'intTypeId' => isset($caseinfo['f_type_id'])?$caseinfo['f_type_id']:0,
                // 'strPicSrc' => isset($caseinfo['f_pic_main'])?$caseinfo['f_pic_main']:'',
                // 'content' => isset($row['f_desc']) ? filter_value($row['f_desc'],1) : '' ,
                'addtime' => isset($row['f_create_time'])?substr($row['f_create_time'],0,10):'',
            );
            array_push($a,$case_list_row);
        }

        render_json_list(0,'',$cms_list['totalNum'],$a);
    }

    /**
     * @fun     获取类型详情
     * @param   id         类型id
     */
    public function get_type_info()
    {
        $this->init_log();
        $this->init_api();

        $type_id = get_post_valueI('id');

        if ($type_id < 1000){
            cilog('error',"type_id 参数错误 [id:{$type_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $typeinfo = $this->cms_service->get_type_info($this->conn,$type_id);
        if(!is_array($typeinfo)){
            render_json($typeinfo,'');
        }
        $rsp = $this->cms_service->export_type($typeinfo);
        render_json(0,'',$rsp);
    }

    /**
     * @fun     获取类型列表
     * @param   page       页码
     * @param   num        每页展示最大数据量
     */
    public function get_type_list()
    {
        $this->init_log();
        $this->init_api();

        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $type_list = $this->cms_service->get_type_list($this->conn,$page,$num);
        if(!is_array($type_list)){
            render_json_list(0,'',0,'');
        }

        $a = array();

        foreach ($type_list['rows'] as $row){
            $type_list_row = $this->cms_service->export_type($row);
            array_push($a,$type_list_row);
        }

        $rsp['totalNum'] = $type_list['totalNum'];
        render_json_list(0,'',$type_list['totalNum'],$a);
    }

    /**
     * @fun     获取常用问题
     * @param   num        每页展示最大数据量
     */
    public function get_comm_problem()
    {
        $this->init_log();
        $this->init_api();

        $num = get_post_valueI('num');

        $num = (($num >0) && ($num <=20)) ? $num : 4;

        $this->load->model("cms/Model_t_case");
        $select = "f_case_id,f_title,f_desc,f_author,f_type_id,f_del_state,f_create_time";
        $tablename = $this->Model_t_case->get_tablename();
        $where = array(1002,1004);
        $key = 'f_type_id';
        $sort = 'f_create_time desc';
        $cms_list = $this->Model_t_case->where_in_case($this->conn,$tablename,$key,$where,$sort,$num);

        $a = array();

        foreach ($cms_list as $row){
            $case_list_row = $this->cms_service->export_case($row);
            if($case_list_row === ''){
                continue;
            }else{
                array_push($a,$case_list_row);
            }
        }

        render_json(0,'',$a);
    }

    /**
     * @fun     获取cms新闻列表信息
     * @param   typeId     类型id    默认新闻类型id为1006
     * @param   page       页码
     * @param   num        每页展示最大数据量
     */
    public function get_news_list()
    {
        $this->init_log();
        $this->init_api();

        $type_id = get_post_valueI('typeId');
        $page = get_post_valueI('page');
        $num = get_post_valueI('num');

        if ($type_id !== 1006){
            cilog('error',"type_id 参数错误 [id:{$type_id}]");
            render_json($this->conf_errcode['PARAM_ERR']);
        }

        $page = ($page !== 0) ? $page : 1;
        $num = ($num !== 0) ? $num : 10;

        $aQuery = array(
            'f_del_state' => 0,
        );
        $cms_list = $this->cms_service->get_case_list($this->conn,$type_id,$page,$num,$aQuery);
        if(!is_array($cms_list)){
            render_json_list(0,'',0,'');
        }

        $a = array();

        foreach ($cms_list['rows'] as $row){
            if ($row['f_del_state'] == 1){
                continue;
            }
            $case_list_row = array(
                'id' => isset($row['f_case_id'])?$row['f_case_id']:0,
                'title' => isset($row['f_title'])?$row['f_title']:'',
                'desc' => isset($row['f_desc'])?$row['f_desc']:'',
                // 'intTypeId' => isset($caseinfo['f_type_id'])?$caseinfo['f_type_id']:0,
                // 'strPicSrc' => isset($caseinfo['f_pic_main'])?$caseinfo['f_pic_main']:'',
                // 'content' => isset($row['f_desc']) ? filter_value($row['f_desc'],1) : '' ,
                'addtime' => isset($row['f_create_time'])?substr($row['f_create_time'],0,10):'',
            );
            array_push($a,$case_list_row);
        }

        render_json_list(0,'',$cms_list['totalNum'],$a);
    }
}