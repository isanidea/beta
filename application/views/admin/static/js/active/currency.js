/**
 * Created by gaoweilong on 2017\7\18
 */


var ciMethod = (function () {
    //渲染表格
    var ciTableList = function (param) {
        $('#ci_list').bootstrapTable({
            url: publicStatic.allurl.currency,
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
                title: "编号",
                align: "left",
                valign: "middle"
            }, {
                field: "Ftitle",
                title: "币种简写",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "BTC";
                }
            }, {
                field: "Fbank_name",
                title: "币种名称",
                align: "left",
                valign: "middle"
            },  {
                field: "Fuin",
                title: "初始价格",
                align: "left",
                valign: "middle"
            },{
                field: "Ftruename",
                title: "比特币兑换率",
                align: "left",
                valign: "middle"
            }, {
                field: "Fatm_money",
                title: "创建时间",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    var s = comMethod.moneySplit(value);
                    return s;
                }
            }, {
                field: "Fstate",
                title: "状态",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                   return '使用中'
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
            s += '<span class="label label-success cp mr5" data-btn="js-coin-update">修改</span>'+
                 '<span class="label label-danger cp mr5" data-btn="js-coin-delete">删除</span>'+
                 '<span class="label label-warning cp mr5" data-btn="js-coin-forbidden">禁用</span>'+
                 '<span class="label label-primary cp" data-btn="js-coin-detail">详情</span></div>';
        return s;
    }

    //给按钮添加事件
    function btnhandle(data) {
        switch ($(event.target).attr('data-btn')) {
            case 'js-coin-detail':
                $('#currency_index').modal({
                    keyboard: false,
                    backdrop: "static"
                });
                getDetail(data);
                break;
            case 'js-coin-forbidden':
                var con = '确认禁用币种名为<span class="pfw">' + 'Bitcoin' + '</span>的虚拟币<br>温馨提示：该币种下的所以相关信息都相应的都被隐藏，您是否还要继续此操作？';
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
                comMethod.dialogPop("禁用确认",con, cs);
                break;
            case 'js-coin-delete':
                var con = '确认删除币种名为<span class="pfw">' + 'Bitcoin' + '</span>的虚拟币<br>温馨提示：该币种下的所以相关信息会相应的都被删除，您是否还要继续此操作？';
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
                comMethod.dialogPop("删除确认",con, cs);
                break;
            case 'js-coin-update':
                window.location.href = "http://st.test.com/xnb_admin/currency/au.html?id=666";
                break;
            default:
                console.log('错误');
                return false;
        }
    }

    //详情展示
    function getDetail(data) {
        $('#ci_id').text('1');
        $('#ci_kind').text('BTC');
        $('#ci_coinname').text('Bitcoin');
        $('#ci_price').text('26202');
        $('#ci_time').text('2017-06-22 20:49:10');
        $('#ci_status').text('使用中');
        $('#ci_exchange').text('0.05');
    }

    //查询功能
    function searchFresh(){
        $('#ci_search').on('click', function(){

            var param = {
                'b': $('#coinkind').val()
            } 

            $("#ci_list").bootstrapTable('destroy');
            ciTableList(param);  
        })
    }

    return {
        ciTableList: ciTableList,
        searchFresh: searchFresh
    };

})();


$(function () {

    var param = {};

    //点击查询   
    ciMethod.searchFresh();
    
    //初始化列表
    ciMethod.ciTableList(param);

});



