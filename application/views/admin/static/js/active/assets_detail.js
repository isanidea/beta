/**
 * Created by gaoweilong on 2017\7\18
 */

var adMethod = (function () {
    //渲染表格
    var adTableList = function (param) {
        $('#ad_list').bootstrapTable({
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
                title: "会员账号",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "2211975224@qq.com";
                }
            }, {
                field: "Ftitle",
                title: "币种",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "BTC";
                }
            }, {
                field: "Fbank_name",
                title: "总量",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "666";
                }
            },  {
                field: "Fuin",
                title: "可用量",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "555";
                }
            },{
                field: "Ftruename",
                title: "冻结量",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return "111";
                }
            }, {
                field: "Fatm_money",
                title: "约值",
                align: "left",
                valign: "middle",
                formatter: function (value, row, index) {
                    return '66600 BIT'
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
                btnhandle(row);
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
                $('#assets_detail').modal({
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
        $('#ad_account').text('2211975224@qq.com');
        $('#ad_kind').text('BTC');
        $('#ad_total').text('666');
        $('#ad_available').text('555');
        $('#ad_freeze').text('111');
        $('#ad_worth').text('666000 BIT');
    }

    //查询功能
    function searchFresh(){
        $('#ad_search').on('click', function(){
            var account = comm.emailCheck($('#user_account').val());

            if(account){
                var type = $('#switch_sel li.active a').attr('data-type');
                var param = {
                    'a': account,
                    'b': $('#coinkind').val(),
                    'type': type
                } 

                $("#ad_list").bootstrapTable('destroy');

                adTableList(param);  

            }else{
                alert('请输入正确的账号！')
            }
        })
    }

    return {
        adTableList: adTableList,
        searchFresh: searchFresh
    };

})();


$(function () {

    var param = {};

    //点击查询   
    adMethod.searchFresh();
    
    //初始化列表
    adMethod.adTableList(param);

});



