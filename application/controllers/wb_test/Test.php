<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class admin  后台管理模块 api部分
 */
// require_once APPPATH . '/libraries/comm/captcha.php';
class Test extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        require_once APPPATH . '/libraries/comm/mail/PHPMailer.php';
        require_once APPPATH . '/libraries/comm/mail/SMTP.php';

    }
    public function length(){
       $str = "1111111111111111111111111111111111111111111111111";
       echo strlen($str);
    }
    public function index(){
        $str = "21,3,5,6";
        $arr = explode(',',$str);
        $arr1 = array();
        $arr1 = array_merge($arr1,$arr);
        var_dump($arr);
        var_dump($arr1);

        $this->load->view("wb_view/test");
    }


    public function send_mes(){
//        $this->load->libraries('data');
//        $this->data->tree();
        $arr_to = array(
            array("vip6211@naver.com"),
//array("wb@fbishare.com"),
//array("carpediem_jo@naver.com"),
//array("esj59777@gmail.com"),
//array("ogju4525@naver.com"),
//array("gkgksj91@naver.com"),
//array("xrayzang@hanmail.net"),
//array("paki50@naver.com"),
//array("lky3701@naver.com"),
//array("cjw5121@gmail.com"),
//array("whddnstls321@naver.com"),
//array("stacyjo3@naver.com"),
//array("ysblovelsh52@gmail.com"),
//array("jss1561@naver.com"),
//array("stacyjo3@gmail.com"),
//array("rnfkdi112@gmail.com"),
//array("reuben.resuello@gmail.com"),
//array("pcdong7@naver.com"),
//array("keh19999@naver.com"),
//array("rlarhdwp@naver.com"),
//array("parkkwanseok@gmail.com"),
//array("ekany1@naver.com"),
//array("dongueri02@gmail.com"),
//array("dongueri01@gmail.com"),
//array("dongueri00@gmail.com"),
//array("wooju96@hanmail.net"),
//array("dongueri@gmail.com"),
//array("hhj03311@naver.com"),
//array("nim2ji@naver.com"),
//array("dongbbae@naver.com"),
//array("sjaj1548@gmail.com"),
//array("tjdusvkvk0460@hanmail.net"),
//array("aorr2224@gmail.com"),
//array("leeji5915@gmail.com"),
//array("sysic8026@gmail.com"),
//array("ksmin0975@gmail.com"),
//array("mjtaek7@gmail.com"),
//array("ks10171@naver.com"),
//array("t1dog@naver.com"),
//array("alnikmed@gmail.com"),
//array("hgjung5670@gmail.com"),
//array("chm2759@gmail.com"),
//array("jsson01234@gmail.com"),
//array("jsson002@gmail.com"),
//array("lawofjin@gmail.com"),
//array("hicoco85@gmail.com"),
//array("raygbiz@gmail.com"),
//array("kingying@naver.com"),
//array("ethsu1234@gmail.com"),
        );

        foreach($arr_to as $k=>$v){
            $message = <<<MES
	<p>Dear $v[0],</p>
	<p>Welcome to Coincoming!</p>
	<p>Congratulations！As the first 10,000 verified users, you will receive 1000 CCT for free. CCT will be distributed within 15 working days after ICO officially ended.</p>
	<p>What are CCTs used for?</p>
	<p>Tokens of our bitcoin exchange.</p>
	<p>Two Functions.</p>
	<p>1.To participate in the lucky draw per day and have chance to earn 1 Bitcoin.</p>
	<p>2.To share 30% of the profits of platform per month.</p>
	<p>The Whitepaper, which contains all information relevant to the CCT and the offering, is available here:  </p>
	<p><a href="http://st.coincoming.com/whitepaper/">http://st.coincoming.com/whitepaper/</a></p>
	<p>Feel free to join our new Telegram Channel here: </p>
	<p><a href="https://t.me/CoinComingex">https://t.me/CoinComingex</a></p>
	<p>ICO link：<a href="http://trade.coincoming.com/ico/pList">http://trade.coincoming.com/ico/pList</a></p>
	<p>Thanks,</p>
	<p>CoinComing Team</p>
	<p><img src="http://img.coincoming.com/0/C123F30DBA8EC3137DD4B3A3FE94C4C8.jpeg" alt=""></p>

MES;
            $this->send_mail("Congratulations！You will get 1000 CCTs for free！",$message,$arr_to[$k]);
        }

    }

    function send_mail($subject, $message, $arr_to, $arr_cc = NULL, $arr_filename = NULL)
    {

        //实例化PHPMailer核心类
        $mail = new PHPMailer();

        $mail_config = array(
            'host' => 'smtp.exmail.qq.com',
            'port' => 465,
            'from' => 'server@coincoming.com',
            'user' => 'server@coincoming.com',
            'password' => 'Fbi123456OK',
            'name' => 'CoinComing Team',
            'charset' => 'UTF-8',
        );

        //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        // $mail->SMTPDebug = 1;
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $mail_config['host'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = $mail_config['port'];;
        $mail->Hostname = 'http://www.test.com';
        $mail->CharSet = $mail_config['charset'];
        $mail->FromName = $mail_config['name'];
        $mail->Username = $mail_config['from'];
        $mail->Password = $mail_config['password'];
        $mail->From = $mail_config['from'];

        //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);

        //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        if ($arr_to !== NULL) {
            foreach ($arr_to as $el) {
                $mail->addAddress($el);
            }
        }

        //添加该邮件的主题
        $mail->Subject = $subject;

        //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = $message;

        // 添加抄送
        if ($arr_cc !== NULL) {
            foreach ($arr_cc as $el) {
                $mail->addCC($el);
            }
        }

        // 添加附件
        if ($arr_filename !== NULL) {
            foreach ($arr_filename as $el) {
                $mail->addAttachment($el, basename($el));
            }
        }
        $status = $mail->send();

        if ($status) {
            echo 1;
            return true;
        } else {
            echo 0;
            cilog('error', "邮件发送失败!");
            cilog('error', $status);
            return false;
        }
    }


    public  function add_admin(){
        $this->init_log();
        $this->init_api();

        $name = get_post_value('name');
        $pw = get_post_value('pw');

        $this->load->service('user/user_service');
        $this->load->model("conf/Model_t_idmaker");
        $uin = $this->Model_t_idmaker->get_id_from_idmaker($this->conn,'ADMIN_ID');
        if (!$uin){
            return $this->user_errcode['USER_GET_IDMAKER_ERR'];
        }
        $str_password=md5($pw);
        $pw_key = create_guid("user_key");
        cilog('debug',$pw_key,$this->log_filename);

        $last_pw = $this->user_service->get_last_pw($pw_key,$str_password);
        cilog('error','生成的最终的密码:'.$last_pw,$this->log_filename);

        $admin_info = array(
            'f_admin_id' =>$uin,
            'f_admin_user' => $name,
            'f_admin_pwd' => $last_pw,
            'f_admin_key' => $pw_key,
            'f_admin_rule' =>'1,2,3,4',
            'f_admin_state' => 1,
            'f_create_time' =>timestamp2time(),
            'f_modify_time' =>timestamp2time(),
        );
        $this->load->model("user/Model_t_admin");
        $res = $this->Model_t_admin->save($this->conn,$this->Model_t_admin->get_tablename(),$admin_info);
        if($res === FALSE){
            cilog('debug',"添加admin管理员失败! [res:{$res}]",$this->log_filename);
            render_json($this->user_service->user_errcode['USER_ADMIN_CREAT_ERR']);
        }
        render_json();
    }

}