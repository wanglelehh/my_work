(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pagesA-fightgroup-checkOut"],{"1db8":function(t,e,i){"use strict";var s=i("ea54"),a=i.n(s);a.a},2851:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var s={name:"bonusSelect",props:{bonusSelectClass:{type:String,default:"none"},bonusList:{type:Object,default:function(){return{}}}},data:function(){return{isLoading:!1,defaultColor:this.app.getColor(this),used_bonus_id:0,bonusMoney:-1}},methods:{toggleBonusSelect:function(){this.$emit("toggleBonusSelect",this.bonusMoney,this.used_bonus_id)},selectBonus:function(t){if(this.used_bonus_id=0,"b0"==t.detail.value)return this.bonusMoney=0,!1;for(var e=0;e<this.bonusList.able.length;e++)if("b"+this.bonusList.able[e].bonus_id===t.detail.value){this.bonusMoney=this.bonusList.able[e].type_money,this.used_bonus_id=this.bonusList.able[e].bonus_id;break}},stopPrevent:function(){}}};e.default=s},"29a3":function(t,e,i){"use strict";i.r(e);var s=i("2851"),a=i.n(s);for(var n in s)"default"!==n&&function(t){i.d(e,t,(function(){return s[t]}))}(n);e["default"]=a.a},"33fb":function(t,e,i){var s=i("c2e2");"string"===typeof s&&(s=[[t.i,s,""]]),s.locals&&(t.exports=s.locals);var a=i("4f06").default;a("469bdac2",s,!0,{sourceMap:!1,shadowMode:!1})},"428e":function(t,e,i){"use strict";i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return n})),i.d(e,"a",(function(){return s}));var s={uIcon:i("c688").default},a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"u-numberbox"},[i("v-uni-view",{staticClass:"u-icon-minus",class:{"u-icon-disabled":t.disabled||t.inputVal<=t.min},style:{background:t.bgColor,height:t.inputHeight+"rpx",color:t.color},on:{touchstart:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.btnTouchStart("minus")},touchend:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.clearTimer.apply(void 0,arguments)}}},[i("u-icon",{attrs:{name:"minus",size:t.size}})],1),i("v-uni-input",{staticClass:"u-number-input",class:{"u-input-disabled":t.disabled},style:{color:t.color,fontSize:t.size+"rpx",background:t.bgColor,height:t.inputHeight+"rpx",width:t.inputWidth+"rpx"},attrs:{disabled:t.disabledInput||t.disabled,"cursor-spacing":t.getCursorSpacing,type:"number"},on:{blur:function(e){arguments[0]=e=t.$handleEvent(e),t.onBlur.apply(void 0,arguments)}},model:{value:t.inputVal,callback:function(e){t.inputVal=e},expression:"inputVal"}}),i("v-uni-view",{staticClass:"u-icon-plus",class:{"u-icon-disabled":t.disabled||t.inputVal>=t.max},style:{background:t.bgColor,height:t.inputHeight+"rpx",color:t.color},on:{touchstart:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.btnTouchStart("plus")},touchend:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.clearTimer.apply(void 0,arguments)}}},[i("u-icon",{attrs:{name:"plus",size:t.size}})],1)],1)},n=[]},"4adf":function(t,e,i){"use strict";i.r(e);var s=i("9709"),a=i.n(s);for(var n in s)"default"!==n&&function(t){i.d(e,t,(function(){return s[t]}))}(n);e["default"]=a.a},5618:function(t,e,i){"use strict";var s;i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return n})),i.d(e,"a",(function(){return s}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"popup ",class:t.bonusSelectClass,on:{touchmove:function(e){e.stopPropagation(),e.preventDefault(),arguments[0]=e=t.$handleEvent(e),t.stopPrevent.apply(void 0,arguments)},click:function(e){arguments[0]=e=t.$handleEvent(e),t.toggleBonusSelect.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"mask"}),i("v-uni-view",{staticClass:"layer attr-content",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.stopPrevent.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"fs36 font-w700"},[t._v(t._s(t.app.langReplace("选择优惠券")))]),i("v-uni-radio-group",{on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.selectBonus.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"item mt20"},[i("v-uni-label",{staticClass:"flex"},[i("v-uni-view",{staticClass:"flex_bd left fs30 "},[t._v(t._s(t.app.langReplace("不使用优惠券")))]),i("v-uni-view",{staticClass:"right "},[i("v-uni-radio",{staticStyle:{transform:"scale(0.7)"},attrs:{value:"b0",color:t.defaultColor}})],1)],1)],1),i("v-uni-scroll-view",{staticClass:"selbonus_list mt20",attrs:{"scroll-y":!0}},[i("v-uni-view",{staticClass:"line_tip "},[i("v-uni-view",{staticClass:"line"}),i("v-uni-view",{staticClass:"tip"},[i("v-uni-text",[t._v(t._s(t.app.langReplace("可使用"))+"("+t._s(t.bonusList.ableNum)+")")])],1)],1),t._l(t.bonusList.able,(function(e,s){return i("v-uni-view",{key:e.bonus_id,staticClass:"item mt20"},[i("v-uni-label",{staticClass:"w100 flex"},[i("v-uni-view",{staticClass:"flex_bd"},[i("v-uni-view",{staticClass:"color-00 fs32 font-w700"},[i("v-uni-text",{staticClass:"color-66 fs28"},[t._v("￥")]),i("v-uni-text",{staticClass:"color-66 mr20"},[t._v(t._s(e.type_money))]),t._v(t._s(e.type_name))],1),i("v-uni-view",{staticClass:"fs26 mt5 color-cc"},[t._v(t._s(t.app.langReplace("满"))+"￥"+t._s(e.min_amount)+t._s(t.app.langReplace("可用")))]),i("v-uni-view",{staticClass:"fs26 mt5 color-cc"},[t._v(t._s(e._use_start_date)+"-"+t._s(e._use_end_date))])],1),i("v-uni-view",{staticClass:"right smll mr10"},[i("v-uni-radio",{staticStyle:{transform:"scale(0.7)"},attrs:{value:"b"+e.bonus_id,color:t.defaultColor}})],1)],1)],1)})),t.bonusList.ableNum<1?i("v-uni-view",{staticClass:"h100 smll"},[i("v-uni-view",{staticClass:"w100 text-center fs28 color-66"},[t._v("---"+t._s(t.app.langReplace("您没有可使用的优惠券"))+"---")])],1):t._e(),i("v-uni-view",{staticClass:"line_tip mt20"},[i("v-uni-view",{staticClass:"line"}),i("v-uni-view",{staticClass:"tip"},[i("v-uni-text",[t._v(t._s(t.app.langReplace("不可使用"))+"("+t._s(t.bonusList.unableNum)+")")])],1)],1),t._l(t.bonusList.unable,(function(e,s){return i("v-uni-view",{key:e.bonus_id,staticClass:"item mt20"},[i("v-uni-label",{staticClass:"w100 flex color-cc"},[i("v-uni-view",{staticClass:"flex_bd"},[i("v-uni-view",{staticClass:" fs32 font-w700"},[i("v-uni-text",{staticClass:" fs28"},[t._v("￥")]),i("v-uni-text",{staticClass:" mr20"},[t._v(t._s(e.type_money))]),t._v(t._s(e.type_name))],1),i("v-uni-view",{staticClass:"fs26 mt5 "},[t._v(t._s(t.app.langReplace("满"))+"￥"+t._s(e.min_amount)+t._s(t.app.langReplace("可用")))]),i("v-uni-view",{staticClass:"fs26 mt5 "},[t._v(t._s(e._use_start_date)+"-"+t._s(e._use_end_date))])],1)],1)],1)}))],2)],1),i("v-uni-view",{staticClass:"btn-box"},[i("v-uni-button",{staticClass:"btn primarybtn",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.toggleBonusSelect.apply(void 0,arguments)}}},[t._v(t._s(t.app.langReplace("完成")))])],1)],1)],1)},n=[]},"56af":function(t,e,i){"use strict";var s=i("33fb"),a=i.n(s);a.a},"5ae8":function(t,e,i){"use strict";i("a9e3"),i("b680"),i("d3b7"),i("ac1f"),i("25f0"),i("1276"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var s={name:"u-number-box",props:{value:{type:Number,default:1},bgColor:{type:String,default:"#F2F3F5"},min:{type:Number,default:0},max:{type:Number,default:99999},step:{type:Number,default:1},disabled:{type:Boolean,default:!1},size:{type:[Number,String],default:26},color:{type:String,default:"#323233"},inputWidth:{type:[Number,String],default:80},inputHeight:{type:[Number,String],default:50},index:{type:[Number,String],default:""},disabledInput:{type:Boolean,default:!1},cursorSpacing:{type:[Number,String],default:100},longPress:{type:Boolean,default:!0},pressTime:{type:[Number,String],default:250}},watch:{value:function(t,e){Number(t)!=this.inputVal&&(this.inputVal=Number(t))},inputVal:function(t,e){var i=this;if(""!=t){var s=0,a=this.$u.test.number(t);s=a&&t>=this.min&&t<=this.max?t:e,this.handleChange(s,"change"),this.$nextTick((function(){i.inputVal=t}))}}},data:function(){return{inputVal:1,timer:null}},created:function(){this.inputVal=Number(this.value)},computed:{getCursorSpacing:function(){return Number(uni.upx2px(this.cursorSpacing))}},methods:{btnTouchStart:function(t){var e=this;this[t](),this.longPress&&(clearInterval(this.timer),this.timer=null,this.timer=setInterval((function(){e[t]()}),this.pressTime))},clearTimer:function(){var t=this;this.$nextTick((function(){clearInterval(t.timer),t.timer=null}))},minus:function(){this.computeVal("minus")},plus:function(){this.computeVal("plus")},calcPlus:function(t,e){var i,s,a;try{s=t.toString().split(".")[1].length}catch(o){s=0}try{a=e.toString().split(".")[1].length}catch(o){a=0}i=Math.pow(10,Math.max(s,a));var n=s>=a?s:a;return((t*i+e*i)/i).toFixed(n)},calcMinus:function(t,e){var i,s,a;try{s=t.toString().split(".")[1].length}catch(o){s=0}try{a=e.toString().split(".")[1].length}catch(o){a=0}i=Math.pow(10,Math.max(s,a));var n=s>=a?s:a;return((t*i-e*i)/i).toFixed(n)},computeVal:function(t){if(uni.hideKeyboard(),!this.disabled){var e=0;"minus"===t?e=this.calcMinus(this.inputVal,this.step):"plus"===t&&(e=this.calcPlus(this.inputVal,this.step)),e<this.min||e>this.max||(this.inputVal=e,this.handleChange(e,t))}},onBlur:function(t){var e=this,i=0,s=t.detail.value;/(^\d+$)/.test(s)&&0!=s[0]||(i=this.min),i=+s,i>this.max?i=this.max:i<this.min&&(i=this.min),this.$nextTick((function(){e.inputVal=i})),this.handleChange(i,"blur")},handleChange:function(t,e){this.disabled||(this.$emit("input",Number(t)),this.$emit(e,{value:Number(t),index:this.index}))}}};e.default=s},6049:function(t,e,i){var s=i("846c");"string"===typeof s&&(s=[[t.i,s,""]]),s.locals&&(t.exports=s.locals);var a=i("4f06").default;a("4e60ea8a",s,!0,{sourceMap:!1,shadowMode:!1})},"691d":function(t,e,i){"use strict";i.r(e);var s=i("a728"),a=i("4adf");for(var n in a)"default"!==n&&function(t){i.d(e,t,(function(){return a[t]}))}(n);i("1db8");var o,l=i("f0c5"),r=Object(l["a"])(a["default"],s["b"],s["c"],!1,null,"622d1220",null,!1,s["a"],o);e["default"]=r.exports},"6c35":function(t,e,i){var s=i("24fb");e=s(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/uni-page-body[data-v-622d1220]{background:#f8f8f8;padding-bottom:%?100?%}.address-section[data-v-622d1220]{padding:%?30?% 0;background:#fff;position:relative;margin:%?20?%;border-radius:10px}.address-section .empty-address[data-v-622d1220]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;-webkit-box-align:center;-webkit-align-items:center;align-items:center;font-size:%?28?%;font-weight:600;padding:%?20?% 0}.address-section .address-content[data-v-622d1220]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.address-section .icon-shouhuodizhi[data-v-622d1220]{-webkit-flex-shrink:0;flex-shrink:0;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;width:%?90?%;color:#888;font-size:%?44?%}.address-section .cen[data-v-622d1220]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-flex:1;-webkit-flex:1;flex:1;font-size:%?28?%;color:#333}.address-section .name[data-v-622d1220]{font-size:%?30?%;margin-right:%?24?%}.address-section .address[data-v-622d1220]{margin-top:%?16?%;margin-right:%?20?%;color:#999}.address-section .icon-you[data-v-622d1220]{font-size:%?32?%;color:#999;margin-right:%?30?%}.address-section .arrow-right[data-v-622d1220]{position:absolute;right:%?10?%;top:40%}.goods-section[data-v-622d1220]{margin:%?20?%;border-radius:10px;background:#fff}.goods-section .g-item[data-v-622d1220]{display:-webkit-box;display:-webkit-flex;display:flex;padding:%?20?%}.goods-section .g-item uni-image[data-v-622d1220]{-webkit-flex-shrink:0;flex-shrink:0;display:block;width:%?160?%;height:%?160?%;border-radius:%?10?%}.goods-section .g-item .right[data-v-622d1220]{-webkit-box-flex:1;-webkit-flex:1;flex:1;padding-left:%?20?%;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column}.goods-section .g-item .title[data-v-622d1220]{display:block;font-size:%?28?%;color:#333;line-height:%?40?%;height:%?80?%;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}.yt-list[data-v-622d1220]{margin:%?20?%;border-radius:10px;background:#fff}.yt-list-cell[data-v-622d1220]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding:%?30?% %?20?%;position:relative}.yt-list-cell.cell-hover[data-v-622d1220]{background:#fafafa}.yt-list-cell.b-b[data-v-622d1220]:after{left:%?30?%}.yt-list-cell .cell-icon[data-v-622d1220]{height:%?32?%;width:%?32?%;font-size:%?22?%;color:#fff;text-align:center;line-height:%?32?%;background:#f85e52;border-radius:%?4?%;margin-right:%?12?%}.yt-list-cell .cell-icon.hb[data-v-622d1220]{background:#ffaa0e}.yt-list-cell .cell-icon.lpk[data-v-622d1220]{background:#3ab54a}.yt-list-cell .cell-more[data-v-622d1220]{-webkit-align-self:center;align-self:center;font-size:%?24?%;color:#999;margin-left:%?8?%;margin-right:%?-10?%}.yt-list-cell .cell-tit[data-v-622d1220]{-webkit-box-flex:1;-webkit-flex:1;flex:1;font-size:%?28?%;color:#333;margin-right:%?10?%}.yt-list-cell .cell-tip.disabled[data-v-622d1220]{color:#999}.yt-list-cell .cell-tip.active[data-v-622d1220]{color:#3b7bf6}.yt-list-cell .cell-tip.red[data-v-622d1220]{color:#3b7bf6}.yt-list-cell.desc-cell .cell-tit[data-v-622d1220]{max-width:%?120?%}.yt-list-cell .desc[data-v-622d1220]{-webkit-box-flex:1;-webkit-flex:1;flex:1;font-size:%?28?%;color:#333}.footer[data-v-622d1220]{position:fixed;left:0;bottom:0;z-index:995;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;width:100%;height:%?98?%;-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end;font-size:%?30?%;background-color:#fff;z-index:998;color:#3b7bf6;padding:0 %?20?%}.footer .price-content[data-v-622d1220]{padding-right:%?30?%}.footer .price-tip[data-v-622d1220]{color:#fc4a5b;margin-left:%?8?%}.footer .price[data-v-622d1220]{font-size:%?40?%}.footer .price[data-v-622d1220]:before{content:"￥";font-size:%?24?%}.footer .submit[data-v-622d1220]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;width:%?200?%;height:%?70?%;color:#fff;border-radius:%?40?%;font-size:%?28?%}body.?%PAGE?%[data-v-622d1220]{background:#f8f8f8}',""]),t.exports=e},"72b3":function(t,e,i){"use strict";i.r(e);var s=i("5ae8"),a=i.n(s);for(var n in s)"default"!==n&&function(t){i.d(e,t,(function(){return s[t]}))}(n);e["default"]=a.a},"846c":function(t,e,i){var s=i("24fb");e=s(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-numberbox[data-v-5323c6bf]{display:-webkit-inline-box;display:-webkit-inline-flex;display:inline-flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.u-number-input[data-v-5323c6bf]{position:relative;text-align:center;padding:0;margin:0 %?6?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.u-icon-plus[data-v-5323c6bf],\r\n.u-icon-minus[data-v-5323c6bf]{width:%?60?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.u-icon-plus[data-v-5323c6bf]{border-radius:0 %?8?% %?8?% 0}.u-icon-minus[data-v-5323c6bf]{border-radius:%?8?% 0 0 %?8?%}.u-icon-disabled[data-v-5323c6bf]{color:#c8c9cc!important;background:#f7f8fa!important}.u-input-disabled[data-v-5323c6bf]{color:#c8c9cc!important;background-color:#f2f3f5!important}',""]),t.exports=e},"8ba8":function(t,e,i){"use strict";var s=i("6049"),a=i.n(s);a.a},9709:function(t,e,i){"use strict";var s=i("4ea4");i("b680"),i("acd8"),i("e25e"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("96cf");var a=s(i("1da1")),n=s(i("a1cb")),o={components:{bonusSelect:n.default},data:function(){return{fg_id:0,join_id:0,goods_name:{},fg_sku_name:"",goods_number:0,goods_image:"",sku_id:0,fg_sale_price:0,shipping_fee_ing:1,setting:{},baseUrl:this.config.baseUrl,isLoading:!1,desc:"",address_id:0,addressData:{},goods_total:0,shipping_fee:0,order_total:0,bonusSelectClass:"none",used_bonus_id:0,is_usd_bonus:0,showBonus:!1,bonusList:{},bonusMoney:0,bonusTip:this.app.langReplace("选择优惠券")}},onLoad:function(t){var e=this.app.langReplace("结算页");uni.setNavigationBarTitle({title:e}),this.fg_id=parseInt(t.fg_id),this.join_id=parseInt(t.join_id),this.sku_id=parseInt(t.sku_id),this.goods_number=parseInt(t.number),this.setting=uni.getStorageSync("setting"),this.getGoodsInfo(),this.app.isLogin(this),this.getAddress()},onShow:function(){},methods:{getGoodsInfo:function(){var t=this;return(0,a.default)(regeneratorRuntime.mark((function e(){return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:t.$u.post("fightgroup/api.goods/info",{fg_id:t.fg_id}).then((function(e){t.goods_name=e.data.goods.goods_name,t.goods_image=t.baseUrl+e.data.goods.goods_thumb,1==e.data.goods.is_spec?(t.fg_sale_price=e.data.goods.sub_goods[t.sku_id].fg_sale_price,t.fg_sku_name=e.data.goods.sub_goods[t.sku_id].sku_name):t.fg_sale_price=e.data.goods.fg_sale_price,t.is_usd_bonus=e.data.is_usd_bonus,t.getBonusList(),t.calcTotal()}));case 1:case"end":return e.stop()}}),e)})))()},getAddress:function(){var t=this;return(0,a.default)(regeneratorRuntime.mark((function e(){var i;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:i=uni.getStorageSync("address_id"),t.$u.post("member/api.address/getAddress",{address_id:i}).then((function(e){t.addressData=e.data,t.addressData.address_id>0?t.address_id=t.addressData.address_id:t.address_id=0,t.evalShippingFee()}));case 2:case"end":return e.stop()}}),e)})))()},evalShippingFee:function(){var t=this;return(0,a.default)(regeneratorRuntime.mark((function e(){var i;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.address_id<1)){e.next=2;break}return e.abrupt("return",!1);case 2:i={},i.fg_id=t.fg_id,i.sku_id=t.sku_id,i.number=t.goods_number,i.address_id=t.address_id,i.goods_type=2,t.shipping_fee_ing=1,t.$u.post("shop/api.flow/evalShippingFee",i).then((function(e){t.shipping_fee_ing=0,t.shipping_fee=e.data.shippingFee.shipping_fee,t.supplyerShippingFee=e.data.shippingFee.supplyerShippingFee,t.calcTotal()}));case 10:case"end":return e.stop()}}),e)})))()},numberChange:function(t){if(this.goods_number==t.value)return!1;this.goods_number=t.value,this.evalShippingFee(),this.getBonusList(),this.calcTotal()},calcTotal:function(){var t=this.fg_sale_price*this.goods_number;this.goods_total=t.toFixed(2),t=parseFloat(t)-parseFloat(this.bonusMoney)+parseFloat(this.shipping_fee),this.order_total=t.toFixed(2)},getBonusList:function(){var t=this;return(0,a.default)(regeneratorRuntime.mark((function e(){var i;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(0!=t.setting.sys_model_shop_bonus&&0!=t.is_usd_bonus){e.next=3;break}return t.showBonus=!1,e.abrupt("return",!1);case 3:t.showBonus=!0,i={},i.goods_type=2,i.fg_id=t.fg_id,i.sku_id=t.sku_id,i.number=t.goods_number,t.$u.post("shop/api.bonus/getBonusListToCheckout",i).then((function(e){t.bonusList=e.data}));case 10:case"end":return e.stop()}}),e)})))()},toggleBonusSelect:function(t,e){var i=this;this.used_bonus_id=0,"show"===this.bonusSelectClass?(0==t?(this.bonusTip="不使用优惠券",this.bonusMoney=0):t>0&&(this.bonusTip=" - "+t,this.bonusMoney=t,this.used_bonus_id=e),this.bonusSelectClass="hide",setTimeout((function(){i.bonusSelectClass="none"}),250),this.calcTotal()):"none"===this.bonusSelectClass&&(this.bonusSelectClass="show")},submit:function(){var t=this;if(1==this.isLoading)return!1;var e={};if(this.address_id<1)return uni.showToast({title:"请选择收货地址.",duration:2e3,icon:"none"}),!1;e.address_id=this.address_id,e.buy_msg=this.desc,e.used_bonus_id=this.used_bonus_id,e.fg_id=this.fg_id,e.join_id=this.join_id,e.sku_id=this.sku_id,e.number=this.goods_number,this.isLoading=!0,this.$u.post("fightgroup/api.flow/addOrder",e).then((function(t){var e=t.data.order_id;uni.redirectTo({url:"pay?order_id=".concat(e)})})).catch((function(e){t.isLoading=!1}))},stopPrevent:function(){}}};e.default=o},a1cb:function(t,e,i){"use strict";i.r(e);var s=i("5618"),a=i("29a3");for(var n in a)"default"!==n&&function(t){i.d(e,t,(function(){return a[t]}))}(n);i("56af");var o,l=i("f0c5"),r=Object(l["a"])(a["default"],s["b"],s["c"],!1,null,"a0992e7a",null,!1,s["a"],o);e["default"]=r.exports},a728:function(t,e,i){"use strict";i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return n})),i.d(e,"a",(function(){return s}));var s={uIcon:i("c688").default,uNumberBox:i("f5ff").default},a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"page-body",class:[t.app.setCStyle()]},[t.addressData.address_id>0?i("v-uni-view",[i("v-uni-navigator",{staticClass:"address-section",attrs:{url:"/pages/member/address/list?source=1&from=shop"}},[i("v-uni-view",{staticClass:"address-content"},[i("u-icon",{staticClass:"ml20 mr20 base-color",attrs:{name:"map-fill",size:"42"}}),i("v-uni-view",{staticClass:"cen"},[i("v-uni-view",{staticClass:"top"},[i("v-uni-text",{staticClass:"name"},[t._v(t._s(t.addressData.consignee))]),i("v-uni-text",{staticClass:"mobile fs30 font-w600"},[t._v(t._s(t.addressData.mobile))])],1),i("v-uni-text",{staticClass:"address fs28"},[t._v(t._s(t.addressData.merger_name)+" "+t._s(t.addressData.address))])],1),i("u-icon",{staticClass:"arrow-right",attrs:{name:"arrow-right",color:"#333333",size:"20"}})],1)],1)],1):i("v-uni-view",[i("v-uni-navigator",{staticClass:"address-section",attrs:{url:"/pages/member/address/manage?source=1&from=shop"}},[i("v-uni-view",{staticClass:"empty-address"},[i("v-uni-view",{staticClass:"smll"},[i("u-icon",{staticClass:"ml20 mr20",attrs:{name:"/static/public/images/icon_live.png",size:"42"}}),i("v-uni-text",[t._v(t._s(t.app.langReplace("选择收货地址")))])],1),i("u-icon",{staticClass:"arrow-right",attrs:{name:"arrow-right",color:"#333333",size:"20"}})],1)],1)],1),i("v-uni-view",{staticClass:"goods-section"},[i("v-uni-view",{staticClass:"g-item"},[i("v-uni-image",{attrs:{src:t.goods_image}}),i("v-uni-view",{staticClass:"right"},[i("v-uni-text",{staticClass:"title "},[t._v(t._s(t.goods_name))]),i("v-uni-view",{staticClass:"color-99 fs24 flex mt20"},[i("v-uni-view",{staticClass:"flex_bd smll"},[i("v-uni-view",[t._v(t._s(t.fg_sku_name))]),i("v-uni-view",{staticClass:"ml20 ff base-color"},[t._v("￥"),i("v-uni-text",{staticClass:"fs30"},[t._v(t._s(t.fg_sale_price))])],1)],1),i("u-number-box",{attrs:{"long-press":!1,value:t.goods_number,min:1,step:1},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.numberChange.apply(void 0,arguments)}}})],1)],1)],1)],1),i("v-uni-view",{staticClass:"yt-list"},[i("v-uni-view",{staticClass:"yt-list-cell"},[i("v-uni-text",{staticClass:"cell-tit clamp"},[t._v(t._s(t.app.langReplace("商品金额")))]),i("v-uni-text",{staticClass:"cell-tip ff base-color"},[t._v(t._s(t.goods_total))])],1),1==t.showBonus?i("v-uni-view",{staticClass:"yt-list",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.toggleBonusSelect()}}},[i("v-uni-view",{staticClass:"yt-list-cell",staticStyle:{padding:"0rpx"}},[i("v-uni-view",{staticClass:"cell-icon"},[t._v("券")]),i("v-uni-text",{staticClass:"cell-tit clamp "},[t._v(t._s(t.app.langReplace("优惠券")))]),t.bonusMoney>0?i("v-uni-view",[i("v-uni-text",{staticClass:"cell-tip ff base-color"},[t._v("- "+t._s(t.bonusMoney))])],1):i("v-uni-view",{staticClass:"cell-tip"},[i("v-uni-text",{staticClass:" color-99 fs30"},[t._v(t._s(t.bonusTip))]),i("u-icon",{staticClass:"arrow-right",attrs:{name:"arrow-right",color:"#999999",size:"22"}})],1)],1)],1):t._e(),i("v-uni-view",{staticClass:"yt-list-cell"},[i("v-uni-text",{staticClass:"cell-tit clamp"},[t._v(t._s(t.app.langReplace("运费")))]),0==t.shipping_fee_ing?i("v-uni-text",{staticClass:"cell-tip ff base-color"},[t._v(t._s(t.shipping_fee))]):i("v-uni-text",{staticClass:"cell-tip base-color"},[t._v(t._s(t.app.langReplace("运费计算中"))+"...")])],1),i("v-uni-view",{staticClass:"yt-list-cell desc-cell"},[i("v-uni-text",{staticClass:"cell-tit clamp"},[t._v(t._s(t.app.langReplace("备注")))]),i("v-uni-input",{staticClass:"desc",attrs:{type:"text",placeholder:t.app.langReplace("请输入备注内容"),"placeholder-class":"placeholder"},model:{value:t.desc,callback:function(e){t.desc=e},expression:"desc"}})],1)],1),i("v-uni-view",{staticClass:"footer"},[i("v-uni-view",{staticClass:"price-content base-color"},[i("v-uni-text",{staticClass:"fs28"},[t._v(t._s(t.app.langReplace("合计"))+":")]),i("v-uni-text",{staticClass:"price ff"},[t._v(t._s(t.order_total))])],1),0==t.join_id?i("v-uni-text",{staticClass:"submit primarybtn",attrs:{loading:t.isLoading},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.submit.apply(void 0,arguments)}}},[t._v(t._s(t.app.langReplace("发起拼单")))]):t.join_id>0?i("v-uni-text",{staticClass:"submit primarybtn",attrs:{loading:t.isLoading},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.submit.apply(void 0,arguments)}}},[t._v(t._s(t.app.langReplace("参与拼单")))]):t._e()],1),i("bonusSelect",{attrs:{bonusSelectClass:t.bonusSelectClass,bonusList:t.bonusList},on:{toggleBonusSelect:function(e){arguments[0]=e=t.$handleEvent(e),t.toggleBonusSelect.apply(void 0,arguments)}}})],1)},n=[]},c2e2:function(t,e,i){var s=i("24fb");e=s(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.selbonus_list[data-v-a0992e7a]{height:%?600?%}.selbonus_list .item[data-v-a0992e7a]{width:100%;margin-top:%?10?%}.selbonus_list .item .right[data-v-a0992e7a]{width:%?50?%}.selbonus_list .line_tip[data-v-a0992e7a]{position:relative;padding:%?20?% %?0?%}.selbonus_list .line_tip .line[data-v-a0992e7a]{border-top:%?1?% #ccc dotted}.selbonus_list .line_tip .tip[data-v-a0992e7a]{position:absolute;top:%?0?%;padding:%?0?% %?20?%;text-align:center;width:100%}.selbonus_list .line_tip .tip uni-text[data-v-a0992e7a]{background-color:#fff;color:#999;font-size:%?26?%}',""]),t.exports=e},ea54:function(t,e,i){var s=i("6c35");"string"===typeof s&&(s=[[t.i,s,""]]),s.locals&&(t.exports=s.locals);var a=i("4f06").default;a("696d0a70",s,!0,{sourceMap:!1,shadowMode:!1})},f5ff:function(t,e,i){"use strict";i.r(e);var s=i("428e"),a=i("72b3");for(var n in a)"default"!==n&&function(t){i.d(e,t,(function(){return a[t]}))}(n);i("8ba8");var o,l=i("f0c5"),r=Object(l["a"])(a["default"],s["b"],s["c"],!1,null,"5323c6bf",null,!1,s["a"],o);e["default"]=r.exports}}]);