(function () {
    var path = window.location.pathname;
    var pathArr = path.split('/');

    var baseUrl = './';
    if (pathArr[3]) {
        baseUrl = '../';
    }
    createAsideNav(baseUrl);

    var activeDir = '.js-nav-' + pathArr[2];
    $(activeDir).addClass('active');



    function createAsideNav(baseUrl) {
        var nav = '';
        nav +=
            '<section class="sidebar" >' +
            '<div class="user-panel">' +
            '<div class="pull-left image">' +
            '<img src="' + baseUrl + 'libs/adminlte/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">' +
            '</div>' +
            '<div class="pull-left info">' +
            '<p>管理员</p>' +
            '<a href="#"><i class="fa fa-circle text-success"></i> 在线</a>' +
            '</div>' +
            '</div>' +
            '<ul class="sidebar-menu">' +

            '<li class="js-nav-index">' +
            '<a href="' + baseUrl + 'index.html">' +
            '<i class="fa fa-dashboard"></i> <span>后台首页</span>' +
            '</a>' +
            '</li>' +

            '<li class="treeview js-nav-deal">' +
            '<a href="#">' +
            '<i class="fa fa-pie-chart"></i>' +
            '<span>订单管理</span>' +
            '<span class="pull-right-container">' +
            '<i class="fa fa-angle-left pull-right"></i>' +
            '</span>' +
            '</a>' +
            '<ul class="treeview-menu">' +
            '<li><a href="' + baseUrl + 'deal/index.html"><i class="fa fa-circle-o"></i> 订单查询</a></li>' +
            '</ul>' +
            '</li>' +

            '<li class="treeview js-nav-assets">' +
            '<a href="#">' +
            '<i class="fa fa-laptop"></i>' +
            '<span>资产管理</span>' +
            '<span class="pull-right-container">' +
            '<i class="fa fa-angle-left pull-right"></i>' +
            '</span>' +
            '</a>' +
            '<ul class="treeview-menu">' +
            '<li><a href="' + baseUrl + 'assets/index.html"><i class="fa fa-circle-o"></i> 提币审核</a></li>' +
            '<li><a href="' + baseUrl + 'assets/detail.html"><i class="fa fa-circle-o"></i> 资产明细</a></li>' +
            '<li><a href="' + baseUrl + 'assets/history.html"><i class="fa fa-circle-o"></i> 充币记录</a></li>' +
            '</ul>' +
            '</li>' +

            '<li class="treeview js-nav-currency">' +
            '<a href="#">' +
            '<i class="fa fa-rmb"></i>' +
            '<span>币种管理</span>' +
            '<span class="pull-right-container">' +
            '<i class="fa fa-angle-left pull-right"></i>' +
            '</span>' +
            '</a>' +
            '<ul class="treeview-menu">' +
            '<li><a href="' + baseUrl + 'currency/index.html"><i class="fa fa-circle-o"></i> 币种列表</a></li>' +
            '<li><a href="' + baseUrl + 'currency/au.html"><i class="fa fa-circle-o"></i> 添加币种</a></li>' +
            '</ul>' +
            '</li>' +

            '<li class="treeview js-nav-entrust">' +
            '<a href="#">' +
            '<i class="fa fa-tasks"></i>' +
            '<span>委托管理</span>' +
            '<span class="pull-right-container">' +
            '<i class="fa fa-angle-left pull-right"></i>' +
            '</span>' +
            '</a>' +
            '<ul class="treeview-menu">' +
            '<li><a href="' + baseUrl + 'entrust/index.html"><i class="fa fa-circle-o"></i> 委托审核列表</a></li>' +
            '</ul>' +
            '</li>' +

            '<li class="treeview js-nav-member">' +
            '<a href="#">' +
            '<i class="fa fa-user-plus"></i> <span>会员管理</span>' +
            '<span class="pull-right-container">' +
            '<i class="fa fa-angle-left pull-right"></i>' +
            '</span>' +
            '</a>' +
            '<ul class="treeview-menu">' +
            '<li><a href="' + baseUrl + 'member/index.html"><i class="fa fa-circle-o"></i> 会员列表</a></li>' +
            '<li><a href="' + baseUrl + 'member/verify.html"><i class="fa fa-circle-o"></i> 会员信息验证</a></li>' +
            '</ul>' +
            '</li>' +

            '<li class="treeview js-nav-btc_ad">' +
            '<a href="#">' +
            '<i class="fa fa-btc"></i> <span>BTC广告管理</span>' +
            '<span class="pull-right-container">' +
            '<i class="fa fa-angle-left pull-right"></i>' +
            '</span>' +
            '</a>' +
            '<ul class="treeview-menu">' +
            '<li><a href="' + baseUrl + 'btc_ad/index.html"><i class="fa fa-circle-o"></i> BTC广告列表</a></li>' +
            '</ul>' +
            '</li>' +
            
            '<li class="treeview js-nav-problem">' +
            '<a href="#">' +
            '<i class="fa  fa-question-circle"></i>' +
            '<span>常见问题管理</span>' +
            '<span class="pull-right-container">' +
            '<i class="fa fa-angle-left pull-right"></i>' +
            '</span>' +
            '</a>' +
            '<ul class="treeview-menu">' +
            '<li><a href="' + baseUrl + 'problem/index.html"><i class="fa fa-circle-o"></i> 问题列表 </a></li>' +
            '<li><a href="' + baseUrl + 'problem/ae.html"><i class="fa fa-circle-o"></i> 添加问题 </a></li>' +
            '</ul>' +
            '</li>' +

            '<li class="treeview js-nav-system_notice">' +
            '<a href="#">' +
            '<i class="fa fa-bullhorn"></i>' +
            '<span>系统公告</span>' +
            '<span class="pull-right-container">' +
            '<i class="fa fa-angle-left pull-right"></i>' +
            '</span>' +
            '</a>' +
            '<ul class="treeview-menu">' +
            '<li><a href="' + baseUrl + 'system_notice/index.html"><i class="fa fa-circle-o"></i> 公告列表 </a></li>' +
            '<li><a href="' + baseUrl + 'system_notice/ae.html"><i class="fa fa-circle-o"></i> 添加公告 </a></li>' +
            '</ul>' +
            '</li>' +
            '</ul>' +
            '</section>';

        $('.main-sidebar').html(nav);

    }
})();
