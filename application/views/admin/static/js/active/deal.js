/**
 * Created by Administrator on 2017\6\26 0026.
 */
var deallist = (function () {

    var queryStateDeal = function (arg) {
        $('#deal_list').bootstrapTable({
            url: publicStatic.allurl.get_Deal_List,
            dataField: "rows",
            cache: false, //是否使用缓存，默认为true
            striped: true, //是否显示行间隔色
            pagination: true, //是否显示分页
            pageSize: 10, // 每页的记录行数
            pageNumber: 1, // 初始化加载第一页，默认第一页
            pageList: [10, 20, 50, 100, 150, 200], //可供选择的每页的行数
            sortable: false, //排序方式
            sortOrder: "asc",
            escape: true,
            search: false, //是否显示表格搜索
            showRefresh: false, //是否显示刷新按钮
            clickToSelect: true, //是否启用点击选中行
            toolbar: "#toolbar_screen", //工具按钮用哪个容器
            sidePagination: "server", //分页方式：client客户端分页，server服务端分页
            idField: "Fdeal_id",
            queryParams: function (params) {
                arg["pageNumber"] = params.limit, //每页记录数
                    arg["pageSize"] = (params.offset/params.limit) + 1, //当前页
                    console.log(arg);
                return arg;
            },
            columns: [ {
                checkbox: true,
            }, {
                field: "Fmid",
                title: "会员账号",
                align: "center",
                valign: "middle"
            },{
                field: "Fdeal_id",
                title: "订单编号",
                align: "center",
                valign: "middle",
                formatter: function (value, row, index) {
                    return value ;
                }
            },{
                field: "Ftitle",
                title: "下单时间",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "2017-06-22 20:49:10";
                }
            }, {
                field: "Fdetail",
                title: "价格／BTC",
                align: "center",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "20610.48 CNY";
                }
            }, {
                field: "Ftotal_pay",
                title: "交易数量（BTC）",
                align: "center",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "0.02000000";
                }
            },{
                field: "Ftotal_pay",
                title: "交易金额",
                align: "center",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "412.21 CNY";
                }
            },{
                field: "Ftotal_pay",
                title: "交易对方",
                align: "center",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "349612137";
                }
            },{
                field: "Fstate",
                title: "交易状态",
                align: "center",
                valign: "middle",
                formatter: function (value, row, index) {
                    return publicStatic.statuemessage[value];
                }
            }, {
                field: "Fstate",
                title: "操作",
                align: "center",
                valign: "middle",
                formatter: function (value, row, index) {
                    return showcontralbtn(value);
                }
            }],
            formatNoMatches: function () {
                return '无符合条件的记录';
            },
            onClickRow: function (row,  $element) {
                btnhandle(row, $element);
            }
        });
    };

    //根据不同状态显示不同操作按钮
    function  showcontralbtn(statue) {
        var con =  '<div class="js-wrap-btn">';
        if(statue == "1"){
            con +=  '<span class="label label-danger cp mr5" data-handle="js-dl-close">取消</span>';
        }else if(statue  == '2'){
            con += '<span class="label label-success cp mr5" data-handle="js-express-btn">放币</span>';
        }
        return con + '<span  class="label label-primary cp mr5" data-handle="js-dl-detail">详情</span></div>';
    }

    //设置表单查询参数值
    function getsearchval(obj, arg) {
        var searchval = $('#orderid').val();
        var receivename = $('#receivename').val();
        var receivephone = $('#receivephone').val();
        var membergrade = $('#membergrade option:selected').val();
        var ordertimebeg = $('#ordertimebeg').val();
        var ordertimeend = $('#ordertimeend').val();

        var params = {
            pageNumber: obj.limit, //每页记录数
            pageSize: (obj.offset/obj.limit) + 1, //当前页
            type:type,
            ordertimebeg: ordertimebeg,
            ordertimeend: ordertimeend,
            searchval: searchval.replace(/\s/g,''),
            receivename: receivename.replace(/\s/g,''),
            receivephone: receivephone.replace(/\s/g,''),
            membergrade: membergrade.replace(/\s/g,'')
        }
        return params;
    }

    //给按钮添加事件
    function btnhandle(data, eles) {
        switch($(event.target).attr('data-handle')){
            //详情
            case 'js-dl-detail':
                window.location.href = "dealdetail.html";
                break;
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
            //发货
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

    //时间处理
    function timeHandle(){
        //起始时间
        laydate({
            elem: '#ordertimebeg'
        });
        //结束时间
        laydate({
            elem: '#ordertimeend',
            choose: function () {
                timeTotip();
            }
        });
    }

    //时间提示
    function timeTotip() {
        var bt = new Date($('#ordertimebeg').val()).getTime();
        var et = new Date($('#ordertimeend').val()).getTime();
        if(bt > et){
            $('#ordertimeend').val("");
            alert('输入的起始"截止日期"不能大于"起始日期"');
        }
    }

    //不同状态的提现切换
    function switchStatus(){
        $('#switch_sel').on('click', 'li a', function () {

            var s = $(this).attr('data-type');
            $(this).parent('li').addClass('active').siblings().removeClass('active');

            $("#deal_list").bootstrapTable('destroy');
            queryStateDeal({ type: s});

            emptyCondition();
        })
    }

    //清空条件
    function emptyCondition(){
        $("#ordertimebeg").val("");
        $("#ordertimeend").val("");
        $("#receivename").val("");
        $("#orderid").val("");
        $("#receivephone").val("");
        $("#membergrade").val("");
    }

    //查询功能
    function searchFresh(){

        $('#ordersearch').on('click', function(){

            var account = comm.emailCheck($('#memberId').val());
            var orderId = comm.orderidCheck($('#orderid').val());

            if(account != 'error'){

                if(orderId != 'error'){
                    var type = $('#switch_sel li.active a').attr('data-type');
                    var param = {
                        'a': account,
                        'b': $('#orderid').val(),
                        'e': $('#memberId').val(),
                        'c': $('#ordertimebeg').val(),
                        'd': $('#ordertimeend').val(),
                        'type': type
                    } 

                    $("#deal_list").bootstrapTable('destroy');

                    queryStateDeal(param);  
                    
                }else{
                    alert('请输入正确的订单号！')
                }

            }else{
                alert('请输入正确的账号！')
            }
        })
    }

    //导出功能
    function orderExport(){

        $(document).on('click', '#exportbtn', function () {
            method1('deal_list');
        })

    }



    return {
        queryStateDeal: queryStateDeal,
        timeHandle: timeHandle,
        switchStatus: switchStatus,
        searchFresh: searchFresh,
        orderExport: orderExport
    };

})();

$(function () {

    var param = {type: "1"};

    deallist.queryStateDeal(param);

    deallist.timeHandle(); 

    deallist.switchStatus(); 

    deallist.searchFresh(); 

    deallist.orderExport(); 

});

