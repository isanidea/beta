/**
 * Created by gaoweilong on 2017\7\18
 */

var ahMethod = (function () {
    //渲染表格
    var ahTableList = function (param) {
        $('#ah_list').bootstrapTable({
            url: publicStatic.allurl.get_Deal_List,
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
                title: "ID",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "1";
                }
            }, {
                field: "Ftitle",
                title: "地址",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "19LQz2ZR4PvKvD9Dp5qpmjuM8MzJ43fFoS";
                }
            }, {
                field: "Fbank_name",
                title: "数量",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "3";
                }
            },  {
                field: "Fuin",
                title: "时间",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "2017-08-01";
                }
            },{
                field: "Ftruename",
                title: "状态",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "已通过";
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
            s +='<span class="label label-primary cp" data-btn="js-assets-detail">详情</span></div>';
        return s;
    }

    //给按钮添加事件
    function btnhandle(data) {
        switch ($(event.target).attr('data-btn')) {          
            case 'js-assets-detail':
                $('#assets_history').modal({
                    keyboard: false,
                    backdrop: "static"
                });
                getDetail(data);
                break;
            default:
                console.log('错误');
                return false;
        }
    }

    //流水详情展示
    function getDetail(data) {
        $('#ah_id').text('1');
        $('#ah_address').text('19LQz2ZR4PvKvD9Dp5qpmjuM8MzJ43fFoS');
        $('#ah_num').text('3');
        $('#ah_time').text('2017-08-01');
        $('#ah_status').text('通过');
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

            $("#ah_list").bootstrapTable('destroy');
            ahTableList({type: s});

            emptyCondition();
        })
    }

    //清空条件
    function emptyCondition(){
        $('#ordertimebeg').val('');
        $('#ordertimeend').val('');
        $('#ah_user_account').val('');
        $("#coinkind option:first").prop('selected', 'selected');
    }

    //查询功能
    function searchFresh(){

        $('#ah_search').on('click', function(){

            var account = comm.emailCheck($('#ah_user_account').val());

            if(account && account != "error"){
                var type = $('#switch_sel li.active a').attr('data-type');
                var param = {
                    'a': account,
                    'b': $('#coinkind').val(),
                    'c': $('#ordertimebeg').val(),
                    'd': $('#ordertimeend').val(),
                    'type': type
                } 

                $("#ah_list").bootstrapTable('destroy');

                ahTableList(param);  

            }else{
                alert('请输入正确的账号！')
            }
        })
    }

    return {
        ahTableList: ahTableList,
        timeHandle: timeHandle,
        switchStatus: switchStatus,
        searchFresh: searchFresh
    };
})();


$(function () {    

    var param = {type: "3"};

    ahMethod.ahTableList(param);

    ahMethod.timeHandle();

    ahMethod.switchStatus();

    ahMethod.searchFresh();

});



