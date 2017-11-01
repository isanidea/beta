<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * fun : 通用辅助函数
 */

// 获取get post参数 默认为 ''
function get_post_value($key, $df = '')
{
    $CI =& get_instance();
    $v = $CI->input->get_post($key, TRUE);
    $val = isset($v) ? $v : $df;
    if (is_string($val)) {
        $val = trim($val);
    }
    return filter_value($val);
}

// 过滤字符串
function filter_value($val,$option = 0)
{
    if($option === 0){
        if ($val) {
            $val = str_replace("<", "＜", $val);
            $val = str_replace(">", "＞", $val);
        }
        return $val;
    }elseif($option === 1){
        if ($val) {
            $val = str_replace("＜", "<", $val);
            $val = str_replace("＞", ">", $val);
        }
        return $val;
    }else{
        return $val;
    }


}

// 获取get post参数 默认为0
function get_post_valueI($key, $df = 0)
{
    $val = get_post_value($key, $df);
    if ($val !== NULL) {
        $val = (int)$val;
    }
    return $val;
}



// 时间戳转化为标准时间
function timestamp2time($strTime = NULL)
{
    date_default_timezone_set('PRC');
    $strTime = isset($strTime) ? $strTime : time();
    return date("Y-m-d H:i:s", $strTime);
}

// 标准时间转化为时间戳
function time2timestamp($strTime = NULL)
{
    $strTime = isset($strTime) ? $strTime : date(("Y-m-d H:i:s"), time());
    return strtotime($strTime);
}

//返回当前的毫秒时间戳
function get_microtime()
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}

// 获取固定的时间的时间戳 默认为当天0点的时间戳
function get_fixed_time($timestamp = NULL, $num = NULL)
{
    $timestamp = isset($timestamp) ? $timestamp : time();
    $num = isset($num) ? $num : 0;
    $today = strtotime(date('Y-m-d', $timestamp));
    return $today + $num;
}

// 字符串过滤
function strFilter($str)
{
    $str = isset($str) ? $str : '';

    //特殊字符的过滤方法
    $str = str_replace('`', '', $str);
    $str = str_replace('·', '', $str);
    $str = str_replace('~', '', $str);
    $str = str_replace('!', '', $str);
    $str = str_replace('！', '', $str);
    $str = str_replace('@', '', $str);
    $str = str_replace('#', '', $str);
    $str = str_replace('$', '', $str);
    $str = str_replace('￥', '', $str);
    $str = str_replace('%', '', $str);
    $str = str_replace('^', '', $str);
    $str = str_replace('……', '', $str);
    $str = str_replace('&', '', $str);
    $str = str_replace('*', '', $str);
    $str = str_replace('(', '', $str);
    $str = str_replace(')', '', $str);
    $str = str_replace('（', '', $str);
    $str = str_replace('）', '', $str);
    $str = str_replace('-', '', $str);
    $str = str_replace('_', '', $str);
    $str = str_replace('——', '', $str);
    $str = str_replace('+', '', $str);
    $str = str_replace('=', '', $str);
    $str = str_replace('|', '', $str);
    $str = str_replace('\\', '', $str);
    $str = str_replace('[', '', $str);
    $str = str_replace(']', '', $str);
    $str = str_replace('【', '', $str);
    $str = str_replace('】', '', $str);
    $str = str_replace('{', '', $str);
    $str = str_replace('}', '', $str);
    $str = str_replace(';', '', $str);
    $str = str_replace('；', '', $str);
    $str = str_replace(':', '', $str);
    $str = str_replace('：', '', $str);
    $str = str_replace('\'', '', $str);
    $str = str_replace('"', '', $str);
    $str = str_replace('“', '', $str);
    $str = str_replace('”', '', $str);
    $str = str_replace(',', '', $str);
    $str = str_replace('，', '', $str);
    $str = str_replace('<', '', $str);
    $str = str_replace('>', '', $str);
    $str = str_replace('《', '', $str);
    $str = str_replace('》', '', $str);
    $str = str_replace('.', '', $str);
    $str = str_replace('。', '', $str);
    $str = str_replace('/', '', $str);
    $str = str_replace('、', '', $str);
    $str = str_replace('?', '', $str);
    $str = str_replace('？', '', $str);

    //防sql防注入代码的过滤方法
    $str = str_replace('and', '', $str);
    $str = str_replace('execute', '', $str);
    $str = str_replace('update', '', $str);
    $str = str_replace('count', '', $str);
    $str = str_replace('chr', '', $str);
    $str = str_replace('mid', '', $str);
    $str = str_replace('master', '', $str);
    $str = str_replace('truncate', '', $str);
    $str = str_replace('char', '', $str);
    $str = str_replace('declare', '', $str);
    $str = str_replace('select', '', $str);
    $str = str_replace('create', '', $str);
    $str = str_replace('delete', '', $str);
    $str = str_replace('insert', '', $str);
    $str = str_replace('or', '', $str);
    return trim($str);
}

// 发邮件 暂时废弃
function send_mail($arr_to, $subject, $message, $filename = NULL)
{
    $ci = get_instance();
    $ci->load->library('email');            //加载CI的email类

    //以下设置Email参数
    $config['protocol'] = 'SMTP';
    $config['smtp_host'] = 'smtp.163.com';
    $config['smtp_user'] = '13554187129';
    $config['smtp_pass'] = 'cyf911729cyf';
    $config['smtp_port'] = '25';
    $config['charset'] = 'utf-8';
    $config['wordwrap'] = TRUE;
    $config['mailtype'] = 'html';
    $ci->email->initialize($config);

    //以下设置Email内容
    $ci->email->from('13554187129@163.com', '测试邮箱地址');
    $ci->email->to($arr_to);
    $ci->email->subject($subject);
    $ci->email->message($message);
    if ($filename) {
        $ci->email->attach($filename);           //相对于index.php的路径
    }

    $flag = $ci->email->send();
    if (!$flag) {
        return $ci->email->print_debugger();
    } else {
        return 0;
    }
    // echo $ci->email->print_debugger();        //返回包含邮件内容的字符串，包括EMAIL头和EMAIL正文。用于调试。
}


// 发邮件
function send_mail_v($subject, $message, $to, $arr_cc = NULL, $arr_filename = NULL,$log_filename=NULL)
{
    if(is_env_dev()){
        $mail_config = array(
            'host' => 'smtp.qq.com',
            'port' => 465,
            'from' => '1163445912@qq.com',
            'user' => '1163445912@qq.com',
            'password' => 'peuiuqrykptyheba',
            'name' => '【test】CoinComing Team',
            'charset' => 'UTF-8',
        );
    }else{
        $mail_config = array(
            'host' => 'smtp.exmail.qq.com',
            'port' => 465,
            'from' => 'server@coincoming.com',
            'user' => 'server@coincoming.com',
            'password' => 'Fbi123456OK',
            'name' => 'CoinComing Team',
            'charset' => 'UTF-8',
        );
    }

    //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
    require_once APPPATH . '/libraries/comm/mail/PHPMailer.php';
    require_once APPPATH . '/libraries/comm/mail/SMTP.php';

    //实例化PHPMailer核心类
    $mail = new PHPMailer();
    // $mail->SMTPDebug = 1;
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = $mail_config['host'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = $mail_config['port'];;
    $mail->Hostname = 'http://www.coincoming.com';
    $mail->CharSet = $mail_config['charset'];
    $mail->FromName = $mail_config['name'];
    $mail->Username = $mail_config['from'];
    $mail->Password = $mail_config['password'];
    $mail->From = $mail_config['from'];

    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    $mail->isHTML(true);

    //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
    $arr_to = array();
    if(!is_array($to)){
        array_push($arr_to,$to);
    }else{
        $arr_to = $to;
    }

    foreach ($arr_to as $el) {
        $mail->addAddress($el);
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

    $statue = $mail->send();
    if($statue){
        cilog('debug', "邮件发送成功! [fun:send_mail_v] [from:{$mail_config['from']}] [to:{$arr_to[0]}] [subject:{$subject}]",$log_filename);
        return TRUE;
    }else{
        cilog('error', "邮件发送失败! [fun:send_mail_v] [from:{$mail_config['from']}] [to:{$arr_to[0]}] [subject:{$subject}] [errmsg:{$mail->ErrorInfo}]",$log_filename);
        return FALSE;
    }
}


// 记录日志信息到本地
function cilog($level, $msg = NULL, $filename = NULL)
{
    if (NULL === $msg) {
        $msg = $level;
        $level = 'error';
    }
    if (is_array($msg) or is_object($msg)) {
        $msg = var_export($msg, true);
    }
    date_default_timezone_set("PRC");
    $ci = get_instance();
    $ci->load->library('log');
    return $ci->log->write_log($level, $msg, $filename);
}

// 生成唯一标识字符串
function create_guid($namespace = '')
{
    static $guid = '';
    $uid = uniqid("", true);
    $data = $namespace;
    $data .= $_SERVER['REQUEST_TIME'];
    $data .= $_SERVER['HTTP_USER_AGENT'];
    $data .= $_SERVER['REMOTE_ADDR'];
    $data .= $_SERVER['REMOTE_PORT'];
    $data .= microtime();
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $guid = substr($hash, 0, 8) .
        '-' .
        substr($hash, 8, 4) .
        '-' .
        substr($hash, 12, 4) .
        '-' .
        substr($hash, 16, 4) .
        '-' .
        substr($hash, 20, 12);
    return str_replace("-", "", $guid);
}

// 根据密钥和类型生成图片路径
function get_upload_path($key, $type = 'jpeg')
{
    $data = $key % 100;
    return $data . '/' . $key . '.' . $type;
}

// 获取当前用户IP
function getRealIpAddr()
{
    $CI =& get_instance();
    return $CI->input->ip_address();
}

// 返回json参数
// option 默认为返回jsonp 0 jsonp 1 json
function render_json($iRet = 0, $errMsg = '', $data = '', $option = 0)
{
    if ($option == 1) {
        // 返回json
        echo json_encode(array(
            'iRet' => $iRet,
            'sMsg' => $errMsg,
            'data' => $data,
        ));
    } else {
        // 返回jsonp 默认走jsonp
        $flag = get_post_value('callback');
        $jsonp = ($flag != "") ? $flag : "jsonp_callback";
        $rsp = json_encode(array(
            'iRet' => $iRet,
            'sMsg' => $errMsg,
            'data' => $data,
        ));
        echo $jsonp . "(" . $rsp . ")";
    }
    exit();
}

// 返回json参数
function render_json_list($iRet = 0, $errMsg = '', $num = 0, $data = '')
{
    $flag = get_post_value('callback');
    $jsonp = ($flag != "") ? $flag : "jsonp_callback";
    $data = json_encode(array(
        'iRet' => $iRet,
        'sMsg' => $errMsg,
        'total' => $num,
        'data' => $data,
    ));
    echo $jsonp . "(" . $data . ")";
    exit();
}

// 按照key,删除数组中的元素
function array_remove($data, $key)
{
    if (!array_key_exists($key, $data)) {
        return $data;
    }
    $keys = array_keys($data);
    $index = array_search($key, $keys);
    if ($index !== FALSE) {
        array_splice($data, $index, 1);
    }
    return $data;
}

// 获取一个多位数
function get_str_num($id, $length)
{
    $length = 0 - $length;
    return $data = substr(strval($id + 1000000000000), $length);
}

// 格式化当前内存消耗
function get_memory()
{
    $size = memory_get_usage();
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

// market_id 和coinid 映射表
function get_coinid_by_marketid($market_id)
{
    $arr_data = array(
        '0' => 1,      // 计数单位
        '1' => 10000,  // 人民币
        '2' => 10001,  // 比特币
    );
    return $arr_data[$market_id];

}

// 对象转数组
function objtoarr($obj){
    $ret = array();
    foreach($obj as $key =>$value){
        if(gettype($value) == 'array' || gettype($value) == 'object'){
            $ret[$key] = objtoarr($value);
        }else{
            $ret[$key] = $value;
        }
    }
    return $ret;
}


// 生成对外的单id
function todealid($dealid,$uin)
{
    $last_deal_id = get_str_num($dealid,8);
    $last_uin = get_str_num($uin,2);
    $pretime = date("ymd",time());
    $data =  $pretime .$last_deal_id. $last_uin;
    return $data;
}

// 影藏邮箱地址
function hideStar($str)
{
    if (strpos($str, '@')) {
        $email_array = explode("@", $str);
        $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($str, 0, 3); //邮箱前缀
        $count = 0;
        $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
        $rs = $prevfix . $str;
    } else {
        $pattern = '/(1[3458]{1}[0-9])[0-9]{4}([0-9]{4})/i';
        if (preg_match($pattern, $str)) {
            $rs = preg_replace($pattern, '$1****$2', $str); // substr_replace($name,'****',3,4);
        } else {
            $rs = substr($str, 0, 3) . "***" . substr($str, -1);
        }
    }
    return $rs;
}

// 判断当前环境是否为开发环境zs
function is_env_dev()
{
    if(ENVIRONMENT === "development") {
        return TRUE;
    }else{
        return FALSE;
    }
}

// 获取主站数据网址
function get_web_base_url()
{
    if(is_env_dev()){
        return "http://trade.test.com/";
    }else{
        return "http://trade.coincoming.com/";
    }
}

// 获取主站图片网址
function get_pic_base_url()
{
    if(is_env_dev()){
        return "http://img.test.com/";
    }else{
        return "http://img.coincoming.com/";
    }
}

// 获取一个标准数  float
function get_base_num($v,$num=8)
{
    return round($v,$num);
}

/**
 * 精确加法
 * @param [type] $a [description]
 * @param [type] $b [description]
 */
function math_add($a,$b,$scale = '6') {
    return bcadd($a,$b,$scale);
}
/**
 * 精确减法
 * @param [type] $a [description]
 * @param [type] $b [description]
 */
function math_sub($a,$b,$scale = '6') {
    return bcsub($a,$b,$scale);
}
/**
 * 精确乘法
 * @param [type] $a [description]
 * @param [type] $b [description]
 */
function math_mul($a,$b,$scale = '6') {
    return bcmul($a,$b,$scale);
}
/**
 * 精确除法
 * @param [type] $a [description]
 * @param [type] $b [description]
 */
function math_div($a,$b,$scale = '6') {
    return bcdiv($a,$b,$scale);
}
/**
 * 精确求余/取模
 * @param [type] $a [description]
 * @param [type] $b [description]
 */
function math_mod($a,$b) {
    return bcmod($a,$b);
}
/**
 * 比较大小
 * @param [type] $a [description]
 * @param [type] $b [description]
 * 大于 返回 1 等于返回 0 小于返回 -1
 */
function math_comp($a,$b,$scale = '6') {
    return bccomp($a,$b,$scale); // 比较到小数点位数
}

function getMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

?>