(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-member-center-editReceivePay"],{"0497":function(e,i,t){"use strict";t.r(i);var a=t("1c4e"),n=t("27c1");for(var r in n)"default"!==r&&function(e){t.d(i,e,(function(){return n[e]}))}(r);t("c8ef");var o,s=t("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"7be551fb",null,!1,a["a"],o);i["default"]=l.exports},1544:function(e,i,t){"use strict";t.r(i);var a=t("ffce"),n=t.n(a);for(var r in a)"default"!==r&&function(e){t.d(i,e,(function(){return a[e]}))}(r);i["default"]=n.a},"1b0b":function(e,i,t){"use strict";t.d(i,"b",(function(){return n})),t.d(i,"c",(function(){return r})),t.d(i,"a",(function(){return a}));var a={uForm:t("822d").default,uImage:t("90d0").default,uLoading:t("dd04").default,uFormItem:t("e728").default,uSwitch:t("0497").default,uInput:t("aee4").default,uPicker:t("a476").default},n=function(){var e=this,i=e.$createElement,t=e._self._c||i;return t("v-uni-view",{staticClass:"page-body",class:[e.app.setCStyle(e.from)]},[t("u-form",{ref:"uForm",attrs:{model:e.model,errorType:e.errorType}},[1==e.app.isMPTextModel()?t("v-uni-view",{staticClass:"pbox text-center lh80 mt80"},[e._v("正在开发中...")]):t("v-uni-view",{staticClass:"pbox"},[1==e.setting.member_receive_wxpay?t("v-uni-view",{staticClass:"p30 bg-white mt20 relative br20"},[t("u-image",{staticClass:"pay_icon",attrs:{width:"60rpx",height:"60rpx",src:"/static/public/images/pay/weixin.png"}},[t("u-loading",{attrs:{slot:"loading"},slot:"loading"}),t("v-uni-view",{attrs:{slot:"error"},slot:"error"},[e._v("加载失败")])],1),t("v-uni-view",{staticClass:"info_box"},[t("u-form-item",{staticClass:"fs28",attrs:{label:"微信收款","label-width":"350"}},[t("u-switch",{staticStyle:{position:"absolute",top:"20%",right:"0rpx"},attrs:{"active-color":"#fe0079"},model:{value:e.model.weixin_usd,callback:function(i){e.$set(e.model,"weixin_usd",i)},expression:"model.weixin_usd"}})],1),1==e.model.weixin_usd?t("v-uni-view",[t("u-form-item",{attrs:{label:"微信昵称",prop:"weixin_name","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入微信昵称",type:"text"},model:{value:e.model.weixin_name,callback:function(i){e.$set(e.model,"weixin_name",i)},expression:"model.weixin_name"}})],1),t("u-form-item",{attrs:{label:"微信号",prop:"weixin_account","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入微信号",type:"text"},model:{value:e.model.weixin_account,callback:function(i){e.$set(e.model,"weixin_account",i)},expression:"model.weixin_account"}})],1)],1):e._e()],1)],1):e._e(),1==e.setting.member_receive_alipay?t("v-uni-view",{staticClass:"p30 bg-white mt20 relative br20"},[t("u-image",{staticClass:"pay_icon",attrs:{width:"60rpx",height:"60rpx",src:"/static/public/images/pay/alipay.png"}},[t("u-loading",{attrs:{slot:"loading"},slot:"loading"}),t("v-uni-view",{attrs:{slot:"error"},slot:"error"},[e._v("加载失败")])],1),t("v-uni-view",{staticClass:"info_box"},[t("u-form-item",{staticClass:"fs28",attrs:{label:"支付宝收款","label-width":"350"}},[t("u-switch",{staticStyle:{position:"absolute",top:"20%",right:"0rpx"},attrs:{"active-color":"#fe0079"},model:{value:e.model.alipay_usd,callback:function(i){e.$set(e.model,"alipay_usd",i)},expression:"model.alipay_usd"}})],1),1==e.model.alipay_usd?t("v-uni-view",[t("u-form-item",{attrs:{label:"帐号姓名",prop:"alipay_name","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入支付宝帐号姓名",type:"text"},model:{value:e.model.alipay_name,callback:function(i){e.$set(e.model,"alipay_name",i)},expression:"model.alipay_name"}})],1),t("u-form-item",{attrs:{label:"支付宝帐号",prop:"alipay_account","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入支付宝帐号",type:"text"},model:{value:e.model.alipay_account,callback:function(i){e.$set(e.model,"alipay_account",i)},expression:"model.alipay_account"}})],1)],1):e._e()],1)],1):e._e(),1==e.setting.member_receive_bank?t("v-uni-view",{staticClass:"p30 bg-white mt20 relative br20"},[t("u-image",{staticClass:"pay_icon",attrs:{width:"60rpx",height:"60rpx",src:"/static/public/images/pay/bank.png"}},[t("u-loading",{attrs:{slot:"loading"},slot:"loading"}),t("v-uni-view",{attrs:{slot:"error"},slot:"error"},[e._v("加载失败")])],1),t("v-uni-view",{staticClass:"info_box"},[t("u-form-item",{staticClass:"fs32",attrs:{label:"银行卡收款","label-width":"350"}},[t("u-switch",{staticStyle:{position:"absolute",top:"20%",right:"0rpx"},attrs:{"active-color":"#fe0079"},model:{value:e.model.bank_usd,callback:function(i){e.$set(e.model,"bank_usd",i)},expression:"model.bank_usd"}})],1),1==e.model.bank_usd?t("v-uni-view",[t("u-form-item",{attrs:{label:"开户银行",prop:"bank_name","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入开户银行",type:"text"},model:{value:e.model.bank_name,callback:function(i){e.$set(e.model,"bank_name",i)},expression:"model.bank_name"}})],1),t("u-form-item",{attrs:{label:"所属省市",prop:"bankRegionText","label-width":"150"}},[t("u-input",{attrs:{border:e.border,type:"select","select-open":e.showRegion,placeholder:"请选择地区"},on:{click:function(i){arguments[0]=i=e.$handleEvent(i),e.showRegion=!0}},model:{value:e.model.bankRegionText,callback:function(i){e.$set(e.model,"bankRegionText",i)},expression:"model.bankRegionText"}})],1),t("u-picker",{attrs:{mode:"region",params:e.picker_params,"area-code":e.region_code},on:{confirm:function(i){arguments[0]=i=e.$handleEvent(i),e.confirmRegion.apply(void 0,arguments)}},model:{value:e.showRegion,callback:function(i){e.showRegion=i},expression:"showRegion"}}),t("u-form-item",{attrs:{label:"开户支行",prop:"bank_subbranch","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入开户支行",type:"text"},model:{value:e.model.bank_subbranch,callback:function(i){e.$set(e.model,"bank_subbranch",i)},expression:"model.bank_subbranch"}})],1),t("u-form-item",{attrs:{label:"银行卡号",prop:"bank_card_number","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入银行卡号",type:"number"},model:{value:e.model.bank_card_number,callback:function(i){e.$set(e.model,"bank_card_number",i)},expression:"model.bank_card_number"}})],1),t("u-form-item",{attrs:{label:"持卡人名",prop:"bank_user_name","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入持卡人名",type:"text"},model:{value:e.model.bank_user_name,callback:function(i){e.$set(e.model,"bank_user_name",i)},expression:"model.bank_user_name"}})],1),t("u-form-item",{attrs:{label:"身份证",prop:"bank_idcard_munber","label-width":"150"}},[t("u-input",{attrs:{border:e.border,placeholder:"请输入身份证",type:"text"},model:{value:e.model.bank_idcard_munber,callback:function(i){e.$set(e.model,"bank_idcard_munber",i)},expression:"model.bank_idcard_munber"}})],1)],1):e._e()],1)],1):e._e(),t("v-uni-view",{staticClass:"p20 mt40"},[t("v-uni-button",{staticClass:"primarybtn",attrs:{size:"default",shape:"circle",type:"warning"},on:{click:function(i){arguments[0]=i=e.$handleEvent(i),e.submit.apply(void 0,arguments)}}},[e._v("保存")])],1)],1),t("v-uni-view",{staticClass:"h50"})],1)],1)},r=[]},"1c4e":function(e,i,t){"use strict";t.d(i,"b",(function(){return n})),t.d(i,"c",(function(){return r})),t.d(i,"a",(function(){return a}));var a={uLoading:t("dd04").default},n=function(){var e=this,i=e.$createElement,t=e._self._c||i;return t("v-uni-view",{staticClass:"u-switch",class:[1==e.value?"u-switch--on":"",e.disabled?"u-switch--disabled":""],style:[e.switchStyle],on:{click:function(i){arguments[0]=i=e.$handleEvent(i),e.onClick.apply(void 0,arguments)}}},[t("v-uni-view",{staticClass:"u-switch__node node-class"},[t("u-loading",{staticClass:"u-switch__loading",attrs:{show:e.loading,size:.6*e.size,color:e.loadingColor}})],1)],1)},r=[]},"25fc":function(e,i,t){var a=t("3d8f");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=t("4f06").default;n("6f89d5d6",a,!0,{sourceMap:!1,shadowMode:!1})},"27c1":function(e,i,t){"use strict";t.r(i);var a=t("9a66"),n=t.n(a);for(var r in a)"default"!==r&&function(e){t.d(i,e,(function(){return a[e]}))}(r);i["default"]=n.a},"3d8f":function(e,i,t){var a=t("24fb");i=a(!1),i.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-loading-circle[data-v-c76b6aea]{display:inline-block;vertical-align:middle;width:%?28?%;height:%?28?%;background:0 0;border-radius:50%;border:2px solid;border-color:#e5e5e5 #e5e5e5 #e5e5e5 #8f8d8e;-webkit-animation:u-circle-data-v-c76b6aea 1s linear infinite;animation:u-circle-data-v-c76b6aea 1s linear infinite}.u-loading-flower[data-v-c76b6aea]{width:20px;height:20px;display:inline-block;vertical-align:middle;-webkit-animation:a 1s steps(12) infinite;animation:u-flower-data-v-c76b6aea 1s steps(12) infinite;background:transparent url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCI+PHBhdGggZmlsbD0ibm9uZSIgZD0iTTAgMGgxMDB2MTAwSDB6Ii8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjRTlFOUU5IiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgLTMwKSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iIzk4OTY5NyIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgzMCAxMDUuOTggNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjOUI5OTlBIiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKDYwIDc1Ljk4IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0EzQTFBMiIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSg5MCA2NSA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNBQkE5QUEiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoMTIwIDU4LjY2IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0IyQjJCMiIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgxNTAgNTQuMDIgNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjQkFCOEI5IiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKDE4MCA1MCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNDMkMwQzEiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTE1MCA0NS45OCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNDQkNCQ0IiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTEyMCA0MS4zNCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNEMkQyRDIiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTkwIDM1IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0RBREFEQSIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgtNjAgMjQuMDIgNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjRTJFMkUyIiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKC0zMCAtNS45OCA2NSkiLz48L3N2Zz4=) no-repeat;background-size:100%}@-webkit-keyframes u-flower-data-v-c76b6aea{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes u-flower-data-v-c76b6aea{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@-webkit-keyframes u-circle-data-v-c76b6aea{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}',""]),e.exports=i},"51c8":function(e,i,t){"use strict";var a=t("bb4a"),n=t.n(a);n.a},"5e5b":function(e,i,t){"use strict";var a;t.d(i,"b",(function(){return n})),t.d(i,"c",(function(){return r})),t.d(i,"a",(function(){return a}));var n=function(){var e=this,i=e.$createElement,t=e._self._c||i;return e.show?t("v-uni-view",{staticClass:"u-loading",class:"circle"==e.mode?"u-loading-circle":"u-loading-flower",style:[e.cricleStyle]}):e._e()},r=[]},"63ce":function(e,i,t){var a=t("7d9b");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=t("4f06").default;n("4c9a8428",a,!0,{sourceMap:!1,shadowMode:!1})},"6ad0":function(e,i,t){"use strict";var a=t("25fc"),n=t.n(a);n.a},"6fa7":function(e,i,t){var a=t("24fb");i=a(!1),i.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-switch[data-v-7be551fb]{position:relative;display:inline-block;-webkit-box-sizing:initial;box-sizing:initial;width:2em;height:1em;background-color:#fff;border:1px solid rgba(0,0,0,.1);border-radius:1em;-webkit-transition:background-color .3s;transition:background-color .3s;font-size:%?50?%}.u-switch__node[data-v-7be551fb]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;position:absolute;top:0;left:0;border-radius:100%;z-index:1;width:1em;height:1em;background-color:#fff;background-color:#fff;-webkit-box-shadow:0 3px 1px 0 rgba(0,0,0,.05),0 2px 2px 0 rgba(0,0,0,.1),0 3px 3px 0 rgba(0,0,0,.05);box-shadow:0 3px 1px 0 rgba(0,0,0,.05),0 2px 2px 0 rgba(0,0,0,.1),0 3px 3px 0 rgba(0,0,0,.05);box-shadow:0 3px 1px 0 rgba(0,0,0,.05),0 2px 2px 0 rgba(0,0,0,.1),0 3px 3px 0 rgba(0,0,0,.05);-webkit-transition:-webkit-transform .3s cubic-bezier(.3,1.05,.4,1.05);transition:-webkit-transform .3s cubic-bezier(.3,1.05,.4,1.05);transition:transform .3s cubic-bezier(.3,1.05,.4,1.05);transition:transform .3s cubic-bezier(.3,1.05,.4,1.05),-webkit-transform .3s cubic-bezier(.3,1.05,.4,1.05);-webkit-transition:-webkit-transform cubic-bezier(.3,1.05,.4,1.05);transition:-webkit-transform cubic-bezier(.3,1.05,.4,1.05);transition:transform cubic-bezier(.3,1.05,.4,1.05);transition:transform cubic-bezier(.3,1.05,.4,1.05),-webkit-transform cubic-bezier(.3,1.05,.4,1.05);transition:transform .3s cubic-bezier(.3,1.05,.4,1.05)}.u-switch__loading[data-v-7be551fb]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.u-switch--on[data-v-7be551fb]{background-color:#1989fa}.u-switch--on .u-switch__node[data-v-7be551fb]{-webkit-transform:translateX(1em);transform:translateX(1em)}.u-switch--disabled[data-v-7be551fb]{opacity:.4}',""]),e.exports=i},"72c1":function(e,i,t){"use strict";t("b64b"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={data:function(){var e=this;return{setting:uni.getStorageSync("setting"),from:"",source:"",picker_params:{province:!0,city:!0,area:!1},region_code:[],upAction:this.config.upImageUrl,alipayFileList:[],weixinFileList:[],showRegion:!1,model:{weixin_usd:!1,weixin_name:"",weixin_account:"",weixin_qrcode:"",edit_weixin_qrcode:0,alipay_usd:!1,alipay_name:"",alipay_account:"",alipay_qrcode:"",edit_alipay_qrcode:0,bank_usd:!1,bank_name:"",bank_province:"",bank_city:"",bank_subbranch:"",bank_card_number:"",bank_user_name:"",bank_idcard_munber:"",bankRegionText:""},border:!1,errorType:["toast"],weixin_rules:{weixin_name:[{required:!0,message:"请输入微信昵称.",trigger:["change","blur"]}],weixin_account:[{required:!0,message:"请输入微信号.",trigger:["change","blur"]}]},alipay_rules:{alipay_name:[{required:!0,message:"请输入支付宝帐号姓名.",trigger:["change","blur"]}],alipay_account:[{required:!0,message:"请输入支付宝帐号.",trigger:["change","blur"]}]},bank_rules:{bank_name:[{required:!0,message:"请输入开户银行.",trigger:["change","blur"]}],bankRegionText:[{required:!0,message:"请输入所属省市.",trigger:["change","blur"]}],bank_subbranch:[{required:!0,message:"请输入开户支行.",trigger:["change","blur"]}],bank_card_number:[{required:!0,message:"请输入银行卡号.",trigger:["change","blur"]},{min:8,message:"银行卡号长度不能少于13位",trigger:"change"}],bank_user_name:[{required:!0,message:"请输入持卡人名",trigger:"blur"},{min:2,max:15,message:"持卡人名长度在2到15个字符",trigger:["change","blur"]},{validator:function(i,t,a){return e.$u.test.chinese(t)},message:"姓名必须为中文",trigger:["change","blur"]}],bank_idcard_munber:[{required:!0,message:"请输入身份证号码",trigger:["change","blur"]},{validator:function(i,t,a){return e.$u.test.idCard(t)},message:"身份证号码不正确",trigger:["change","blur"]}]}}},onLoad:function(e){this.app.isLogin(this),this.getUserInfo(),this.source=e.source,this.from=e.from},computed:{},onReady:function(){},methods:{getUserInfo:function(){var e=this;this.$u.api.getUserInfo().then((function(i){var t=i.data;e.model.weixin_usd=1==t.weixin_usd,e.model.weixin_name=t.weixin_name,e.model.weixin_account=t.weixin_account,e.model.weixin_qrcode=t.weixin_qrcode,""!=t.weixin_qrcode&&(e.weixinFileList=[{url:e.config.baseUrl+t.weixin_qrcode}]),e.model.alipay_usd=1==t.alipay_usd,e.model.alipay_name=t.alipay_name,e.model.alipay_account=t.alipay_account,e.model.alipay_qrcode=t.alipay_qrcode,""!=t.alipay_qrcode&&(e.alipayFileList=[{url:e.config.baseUrl+t.alipay_qrcode}]),e.model.bank_usd=1==t.bank_usd,e.model.bank_name=t.bank_name,e.model.bank_province=t.bank_province,e.model.bank_city=t.bank_city,e.model.bank_subbranch=t.bank_subbranch,e.model.bank_card_number=t.bank_card_number,e.model.bank_user_name=t.bank_user_name,e.model.bank_idcard_munber=t.bank_idcard_munber,t.bank_city>0&&(e.region_code=[t.bank_province,t.bank_city],e.model.bankRegionText=t.bank_region_text)}))},uploaded:function(e,i,t){e=JSON.parse(e.data);"weixin_qrcode"==e.type?(this.model.weixin_qrcode=e.file,this.model.edit_weixin_qrcode=1):"alipay_qrcode"==e.type&&(this.model.alipay_qrcode=e.file,this.model.edit_alipay_qrcode=1)},removeImg:function(e){"weixin_qrcode"==e?(this.model.weixin_qrcode="",this.model.edit_weixin_qrcode=1):"alipay_qrcode"==e&&(this.model.alipay_qrcode="",this.model.edit_alipay_qrcode=1)},confirmRegion:function(e){this.model.bankRegionText=e.province.label+","+e.city.label,this.model.bank_province=e.province.value,this.model.bank_city=e.city.value},submit:function(){var e=this,i={};if(1==this.model.weixin_usd&&Object.assign(i,this.weixin_rules),1==this.model.alipay_usd&&Object.assign(i,this.alipay_rules),1==this.model.bank_usd&&Object.assign(i,this.bank_rules),0==Object.keys(i).length)return this._post();this.$refs.uForm.setRules(i),this.$refs.uForm.validate((function(i){if(!i)return!1;e._post()}))},_post:function(){var e=this;this.$u.post("member/api.users/editReceivePay",this.model).then((function(i){uni.showModal({title:"提示",content:i.msg,showCancel:!1,success:function(i){i.confirm&&("channel_withdraw"==e.source?uni.redirectTo({url:"/pagesB/channel/wallet/withdraw"}):"withdraw"==e.source?uni.redirectTo({url:"/pages/member/wallet/withdraw"}):uni.redirectTo({url:"/pages/member/center/set"}))}})}))}}};i.default=a},"7d9b":function(e,i,t){var a=t("24fb");i=a(!1),i.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.pay_icon[data-v-1b1abf80]{position:absolute;top:%?50?%}.info_box[data-v-1b1abf80]{padding-left:%?80?%}.info_box .u-form-item[data-v-1b1abf80]{padding:%?10?% 0;line-height:none}.u-switch[data-v-1b1abf80]{font-size:%?40?%!important}',""]),e.exports=i},"81c9":function(e,i,t){"use strict";t.r(i);var a=t("a40e"),n=t.n(a);for(var r in a)"default"!==r&&function(e){t.d(i,e,(function(){return a[e]}))}(r);i["default"]=n.a},"90d0":function(e,i,t){"use strict";t.r(i);var a=t("e9f8"),n=t("81c9");for(var r in n)"default"!==r&&function(e){t.d(i,e,(function(){return n[e]}))}(r);t("51c8");var o,s=t("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"4d0290e0",null,!1,a["a"],o);i["default"]=l.exports},"98fa":function(e,i,t){var a=t("24fb");i=a(!1),i.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-image[data-v-4d0290e0]{background-color:#f3f4f6;position:relative;-webkit-transition:opacity .5s ease-in-out;transition:opacity .5s ease-in-out}.u-image__image[data-v-4d0290e0]{width:100%;height:100%}.u-image__loading[data-v-4d0290e0], .u-image__error[data-v-4d0290e0]{position:absolute;top:0;left:0;width:100%;height:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;background-color:#f3f4f6;color:#909399;font-size:%?46?%}',""]),e.exports=i},"9a66":function(e,i,t){"use strict";t("a9e3"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={name:"u-switch",props:{loading:{type:Boolean,default:!1},disabled:{type:Boolean,default:!1},size:{type:[Number,String],default:50},activeColor:{type:String,default:"#2979ff"},inactiveColor:{type:String,default:"#ffffff"},value:{type:Boolean,default:!1},vibrateShort:{type:Boolean,default:!1},activeValue:{type:[Number,String,Boolean],default:!0},inactiveValue:{type:[Number,String,Boolean],default:!1}},data:function(){return{}},computed:{switchStyle:function(){var e={};return e.fontSize=this.size+"rpx",e.backgroundColor=this.value?this.activeColor:this.inactiveColor,e},loadingColor:function(){return this.value?this.activeColor:null}},methods:{onClick:function(){var e=this;this.disabled||this.loading||(this.vibrateShort&&uni.vibrateShort(),this.$emit("input",!this.value),this.$nextTick((function(){e.$emit("change",e.value?e.activeValue:e.inactiveValue)})))}}};i.default=a},a40e:function(e,i,t){"use strict";t("a9e3"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={props:{src:{type:String,default:""},mode:{type:String,default:"aspectFill"},width:{type:[String,Number],default:"100%"},height:{type:[String,Number],default:"auto"},shape:{type:String,default:"square"},borderRadius:{type:[String,Number],default:0},lazyLoad:{type:Boolean,default:!0},showMenuByLongpress:{type:Boolean,default:!0},loadingIcon:{type:String,default:"photo"},errorIcon:{type:String,default:"error-circle"},showLoading:{type:Boolean,default:!0},showError:{type:Boolean,default:!0},fade:{type:Boolean,default:!0},webp:{type:Boolean,default:!1},duration:{type:[String,Number],default:500}},data:function(){return{isError:!1,loading:!0,opacity:1,durationTime:this.duration,backgroundStyle:{}}},computed:{wrapStyle:function(){var e={};return e.width=this.$u.addUnit(this.width),e.height=this.$u.addUnit(this.height),e.borderRadius="circle"==this.shape?"50%":this.$u.addUnit(this.borderRadius),e.overflow=this.borderRadius>0?"hidden":"visible",this.fade&&(e.opacity=this.opacity,e.transition="opacity ".concat(Number(this.durationTime)/1e3,"s ease-in-out")),e}},methods:{onClick:function(){this.$emit("click")},onErrorHandler:function(){this.loading=!1,this.isError=!0,this.$emit("error")},onLoadHandler:function(){var e=this;if(this.loading=!1,this.isError=!1,this.$emit("load"),!this.fade)return this.removeBgColor();this.opacity=0,this.durationTime=0,setTimeout((function(){e.durationTime=e.duration,e.opacity=1,setTimeout((function(){e.removeBgColor()}),e.durationTime)}),50)},removeBgColor:function(){this.backgroundStyle={backgroundColor:"transparent"}}}};i.default=a},bb4a:function(e,i,t){var a=t("98fa");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=t("4f06").default;n("f9c0ac3c",a,!0,{sourceMap:!1,shadowMode:!1})},c8ef:function(e,i,t){"use strict";var a=t("e86f"),n=t.n(a);n.a},ca14:function(e,i,t){"use strict";t.r(i);var a=t("1b0b"),n=t("cfd9");for(var r in n)"default"!==r&&function(e){t.d(i,e,(function(){return n[e]}))}(r);t("d4d7");var o,s=t("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"1b1abf80",null,!1,a["a"],o);i["default"]=l.exports},cfd9:function(e,i,t){"use strict";t.r(i);var a=t("72c1"),n=t.n(a);for(var r in a)"default"!==r&&function(e){t.d(i,e,(function(){return a[e]}))}(r);i["default"]=n.a},d4d7:function(e,i,t){"use strict";var a=t("63ce"),n=t.n(a);n.a},dd04:function(e,i,t){"use strict";t.r(i);var a=t("5e5b"),n=t("1544");for(var r in n)"default"!==r&&function(e){t.d(i,e,(function(){return n[e]}))}(r);t("6ad0");var o,s=t("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"c76b6aea",null,!1,a["a"],o);i["default"]=l.exports},e86f:function(e,i,t){var a=t("6fa7");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=t("4f06").default;n("be9b03cc",a,!0,{sourceMap:!1,shadowMode:!1})},e9f8:function(e,i,t){"use strict";t.d(i,"b",(function(){return n})),t.d(i,"c",(function(){return r})),t.d(i,"a",(function(){return a}));var a={uIcon:t("c688").default},n=function(){var e=this,i=e.$createElement,t=e._self._c||i;return t("v-uni-view",{staticClass:"u-image",style:[e.wrapStyle,e.backgroundStyle],on:{click:function(i){arguments[0]=i=e.$handleEvent(i),e.onClick.apply(void 0,arguments)}}},[e.isError?e._e():t("v-uni-image",{staticClass:"u-image__image",style:{borderRadius:"circle"==e.shape?"50%":e.$u.addUnit(e.borderRadius)},attrs:{src:e.src,mode:e.mode,"lazy-load":e.lazyLoad},on:{error:function(i){arguments[0]=i=e.$handleEvent(i),e.onErrorHandler.apply(void 0,arguments)},load:function(i){arguments[0]=i=e.$handleEvent(i),e.onLoadHandler.apply(void 0,arguments)}}}),e.showLoading&&e.loading?t("v-uni-view",{staticClass:"u-image__loading",style:{borderRadius:"circle"==e.shape?"50%":e.$u.addUnit(e.borderRadius)}},[e.$slots.loading?e._t("loading"):t("u-icon",{attrs:{name:e.loadingIcon}})],2):e._e(),e.showError&&e.isError&&!e.loading?t("v-uni-view",{staticClass:"u-image__error",style:{borderRadius:"circle"==e.shape?"50%":e.$u.addUnit(e.borderRadius)}},[e.$slots.error?e._t("error"):t("u-icon",{attrs:{name:e.errorIcon}})],2):e._e()],1)},r=[]},ffce:function(e,i,t){"use strict";t("a9e3"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={name:"u-loading",props:{mode:{type:String,default:"circle"},color:{type:String,default:"#c7c7c7"},size:{type:[String,Number],default:"34"},show:{type:Boolean,default:!0}},computed:{cricleStyle:function(){var e={};return e.width=this.size+"rpx",e.height=this.size+"rpx","circle"==this.mode&&(e.borderColor="#e4e4e4 #e4e4e4 #e4e4e4 ".concat(this.color?this.color:"#c7c7c7")),e}}};i.default=a}}]);