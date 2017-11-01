/**
 * Created by Administrator on 2017\6\28 0028.
 */
var comm = {

    //会员账户校验
    memberAccountCheck: function (val) {
        var s = val.replace(/\s/g,'');
        if(s){
            RegularExp=/^[A-Za-z0-9]+$/;
            if (!RegularExp.test(s)) {
                alert("会员账号格式不正确，只能输入英文字母和数字！");
                return "";
            }else{
                return s;
            }
        }else{
            return "1";
        }
    },

    //快递单号(必填)
    expressCheck: function (val) {
        var s = val.replace(/\s/g,'');
        if(s){
            RegularExp=/^[0-9]*$/;
            if (!RegularExp.test(s)) {
                alert("您输入的有误，只能输入数字！");
                return "";
            }else{
                return s;
            }
        }else{
            alert("快递单号不能为空!");
            return "";
        }
    },

    //去空格
    deltrim: function (val) {
        var s = val.replace(/\s/g,'');
        return s;
    },

    //电话号码
    phoneCheck: function (val) {
        var s = val.replace(/\s/g,'');
        if(s){
            RegularExp=/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/;
            if (!RegularExp.test(s)) {
                alert("请输入正确的电话号码！");
                return "";
            }else{
                return s;
            }
        }else{
            return "1";
        }
    },

    //订单号
    orderidCheck: function (val) {
        var s = val.replace(/\s/g,'');
        if(s){
            RegularExp=/^[0-9]*$/;
            if (!RegularExp.test(s)) {
                alert("您输入订单号的有误，只能输入数字！");
                return "";
            }else{
                return s;
            }
        }else{
            return "1";
        }
    },

    //姓名
    nameCheck: function (val) {
        var s = val.replace(/\s/g,'');
        if(s){
            RegularExp=/^[\u4e00-\u9fa5]{0,}$/;
            if (!RegularExp.test(s)) {
                alert("您输入的姓名有误，只能输入汉字！");
                return "";
            }else{
                return s;
            }
        }else{
            return "1";
        }
    },

    //邮箱验证
    emailCheck: function(val){
        var s = val.replace(/\s/g,'');
        if(s){
            RegularExp=/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
            if (!RegularExp.test(s)) {
                return "";
            }else{
                return s;
            }
        }else{
            return "";
        }        
    }
}