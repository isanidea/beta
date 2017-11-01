<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Upload_pic extends MY_Controller {
    /**
     * fun:图片上传控制器
     */
    public function __construct()
    {
        parent::__construct();
        $this->base_pic_path = "/data/upload/pic/";  // 该目录配置成图片服务器的主目录
        $this->pic_host = (ENVIRONMENT === "development") ? "http://img.test.com/" : "http://img.coincoming.com/";
        $this->keyword = "5jvgFalgkjl3";
        $this->file_size = 1024 * 1024;     // 1M
        $this->log_filename = NULL;
    }

    /**
     * @fun    原生上传文件
     *
     * json返回
     * 
     * 原生接口上传返回图片url   /0/q94891598.jpeg
     */
    public function upload_original()
    {
        $this->init_log();
        $this->init_api();

        // $this->check_login();
        cilog('error',"开始上传！",$this->log_filename);
        

        // 获取上传的文件信息
        $file = $_FILES["file"];

        // 判断文件上传中是否发生错误
        if ($file['error'] > 0) {
            cilog('error',"文件非法! [errcode:{$file["error"]}]",$this->log_filename);
            render_json($this->conf_errcode['FILE_TYPE_ERR'],1);
        }

        // 文件类型校验
        $typeArr = array(
            "image/jpeg" => 'jpeg',
        );
        if(!in_array($file['type'],array_keys($typeArr))){
            // 文件类型错误
            cilog('error',"上传文件类型错误 [type:{$file['type']}  {$file['name']}]",$this->log_filename);
            render_json($this->conf_errcode['FILE_TYPE_ERR'],1);
        }

        // 文件大小判断
        $filesize = $this->file_size;
        if($file['size'] > $filesize){
            cilog('error',"上传文件大小非法 [size:{$file['size']}]",$this->log_filename);
            render_json($this->conf_errcode['FILE_SIZE_ERR'],1);
        }
        
        // 生成文件路径
        $key = create_guid($this->keyword);
        $type = "jpeg";
        $pathBase = get_upload_path($key,$type);
        $path = $this->base_pic_path.$pathBase;
        $up_dir = dirname($path);
        if(!is_dir($up_dir)){
            mkdir($up_dir,0777,true);
        }
        
        $config['upload_path']      = $up_dir;
        $config['file_name']        = basename($path);
        $config['allowed_types']    = 'jpg|gif|png|jpeg';
        $config['max_size']         = $filesize;
        $config['max_width']        = 2000;
        $config['max_height']       = 2000;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file'))
        {
            $file = $this->upload->data();
            $errmsg = $this->upload->display_errors();
            cilog('error',"上传文件失败! [path:{$path}] [errmsg:{$errmsg}]",$this->log_filename);
            cilog('error',$file,$this->log_filename,1);
            render_json($this->conf_errcode['FILE_UPLOAD_ERR']);
        }
        else
        {
            cilog('debug',"上传文件成功! [path:{$path}]",$this->log_filename);
            render_json(0,'',get_upload_path($key),1);
        }   
    }



    /**
     * @fun    base64图片上传
     *
     * jsonp返回
     * 
     * base64接口返回值为 字符串key
     */
    public function upload_base64()
    {
        $this->init_log();
        $this->init_api();
        $this->check_login();

        $img_base64 = $this->input->post('base64');
        // cilog('error',"pic:{$img_base64}");

        $mime = array(
            'image/jpeg' => '.jpg',
            'image/jpeg' => '.jpeg',
        );

        $typeArr = array(
            "image/jpeg" => 'jpeg',
        );

        // $types = empty($types) ? $mime : $types;
        $img = str_replace(array('_','-'), array('/','+'), $img_base64);
        $b64img = substr($img, 0,100);

        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $b64img, $matches)){

            $type = $matches[2];
            if(!in_array($type, $typeArr)){
                cilog('error',"上传文件类型非法 [type:{$type}]");
                render_json($this->conf_errcode['FILE_TYPE_ERR'],'','',1);
            }
            $img = str_replace($matches[1], '', $img);
            $img = base64_decode($img);

            $key = create_guid($this->keyword);
            $pathBase = get_upload_path($key,$type);
            $path = $this->base_pic_path.$pathBase;
            $up_dir = dirname($path);
            if(!is_dir($up_dir)){
                mkdir($up_dir,0777,true);
            }

            if(!file_put_contents($path, $img)){
                cilog('error',"图片上传失败");
                render_json($this->conf_errcode['FILE_UPLOAD_ERR'],'','',1);
            };
            cilog('debug',"图片上传成功! [path:{$path}]");
            render_json(0,'',$key,1);
        }
    }
}