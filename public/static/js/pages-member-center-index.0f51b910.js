(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-member-center-index"],{"0680":function(e,t,n){"use strict";var i=n("e02c"),a=n.n(i);a.a},"315d":function(e,t,n){"use strict";n.d(t,"b",(function(){return a})),n.d(t,"c",(function(){return s})),n.d(t,"a",(function(){return i}));var i={uIcon:n("c688").default,uGrid:n("6ab1").default,uGridItem:n("de78").default,uBadge:n("2273").default},a=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("v-uni-view",{staticClass:"page-body",class:[e.app.setCStyle()]},[n("v-uni-view",{staticClass:"top_title",style:{"background-image":"url("+e.top_bg+")"}},[n("v-uni-view",{staticClass:"fs34 text-center p20"},[e._v(e._s(e.app.langReplace("个人中心")))])],1),n("v-uni-view",{staticClass:"top_box",style:{"background-image":"url("+e.top_bg+")"}},[n("v-uni-view",{staticClass:"user_info relative smll"},[n("v-uni-view",{staticClass:"headimgurl ",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("editInfo")}}},[n("v-uni-image",{attrs:{width:"120rpx",height:"120rpx",mode:"aspectFill",src:e.headimgurl?e.headimgurl:"/static/public/images/headimgurl.jpg"}})],1),e.userInfo.user_id>0?n("v-uni-view",{staticClass:"flex_bd ml20"},[n("v-uni-view",{staticClass:"smll"},[n("v-uni-text",{staticClass:"fs32 nickname"},[e._v(e._s(e.userInfo.nick_name))]),1==e.setting.user_center_show_role?n("v-uni-view",{staticClass:"role_name fs20"},[e._v(e._s(e.userInfo.role.role_name))]):e._e()],1),n("v-uni-view",{staticClass:"smll mt20 fs22"},[n("v-uni-view",{staticClass:"mr30"},[e._v("UID："+e._s(e.userInfo.user_id))]),n("v-uni-view",[e._v(e._s(e.app.langReplace("手机号码"))+"："+e._s(e.userInfo.mobile))])],1),n("v-uni-view",{staticClass:"smll mt20 fs22"},[n("v-uni-view",[e._v(e._s(e.app.langReplace("我的邀请码"))+"："+e._s(e.userInfo.token))])],1)],1):n("v-uni-view",{staticClass:"plr10"},[n("v-uni-view",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/member/passport/login")}}},[e._v(e._s(e.app.langReplace("登陆/注册")))])],1),n("v-uni-view",{staticClass:"icon_set smll text-center"},[n("v-uni-view",{staticClass:"mr30 relative",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/public/message/index")}}},[n("u-icon",{attrs:{name:"bell",color:"#FFFFFF",size:"45"}}),n("v-uni-view",{staticClass:"set_text"},[e._v(e._s(e.app.langReplace("通知")))]),e.unReadMsgNum>0?n("v-uni-view",{staticClass:"tip-msg-dian"}):e._e()],1),n("v-uni-view",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("set")}}},[n("u-icon",{attrs:{name:"setting",color:"#FFFFFF",size:"45"}}),n("v-uni-view",{staticClass:"set_text"},[e._v(e._s(e.app.langReplace("设置")))])],1)],1)],1),n("v-uni-view",{staticClass:"card_box fs40"},[n("u-grid",{attrs:{col:4,border:!1}},[n("u-grid-item",{staticClass:"p0",attrs:{"bg-color":"none"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/member/wallet/index")}}},[n("v-uni-view",{staticClass:"ff"},[e._v(e._s(e.userInfo.account.balance_money))]),n("v-uni-view",{staticClass:"fs26 color-99"},[e._v(e._s(e.app.langReplace("余额")))])],1),n("u-grid-item",{staticClass:"p0",attrs:{"bg-color":"none"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/member/fans/index")}}},[n("v-uni-view",{staticClass:"ff"},[e._v(e._s(e.teamCount.allNum>0?e.teamCount.allNum:0))]),n("v-uni-view",{staticClass:"fs26 color-99"},[e._v(e._s(e.app.langReplace("粉丝")))])],1),n("u-grid-item",{staticClass:"p0",attrs:{"bg-color":"none"}},[n("v-uni-view",{staticClass:"ff"},[e._v(e._s(e.teamConsume>0?e.teamConsume:0))]),n("v-uni-view",{staticClass:"fs26 color-99"},[e._v(e._s(e.app.langReplace("团队业绩")))])],1),e.userInfo.role_id>19?n("u-grid-item",{staticClass:"p0",attrs:{"bg-color":"none"}},[n("v-uni-view",{staticClass:"ff"},[e._v(e._s(e.setting.team_pool>0?e.setting.team_pool:0))]),n("v-uni-view",{staticClass:"fs26 color-99"},[e._v(e._s(e.app.langReplace("奖金池")))])],1):n("u-grid-item",{staticClass:"p0",attrs:{"bg-color":"none"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/member/center/collect")}}},[n("v-uni-view",{staticClass:"ff"},[e._v(e._s(e.collectNum))]),n("v-uni-view",{staticClass:"fs26 color-99"},[e._v(e._s(e.app.langReplace("收藏")))])],1)],1)],1)],1),n("v-uni-view",{staticClass:"pbox pt10"},[n("v-uni-view",{staticClass:"menu_box mt20"},[n("v-uni-view",{staticClass:"p20"},[n("v-uni-text",{staticClass:"fs28 font-w600"},[e._v(e._s(e.app.langReplace("我的订单")))]),n("v-uni-view",{staticClass:"fs24 fr color-99 font-wn",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/shop/order/list?state=0")}}},[e._v(e._s(e.app.langReplace("查看全部"))),n("u-icon",{attrs:{name:"arrow-right"}})],1)],1),n("u-grid",{staticClass:"fs28",attrs:{col:e.setting.shop_after_sale_limit>0?5:4,border:!1}},[n("u-grid-item",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/shop/order/list?state=1")}}},[n("u-badge",{attrs:{count:e.orderStats.wait_pay_num,offset:[20,20]}}),n("u-icon",{attrs:{name:e.baseUrl+e.setting.user_center_oicon_wait_pay,size:48}}),n("v-uni-view",{staticClass:"mt10 fs26 color-99 text-center "},[e._v(e._s(e.app.langReplace("待付款")))])],1),n("u-grid-item",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/shop/order/list?state=2")}}},[n("u-badge",{attrs:{count:e.orderStats.wait_shipping_num,offset:[20,20]}}),n("u-icon",{attrs:{name:e.baseUrl+e.setting.user_center_oicon_wait_shipping,size:48}}),n("v-uni-view",{staticClass:"mt10 fs26 color-99 text-center"},[e._v(e._s(e.app.langReplace("待发货")))])],1),n("u-grid-item",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/shop/order/list?state=3")}}},[n("u-badge",{attrs:{count:e.orderStats.wait_sign_num,offset:[20,20]}}),n("u-icon",{attrs:{name:e.baseUrl+e.setting.user_center_oicon_wait_sign,size:48}}),n("v-uni-view",{staticClass:"mt10 fs26 color-99 text-center"},[e._v(e._s(e.app.langReplace("待收货")))])],1),n("u-grid-item",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/shop/order/list?state=4")}}},[n("u-icon",{attrs:{name:e.baseUrl+e.setting.user_center_oicon_sign,size:48}}),n("v-uni-view",{staticClass:"mt10 fs26 color-99 text-center"},[e._v(e._s(e.app.langReplace("已完成")))])],1),n("u-grid-item",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pages/shop/aftersale/index")}}},[n("u-icon",{attrs:{name:e.baseUrl+e.setting.user_center_oicon_after_sale,size:48}}),n("v-uni-view",{staticClass:"mt10 fs26 color-99 text-center"},[e._v(e._s(e.app.langReplace("售后")))])],1)],1),n("v-uni-view",{staticClass:"p5"})],1),n("v-uni-view",{staticClass:"menu_box mt20 mb60"},[n("v-uni-view",{staticClass:"p20 b-tottom"},[n("v-uni-text",{staticClass:"fs30 font-w700"},[e._v(e._s(e.app.langReplace("我的工具")))])],1),0==e.setting.user_center_nav_tpl?n("u-grid",{staticClass:"fs28",attrs:{col:4,border:!1}},e._l(e.navMenu,(function(t,i){return n("u-grid-item",{key:i,staticClass:"mt10 mb10",on:{click:function(n){arguments[0]=n=e.$handleEvent(n),e.centerNav(t)}}},[n("u-icon",{attrs:{name:e.baseUrl+t.imgurl,size:68}}),n("v-uni-view",{staticClass:"mt10 fs24"},[e._v(e._s(t.title))])],1)})),1):e._e(),e._l(e.navMenu,(function(t,i){return 1==e.setting.user_center_nav_tpl?n("v-uni-view",{key:i},[n("v-uni-view",{staticClass:"list-cell b-b mt20",attrs:{"hover-class":"cell-hover","hover-stay-time":50},on:{click:function(n){arguments[0]=n=e.$handleEvent(n),e.centerNav(t)}}},[n("u-icon",{staticClass:"mr20",attrs:{name:e.baseUrl+t.imgurl,size:68}}),n("v-uni-view",{staticClass:"cell-tit fs28"},[e._v(e._s(t.title))]),n("v-uni-view",{staticClass:"cell-more"},[n("u-icon",{attrs:{name:"arrow-right"}})],1)],1)],1):e._e()}))],2)],1),n("tabbar",{attrs:{now_page:e.now_page,getCartNum:0}})],1)},s=[]},5233:function(e,t,n){"use strict";var i;n.d(t,"b",(function(){return a})),n.d(t,"c",(function(){return s})),n.d(t,"a",(function(){return i}));var a=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("v-uni-view",{staticClass:"u-grid",class:{"u-border-top u-border-left":e.border},style:[e.gridStyle]},[e._t("default")],2)},s=[]},"5f37":function(e,t,n){"use strict";n.r(t);var i=n("f094"),a=n.n(i);for(var s in i)"default"!==s&&function(e){n.d(t,e,(function(){return i[e]}))}(s);t["default"]=a.a},"6ab1":function(e,t,n){"use strict";n.r(t);var i=n("5233"),a=n("5f37");for(var s in a)"default"!==s&&function(e){n.d(t,e,(function(){return a[e]}))}(s);n("9b9a");var r,o=n("f0c5"),c=Object(o["a"])(a["default"],i["b"],i["c"],!1,null,"1bbe4ec0",null,!1,i["a"],r);t["default"]=c.exports},"82d9":function(e,t,n){var i=n("24fb");t=i(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-grid-item[data-v-af8b2ed2]{-webkit-box-sizing:border-box;box-sizing:border-box;background:#fff;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;position:relative;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.u-grid-item-hover[data-v-af8b2ed2]{background:#f7f7f7!important}.u-grid-marker-box[data-v-af8b2ed2]{position:absolute;display:inline-block;line-height:0}.u-grid-marker-wrap[data-v-af8b2ed2]{position:absolute}.u-grid-item-box[data-v-af8b2ed2]{padding:%?30?% 0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-flex:1;-webkit-flex:1;flex:1;width:100%;height:100%}',""]),e.exports=t},"9b9a":function(e,t,n){"use strict";var i=n("c858"),a=n.n(i);a.a},"9d06":function(e,t,n){"use strict";n.r(t);var i=n("315d"),a=n("b760");for(var s in a)"default"!==s&&function(e){n.d(t,e,(function(){return a[e]}))}(s);n("0680");var r,o=n("f0c5"),c=Object(o["a"])(a["default"],i["b"],i["c"],!1,null,"5fb6d9f6",null,!1,i["a"],r);t["default"]=c.exports},a799:function(e,t,n){"use strict";var i=n("b64f"),a=n.n(i);a.a},ad07:function(e,t,n){"use strict";n("a9e3"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i={name:"u-grid-item",props:{bgColor:{type:String,default:"#ffffff"},index:{type:[Number,String],default:""}},inject:["uGrid"],data:function(){return{hoverClass:""}},created:function(){this.hoverClass=this.uGrid.hoverClass},computed:{colNum:function(){return this.col<2?2:this.col>12?12:this.col},width:function(){return 100/Number(this.uGrid.col)+"%"},showBorder:function(){return this.uGrid.border}},methods:{click:function(){this.$emit("click",this.index),this.uGrid.click(this.index)}}};t.default=i},b64f:function(e,t,n){var i=n("82d9");"string"===typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);var a=n("4f06").default;a("1b330fbd",i,!0,{sourceMap:!1,shadowMode:!1})},b760:function(e,t,n){"use strict";n.r(t);var i=n("e11c"),a=n.n(i);for(var s in i)"default"!==s&&function(e){n.d(t,e,(function(){return i[e]}))}(s);t["default"]=a.a},baa0:function(e,t,n){var i=n("24fb");t=i(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/uni-button[data-v-5fb6d9f6]::after{border:none}.ser-son uni-button[data-v-5fb6d9f6]{position:relative;display:block;margin-left:auto;margin-right:auto;padding-left:0;padding-right:0;-webkit-box-sizing:border-box;box-sizing:border-box;text-align:center;text-decoration:none;line-height:1.35;-webkit-tap-highlight-color:transparent;overflow:hidden;color:#000;background-color:#fff;width:100%;height:100%}.u-badge[data-v-5fb6d9f6]{z-index:999}.top_title[data-v-5fb6d9f6]{position:relative;top:0;width:100%;z-index:99;background-size:100%;background-repeat:no-repeat;height:%?90?%;background-position:%?0?% %?-130?%;color:#fff}.top_box[data-v-5fb6d9f6]{height:%?360?%;background-repeat:no-repeat;background-size:100%;background-position:%?0?% %?-220?%;color:#fff}.top_box .user_info[data-v-5fb6d9f6]{height:%?200?%}.top_box .headimgurl[data-v-5fb6d9f6]{margin-left:%?20?%;width:%?120?%;height:%?120?%;border-radius:50%;overflow:hidden;border:%?3?% solid #fff;background-color:#fff}.top_box .headimgurl uni-image[data-v-5fb6d9f6]{border-radius:50%;width:100%;height:100%}.top_box .headimgEdit[data-v-5fb6d9f6]{position:absolute;top:%?100?%;left:%?70?%}.top_box .icon_set[data-v-5fb6d9f6]{position:absolute;top:%?10?%;right:%?20?%}.top_box .icon_set .set_text[data-v-5fb6d9f6]{font-size:%?22?%;color:#fff}.card_box[data-v-5fb6d9f6]{background-color:#fff;border-radius:%?20?%;margin:%?20?%;width:auto;color:#333;padding:%?30?% 0}.nickname[data-v-5fb6d9f6]{min-width:%?100?%;max-width:%?300?%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;-webkit-box-orient:vertical}.role_name[data-v-5fb6d9f6]{min-width:%?90?%;line-height:%?40?%;color:#fff;padding:%?2?% %?10?%;display:inline-block;text-align:center;background:-webkit-linear-gradient(350deg,#f8cd89 1%,#f6a957);background:linear-gradient(100deg,#f8cd89 1%,#f6a957);border-radius:%?20?%}.menu_box[data-v-5fb6d9f6]{border-radius:%?20?%;background-color:#fff}.tip-msg-dian[data-v-5fb6d9f6]{position:absolute;right:%?-5?%;top:%?0?%;width:%?15?%;height:%?15?%;background-color:#fff;border-radius:50%}',""]),e.exports=t},c858:function(e,t,n){var i=n("d6ff");"string"===typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);var a=n("4f06").default;a("cc9c64b2",i,!0,{sourceMap:!1,shadowMode:!1})},d6ff:function(e,t,n){var i=n("24fb");t=i(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-grid[data-v-1bbe4ec0]{width:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap;-webkit-box-align:center;-webkit-align-items:center;align-items:center}',""]),e.exports=t},de78:function(e,t,n){"use strict";n.r(t);var i=n("f49c"),a=n("e8dd");for(var s in a)"default"!==s&&function(e){n.d(t,e,(function(){return a[e]}))}(s);n("a799");var r,o=n("f0c5"),c=Object(o["a"])(a["default"],i["b"],i["c"],!1,null,"af8b2ed2",null,!1,i["a"],r);t["default"]=c.exports},e02c:function(e,t,n){var i=n("baa0");"string"===typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);var a=n("4f06").default;a("592ef476",i,!0,{sourceMap:!1,shadowMode:!1})},e11c:function(e,t,n){"use strict";var i=n("4ea4");Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i(n("d6ca")),s={components:{tabbar:a.default},data:function(){return{contact_imgurl:"/static/public/images/cneter_comment.png",now_page:"",baseUrl:this.config.baseUrl,setting:{},top_bg:"",userInfo:{user_id:0,role:{},account:{balance_money:"0.00",use_integral:"0"}},orderStats:{wait_pay_num:0,wait_shipping_num:0,wait_sign_num:0},collectNum:0,unReadMsgNum:0,headimgurl:"",navMenu:{},teamCount:{allNum:0,underNum:0},teamConsume:0}},onLoad:function(e){this.now_page=this.$mp.page.route},onShow:function(){this.setting=uni.getStorageSync("setting"),""!=this.setting.user_center_top_bg&&(this.top_bg=this.config.baseUrl+this.setting.user_center_top_bg);var e=this;""!=this.setting.user_center_top_nav_bgc&&setTimeout((function(){uni.setNavigationBarColor({backgroundColor:e.setting.user_center_top_nav_bgc})}),500),this.getCenterInfo(),this.setToken()},methods:{getCenterInfo:function(){var e=this;this.$u.post("member/api.center/getCenterInfo").then((function(t){if(console.log(t),t.data.userInfo?(e.userInfo=t.data.userInfo,e.orderStats=t.data.orderStats,e.collectNum=t.data.collectNum,e.teamCount=t.data.teamCount,e.teamConsume=t.data.teamConsume,e.unReadMsgNum=t.data.unReadMsgNum,""!=e.userInfo.headimgurl&&(e.headimgurl=e.config.baseUrl+e.userInfo.headimgurl)):e.app.delAuthCode(),e.userInfo["role_id"]>12&&""==e.userInfo["signature"])return uni.showModal({title:e.app.langReplace("提示"),content:"成为代理需签署合同,是否前往签署.",showCancel:!0,confirmText:e.app.langReplace("前往签署"),cancelText:e.app.langReplace("取消"),success:function(e){e.confirm?uni.redirectTo({url:"/pages/member/center/signContract"}):uni.redirectTo({url:"/pages/shop/index/index"})}}),!1})),this.$u.post("member/api.center/getCenterNavMenu").then((function(t){e.navMenu=t.data.navMenu}))},setToken:function(){var e=uni.getStorageSync("user_token");e==this.userInfo["token"]&&""!=e||uni.setStorageSync("user_token",this.userInfo["token"])},centerNav:function(e){"tel"==e.bind_type?uni.makePhoneCall({phoneNumber:e.url+"",success:function(e){console.log("调用成功!")},fail:function(e){console.log("调用失败!")}}):this.app.goPage(e.url)}}};t.default=s},e8dd:function(e,t,n){"use strict";n.r(t);var i=n("ad07"),a=n.n(i);for(var s in i)"default"!==s&&function(e){n.d(t,e,(function(){return i[e]}))}(s);t["default"]=a.a},f094:function(e,t,n){"use strict";n("a9e3"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i={name:"u-grid",props:{col:{type:[Number,String],default:3},border:{type:Boolean,default:!0},align:{type:String,default:"left"},hoverClass:{type:String,default:"u-hover-class"}},data:function(){return{index:0}},provide:function(){return{uGrid:this}},computed:{gridStyle:function(){var e={};switch(this.align){case"left":e.justifyContent="flex-start";break;case"center":e.justifyContent="center";break;case"right":e.justifyContent="flex-end";break;default:e.justifyContent="flex-start"}return e}},methods:{click:function(e){this.$emit("click",e)}}};t.default=i},f49c:function(e,t,n){"use strict";var i;n.d(t,"b",(function(){return a})),n.d(t,"c",(function(){return s})),n.d(t,"a",(function(){return i}));var a=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("v-uni-view",{staticClass:"u-grid-item",style:{background:e.bgColor,width:e.width},attrs:{"hover-class":e.hoverClass,"hover-stay-time":200},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.click.apply(void 0,arguments)}}},[n("v-uni-view",{staticClass:"u-grid-item-box",class:[e.showBorder?"u-border-right u-border-bottom":""]},[e._t("default")],2)],1)},s=[]}}]);