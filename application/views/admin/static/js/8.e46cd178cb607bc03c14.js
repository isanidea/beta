webpackJsonp([8],{EJeR:function(t,e,a){var i=a("bhG8");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);a("rjj0")("4d00a263",i,!0)},Iwxg:function(t,e,a){"use strict";var i=a("edKx"),l=a("mZex"),n=a.n(l),o=a("tTwd"),r=n.a.api+"/find_coin_list",s=n.a.api+"/find_finance_log",c=n.a.api+"/check_finance_state";e.a={name:"assets",data:function(){return{showTip:!0,loading:!1,coin:[],query:{state:"",coin_id:"",uin:"",b_time:"",e_time:""},pager:{page:1,num:10,total:0},tableData:[],dialog:{visible:!1,detail:{}}}},created:function(){this._getCoin()},methods:{_getCoin:function(){var t=this;this.$ajax(r,{},function(e){if(0===e.iRet){var i=e.data;i?t.coin=i:t.$message.warning("获取币种信息失败")}else t.$message.error(a.i(o.a)(e.iRet))})},_getData:function(){var t=this;if(!this._checkQuery())return!1;this.loading=!0;var e={uin:a.i(i.a)(this.query.uin),state:this.query.state,coin_id:this.query.coin_id,b_time:this.query.b_time,e_time:this.query.e_time,page:this.pager.page,num:this.pager.num};console.log(e),this.$ajax(s,e,function(e){if(0===e.iRet){var i=e.data;if(i){t.showTip=!1;var l=t,n=setTimeout(function(){clearTimeout(n),l.loading=!1,l.pager.total=e.total,l.tableData=i},300)}else t.loading=!1,t.tableData=[],t.pager.total=0,t.$message.warning("没有查到相关信息")}else t.loading=!1,t.$message.error(a.i(o.a)(e.iRet))})},_checkQuery:function(){if(!this.query.state)return this.$message.error("请选择审核状态！"),!1;if(!this.query.coin_id)return this.$message.error("请选择币种！"),!1;var t=this.query.b_time?new Date(this.query.b_time).getTime():0,e=this.query.e_time?new Date(this.query.e_time).getTime():0;return!(e&&t&&e<t)||(this.$message.error("结束时间不能小于开始时间！"),!1)},onSubmit:function(){1!==this.pager.page?this.pager.page=1:this._getData()},handleSizeChange:function(t){if(this.pager.num=t,1===this.pager.page)return this._getData();this.pager.page=1},handleCurrentChange:function(t){this.pager.page=t,this._getData()},setBeginTime:function(t){this.query.b_time=t||""},setEndTime:function(t){this.query.e_time=t||""},showDetail:function(t){this.dialog.detail=t,this.dialog.visible=!0},stateText:function(t){switch(+t){case 1:return"审核通过";case 2:return"审核不通过";case 3:return"待审核";case 4:return"系统确认中";case 5:return"已取消"}},stateColor:function(t){switch(+t){case 1:return"#13ce66";case 2:return"#ccc";case 3:return"#f7ba2a"}},coinType:function(t){var e=this.coin||"";if(e){var a=void 0;return e.forEach(function(e){t===e.coin_id&&(a=e.abbreviation)}),a}},confirmAction:function(t){var e=this,a=void 0,i=this.dialog.detail.id;1===t?a="确定让ID为 "+i+" 的提币记录通过审核？":2===t&&(a="确定不让ID为 "+i+" 的提币记录通过审核？"),this.$confirm(a,"提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){console.log("确定"),e._updateState(t)}).catch(function(){console.log("取消")})},_updateState:function(t){var e=this,i={id:this.dialog.detail.id,coin_id:this.dialog.detail.coin_id,uin:this.dialog.detail.uin,state:t};this.$ajax(c,i,function(t){0===t.iRet?(e.$message.success("操作成功"),e.dialog.visible=!1,e._getData()):e.$message.error(a.i(o.a)(t.iRet))})}}}},bhG8:function(t,e,a){e=t.exports=a("FZ+f")(!1),e.push([t.i,".tip[data-v-194f697a]{margin-top:50px;text-align:center}.page[data-v-194f697a],.table[data-v-194f697a]{margin-top:20px}.dialog-footer[data-v-194f697a]{padding:20px 0}",""])},edKx:function(t,e,a){"use strict";function i(t,e){return t&&(t+=""),t?t.replace(e||/[&<">'\/](?:(amp|lt|quot|gt|#39|nbsp|#\d+);)?/g,function(t,e){return e?t:{"<":"&lt;","&":"&amp;",'"':"&quot;",">":"&gt;","'":"&#x27;","/":"&#x2F;"}[t]}):""}e.a=i},fBaF:function(t,e,a){"use strict";var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"assets"},[a("el-breadcrumb",{staticClass:"breadcrumb",attrs:{separator:"/"}},[a("el-breadcrumb-item",{attrs:{to:{path:"/"},replace:""}},[t._v("后台首页")]),t._v(" "),a("el-breadcrumb-item",[t._v("资产管理")]),t._v(" "),a("el-breadcrumb-item",[t._v("提币审核")])],1),t._v(" "),a("el-row",{attrs:{gutter:5}},[a("el-col",{attrs:{span:4}},[a("el-select",{staticStyle:{width:"100%"},attrs:{placeholder:"请选择提币记录状态"},model:{value:t.query.state,callback:function(e){t.query.state=e},expression:"query.state"}},[a("el-option",{attrs:{label:"全部",value:"100"}}),t._v(" "),a("el-option",{attrs:{label:"待审核",value:"3"}}),t._v(" "),a("el-option",{attrs:{label:"审核通过",value:"1"}}),t._v(" "),a("el-option",{attrs:{label:"审核不通过",value:"2"}})],1)],1),t._v(" "),a("el-col",{attrs:{span:3}},[a("el-select",{staticStyle:{width:"100%"},attrs:{placeholder:"请选择币种"},model:{value:t.query.coin_id,callback:function(e){t.query.coin_id=e},expression:"query.coin_id"}},t._l(t.coin,function(t){return a("el-option",{key:t.coin_id,attrs:{label:t.abbreviation,value:t.coin_id}})}))],1),t._v(" "),a("el-col",{attrs:{span:3}},[a("el-input",{attrs:{placeholder:"会员ID",type:"text"},nativeOn:{keyup:function(e){if(!("button"in e)&&t._k(e.keyCode,"enter",13))return null;t.onSubmit(e)}},model:{value:t.query.uin,callback:function(e){t.query.uin=e},expression:"query.uin"}})],1),t._v(" "),a("el-col",{attrs:{span:8}},[a("el-date-picker",{staticStyle:{width:"46%"},attrs:{type:"date",editable:!1,placeholder:"开始时间"},on:{change:t.setBeginTime},model:{value:t.query.b_time,callback:function(e){t.query.b_time=e},expression:"query.b_time"}}),t._v(" "),a("span",{staticStyle:{width:"6%",overflow:"hidden"}},[t._v("--")]),t._v(" "),a("el-date-picker",{staticStyle:{width:"46%"},attrs:{type:"date",editable:!1,placeholder:"结束时间"},on:{change:t.setEndTime},model:{value:t.query.e_time,callback:function(e){t.query.e_time=e},expression:"query.e_time"}})],1),t._v(" "),a("el-col",{attrs:{span:2}},[a("el-button",{attrs:{type:"primary"},on:{click:t.onSubmit}},[a("i",{staticClass:"el-icon-search"}),t._v("查询")])],1)],1),t._v(" "),t.showTip?a("div",{staticClass:"tip"},[t._v(" 请输入参数进行查询 ")]):a("div",{staticClass:"table"},[a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticStyle:{width:"100%"},attrs:{data:t.tableData,"max-height":"950",border:""}},[a("div",{slot:"empty"},[t._v(" 暂无数据! ")]),t._v(" "),a("el-table-column",{attrs:{prop:"uin",label:"会员ID",width:"120"}}),t._v(" "),a("el-table-column",{attrs:{prop:"coin_addr",label:"提币地址",width:"120"}}),t._v(" "),a("el-table-column",{attrs:{prop:"vol",label:"交易数量",width:"120"}}),t._v(" "),a("el-table-column",{attrs:{prop:"atm_rate_vol",label:"手续费",width:"120"}}),t._v(" "),a("el-table-column",{attrs:{prop:"real_revice_vol",label:"实际到账数量",width:"120"}}),t._v(" "),a("el-table-column",{attrs:{prop:"coin_id",label:"币种类型",width:"100"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("el-tag",{domProps:{textContent:t._s(t.coinType(e.row.coin_id))}})]}}])}),t._v(" "),a("el-table-column",{attrs:{prop:"create_time",label:"交易时间"}}),t._v(" "),a("el-table-column",{attrs:{prop:"state",label:"审核状态"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("el-tag",{attrs:{color:t.stateColor(e.row.state)},domProps:{textContent:t._s(t.stateText(e.row.state))}})]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"操作",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("el-button",{attrs:{type:"primary",size:"small"},on:{click:function(a){t.showDetail(e.row)}}},[t._v("详情")])]}}])})],1),t._v(" "),a("div",{staticClass:"page"},[a("el-pagination",{attrs:{"current-page":t.pager.page,"page-sizes":[10,20,50,100],"page-size":t.pager.num,layout:"sizes, prev, pager, next",total:t.pager.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.pager.page=e}}})],1)],1),t._v(" "),a("div",{staticClass:"dialog"},[a("el-dialog",{attrs:{title:"提币详情",visible:t.dialog.visible},on:{"update:visible":function(e){t.dialog.visible=e}}},[a("el-card",[a("el-row",{attrs:{gutter:10}},[a("el-form",{attrs:{model:t.dialog.detail,"label-width":"120px"}},[a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"记录ID:"}},[a("el-input",{attrs:{disabled:""},model:{value:t.dialog.detail.id,callback:function(e){t.dialog.detail.id=e},expression:"dialog.detail.id"}})],1)],1),t._v(" "),a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"会员ID:"}},[a("el-input",{attrs:{disabled:""},model:{value:t.dialog.detail.uin,callback:function(e){t.dialog.detail.uin=e},expression:"dialog.detail.uin"}})],1)],1),t._v(" "),a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"币种类型:"}},[a("el-tag",{domProps:{textContent:t._s(t.coinType(t.dialog.detail.coin_id))}})],1)],1),t._v(" "),a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"审核状态:"}},[a("el-tag",{attrs:{color:t.stateColor(t.dialog.detail.state)},domProps:{textContent:t._s(t.stateText(t.dialog.detail.state))}})],1)],1),t._v(" "),a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"提币地址:"}},[a("el-input",{attrs:{disabled:""},model:{value:t.dialog.detail.coin_addr,callback:function(e){t.dialog.detail.coin_addr=e},expression:"dialog.detail.coin_addr"}})],1)],1),t._v(" "),a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"交易数量:"}},[a("el-input",{attrs:{disabled:""},model:{value:t.dialog.detail.vol,callback:function(e){t.dialog.detail.vol=e},expression:"dialog.detail.vol"}})],1)],1),t._v(" "),a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"手续费:"}},[a("el-input",{attrs:{disabled:""},model:{value:t.dialog.detail.atm_rate_vol,callback:function(e){t.dialog.detail.atm_rate_vol=e},expression:"dialog.detail.atm_rate_vol"}})],1)],1),t._v(" "),a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"实际到账数量:"}},[a("el-input",{attrs:{disabled:""},model:{value:t.dialog.detail.real_revice_vol,callback:function(e){t.dialog.detail.real_revice_vol=e},expression:"dialog.detail.real_revice_vol"}})],1)],1),t._v(" "),a("el-col",{attrs:{span:12}},[a("el-form-item",{attrs:{label:"交易时间:"}},[a("el-input",{attrs:{disabled:""},model:{value:t.dialog.detail.create_time,callback:function(e){t.dialog.detail.create_time=e},expression:"dialog.detail.create_time"}})],1)],1)],1)],1)],1),t._v(" "),3==t.dialog.detail.state?a("div",{staticClass:"dialog-footer clearfix"},[a("el-button",{attrs:{type:"danger fl"},on:{click:function(e){t.confirmAction(2)}}},[t._v("不通过")]),t._v(" "),a("el-button",{attrs:{type:"success fr"},on:{click:function(e){t.confirmAction(1)}}},[t._v("通过")])],1):t._e()],1)],1)],1)},l=[],n={render:i,staticRenderFns:l};e.a=n},tTwd:function(t,e,a){"use strict";function i(t,e){if(t){var a={536936460:"密码校验失败",536936454:"用户未激活",536936459:"邮箱不存在",536975769:"用户未登录",536936450:"该邮箱已经注册过",536870912:"参数错误",536870913:"图片验证码失效",536870914:"图片验证码校验失败",536870915:"图片验证码失效",537198599:"账户余额不足",536870923:"用户免费抽奖次数已经用完",537198594:"查不到相关信息"},i=t+"";return a[i]?a[i]:e||"系统错误，请稍后再试。"}return"系统错误，请稍后再试。"}e.a=i},xm8x:function(t,e,a){"use strict";function i(t){a("EJeR")}Object.defineProperty(e,"__esModule",{value:!0});var l=a("Iwxg"),n=a("fBaF"),o=a("VU/8"),r=i,s=o(l.a,n.a,r,"data-v-194f697a",null);e.default=s.exports}});