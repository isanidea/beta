/**
 * Created by Administrator on 2017\7\7 0007.
 * 公共静态变量
 */
var publicStatic = ( function() {

    //所有接口地址
    var allurl = {
        get_deal_detail: "../static/js/active/dealdetail.json",   //获取订单详情
        get_Deal_List: "../static/js/active/orderlist.json", //订单列表
        deliver_goods: "../static/js/active/orderlist.json", //发货
        cannel_goods: "../static/js/active/orderlist.json", //取消订单
        update_goods: "../static/js/active/orderlist.json", //修改订单
        get_member_List: "../static/js/active/userinfo.json", //获取会员信息列表
        contral_delete: "../static/js/active/userinfo.json", //删除会员
        get_user_info: "../static/js/active/userinfoonly.json", //获取用户信息
        update_user_info: "../static/js/active/userinfoonly.json", //修改用户信息
        get_cash_List: "../static/js/active/cash_list.json", //提现列表
        submit_check: "http://st.test.com/xnb_admin/static/js/active/assets.json", //提现审核
        assets: "http://st.test.com/xnb_admin/static/js/active/assets.json", //提现审核
        entrust: "http://st.test.com/xnb_admin/static/js/active/entrust.json", //提现审核
        currency: "http://st.test.com/xnb_admin/static/js/active/currency.json" //提现审核
    }

    //订单状态
    var statuemessage = {
        '1': '待付款',
        '2': '待发货',
        '3': '已发货',
        '5': '已完成',
        '6': '已关闭'
    }

    //提现状态
    var cashstatue = {
        "1": "通过",
        "2": "未通过",
        "3": "审核中"
    }

    var memberstatue = {
        "1": "正常",
        "0": "未激活"
    }

    return {
        allurl: allurl,
        statuemessage: statuemessage,
        cashstatue: cashstatue,
        memberstatue: memberstatue
    }

} )();



