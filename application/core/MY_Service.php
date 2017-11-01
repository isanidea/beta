<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Service
{
    public function __construct()
    {
        log_message('debug', "Service Class Initialized");
    }

    function __get($key)
    {
        $CI = & get_instance();
        return $CI->$key;
    }

    // 登陆态校验
    public function checkLogin($oSkey){
         if ((!is_object($oSkey)) || ($oSkey->mid == null) || ($oSkey->mid == null) || ($oSkey->mid == null)){
             return false;
         }
         $key =  $this->aDefineRedis['SESSION_KEY_PRE'].$oSkey->mid;
         $skey_redis = $this->cache->redis->get($key);
         if ($skey_redis === $oSkey->skey){
             // 续期
             $this->cache->redis->save($key,$skey_redis,3600);
             set_cookie('mid',$oSkey->mid,3600);
             set_cookie('skey',$oSkey->skey,3600);
             set_cookie('mk',$oSkey->mk,3600);
             return true;
         }else{
             return false;
         }
    }
}

?>