/**
 * Created by gaoweilong on 2017\7\18
 */

var aiMethod = (function () {
    //渲染表格
    var aiTableList = function (param) {
        $('#ai_list').bootstrapTable({
            url: publicStatic.allurl.assets,
            dataField: "rows",
            cache: false, //是否使用缓存，默认为true
            striped: true, //是否显示行间隔色
            pagination: true, //是否显示分页
            pageSize: 10, // 每页的记录行数
            pageNumber: 1, // 初始化加载第一页，默认第一页
            pageList: [10, 20, 50, 80, 100, 120, 150, 180, 200], //可供选择的每页的行数
            sortable: false, //排序方式
            sortOrder: "asc",
            escape: true,
            search: false, //是否显示表格搜索
            showRefresh: false, //是否显示刷新按钮
            clickToSelect: true, //是否启用点击选中行
            toolbar: "#toolbar_screen", //工具按钮用哪个容器
            sidePagination: "server", //分页方式：client客户端分页，server服务端分页
            idField: "Fmid",
            queryParams: function (params) {
                param['pageNumber'] = params.limit;
                param['pageSize'] = (params.offset / params.limit) + 1;
                console.log(param);
                return param;
            },
            columns: [{
                field: "Fmid",
                title: "会员账号",
                align: "left",
                valign: "middle"
            }, {
                field: "Ftitle",
                title: "手机号",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "13345678900";
                }
            }, {
                field: "Fbank_name",
                title: "币种",
                align: "left",
                valign: "middle"
            },  {
                field: "Fuin",
                title: "名称",
                align: "left",
                valign: "middle"
            },{
                field: "Ftruename",
                title: "可提数量",
                align: "left",
                valign: "middle"
            }, {
                field: "Fatm_money",
                title: "提币数量",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    var s = comMethod.moneySplit(value);
                    return s;
                }
            }, {
                field: "Factual_get_cash",
                title: "实收币",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    var s = comMethod.moneySplit(value);
                    if(s && s != null){
                        return '<span style="color:red">' + s +'</span>';
                    }else{
                        return "-";
                    }
                }
            }, {
                field: "Faddtime",
                title: "提币日期",
                align: "left",
                valign: "middle"
            }, {
                field: "Fstate",
                title: "状态",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    var s = publicStatic.cashstatue[value];
                    if(value == "2" || value == "3"){
                        return '<span class="pfc">'+ s +'</span>';
                    }else{
                        return s;
                    }
                }
            }, {
                field: "Fstate",
                title: "操作",
                align: "center",
                valign: "middle",
                formatter: function (value, row, index) {
                    var s = statuebtn(value);
                    return s;
                }
            }],
            formatNoMatches: function () {
                return '无符合条件的记录';
            },
            onClickRow: function (row, $element) {
                btnhandle(row, $element);
            }
        });
    };

    //根据状态加载操作按钮
    function statuebtn(val) {
        var s = '<div class="js-contral-btn">';
        if (val == "1" || val == "2") {
            s += '<span class="label label-primary cp" data-btn="js-cashdetail-btn">详情</span></div>';
        } else if (val == "3") {
            s += '<span class="label label-success cp mr5" data-btn="js-pass">通过</span>' +
                '<span class="label label-danger cp" data-btn="js-nopass">拒绝</span></div>';
        } else {
            s += '<span class="label label-primary cp" data-btn="js-cashdetail-btn">详情</span></div>';
        }
        return s;
    }

    //给按钮添加事件
    function btnhandle(data) {
        switch ($(event.target).attr('data-btn')) {
            case 'js-cashdetail-btn':
                $('#assets_index').modal({
                    keyboard: false,
                    backdrop: "static"
                });
                getDetail(data);
                break;
            case 'js-pass':
                var con = '同意<span class="pfw">' + data.Fmid + '</span>会员提币<span class="pfc">' + data.Fatm_money + '个</span>？';
                var cs = {
                    url:publicStatic.allurl.submit_check,
                    type: "get",
                    data:{
                        cashstu:"1",
                        cashfuin: data.Fuin
                    },
                    fun: function (testdata) {
                        console.log(testdata);
                    }
                }
                comMethod.dialogPop("同意提币申请",con, cs);
                break;
            case 'js-nopass':
                var con = '拒绝<span class="pfw">' + data.Fmid + '</span>会员提币<span class="pfc">' + data.Fatm_money + '个</span>？';
                var cs = {
                    url:publicStatic.allurl.submit_check,
                    type: "get",
                    data:{
                        cashstu:"0",
                        cashfuin: data.Fuin
                    },
                    fun: function (testdata) {
                        console.log(testdata);
                    }
                }
                comMethod.dialogPop("拒绝提币申请",con, cs);
                break;
            default:
                console.log('错误');
                return false;
        }
    }

    //流水详情展示
    function getDetail(data) {
        $('#ai_account').text('2211987654@qq.com');
        $('#ai_mobile').text('13345678900');
        $('#ai_kind').text('LTC');
        $('#ai_coinname').text('Litecoin');
        $('#ai_available').text('30.56');
        $('#ai_extract').text('0.5');
        $('#ai_real').text('0.49');
        $('#ai_time').text('2017-06-22 20:49:10');
        $('#status').text(publicStatic.cashstatue[data.Fstate]);
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

            $("#ai_list").bootstrapTable('destroy');
            aiTableList({type: s});

            emptyCondition();
        })
    }

    //清空条件
    function emptyCondition(){
        $('#ordertimebeg').val('');
        $('#ordertimeend').val('');
        $('#ai_user_account').val('');
        $("#coinkind option:first").prop('selected', 'selected');
    }

     //查询功能
    function searchFresh(){

        $('#ai_search').on('click', function(){

            var account = comm.emailCheck($('#ai_user_account').val());

            if(account && account != "error")
                var type = $('#switch_sel li.active a').attr('data-type');
                var param = {
                    'a': account,
                    'b': $('#coinkind').val(),
                    'c': $('#ordertimebeg').val(),
                    'd': $('#ordertimeend').val(),
                    'type': type
                } 

                $("#ai_list").bootstrapTable('destroy');

                aiTableList(param);  

            }else{
                alert('请输入正确的账号！')
            }
        })
    }

    return {
        aiTableList: aiTableList,
        timeHandle: timeHandle,
        switchStatus: switchStatus,
        searchFresh: searchFresh
    };

})();


$(function () {

    var param = {type: "3"};

    aiMethod.aiTableList(param);

    aiMethod.timeHandle(); 

    aiMethod.switchStatus(); 

    aiMethod.searchFresh(); 

});



