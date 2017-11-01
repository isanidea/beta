(function () {
    
    var footCon = '<footer class="main-footer">'+
                        '<div class="pull-right hidden-xs">'+
                            '<b>98bit后台管理系统</b>'+
                        '</div>'+
                        '<strong>Copyright © 2017 <a href="#">98bit后台</a>.</strong> All rightsreserved.'+
                  '</footer>';

    var headCon =   '<header class="main-header">'+
                        '<a href="#" class="logo">'+
                            '<span class="logo-mini"><b>98bit</b></span>'+
                            '<span class="logo-lg"><b>98bit管理后台</b></span>'+
                        '</a>'+
                        '<nav class="navbar navbar-static-top">                     '+
                            '<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">'+
                                '<span class="sr-only">Toggle navigation</span>'+
                            '</a>'+
                            '<div class="navbar-custom-menu">'+
                                '<ul class="nav navbar-nav">'+
                                    '<li class="dropdown user user-menu">'+
                                        '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'+
                                            '<img src="http://st.test.com/xnb_admin/libs/adminlte/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">'+
                                            '<span class="hidden-xs">管理员</span>'+
                                        '</a>'+
                                    '</li>'+
                                    '<li>'+
                                        '<a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>'+
                                    '</li>'+
                                '</ul>'+
                            '</div>'+
                        '</nav>'+
                    '</header>';

    $('.wrapper').append(footCon).prepend(headCon);

})();
