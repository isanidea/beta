webpackJsonp([3],{BkaM:function(t,e,a){e=t.exports=a("FZ+f")(!1),e.push([t.i,".problem .tab[data-v-1bdbf996]{margin-top:20px}",""])},DLzQ:function(t,e,a){"use strict";var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"problem"},[a("el-breadcrumb",{staticClass:"breadcrumb",attrs:{separator:"/"}},[a("el-breadcrumb-item",{attrs:{to:{path:"/"},replace:""}},[t._v("后台首页")]),t._v(" "),a("el-breadcrumb-item",[t._v("问题管理")]),t._v(" "),a("el-breadcrumb-item",[t._v("问题列表")])],1),t._v(" "),a("router-link",{attrs:{to:{path:"/problem-au"}}},[a("el-button",{attrs:{type:"primary",icon:"plus"}},[t._v("新增问题")])],1),t._v(" "),a("div",{staticClass:"tab"},[a("el-tabs",{attrs:{type:"border-card"},model:{value:t.activeTab,callback:function(e){t.activeTab=e},expression:"activeTab"}},[a("el-tab-pane",{attrs:{label:"平台常见问题",name:"1"}},[a("cms-list",{attrs:{typeId:"1002"}})],1),t._v(" "),a("el-tab-pane",{attrs:{label:"场外交易常见问题",name:"2"}},[a("cms-list",{attrs:{typeId:"1004"}})],1)],1)],1)],1)},n=[],i={render:r,staticRenderFns:n};e.a=i},GRIZ:function(t,e,a){var r=a("BkaM");"string"==typeof r&&(r=[[t.i,r,""]]),r.locals&&(t.exports=r.locals);a("rjj0")("23008b7b",r,!0)},KbF4:function(t,e,a){"use strict";function r(t){a("GRIZ")}Object.defineProperty(e,"__esModule",{value:!0});var n=a("brRo"),i=a("DLzQ"),s=a("VU/8"),l=r,o=s(n.a,i.a,l,"data-v-1bdbf996",null);e.default=o.exports},PhnO:function(t,e,a){e=t.exports=a("FZ+f")(!1),e.push([t.i,".table[data-v-3b604242]{width:100%;margin-top:20px}.table .page[data-v-3b604242]{margin-top:20px}",""])},W2OU:function(t,e,a){"use strict";function r(t){a("la+H")}var n=a("vwqJ"),i=a("sC/l"),s=a("VU/8"),l=r,o=s(n.a,i.a,l,"data-v-3b604242",null);e.a=o.exports},brRo:function(t,e,a){"use strict";var r=a("W2OU");e.a={name:"problem",data:function(){return{activeTab:"1"}},components:{cmsList:r.a}}},"la+H":function(t,e,a){var r=a("PhnO");"string"==typeof r&&(r=[[t.i,r,""]]),r.locals&&(t.exports=r.locals);a("rjj0")("16b0a22b",r,!0)},"sC/l":function(t,e,a){"use strict";var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"cms-list"},[a("div",{staticClass:"table"},[a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:t.tableData,"max-height":"950",border:""}},[a("div",{slot:"empty"},[t._v(" 暂无数据! ")]),t._v(" "),a("el-table-column",{attrs:{prop:"id",label:"ID",width:"120"}}),t._v(" "),a("el-table-column",{attrs:{prop:"title",label:t.title,width:"250"}}),t._v(" "),a("el-table-column",{attrs:{prop:"addtime",label:"发布时间",width:"180"}}),t._v(" "),a("el-table-column",{attrs:{prop:"username",label:"发布人",width:"180"}}),t._v(" "),a("el-table-column",{attrs:{prop:"state",label:"状态"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("el-tag",{attrs:{color:t.stateColor(e.row.state)},domProps:{textContent:t._s(t.stateText(e.row.state))}})]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"操作",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("a",{attrs:{target:"_blank",href:t.previewLink(e.row.id)},on:{click:function(a){t.previewHandle(e.row.state,a)}}},[a("el-button",{attrs:{type:"danger",size:"small",icon:"search"}},[t._v("预览")])],1),t._v(" "),a("router-link",{attrs:{to:{path:t.auLink,query:{id:e.row.id}}}},[a("el-button",{attrs:{type:"primary",size:"small",icon:"edit"}},[t._v("修改")])],1)]}}])})],1),t._v(" "),a("div",{staticClass:"page"},[a("el-pagination",{attrs:{"current-page":t.pager.page,"page-sizes":[10,20,50,100],"page-size":t.pager.num,layout:"sizes, prev, pager, next",total:t.pager.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.pager.page=e}}})],1)],1)])},n=[],i={render:r,staticRenderFns:n};e.a=i},tTwd:function(t,e,a){"use strict";function r(t,e){if(t){var a={536936460:"密码校验失败",536936454:"用户未激活",536936459:"邮箱不存在",536975769:"用户未登录",536936450:"该邮箱已经注册过",536870912:"参数错误",536870913:"图片验证码失效",536870914:"图片验证码校验失败",536870915:"图片验证码失效",537198599:"账户余额不足",536870923:"用户免费抽奖次数已经用完",537198594:"查不到相关信息"},r=t+"";return a[r]?a[r]:e||"系统错误，请稍后再试。"}return"系统错误，请稍后再试。"}e.a=r},vwqJ:function(t,e,a){"use strict";var r=a("mZex"),n=a.n(r),i=a("tTwd"),s=n.a.api+"/get_cms_list",l=n.a.doMain+"/cms/pdetail",o=n.a.doMain+"/cms/pPro_detail";e.a={name:"cms-list",data:function(){return{loading:!1,pager:{page:1,num:10,total:0},tableData:[],auLink:""}},props:{typeId:{type:String,required:!0,default:"1001"}},created:function(){this._init()},methods:{_init:function(){this._getData()},_getData:function(){var t=this;this.loading=!0;var e={page:this.pager.page,num:this.pager.num,type_id:this.typeId};this.$ajax(s,e,function(e){if(0===e.iRet){var r=e.data;if(r)var n=t,s=setTimeout(function(){clearTimeout(s),n.loading=!1,n.pager.total=e.total,n.tableData=r},300);else t.$message.warning("没有相关数据"),t.loading=!1}else t.$message.error(a.i(i.a)(e.iRet)),t.loading=!1})},handleSizeChange:function(t){if(this.pager.num=t,1===this.pager.page)return this._getData();this.pager.page=1},handleCurrentChange:function(t){this.pager.page=t,this._getData()},stateText:function(t){switch(+t){case 0:return"正常";case 1:return"关闭"}},stateColor:function(t){switch(+t){case 0:return"#13ce66";case 1:return"#bfcbd9"}},previewLink:function(t){return"1001"===this.typeId?l+"?id="+t:o+"?id="+t},previewHandle:function(t,e){0!=+t&&(e.preventDefault(),this.$message.error("关闭状态不能预览！"))}},computed:{title:function(){var t=this.typeId;return"1001"===t?(this.auLink="/notice-au","系统公告标题"):"1002"===t||"1004"===t?(this.auLink="/problem-au","常见问题"):"标题"}}}}});