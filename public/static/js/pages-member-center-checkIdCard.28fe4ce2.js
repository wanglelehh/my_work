(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-member-center-checkIdCard"],{"148c7":function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 下方引入的为uView UI的集成样式文件，为scss预处理器，其中包含了一些"u-"开头的自定义变量\r\n * uView自定义的css类名和scss变量，均以"u-"开头，不会造成冲突，请放心使用 \r\n */\r\n/* 页面左右间距 */\r\n/* 文字尺寸 */\r\n/* 边框颜色 */\r\n/* 图片加载中颜色 */\r\n/* 行为相关颜色 */\r\n/*文字颜色*/.top_tip_icon[data-v-56795aee]{width:%?150?%!important;height:%?150?%!important;margin:%?0?% auto}.imagemz[data-v-56795aee]{width:%?324?%;height:%?279?%}.color-text[data-v-56795aee]{color:#3b7bf6}',""]),t.exports=i},1603:function(t,i,e){"use strict";var a=e("4517"),r=e.n(a);r.a},4333:function(t,i,e){"use strict";e.r(i);var a=e("6f9e"),r=e("7670");for(var s in r)"default"!==s&&function(t){e.d(i,t,(function(){return r[t]}))}(s);e("1603");var c,n=e("f0c5"),d=Object(n["a"])(r["default"],a["b"],a["c"],!1,null,"56795aee",null,!1,a["a"],c);i["default"]=d.exports},4517:function(t,i,e){var a=e("148c7");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var r=e("4f06").default;r("a7cd31ec",a,!0,{sourceMap:!1,shadowMode:!1})},"6f9e":function(t,i,e){"use strict";e.d(i,"b",(function(){return r})),e.d(i,"c",(function(){return s})),e.d(i,"a",(function(){return a}));var a={uForm:e("822d").default,uFormItem:e("e728").default,uInput:e("aee4").default},r=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"page-body",class:[t.app.setCStyle(t.from)]},[e("u-form",{ref:"uForm",attrs:{model:t.model,errorType:t.errorType}},[e("v-uni-view",{staticClass:"p20 bg-white pt50 mlr20 br20 mt20"},[e("v-uni-view",{staticClass:"fs30 font-w600"},[t._v("请填写您的身份证号码")]),e("u-form-item",{attrs:{"label-width":"0","border-bottom":!1,prop:"id_card_number"}},[e("u-input",{staticClass:"bg-fa",attrs:{border:t.border,placeholder:"请输入身份证号码",type:"text"},model:{value:t.model.id_card_number,callback:function(i){t.$set(t.model,"id_card_number",i)},expression:"model.id_card_number"}})],1),e("u-form-item",{staticClass:"hide",attrs:{prop:"id_card_positive"}},[e("u-input",{attrs:{type:"text"},model:{value:t.model.id_card_positive,callback:function(i){t.$set(t.model,"id_card_positive",i)},expression:"model.id_card_positive"}})],1),e("u-form-item",{staticClass:"hide",attrs:{prop:"id_card_back"}},[e("u-input",{attrs:{type:"text"},model:{value:t.model.id_card_back,callback:function(i){t.$set(t.model,"id_card_back",i)},expression:"model.id_card_back"}})],1)],1),e("v-uni-view",{staticClass:"mt20 p20 bg-white mlr20 br20"},[e("v-uni-view",{staticClass:"fs30 font-w600"},[t._v("请上传你的身份证照片")]),e("v-uni-view",{staticClass:"mt30 flex"},[e("v-uni-view",{staticClass:"ml20",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.upload("positive")}}},[e("v-uni-image",{staticClass:"imagemz",attrs:{src:t.id_card_positive},on:{error:function(i){arguments[0]=i=t.$handleEvent(i),t.id_card_positive="/static/public/images/id_card/img_front.jpg"}}})],1),e("v-uni-view",{staticClass:"ml20",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.upload.apply(void 0,arguments)}}},[e("v-uni-image",{staticClass:"imagemz",attrs:{src:t.id_card_back},on:{error:function(i){arguments[0]=i=t.$handleEvent(i),t.id_card_back="/static/public/images/id_card/img_back.jpg"}}})],1)],1)],1),e("v-uni-view",{staticClass:"mt20 p20 bg-white mlr20 br20"},[e("v-uni-view",{staticClass:"fs30 font-w600"},[t._v("拍摄身份证要求：")]),e("v-uni-view",{staticClass:"mt30 fs26 color-99"},[e("v-uni-view",[t._v("只能上传不超过5M、jpg、png格式的图片")]),e("v-uni-view",[t._v("拍摄时确保身份证"),e("v-uni-text",{staticStyle:{color:"#e73146"}},[t._v("边框完整，字体清晰，亮度均匀；")])],1)],1),e("v-uni-view",{staticClass:"mt60"},[e("v-uni-image",{staticStyle:{width:"100%"},attrs:{mode:"widthFix",src:"/static/public/images/id_card/img_long.jpg"}})],1)],1),e("v-uni-view",{staticClass:"mt40 p20"},[0==t.check_id_card?e("v-uni-button",{staticClass:"primarybtn",attrs:{size:"default",shape:"circle",type:"warning"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.submit.apply(void 0,arguments)}}},[t._v("提交认证")]):1==t.check_id_card?e("v-uni-button",{staticClass:"graybtn",attrs:{size:"default",shape:"circle",type:"success"}},[t._v("已认证")]):2==t.check_id_card?e("v-uni-button",{staticClass:"graybtn",attrs:{size:"default",shape:"circle",type:"warning"}},[t._v("审核中")]):3==t.check_id_card?e("v-uni-button",{staticClass:"primarybtn",attrs:{size:"default",shape:"circle",type:"warning"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.submit.apply(void 0,arguments)}}},[t._v("重新提交")]):t._e()],1)],1)],1)},s=[]},7670:function(t,i,e){"use strict";e.r(i);var a=e("a717"),r=e.n(a);for(var s in a)"default"!==s&&function(t){e.d(i,t,(function(){return a[t]}))}(s);i["default"]=r.a},a717:function(t,i,e){"use strict";Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={data:function(){var t=this;return{from:"",upsize:5242880,check_id_card:0,id_card_positive:"/static/public/images/id_card/img_front.jpg",id_card_back:"/static/public/images/id_card/img_back.jpg",model:{id_card_number:"",id_card_positive:"",id_card_back:""},border:!1,labelPosition:"left",errorType:["toast"],rules:{id_card_number:[{required:!0,message:"请输入身份证号码",trigger:["change","blur"]},{validator:function(i,e,a){return t.$u.test.idCard(e)},message:"身份证号码不正确",trigger:["change","blur"]}],id_card_positive:[{required:!0,message:"请上传身份证头像面.",trigger:["change","blur"]}],id_card_back:[{required:!0,message:"请上传身份证国微面.",trigger:["change","blur"]}]}}},onLoad:function(t){this.app.isLogin(this),this.getUserInfo(),this.from=t.from},computed:{borderCurrent:function(){return this.border?0:1}},onReady:function(){this.$refs.uForm.setRules(this.rules)},methods:{getUserInfo:function(){var t=this;this.$u.api.getUserInfo().then((function(i){t.check_id_card=i.data.check_id_card,t.model.id_card_number=i.data.id_card_number,i.data.id_card_positive&&(t.id_card_positive=t.config.baseUrl+i.data.id_card_positive),i.data.id_card_back&&(t.id_card_back=t.config.baseUrl+i.data.id_card_back)}))},upload:function(t){var i=this;return 1==this.check_id_card?(this.$u.toast("已认证不能重新上传."),!1):2==this.check_id_card?(this.$u.toast("正在审核中，不能重新上传."),!1):void uni.chooseImage({count:1,sizeType:["original","compressed"],sourceType:["album"],success:function(e){var a=e.tempFilePaths;if(e.tempFiles[0].size>i.upsize)return i.$u.toast("图片不能大于5MB."),!1;uni.uploadFile({url:i.config.upImageUrl,filePath:a[0],name:"file",success:function(e){if(e=JSON.parse(e.data),e.code<1)return i.$u.toast(e.msg),!1;"positive"==t?(i.id_card_positive=i.config.baseUrl+e.file,i.model.id_card_positive=e.file):(i.id_card_back=i.config.baseUrl+e.file,i.model.id_card_back=e.file)}})},error:function(t){console.log(t)}})},submit:function(){var t=this;this.$refs.uForm.validate((function(i){if(!i)return!1;t.$u.post("member/api.users/postIdCard",t.model).then((function(t){uni.showModal({title:"提示",content:t.msg,showCancel:!1,success:function(t){t.confirm&&uni.switchTab({url:"/pages/channel/center/index"})}})}))}))}}};i.default=a}}]);