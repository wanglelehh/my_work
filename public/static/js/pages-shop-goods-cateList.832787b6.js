(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-shop-goods-cateList"],{"012c":function(t,e,i){"use strict";i("a9e3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"u-search",props:{shape:{type:String,default:"round"},bgColor:{type:String,default:"#f2f2f2"},placeholder:{type:String,default:"请输入关键字"},clearabled:{type:Boolean,default:!0},focus:{type:Boolean,default:!1},showAction:{type:Boolean,default:!0},actionStyle:{type:Object,default:function(){return{}}},actionText:{type:String,default:"搜索"},inputAlign:{type:String,default:"left"},disabled:{type:Boolean,default:!1},animation:{type:Boolean,default:!1},borderColor:{type:String,default:"none"},value:{type:String,default:""},height:{type:[Number,String],default:64},inputStyle:{type:Object,default:function(){return{}}},maxlength:{type:[Number,String],default:-1},searchIconColor:{type:String,default:""},color:{type:String,default:"#606266"},placeholderColor:{type:String,default:"#909399"},margin:{type:String,default:"0"},searchIcon:{type:String,default:"search"}},data:function(){return{keyword:"",showClear:!1,show:!1,focused:this.focus}},watch:{keyword:function(t){this.$emit("input",t),this.$emit("change",t)},value:{immediate:!0,handler:function(t){this.keyword=t}}},computed:{showActionBtn:function(){return!(this.animation||!this.showAction)},borderStyle:function(){return this.borderColor?"1px solid ".concat(this.borderColor):"none"},getMaxlength:function(){return Number(this.maxlength)}},methods:{inputChange:function(t){this.keyword=t.detail.value},clear:function(){var t=this;this.keyword="",this.$nextTick((function(){t.$emit("clear")}))},search:function(t){this.$emit("search",t.detail.value),uni.hideKeyboard()},custom:function(){this.$emit("custom",this.keyword),uni.hideKeyboard()},getFocus:function(){this.focused=!0,this.animation&&this.showAction&&(this.show=!0),this.$emit("focus",this.keyword)},blur:function(){this.focused=!1,this.show=!1,this.$emit("blur",this.keyword)}}};e.default=a},2067:function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var a={uIcon:i("c688").default},n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"u-search",style:{margin:t.margin}},[i("v-uni-view",{staticClass:"u-content",style:{backgroundColor:t.bgColor,borderRadius:"round"==t.shape?"100rpx":"10rpx",border:t.borderStyle,height:t.height+"rpx"}},[i("v-uni-view",{staticClass:"u-icon-wrap"},[i("u-icon",{staticClass:"u-clear-icon",attrs:{size:30,name:t.searchIcon,color:t.searchIconColor?t.searchIconColor:t.color}})],1),i("v-uni-input",{staticClass:"u-input",style:[{textAlign:t.inputAlign,color:t.color,backgroundColor:t.bgColor},t.inputStyle],attrs:{"confirm-type":"search",value:t.value,disabled:t.disabled,maxlength:t.getMaxlength,focus:t.focus,"placeholder-class":"u-placeholder-class",placeholder:t.placeholder,"placeholder-style":"color: "+t.placeholderColor,type:"text"},on:{blur:function(e){arguments[0]=e=t.$handleEvent(e),t.blur.apply(void 0,arguments)},confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.search.apply(void 0,arguments)},input:function(e){arguments[0]=e=t.$handleEvent(e),t.inputChange.apply(void 0,arguments)},focus:function(e){arguments[0]=e=t.$handleEvent(e),t.getFocus.apply(void 0,arguments)}}}),t.keyword&&t.clearabled&&t.focused?i("v-uni-view",{staticClass:"u-close-wrap",on:{touchstart:function(e){arguments[0]=e=t.$handleEvent(e),t.clear.apply(void 0,arguments)}}},[i("u-icon",{staticClass:"u-clear-icon",attrs:{name:"close-circle-fill",size:"34",color:"#c0c4cc"}})],1):t._e()],1),i("v-uni-view",{staticClass:"u-action",class:[t.showActionBtn||t.show?"u-action-active":""],style:[t.actionStyle],on:{touchstart:function(e){e.stopPropagation(),e.preventDefault(),arguments[0]=e=t.$handleEvent(e),t.custom.apply(void 0,arguments)}}},[t._v(t._s(t.actionText))])],1)},o=[]},"299c":function(t,e,i){"use strict";var a=i("ac9e"),n=i.n(a);n.a},"60b6":function(t,e,i){"use strict";var a=i("e2b3"),n=i.n(a);n.a},"65a2":function(t,e,i){"use strict";i.r(e);var a=i("7a1a"),n=i("cbf8");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("299c");var s,r=i("f0c5"),c=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"335b24f8",null,!1,a["a"],s);e["default"]=c.exports},"7a1a":function(t,e,i){"use strict";i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var a={uSearch:i("e445").default},n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"page-body",class:[t.app.setCStyle()]},[i("v-uni-view",{staticClass:"u-search-box"},[i("u-search",{staticClass:"flex_bd ",attrs:{placeholder:t.setting.shop_index_search_text,"input-align":"left","show-action":!1},on:{blur:function(e){arguments[0]=e=t.$handleEvent(e),t.searchGoods()}},model:{value:t.keyword,callback:function(e){t.keyword=e},expression:"keyword"}})],1),i("v-uni-view",{staticClass:"tabs "},[i("v-uni-scroll-view",{staticClass:"scroll-h base-select",attrs:{id:"tab-bar","scroll-x":!0,"show-scrollbar":!1}},t._l(t.cateList[0],(function(e,a){return i("v-uni-view",{key:e.id,staticClass:"uni-tab-item",class:t.tabIndex==e.id?"current":""},[i("v-uni-text",{staticClass:"uni-tab-item-title",class:t.tabIndex==e.id?"uni-tab-item-title-active":"",attrs:{"data-cid":e.id},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.ontabtap.apply(void 0,arguments)}}},[t._v(t._s(e.name))])],1)})),1),i("v-uni-view",{staticClass:"line-h"})],1),i("v-uni-view",{staticClass:"category-list"},[i("v-uni-scroll-view",{staticClass:"left base-select",attrs:{"scroll-y":"true"}},t._l(t.cateList[t.tabIndex],(function(e,a){return i("v-uni-view",{key:a,staticClass:"row"},[t.cateList[e.id]?i("v-uni-view",{staticClass:"text"},[i("v-uni-view",{staticClass:"tes nor"},[t._v(t._s(e.name))]),t._l(t.cateList[e.id],(function(e,a){return i("v-uni-view",{key:a,staticClass:"texts",class:e.id==t.select_cid?"current":"",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.selectCid(e.id)}}},[i("v-uni-view",{staticClass:"block"}),t._v(t._s(e.name))],1)}))],2):i("v-uni-view",{staticClass:"text"},[i("v-uni-view",{staticClass:"texts",class:e.id==t.select_cid?"current":"",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.selectCid(e.id)}}},[i("v-uni-view",{staticClass:"block"}),t._v(t._s(e.name))],1)],1)],1)})),1),i("v-uni-scroll-view",{staticClass:"right",attrs:{"scroll-y":"true"}},[i("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:!0,expression:"true"}],staticClass:"category"},[i("v-uni-view",{staticClass:"list"},[t._l(t.goodsList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"box",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.app.goPage("/pages/shop/goods/info?goods_id="+e.goods_id)}}},[i("v-uni-image",{attrs:{src:t.baseUrl+e.goods_thumb,model:"aspectFill"}}),i("v-uni-view",{staticClass:"text fs28"},[t._v(t._s(e.goods_name))]),i("v-uni-view",{staticClass:"price mt10"},[t._v(t._s(e.sale_price))])],1)})),t.goodsList.length<1?i("v-uni-view",{staticClass:"w100 text-center color-cc mt30"},[t._v(t._s(t.app.langReplace("暂无相关商品")))]):t._e()],2)],1)],1)],1),i("tabbar",{attrs:{now_page:t.now_page,getCartNum:0}})],1)},o=[]},"86e1":function(t,e,i){"use strict";i.r(e);var a=i("012c"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},ac9e:function(t,e,i){var a=i("f218");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("d63020c6",a,!0,{sourceMap:!1,shadowMode:!1})},b044:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.u-search[data-v-03e728b1]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-flex:1;-webkit-flex:1;flex:1}.u-content[data-v-03e728b1]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding:0 %?18?%;-webkit-box-flex:1;-webkit-flex:1;flex:1}.u-clear-icon[data-v-03e728b1]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.u-input[data-v-03e728b1]{-webkit-box-flex:1;-webkit-flex:1;flex:1;font-size:%?28?%;line-height:1;margin:0 %?10?%;color:#909399}.u-close-wrap[data-v-03e728b1]{width:%?40?%;height:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;border-radius:50%}.u-placeholder-class[data-v-03e728b1]{color:#909399}.u-action[data-v-03e728b1]{font-size:%?28?%;color:#303133;width:0;overflow:hidden;-webkit-transition:all .3s;transition:all .3s;white-space:nowrap;text-align:center}.u-action-active[data-v-03e728b1]{width:%?80?%;margin-left:%?10?%}',""]),t.exports=e},cbf8:function(t,e,i){"use strict";i.r(e);var a=i("d9d7"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},d9d7:function(t,e,i){"use strict";var a=i("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("96cf");var n=a(i("1da1")),o=a(i("d6ca")),s={components:{tabbar:o.default},data:function(){return{baseUrl:this.config.baseUrl,now_page:"",setting:{},cateList:{},tabIndex:0,select_cid:0,goodsList:{},keyword:""}},onLoad:function(t){this.loadCateList(),this.setting=uni.getStorageSync("setting");var e=this.app.langReplace("商品分类");uni.setNavigationBarTitle({title:e}),this.now_page=this.$mp.page.route},computed:{},methods:{loadCateList:function(){var t=this;return(0,n.default)(regeneratorRuntime.mark((function e(){return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:t.$u.post("shop/api.goods/getCateList").then((function(e){t.cateList=e.data.list,t.tabIndex=t.cateList[0][0]["id"],t.getDefaultCid()}));case 1:case"end":return e.stop()}}),e)})))()},ontabtap:function(t){this.tabIndex=t.target.dataset.cid,this.getDefaultCid()},getDefaultCid:function(){var t=0;if("undefined"==typeof this.cateList[this.tabIndex])return this.selectCid(t),!0;t=this.cateList[this.tabIndex][0]["id"],this.selectCid(t)},selectCid:function(t){this.select_cid=t,this.getGoodsList()},getGoodsList:function(){var t=this;if(this.goodsList={},this.select_cid<1)return!1;this.$u.post("shop/api.goods/getList",{cateId:this.select_cid,page_size:999}).then((function(e){t.goodsList=e.data.list}))},searchGoods:function(){var t=this.keyword;return""!=t&&this.app.goPage("index?keyword="+this.keyword)}}};e.default=s},e2b3:function(t,e,i){var a=i("b044");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("7f28ea9d",a,!0,{sourceMap:!1,shadowMode:!1})},e445:function(t,e,i){"use strict";i.r(e);var a=i("2067"),n=i("86e1");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("60b6");var s,r=i("f0c5"),c=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"03e728b1",null,!1,a["a"],s);e["default"]=c.exports},f218:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.page-body[data-v-335b24f8]{height:calc(100vh);height:calc(100vh - var(--window-top));display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;background-color:#fff}.u-search-box[data-v-335b24f8]{padding:%?18?% %?30?%}.tabs[data-v-335b24f8]{-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;overflow:hidden;background-color:#fff;height:%?100?%;border-bottom:%?1?% solid #efefef}.tabs .current[data-v-335b24f8]{position:relative;font-size:%?36?%;font-weight:700}.tabs .current[data-v-335b24f8]:after{position:absolute;content:"";bottom:0;width:%?60?%;left:30%;height:%?6?%;border-radius:%?4?%}.scroll-h[data-v-335b24f8]{width:%?750?%;line-height:%?100?%;height:%?110?%;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;white-space:nowrap}.line-h[data-v-335b24f8]{height:%?1?%;background-color:#ccc}.uni-tab-item[data-v-335b24f8]{display:inline-block;height:85%;-webkit-flex-wrap:nowrap;flex-wrap:nowrap;width:calc(100% / 4.7);text-align:center}.uni-tab-item-title[data-v-335b24f8]{color:#999;font-size:%?30?%;height:%?80?%;line-height:%?80?%;-webkit-flex-wrap:nowrap;flex-wrap:nowrap;white-space:nowrap}.uni-tab-item-title-active[data-v-335b24f8]{color:#333}.price[data-v-335b24f8]{font-size:%?32?%;line-height:1;font-weight:700}.price[data-v-335b24f8]:before{content:"￥";font-size:%?24?%}.category-list[data-v-335b24f8]{-webkit-box-flex:1;-webkit-flex:1;flex:1;height:100%;position:relative;width:100%;background-color:#fff;display:-webkit-box;display:-webkit-flex;display:flex;height:100%;overflow:scroll}.category-list .left[data-v-335b24f8], .category-list .right[data-v-335b24f8]{position:absolute;top:0;height:100%}.category-list .left[data-v-335b24f8]{width:24%;left:%?0?%;background-color:#f2f2f2;height:100%}.category-list .left .row[data-v-335b24f8]{width:100%;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.category-list .left .row .text[data-v-335b24f8]{text-align:center;width:100%;position:relative;font-size:%?28?%;color:#999}.category-list .left .row .text .tes[data-v-335b24f8]{height:%?100?%;line-height:%?100?%}.category-list .left .row .text .tes.nor[data-v-335b24f8]::after{position:absolute;content:"";width:12px;height:1px;background:#c2c2c2;top:24px;left:8px}.category-list .left .row .text .tes.nor[data-v-335b24f8]::before{position:absolute;content:"";width:12px;height:1px;background:#c2c2c2;top:24px;right:8px}.category-list .left .row .text .texts[data-v-335b24f8]{height:%?100?%;line-height:%?100?%;color:#999}.category-list .left .row .text .texts.current[data-v-335b24f8]{background-color:#fff;font-weight:700}.category-list .right[data-v-335b24f8]{width:76%;left:24%;overflow-y:scroll;background-color:#fff;height:100%}.category-list .right .category[data-v-335b24f8]{width:100%;padding:%?20?% 5%;background:#fff}.category-list .right .category .banner[data-v-335b24f8]{width:100%;height:24.262vw;overflow:hidden}.category-list .right .category .banner uni-image[data-v-335b24f8]{width:100%;height:24.262vw}.category-list .right .category .list[data-v-335b24f8]{margin-top:%?40?%;width:100%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap}.category-list .right .category .list .box[data-v-335b24f8]{width:calc(100% / 2);margin-bottom:%?30?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-flex-wrap:wrap;flex-wrap:wrap}.category-list .right .category .list .box uni-image[data-v-335b24f8]{width:%?220?%;height:%?220?%;opacity:1}.category-list .right .category .list .box .text[data-v-335b24f8]{margin-top:%?5?%;width:90%;display:block;text-align:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;font-size:%?26?%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}',""]),t.exports=e}}]);