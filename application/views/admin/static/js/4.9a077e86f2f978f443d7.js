webpackJsonp([4],{"1E7D":function(t,e,a){"use strict";var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"notice"},[a("el-breadcrumb",{staticClass:"breadcrumb",attrs:{separator:"/"}},[a("el-breadcrumb-item",{attrs:{to:{path:"/"},replace:""}},[t._v("后台首页")]),t._v(" "),a("el-breadcrumb-item",[t._v("公告管理")]),t._v(" "),a("el-breadcrumb-item",[t._v("公告列表")])],1),t._v(" "),a("router-link",{attrs:{to:{path:"/notice-au"}}},[a("el-button",{attrs:{type:"primary",icon:"plus"}},[t._v("发布公告")])],1),t._v(" "),a("cms-list",{attrs:{typeId:"1001"}})],1)},n=[],r={render:i,staticRenderFns:n};e.a=r},FPhu:function(t,e,a){"use strict";function i(t){a("OcqD")}Object.defineProperty(e,"__esModule",{value:!0});var n=a("RDrg"),r=a("1E7D"),s=a("VU/8"),o=i,l=s(n.a,r.a,o,"data-v-1621ef50",null);e.default=l.exports},OcqD:function(t,e,a){var i=a("y+k9");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);a("rjj0")("df13b548",i,!0)},PhnO:function(t,e,a){e=t.exports=a("FZ+f")(!1),e.push([t.i,".table[data-v-3b604242]{width:100%;margin-top:20px}.table .page[data-v-3b604242]{margin-top:20px}",""])},RDrg:function(t,e,a){"use strict";var i=a("W2OU");e.a={name:"notice",components:{cmsList:i.a}}},W2OU:function(t,e,a){"use strict";function i(t){a("la+H")}var n=a("vwqJ"),r=a("sC/l"),s=a("VU/8"),o=i,l=s(n.a,r.a,o,"data-v-3b604242",null);e.a=l.exports},"la+H":function(t,e,a){var i=a("PhnO");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);a("rjj0")("16b0a22b",i,!0)},"sC/l":function(t,e,a){"use strict";var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"cms-list"},[a("div",{staticClass:"table"},[a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:t.tableData,"max-height":"950",border:""}},[a("div",{slot:"empty"},[t._v(" 暂无数据! ")]),t._v(" "),a("el-table-column",{attrs:{prop:"id",label:"ID",width:"120"}}),t._v(" "),a("el-table-column",{attrs:{prop:"title",label:t.title,width:"250"}}),t._v(" "),a("el-table-column",{attrs:{prop:"addtime",label:"发布时间",width:"180"}}),t._v(" "),a("el-table-column",{attrs:{prop:"username",label:"发布人",width:"180"}}),t._v(" "),a("el-table-column",{attrs:{prop:"state",label:"状态"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("div",{staticStyle:{cursor:"pointer"},on:{click:function(a){t.updateState(e.row)}}},[0==e.row.state?a("el-tag",{attrs:{type:"success"}},[t._v("正常")]):1==e.row.state?a("el-tag",{attrs:{type:"gray"}},[t._v("关闭")]):t._e()],1)]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"操作",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("a",{attrs:{target:"_blank",href:t.previewLink(e.row.id)},on:{click:function(a){t.previewHandle(e.row.state,a)}}},[a("el-button",{attrs:{type:"danger",size:"small",icon:"search"}},[t._v("预览")])],1),t._v(" "),a("router-link",{attrs:{to:{path:t.auLink,query:{id:e.row.id}}}},[a("el-button",{attrs:{type:"primary",size:"small",icon:"edit"}},[t._v("修改")])],1)]}}])})],1),t._v(" "),a("div",{staticClass:"page"},[a("el-pagination",{attrs:{"current-page":t.pager.page,"page-sizes":[10,20,50,100],"page-size":t.pager.num,layout:"sizes, prev, pager, next",total:t.pager.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.pager.page=e}}})],1)],1)])},n=[],r={render:i,staticRenderFns:n};e.a=r},tTwd:function(t,e,a){"use strict";function i(t,e){if(t){var a={536936460:"密码校验失败",536936454:"用户未激活",536936459:"邮箱不存在",536975769:"用户未登录,请刷新页面",536936450:"该邮箱已经注册过",536870912:"参数错误",536870913:"图片验证码失效",536870914:"图片验证码校验失败",536870915:"图片验证码失效",537198599:"账户余额不足",536870923:"用户免费抽奖次数已经用完",537198594:"查不到相关信息"},i=t+"";return a[i]?a[i]:e||"系统错误，请稍后再试。"}return"系统错误，请稍后再试。"}e.a=i},vwqJ:function(t,e,a){"use strict";var i=a("mZex"),n=a.n(i),r=a("tTwd"),s=n.a.api+"/get_cms_list",o=n.a.api+"/update_cms_info",l=n.a.doMain+"cms/pdetail",c=n.a.doMain+"cms/pPro_detail";e.a={name:"cms-list",data:function(){return{loading:!1,pager:{page:1,num:10,total:0},tableData:[],auLink:""}},props:{typeId:{type:String,required:!0,default:"1001"}},created:function(){this._init()},methods:{_init:function(){this._getData()},_getData:function(){var t=this;this.loading=!0;var e={page:this.pager.page,num:this.pager.num,type_id:this.typeId};this.$ajax(s,e,function(e){if(0===e.iRet){var i=e.data;if(i)var n=t,s=setTimeout(function(){clearTimeout(s),n.loading=!1,n.pager.total=e.total,n.tableData=i},300);else t.$message.warning("没有相关数据"),t.loading=!1}else t.$message.error(a.i(r.a)(e.iRet)),t.loading=!1})},handleSizeChange:function(t){if(this.pager.num=t,1===this.pager.page)return this._getData();this.pager.page=1},handleCurrentChange:function(t){this.pager.page=t,this._getData()},previewLink:function(t){return"1001"===this.typeId?l+"?id="+t:c+"?id="+t},previewHandle:function(t,e){0!=+t&&(e.preventDefault(),this.$message.error("关闭状态不能预览！"))},updateState:function(t){var e=this,a=+t.state,i=void 0,n=void 0;0===a?(i=1,n="确定把 ‘"+t.title+"’ 切换为【关闭】状态？"):(i=0,n="确定把 ‘"+t.title+"’ 切换为【正常】状态？"),this.$confirm(n,"提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){var a={id:t.id,state:i};e._updateCurrencyState(a)}).catch(function(){console.log("取消")})},_updateCurrencyState:function(t){var e=this;this.loading=!0,this.$ajax(o,t,function(i){0===i.iRet?(e.$message.success("切换成功"),e.tableData.forEach(function(e){if(e.id===t.id){var a=e.state;e.state=1==+a?"0":"1"}})):e.$message.error(a.i(r.a)(i.iRet,"切换失败")),e.loading=!1})}},computed:{title:function(){var t=this.typeId;return"1001"===t?(this.auLink="/notice-au","系统公告标题"):"1002"===t||"1004"===t?(this.auLink="/problem-au","常见问题"):"标题"}}}},"y+k9":function(t,e,a){e=t.exports=a("FZ+f")(!1),e.push([t.i,"",""])}});