/**
 * Created by Administrator on 2017\7\5 0005.
 */


//获取币种id
var coinId = comMethod.getQueryString('id');

var param = {};

//添加或者修改初始化
function isAdOrUp(){
    if(coinId){

        param = {type: 1 }

        $.get(publicStatic.allurl.submit_check, param, function(data){
            ca_kind: $('#ca_kind').val('BIT');
            ca_name: $('#ca_name').val("BITION");
            ca_price: $('#ca_price').val('550');
            ca_exchange: $('#ca_exchange').val('0.5');
        })
    }else{
        param = {type: 2 }
    }
}

isAdOrUp();

//修改订单表单校验
var validate = $("#coin_update").validate({
    focusInvalid: false, 
    onkeyup: false,
    submitHandler: function(form){  
        param.ca_kind = $('#ca_kind').val();
        param.ca_name = $('#ca_name').val();
        param.ca_price = $('#ca_price').val();
        param.ca_exchange = $('#ca_exchange').val();
        console.log(param);
    },

    rules:{
        //币种简写
        ca_kind:{
            required:true,
            alnum: true
        },
        //币种名称
        ca_name:{
            required:true,
            alnum: true
        },
        //初始购买价格
        ca_price:{
            required:true,
            allnum: true
        },
        //比特币兑换率
        ca_exchange:{
            required:true,
            allnum: true
        }
    },
    messages:{
        ca_kind:{
            required:"币种简称不能为空"
        },
        ca_name:{
            required:"币种名称不能为空"
        },
        ca_price:{
            required:"初始购买价格不能为空"
        },
        ca_exchange:{
            required: "比特币兑换率不能为空"
        }
    }

});
