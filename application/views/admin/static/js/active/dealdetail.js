/**
 * Created by Administrator on 2017\6\27 0027.
 */

var get_deal_detail = "../static/js/active/dealdetail.json"; //获取订单详情

//获取订单id
var urlarg = window.location.search.split('=')[1];

var statuemessage = {
    '1': '待付款',
    '2': '待发货',
    '3': '已发货',
    '4': '已收货',
    '5': '已完成',
    '6': '已关闭',
    '7': '发货失败'
}

//获取订单详情
$.ajax({
     type: 'get',
     url: get_deal_detail,
     data: {urlarg:urlarg},
     dataType: 'json',
     success: function(data){
         showdealdetail(data.data)
         btnhandle(data.data);
     },
     error: function(){
        console.log("获取失败");
     }
 });

//展示订单详情
function showdealdetail(data) {
    $('#orderId').text(data.Fdeal_id);
    $('#memberId').text(data.Fmid);
    $('#receiv_message').text(data.Fconsignee +'，'+ data.Fmobile +'，'+ data.Fdetail);
    $('#goodsimg').attr('src',data.Fgoodsurl);
    $('#goodstext').on('click', function () {
        window.location.href = 'http://st.taxusglobal.com/shopadmin/goods/preview.html?id=290';
    })
    $('#price').text(data.Fmarket_price);
    $('#tzprice').text(data.Fdiscont_price);
    $('#num').text(data.Fbuy_num);
    $('#total').text(data.Ftotal_pay);
    $('#statue').text(statuemessage[data.Fstate]);
    $('#orderstatue').text(statuemessage[data.Fstate]);
    $('#ntotal').text('¥'+ data.Ftotal_pay);

    $('#contralbtn').html(showcontralbtn(data.Fstate));
}


//根据不同状态显示不同操作按钮
function  showcontralbtn(statue) {
    var con =  '<div class="js-wrap-btn">';
    if(statue == "1"){
        con +=  '<span class="label label-danger cp mr5" data-handle="js-dl-close">取消</span>';
    }else if(statue  == '2'){
        con += '<span class="label label-success cp mr5" data-handle="js-express-btn">放币</span>';
    }
}

//给按钮添加事件
function btnhandle(data, eles) {
    switch($(event.target).attr('data-handle')){
        //取消订单
        case 'js-dl-close':
            $('#changebtn').attr('data-statue', '2');
            var con = '是否要关闭会员<span class="pfc">'+data.Fmid +'</span>所下的订单号为<span class="pfc">'+ data.Fdeal_id +'</span>的订单？'
            var param = {
                goodsId: data.Fdeal_id,
                Fmid: data.Fmid
            }
            var cs = {
                url:publicStatic.allurl.cannel_goods,
                type: "get",
                data: param,
                fun: function (testdata) {
                    console.log("关闭会员");
                    console.dir(testdata);
                }
            }
            comMethod.dialogPop("取消订单",con, cs);
            break;
        //放币
        case 'js-express-btn':
            var con = '<div class="form-group">' +
                '<div class="col-sm-4 text-right"><h5>放币数量（BTC）：</h5></div>' +
                '<div class="col-sm-8">' +
                '<input type="text" id="expressodd" class="form-control" readonly="true" value="0.02"> ' +
                '</div></div>';
            var param = {
                goodsId: data.Fdeal_id,
                Fmid: data.Fmid
            }
            var cs = {
                url:publicStatic.allurl.cannel_goods,
                type: "get",
                data: param,
                fun: function (testdata) {
                    console.log("放币成功");
                    console.dir(testdata);
                }
            }
            var formcheck =
            comMethod.dialogPop("放币",con, cs);
            break;
        default:
            console.log("失败");
            return false;
    }
}


//表单按钮操作
//1：发货 2：取消
function contralbtn(data) {
    $('#changebtn').off('click');
    $('#changebtn').on('click', function () {
        var statueval = $(event.target).attr('data-statue');
        switch(statueval){
            //放币
            case "1":
                if(comm.expressCheck($('#expressodd').val())){
                    var param = {
                        expressId: comm.deltrim($('#expressodd').val()),
                        goodsId: data.Fdeal_id,
                        Fmid: data.Fmid
                    }
                    /*                    $.ajax({
                     type: 'post',
                     url: deliver_goods,
                     data: param,
                     dataType: 'json',
                     success: function(data){
                        window.location.reload();
                     },
                     error: function(){
                     console.log("发货失败");
                     }
                     });*/
                    console.log(param);
                }
                break;
            case "2":
                var param = {
                    goodsId: data.Fdeal_id,
                    Fmid: data.Fmid
                }
                /*                 $.ajax({
                 type: 'post',
                 url: cannel_goods,
                 data: param,
                 dataType: 'json',
                 success: function(data){
                    window.location.reload();
                 },
                 error: function(){
                 console.log("取消订单失败");
                 }
                 });*/
                console.log(param);
                break;
            default:
                return false;
                console.log("失败");
        }
    })
}



