(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pagesB-channel-center-joinCondition"],{"0a7c":function(e,t,i){"use strict";var o=i("3fb3"),n=i.n(o);n.a},1544:function(e,t,i){"use strict";i.r(t);var o=i("ffce"),n=i.n(o);for(var r in o)"default"!==r&&function(e){i.d(t,e,(function(){return o[e]}))}(r);t["default"]=n.a},1616:function(e,t,i){"use strict";var o;i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return r})),i.d(t,"a",(function(){return o}));var n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-button",{staticClass:"u-btn u-line-1 u-fix-ios-appearance",class:["u-size-"+e.size,e.plain?"u-btn--"+e.type+"--plain":"",e.loading?"u-loading":"","circle"==e.shape?"u-round-circle":"",e.hairLine?e.showHairLineBorder:"u-btn--bold-border","u-btn--"+e.type,e.disabled?"u-btn--"+e.type+"--disabled":""],style:[e.customStyle],attrs:{id:"u-wave-btn",disabled:e.disabled,"form-type":e.formType,"open-type":e.openType,"app-parameter":e.appParameter,"hover-stop-propagation":e.hoverStopPropagation,"send-message-title":e.sendMessageTitle,"send-message-path":"sendMessagePath",lang:e.lang,"data-name":e.dataName,"session-from":e.sessionFrom,"send-message-img":e.sendMessageImg,"show-message-card":e.showMessageCard,"hover-class":e.getHoverClass,loading:e.loading},on:{getphonenumber:function(t){arguments[0]=t=e.$handleEvent(t),e.getphonenumber.apply(void 0,arguments)},getuserinfo:function(t){arguments[0]=t=e.$handleEvent(t),e.getuserinfo.apply(void 0,arguments)},error:function(t){arguments[0]=t=e.$handleEvent(t),e.error.apply(void 0,arguments)},opensetting:function(t){arguments[0]=t=e.$handleEvent(t),e.opensetting.apply(void 0,arguments)},launchapp:function(t){arguments[0]=t=e.$handleEvent(t),e.launchapp.apply(void 0,arguments)},click:function(t){t.stopPropagation(),arguments[0]=t=e.$handleEvent(t),e.click(t)}}},[e._t("default"),e.ripple?i("v-uni-view",{staticClass:"u-wave-ripple",class:[e.waveActive?"u-wave-active":""],style:{top:e.rippleTop+"px",left:e.rippleLeft+"px",width:e.fields.targetWidth+"px",height:e.fields.targetWidth+"px","background-color":e.rippleBgColor||"rgba(0, 0, 0, 0.15)"}}):e._e()],2)},r=[]},"219a":function(e,t,i){"use strict";i("c975"),i("d3b7"),i("ac1f"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var o={name:"u-button",props:{hairLine:{type:Boolean,default:!0},type:{type:String,default:"default"},size:{type:String,default:"default"},shape:{type:String,default:"square"},plain:{type:Boolean,default:!1},disabled:{type:Boolean,default:!1},loading:{type:Boolean,default:!1},openType:{type:String,default:""},formType:{type:String,default:""},appParameter:{type:String,default:""},hoverStopPropagation:{type:Boolean,default:!1},lang:{type:String,default:"en"},sessionFrom:{type:String,default:""},sendMessageTitle:{type:String,default:""},sendMessagePath:{type:String,default:""},sendMessageImg:{type:String,default:""},showMessageCard:{type:Boolean,default:!1},hoverBgColor:{type:String,default:""},rippleBgColor:{type:String,default:""},ripple:{type:Boolean,default:!1},hoverClass:{type:String,default:""},customStyle:{type:Object,default:function(){return{}}},dataName:{type:String,default:""}},computed:{getHoverClass:function(){if(this.loading||this.disabled||this.ripple||this.hoverClass)return"";var e="";return e=this.plain?"u-"+this.type+"-plain-hover":"u-"+this.type+"-hover",e},showHairLineBorder:function(){return["primary","success","error","warning"].indexOf(this.type)>=0&&!this.plain?"":"u-hairline-border"}},data:function(){return{rippleTop:0,rippleLeft:0,fields:{},waveActive:!1}},methods:{click:function(e){!0!==this.loading&&!0!==this.disabled&&(this.ripple&&(this.waveActive=!1,this.$nextTick((function(){this.getWaveQuery(e)}))),this.$emit("click"))},getWaveQuery:function(e){var t=this;this.getElQuery().then((function(i){var o=i[0];if(o.width&&o.width&&(o.targetWidth=o.height>o.width?o.height:o.width,o.targetWidth)){t.fields=o;var n="",r="";n=e.touches[0].clientX,r=e.touches[0].clientY,t.rippleTop=r-o.top-o.targetWidth/2,t.rippleLeft=n-o.left-o.targetWidth/2,t.$nextTick((function(){t.waveActive=!0}))}}))},getElQuery:function(){var e=this;return new Promise((function(t){var i="";i=uni.createSelectorQuery().in(e),i.select(".u-btn").boundingClientRect(),i.exec((function(e){t(e)}))}))},getphonenumber:function(e){this.$emit("getphonenumber",e)},getuserinfo:function(e){this.$emit("getuserinfo",e)},error:function(e){this.$emit("error",e)},opensetting:function(e){this.$emit("opensetting",e)},launchapp:function(e){this.$emit("launchapp",e)}}};t.default=o},"25fc":function(e,t,i){var o=i("3d8f");"string"===typeof o&&(o=[[e.i,o,""]]),o.locals&&(e.exports=o.locals);var n=i("4f06").default;n("6f89d5d6",o,!0,{sourceMap:!1,shadowMode:!1})},"3c62":function(e,t,i){"use strict";var o=i("b6bd"),n=i.n(o);n.a},"3d8f":function(e,t,i){var o=i("24fb");t=o(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-loading-circle[data-v-c76b6aea]{display:inline-block;vertical-align:middle;width:%?28?%;height:%?28?%;background:0 0;border-radius:50%;border:2px solid;border-color:#e5e5e5 #e5e5e5 #e5e5e5 #8f8d8e;-webkit-animation:u-circle-data-v-c76b6aea 1s linear infinite;animation:u-circle-data-v-c76b6aea 1s linear infinite}.u-loading-flower[data-v-c76b6aea]{width:20px;height:20px;display:inline-block;vertical-align:middle;-webkit-animation:a 1s steps(12) infinite;animation:u-flower-data-v-c76b6aea 1s steps(12) infinite;background:transparent url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCI+PHBhdGggZmlsbD0ibm9uZSIgZD0iTTAgMGgxMDB2MTAwSDB6Ii8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjRTlFOUU5IiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgLTMwKSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iIzk4OTY5NyIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgzMCAxMDUuOTggNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjOUI5OTlBIiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKDYwIDc1Ljk4IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0EzQTFBMiIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSg5MCA2NSA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNBQkE5QUEiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoMTIwIDU4LjY2IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0IyQjJCMiIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgxNTAgNTQuMDIgNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjQkFCOEI5IiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKDE4MCA1MCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNDMkMwQzEiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTE1MCA0NS45OCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNDQkNCQ0IiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTEyMCA0MS4zNCA2NSkiLz48cmVjdCB3aWR0aD0iNyIgaGVpZ2h0PSIyMCIgeD0iNDYuNSIgeT0iNDAiIGZpbGw9IiNEMkQyRDIiIHJ4PSI1IiByeT0iNSIgdHJhbnNmb3JtPSJyb3RhdGUoLTkwIDM1IDY1KSIvPjxyZWN0IHdpZHRoPSI3IiBoZWlnaHQ9IjIwIiB4PSI0Ni41IiB5PSI0MCIgZmlsbD0iI0RBREFEQSIgcng9IjUiIHJ5PSI1IiB0cmFuc2Zvcm09InJvdGF0ZSgtNjAgMjQuMDIgNjUpIi8+PHJlY3Qgd2lkdGg9IjciIGhlaWdodD0iMjAiIHg9IjQ2LjUiIHk9IjQwIiBmaWxsPSIjRTJFMkUyIiByeD0iNSIgcnk9IjUiIHRyYW5zZm9ybT0icm90YXRlKC0zMCAtNS45OCA2NSkiLz48L3N2Zz4=) no-repeat;background-size:100%}@-webkit-keyframes u-flower-data-v-c76b6aea{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes u-flower-data-v-c76b6aea{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@-webkit-keyframes u-circle-data-v-c76b6aea{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}',""]),e.exports=t},"3fb3":function(e,t,i){var o=i("cd41");"string"===typeof o&&(o=[[e.i,o,""]]),o.locals&&(e.exports=o.locals);var n=i("4f06").default;n("730b9ebf",o,!0,{sourceMap:!1,shadowMode:!1})},"51c8":function(e,t,i){"use strict";var o=i("bb4a"),n=i.n(o);n.a},5388:function(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var o={data:function(){return{supply_headimgurl:"",proxyInfo:{byUser:{},proxyLevel:{}}}},onLoad:function(){},onShow:function(){var e=this;this.$u.api.getProxyInfo().then((function(t){1==t.data.proxyInfo.join_condition&&uni.switchTab({url:"/pagesB/channel/center/index"}),e.proxyInfo=t.data.proxyInfo,e.supply_headimgurl=e.config.baseUrl+e.proxyInfo.supply_headimgurl}))},computed:{},onReady:function(){},methods:{toLogout:function(){var e=this;uni.showModal({content:"确定要退出登录么",success:function(t){t.confirm&&(e.app.delAuthCode(),uni.navigateTo({url:"/pagesB/passport/login"}))}})}}};t.default=o},"5e5b":function(e,t,i){"use strict";var o;i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return r})),i.d(t,"a",(function(){return o}));var n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return e.show?i("v-uni-view",{staticClass:"u-loading",class:"circle"==e.mode?"u-loading-circle":"u-loading-flower",style:[e.cricleStyle]}):e._e()},r=[]},"68b2":function(e,t,i){"use strict";i.r(t);var o=i("219a"),n=i.n(o);for(var r in o)"default"!==r&&function(e){i.d(t,e,(function(){return o[e]}))}(r);t["default"]=n.a},"6ad0":function(e,t,i){"use strict";var o=i("25fc"),n=i.n(o);n.a},"81c9":function(e,t,i){"use strict";i.r(t);var o=i("a40e"),n=i.n(o);for(var r in o)"default"!==r&&function(e){i.d(t,e,(function(){return o[e]}))}(r);t["default"]=n.a},"90d0":function(e,t,i){"use strict";i.r(t);var o=i("e9f8"),n=i("81c9");for(var r in n)"default"!==r&&function(e){i.d(t,e,(function(){return n[e]}))}(r);i("51c8");var a,s=i("f0c5"),l=Object(s["a"])(n["default"],o["b"],o["c"],!1,null,"4d0290e0",null,!1,o["a"],a);t["default"]=l.exports},"98fa":function(e,t,i){var o=i("24fb");t=o(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-image[data-v-4d0290e0]{background-color:#f3f4f6;position:relative;-webkit-transition:opacity .5s ease-in-out;transition:opacity .5s ease-in-out}.u-image__image[data-v-4d0290e0]{width:100%;height:100%}.u-image__loading[data-v-4d0290e0], .u-image__error[data-v-4d0290e0]{position:absolute;top:0;left:0;width:100%;height:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;background-color:#f3f4f6;color:#909399;font-size:%?46?%}',""]),e.exports=t},"9bee":function(e,t,i){"use strict";i.r(t);var o=i("1616"),n=i("68b2");for(var r in n)"default"!==r&&function(e){i.d(t,e,(function(){return n[e]}))}(r);i("3c62");var a,s=i("f0c5"),l=Object(s["a"])(n["default"],o["b"],o["c"],!1,null,"41917922",null,!1,o["a"],a);t["default"]=l.exports},a40e:function(e,t,i){"use strict";i("a9e3"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var o={props:{src:{type:String,default:""},mode:{type:String,default:"aspectFill"},width:{type:[String,Number],default:"100%"},height:{type:[String,Number],default:"auto"},shape:{type:String,default:"square"},borderRadius:{type:[String,Number],default:0},lazyLoad:{type:Boolean,default:!0},showMenuByLongpress:{type:Boolean,default:!0},loadingIcon:{type:String,default:"photo"},errorIcon:{type:String,default:"error-circle"},showLoading:{type:Boolean,default:!0},showError:{type:Boolean,default:!0},fade:{type:Boolean,default:!0},webp:{type:Boolean,default:!1},duration:{type:[String,Number],default:500}},data:function(){return{isError:!1,loading:!0,opacity:1,durationTime:this.duration,backgroundStyle:{}}},computed:{wrapStyle:function(){var e={};return e.width=this.$u.addUnit(this.width),e.height=this.$u.addUnit(this.height),e.borderRadius="circle"==this.shape?"50%":this.$u.addUnit(this.borderRadius),e.overflow=this.borderRadius>0?"hidden":"visible",this.fade&&(e.opacity=this.opacity,e.transition="opacity ".concat(Number(this.durationTime)/1e3,"s ease-in-out")),e}},methods:{onClick:function(){this.$emit("click")},onErrorHandler:function(){this.loading=!1,this.isError=!0,this.$emit("error")},onLoadHandler:function(){var e=this;if(this.loading=!1,this.isError=!1,this.$emit("load"),!this.fade)return this.removeBgColor();this.opacity=0,this.durationTime=0,setTimeout((function(){e.durationTime=e.duration,e.opacity=1,setTimeout((function(){e.removeBgColor()}),e.durationTime)}),50)},removeBgColor:function(){this.backgroundStyle={backgroundColor:"transparent"}}}};t.default=o},b39e:function(e,t,i){var o=i("24fb");t=o(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-btn[data-v-41917922]::after{border:none}.u-btn[data-v-41917922]{position:relative;border:0;display:inline-block;overflow:hidden;line-height:1;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;cursor:pointer;padding:0 %?40?%;z-index:1;-webkit-box-sizing:border-box;box-sizing:border-box;-webkit-transition:all .15s;transition:all .15s}.u-btn--bold-border[data-v-41917922]{border:1px solid #fff}.u-btn--default[data-v-41917922]{color:#606266;border-color:#c0c4cc;background-color:#fff}.u-btn--primary[data-v-41917922]{color:#fff;border-color:#2979ff;background-color:#2979ff}.u-btn--success[data-v-41917922]{color:#fff;border-color:#19be6b;background-color:#19be6b}.u-btn--error[data-v-41917922]{color:#fff;border-color:#fa3534;background-color:#fa3534}.u-btn--warning[data-v-41917922]{color:#fff;border-color:#f90;background-color:#f90}.u-btn--default--disabled[data-v-41917922]{color:#fff;border-color:#e4e7ed;background-color:#fff}.u-btn--primary--disabled[data-v-41917922]{color:#fff!important;border-color:#a0cfff!important;background-color:#a0cfff!important}.u-btn--success--disabled[data-v-41917922]{color:#fff!important;border-color:#71d5a1!important;background-color:#71d5a1!important}.u-btn--error--disabled[data-v-41917922]{color:#fff!important;border-color:#fab6b6!important;background-color:#fab6b6!important}.u-btn--warning--disabled[data-v-41917922]{color:#fff!important;border-color:#fcbd71!important;background-color:#fcbd71!important}.u-btn--primary--plain[data-v-41917922]{color:#2979ff!important;border-color:#a0cfff!important;background-color:#ecf5ff!important}.u-btn--success--plain[data-v-41917922]{color:#19be6b!important;border-color:#71d5a1!important;background-color:#dbf1e1!important}.u-btn--error--plain[data-v-41917922]{color:#fa3534!important;border-color:#fab6b6!important;background-color:#fef0f0!important}.u-btn--warning--plain[data-v-41917922]{color:#f90!important;border-color:#fcbd71!important;background-color:#fdf6ec!important}.u-hairline-border[data-v-41917922]:after{content:" ";position:absolute;pointer-events:none;-webkit-box-sizing:border-box;box-sizing:border-box;-webkit-transform-origin:0 0;transform-origin:0 0;left:0;top:0;width:199.8%;height:199.7%;-webkit-transform:scale(.5);transform:scale(.5);border:1px solid currentColor;z-index:1}.u-wave-ripple[data-v-41917922]{z-index:0;position:absolute;border-radius:100%;background-clip:padding-box;pointer-events:none;-webkit-user-select:none;user-select:none;-webkit-transform:scale(0);transform:scale(0);opacity:1;-webkit-transform-origin:center;transform-origin:center}.u-wave-ripple.u-wave-active[data-v-41917922]{opacity:0;-webkit-transform:scale(2);transform:scale(2);-webkit-transition:opacity 1s linear,-webkit-transform .4s linear;transition:opacity 1s linear,-webkit-transform .4s linear;transition:opacity 1s linear,transform .4s linear;transition:opacity 1s linear,transform .4s linear,-webkit-transform .4s linear}.u-round-circle[data-v-41917922]{border-radius:%?100?%}.u-round-circle[data-v-41917922]::after{border-radius:%?100?%}.u-loading[data-v-41917922]::after{background-color:hsla(0,0%,100%,.35)}.u-size-default[data-v-41917922]{font-size:%?30?%;height:%?80?%;line-height:%?80?%}.u-size-medium[data-v-41917922]{display:-webkit-inline-box;display:-webkit-inline-flex;display:inline-flex;width:auto;font-size:%?26?%;height:%?70?%;line-height:%?70?%;padding:0 %?80?%}.u-size-mini[data-v-41917922]{display:-webkit-inline-box;display:-webkit-inline-flex;display:inline-flex;width:auto;font-size:%?22?%;padding-top:1px;height:%?50?%;line-height:%?50?%;padding:0 %?20?%}.u-primary-plain-hover[data-v-41917922]{color:#fff!important;background:#2b85e4!important}.u-default-plain-hover[data-v-41917922]{color:#2b85e4!important;background:#ecf5ff!important}.u-success-plain-hover[data-v-41917922]{color:#fff!important;background:#18b566!important}.u-warning-plain-hover[data-v-41917922]{color:#fff!important;background:#f29100!important}.u-error-plain-hover[data-v-41917922]{color:#fff!important;background:#dd6161!important}.u-info-plain-hover[data-v-41917922]{color:#fff!important;background:#82848a!important}.u-default-hover[data-v-41917922]{color:#2b85e4!important;border-color:#2b85e4!important;background-color:#ecf5ff!important}.u-primary-hover[data-v-41917922]{background:#2b85e4!important;color:#fff}.u-success-hover[data-v-41917922]{background:#18b566!important;color:#fff}.u-info-hover[data-v-41917922]{background:#82848a!important;color:#fff}.u-warning-hover[data-v-41917922]{background:#f29100!important;color:#fff}.u-error-hover[data-v-41917922]{background:#dd6161!important;color:#fff}',""]),e.exports=t},b6bd:function(e,t,i){var o=i("b39e");"string"===typeof o&&(o=[[e.i,o,""]]),o.locals&&(e.exports=o.locals);var n=i("4f06").default;n("7f7daf40",o,!0,{sourceMap:!1,shadowMode:!1})},bb4a:function(e,t,i){var o=i("98fa");"string"===typeof o&&(o=[[e.i,o,""]]),o.locals&&(e.exports=o.locals);var n=i("4f06").default;n("f9c0ac3c",o,!0,{sourceMap:!1,shadowMode:!1})},cd41:function(e,t,i){var o=i("24fb");t=o(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.superior-box[data-v-262265e3]{position:relative;display:block;overflow:hidden}.superior-box .headimgurl[data-v-262265e3]{width:%?100?%!important;height:%?100?%!important;border:%?5?% solid #fff}.superior-box .persql[data-v-262265e3]{font-size:%?23?%;background:#5392f3;color:#fff;position:absolute;z-index:9;padding:%?0?% %?50?%;top:%?28?%;right:%?-63?%;-webkit-transform:rotate(50deg);transform:rotate(50deg)}.join-box[data-v-262265e3]{border-bottom:1px solid #f1f1f1;line-height:%?60?%}',""]),e.exports=t},d34f:function(e,t,i){"use strict";i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return r})),i.d(t,"a",(function(){return o}));var o={uImage:i("90d0").default,uLoading:i("dd04").default,uButton:i("9bee").default},n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{staticClass:"page-body p20"},[i("v-uni-view",{staticClass:"bg-white mt20 p20 superior-box"},[i("u-image",{staticClass:"fl headimgurl",attrs:{src:e.supply_headimgurl,shape:"circle"}},[i("u-loading",{attrs:{slot:"loading"},slot:"loading"}),i("v-uni-view",{staticStyle:{"font-size":"24rpx"},attrs:{slot:"error"},slot:"error"},[e._v("加载失败")])],1),i("v-uni-view",{staticClass:"fl ml20"},[i("v-uni-text",{staticClass:"block"},[e._v(e._s(e.proxyInfo.supply_name))]),i("v-uni-text",{staticClass:"block mt10"},[e._v("电话："+e._s(e.proxyInfo.supply_mobile))])],1),i("v-uni-view",{staticClass:"persql"},[e._v("上级信息")])],1),i("v-uni-view",{staticClass:"bg-white mt20 p20"},[i("v-uni-view",[e._v("你的层级："+e._s(e.proxyInfo.proxyLevel.proxy_name))]),i("v-uni-view",{staticClass:"join-box mt20"},[e._v("你需要完成以下任务")]),1==e.proxyInfo.proxyLevel.join_uplevel_goods_money_limit?i("v-uni-view",{staticClass:"join-box mt50"},[e._v("充值升级货款：￥"+e._s(e.proxyInfo.proxyLevel.uplevel_goods_money)),0==e.proxyInfo.join_uplevel_goods_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"primary",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/wallet/recharge?type=uplevelGoodsMoney&money="+e.proxyInfo.proxyLevel.uplevel_goods_money)}}},[e._v("前往充值")]):2==e.proxyInfo.join_uplevel_goods_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"warning",shape:"circle"}},[e._v("待审核")]):3==e.proxyInfo.join_uplevel_goods_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"warning",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/wallet/recharge?type=uplevelGoodsMoney&money="+e.proxyInfo.proxyLevel.uplevel_goods_money)}}},[e._v("审核失败，重新提交")]):i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"success",shape:"circle"}},[e._v("已完成")])],1):e._e(),1==e.proxyInfo.proxyLevel.join_earnest_money_limit?i("v-uni-view",{staticClass:"join-box mt50"},[e._v("充值保证金：￥"+e._s(e.proxyInfo.proxyLevel.join_earnest_money)),0==e.proxyInfo.join_earnest_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"primary",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/wallet/recharge?type=earnestMoney&money="+e.proxyInfo.proxyLevel.join_earnest_money)}}},[e._v("前往充值")]):2==e.proxyInfo.join_earnest_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"warning",shape:"circle"}},[e._v("待审核")]):3==e.proxyInfo.join_earnest_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"warning",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/wallet/recharge?type=earnestMoney&money="+e.proxyInfo.proxyLevel.join_earnest_money)}}},[e._v("审核失败，重新提交")]):i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"success",shape:"circle"}},[e._v("已完成")])],1):e._e(),1==e.proxyInfo.proxyLevel.join_goods_money_limit?i("v-uni-view",{staticClass:"join-box mt50"},[e._v("充值货款：￥"+e._s(e.proxyInfo.proxyLevel.join_goods_money)),0==e.proxyInfo.join_goods_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"primary",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/wallet/recharge?type=goodsMoney&money="+e.proxyInfo.proxyLevel.join_goods_money)}}},[e._v("前往充值")]):2==e.proxyInfo.join_goods_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"warning",shape:"circle"}},[e._v("待审核")]):3==e.proxyInfo.join_goods_money_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"warning",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/wallet/recharge?type=earnestMoney&money="+e.proxyInfo.proxyLevel.join_goods_money)}}},[e._v("审核失败，重新提交")]):i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"success",shape:"circle"}},[e._v("已完成")])],1):e._e(),1==e.proxyInfo.proxyLevel.order_first_spot_limit?i("v-uni-view",{staticClass:"join-box mt50"},[e._v("完成首笔现货订单"),e.proxyInfo.proxyLevel.order_first_spot_money>0?i("v-uni-text",[e._v("：￥"+e._s(e.proxyInfo.proxyLevel.order_first_spot_money))]):e._e(),0==e.proxyInfo.order_first_spot_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"primary",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/product/list?purchaseType=2")}}},[e._v("前往下单")]):2==e.proxyInfo.order_first_spot_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"warning",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/order/list?purchaseType=1")}}},[e._v("下单待完成")]):i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"success",shape:"circle"}},[e._v("已完成")])],1):e._e(),1==e.proxyInfo.proxyLevel.order_first_cloud_limit?i("v-uni-view",{staticClass:"join-box mt50"},[e._v("完成首笔云仓订单"),e.proxyInfo.proxyLevel.order_first_cloud_money>0?i("v-uni-text",[e._v("：￥"+e._s(e.proxyInfo.proxyLevel.order_first_cloud_money))]):e._e(),0==e.proxyInfo.order_first_cloud_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"primary",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/product/list?purchaseType=1")}}},[e._v("前往下单")]):2==e.proxyInfo.order_first_cloud_ok?i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"warning",shape:"circle"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.app.goPage("/pagesB/channel/order/list?purchaseType=1")}}},[e._v("下单待完成")]):i("u-button",{staticClass:"fr",attrs:{size:"mini",type:"success",shape:"circle"}},[e._v("已完成")])],1):e._e(),i("v-uni-view",{staticStyle:{padding:"30rpx"}},[i("u-button",{staticClass:"mt40",attrs:{size:"default",shape:"circle",type:"primary"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.toLogout.apply(void 0,arguments)}}},[e._v("退出登录")])],1)],1)],1)},r=[]},dd04:function(e,t,i){"use strict";i.r(t);var o=i("5e5b"),n=i("1544");for(var r in n)"default"!==r&&function(e){i.d(t,e,(function(){return n[e]}))}(r);i("6ad0");var a,s=i("f0c5"),l=Object(s["a"])(n["default"],o["b"],o["c"],!1,null,"c76b6aea",null,!1,o["a"],a);t["default"]=l.exports},e1b6:function(e,t,i){"use strict";i.r(t);var o=i("d34f"),n=i("f4d0");for(var r in n)"default"!==r&&function(e){i.d(t,e,(function(){return n[e]}))}(r);i("0a7c");var a,s=i("f0c5"),l=Object(s["a"])(n["default"],o["b"],o["c"],!1,null,"262265e3",null,!1,o["a"],a);t["default"]=l.exports},e9f8:function(e,t,i){"use strict";i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return r})),i.d(t,"a",(function(){return o}));var o={uIcon:i("c688").default},n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{staticClass:"u-image",style:[e.wrapStyle,e.backgroundStyle],on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.onClick.apply(void 0,arguments)}}},[e.isError?e._e():i("v-uni-image",{staticClass:"u-image__image",style:{borderRadius:"circle"==e.shape?"50%":e.$u.addUnit(e.borderRadius)},attrs:{src:e.src,mode:e.mode,"lazy-load":e.lazyLoad},on:{error:function(t){arguments[0]=t=e.$handleEvent(t),e.onErrorHandler.apply(void 0,arguments)},load:function(t){arguments[0]=t=e.$handleEvent(t),e.onLoadHandler.apply(void 0,arguments)}}}),e.showLoading&&e.loading?i("v-uni-view",{staticClass:"u-image__loading",style:{borderRadius:"circle"==e.shape?"50%":e.$u.addUnit(e.borderRadius)}},[e.$slots.loading?e._t("loading"):i("u-icon",{attrs:{name:e.loadingIcon}})],2):e._e(),e.showError&&e.isError&&!e.loading?i("v-uni-view",{staticClass:"u-image__error",style:{borderRadius:"circle"==e.shape?"50%":e.$u.addUnit(e.borderRadius)}},[e.$slots.error?e._t("error"):i("u-icon",{attrs:{name:e.errorIcon}})],2):e._e()],1)},r=[]},f4d0:function(e,t,i){"use strict";i.r(t);var o=i("5388"),n=i.n(o);for(var r in o)"default"!==r&&function(e){i.d(t,e,(function(){return o[e]}))}(r);t["default"]=n.a},ffce:function(e,t,i){"use strict";i("a9e3"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var o={name:"u-loading",props:{mode:{type:String,default:"circle"},color:{type:String,default:"#c7c7c7"},size:{type:[String,Number],default:"34"},show:{type:Boolean,default:!0}},computed:{cricleStyle:function(){var e={};return e.width=this.size+"rpx",e.height=this.size+"rpx","circle"==this.mode&&(e.borderColor="#e4e4e4 #e4e4e4 #e4e4e4 ".concat(this.color?this.color:"#c7c7c7")),e}}};t.default=o}}]);