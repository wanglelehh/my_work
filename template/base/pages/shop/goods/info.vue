<template>
	<view class="page-body" :class="[app.setCStyle()]">
		<view class="navigation" :style="{'opacity':getOpacity}">
			<view class="nav-tab base-select">
				<view class="tab-item" :class="{current:tabActive == 0}" @click="pageScrollTo(0)">{{app.langReplace('商品')}}</view>
				<view v-if="selectGoods.shop_goods_comment == 1" class="tab-item" :class="{current:tabActive == 1}" @click="pageScrollTo(1)">{{app.langReplace('评论')}}</view>
				<view class="tab-item" :class="{current:tabActive == 2}" @click="pageScrollTo(2)">{{app.langReplace('详情')}}</view>
			</view>
		</view>
		<view class="carousel">
			<swiper indicator-dots circular=true duration="400" :current="current" @change="nextPage">
				<swiper-item v-if="selectGoods.video_position == 1 && selectGoods.video_url != ''" class="swiper-item">
					<view class="image-wrapper">
						<view v-if="videoPaly == false" class="video_cover">
							<image class="bg" :src="baseUrl+selectGoods.video_cover" style="height:100%;width:100%"></image>
							<image class="play" src="/static/public/images/video_icon/video_play.png" style="" @click="startplay"></image>
						</view>
						<video v-else :enable-progress-gesture="false" autoplay="true" @pause="videoPaly = false" @ended="videoPaly = false" :muted="sound" :src="baseUrl+selectGoods.video_url" style="width: 100%;height: 100%;" >
							<cover-image @click="changevoice" class="voice" :src="sound?mute:voice"></cover-image>
						</video>
					</view>
				</swiper-item>
				<swiper-item class="swiper-item" v-for="(item,index) in imgList" :key="index">
					<view class="image-wrapper">
						<image :src="baseUrl+item.goods_img" class="loaded" mode="widthFix"></image>
					</view>
				</swiper-item>
				
			</swiper>
		</view>
		<view class="smll prom" v-if="prom_id > 0">
			<view class="price-box base-color flex_bd">
				<view class="fs24 mt10 color-FFC8CF">{{app.langReplace('活动价')}}</view>
				<text class="color-ff fs24">¥</text>
				<text class="color-ff ff fs36">{{showGoodsPrice}}</text>
				<text v-if="selectGoods.is_spec == 1" class="font-w700 ml10 color-FFC8CF fs26">{{app.langReplace('起')}}</text>
				<text class="fs26 ml30 color-FFC8CF line-through" v-if="selectGoods.show_market_price == 1">{{app.langReplace('原价')}}: ¥{{showGoodSmarketPrice}}</text>
			</view>
			<view class="price-box-radius"></view>
			<view  class="times fs26">
				<view class="base-color text-right fs22 mb10">{{app.langReplace('距结束还剩')}}</view>
				<text>{{times.hou}}</text>:<text>{{times.min}}</text>:<text>{{times.sec}}</text>
			</view>
		</view>
		<view class="introduce-section">
			<view v-if="prom_id == 0" class="smll">
				<view class="price-box base-color flex_bd">
					<text class="price-tip fs24">¥</text>
					<text class="price ff">{{showGoodsPrice}}</text>
					<text v-if="selectGoods.is_spec == 1" class="font-w700 ml10">{{app.langReplace('起')}}</text>
					<text class="m-price ml30 fs24 color-99" v-if="selectGoods.show_market_price == 1">{{app.langReplace('原价')}}: ¥{{showGoodSmarketPrice}}</text>
				</view>
			</view>
			<view v-if="selectGoods.use_integral > 0" class="fs26">
				<text class="base-color font-w700">+{{selectGoods.use_integral}}{{app.langReplace('积分')}}</text>
				<text class="color-99">，{{app.langReplace('此商品需要消耗额外积分才能购买')}}</text>
			</view>
			
			<view class="flex mt10">
				<text class="title flex_bd">{{selectGoods.goods_name}}</text>
					<view class="icon  text-center" @click="showGoodsShare()">
						<u-icon name="share-square" size="50" class="color-cc"></u-icon> 
						<view class="fs20 color-cc">{{app.langReplace('分享')}}</view>
					</view>
			</view>
			
			<view class="bot-row">
				<view>
					<text class="mr30" v-if="selectGoods.show_stock_num == 1 && selectGoods.is_spec == 0">{{app.langReplace('库存')}}: {{selectGoodsNumber}}</text> 
					<text class="mr30" v-if="selectGoods.show_sale_num == 1">{{app.langReplace('已售')}}:{{selectGoods.sale_count}}</text>
					<text class="mr30" v-if="selectGoods.show_collect_num == 1">{{app.langReplace('收藏')}}: {{selectGoods.collect_count}}</text>
				</view>
				
			</view>
		</view>

		<!-- 用户评价 -->
		<view id="comment_box" >
			<view v-if="selectGoods.shop_goods_comment == 1" class="comment_box mt20">
				<view class="title smll">
					<view class="flex_bd">{{app.langReplace('用户评论')}}<text class="num">({{comment.count}})</text></view>
					<view class="more" @click="app.goPage('/pages/shop/comment/index?goods_id='+goods_id)">{{app.langReplace('更多')}}<u-icon name="arrow-right"></u-icon>
					</view>
				</view>
				<view class="list mt20" v-for="(item,index) in comment.list" :key="index">
					<view class="tit smll">
						<view class="image ">
							<image :src="baseUrl + item.headimgurl"></image>
						</view>
						<view class="flex_bd">
							{{item.user_name}}
						</view>
						<view class="time">{{item._time}}</view>
					</view>
					<view class="content">
						{{item.content}}
					</view>
					<view class="images">
						<u-grid :col="4" class="u_grid" :border="false">
							<u-grid-item v-for="(img,imgIndex) in item.imgs" :key="imgIndex" @click="app.showImg(item.imgs,imgIndex,config.baseUrl)">
								<image :src="baseUrl+img"></image>
							</u-grid-item>
						</u-grid>
					</view>
				</view>
			</view>
		</view>
		<view id="detail_desc" class="detail_desc mt20">
			<view class="title ">
				<view >{{app.langReplace('详情')}}</view>
			</view>
			<view  v-if="selectGoods.video_position == 2 && selectGoods.video_url != ''" >
				<view v-if="videoPaly == false" class="video_cover" style="min-height:500rpx;">
					<image class="bg" :src="baseUrl+selectGoods.video_cover" style="height:100%;width:100%"></image>
					<image class="play" src="/static/public/images/video_icon/video_play.png" style="" @click="startplay"></image>
				</view>
				<video v-else :enable-progress-gesture="false" autoplay="true" @pause="videoPaly = false"  @ended="videoPaly = false"  :muted="sound" :src="baseUrl+selectGoods.video_url" style="width: 100%;" >
					<cover-image @click="changevoice" class="voice" :src="sound?mute:voice"></cover-image>
				</video>
			</view>
			<u-parse :content="desc" :imageProp="{'mode':'widthFix'}" noData="" @navigate="goUrl"  ></u-parse>
		</view>

		<!-- 底部操作菜单 -->
		<view class="page-bottom ">
			<view class="flex">
				<view @click="app.goPage('/pages/shop/index/index')" class="p-b-btn ">
					<u-icon  name="home"  size="46" color="#000"></u-icon>
					<text>{{app.langReplace('首页')}}</text>
				</view>
				<view @click="app.goPage('/pages/shop/flow/cart')" class="p-b-btn relative">
					<u-icon name="shopping-cart" size="46" color="#000"></u-icon>
					<text>{{app.langReplace('购物车')}}</text>
					<view class="cartNum bg-base">{{cartNum}}</view>
				</view>
				<view class="p-b-btn" @click="collect">
					<u-icon v-if="selectGoods.is_collect==1" name="heart-fill" size="46" class="base-color" ></u-icon>
					<u-icon v-else name="heart" size="46" color="#000"></u-icon>
					<text >{{app.langReplace('收藏')}}</text>
				</view>

				<view class="action_btn_group primarybtn flex_bd">
					<view class=" action_btn no-border " @click="toggleSpec('oncart')">{{app.langReplace('加入购物车')}}</view>
					<view class=" action_btn no-border " @click="toggleSpec('onbuy')">{{app.langReplace('立即购买')}}</view>
				</view>
			</view>
			<view class="w100" :style="{'padding-bottom':app.iphonexBottom()}"></view>
		</view>
		<!--end 底部按钮 -->
		<goodsSpec :specClass="specClass" @selectSpec="selectSpec" @toggleSpec="toggleSpec" :specSelected="specSelected"
		 :selectGoods="selectGoods" :selectGoodsPrice="selectGoodsPrice" :selectGoodSmarketPrice="selectGoodSmarketPrice"
		 :selectGoodsNumber="selectGoodsNumber" :selectGoodsImg="selectGoodsImg" :specList="specList" :specChildList="specChildList"
		:selectSkuId="selectSkuId" :prom_id="prom_id" :prom_type="prom_type" :byType="byType"></goodsSpec>
		
		<copyright></copyright>
		<view class="w100 h100"></view>
		
		
		<view class="share-image-box" v-if="shareShow==true">
			<view class="share-main">
				<div class="share-mask" @click="shareShow=false" ></div>
				<view class="share-close"  @click="shareShow=false">
					<u-icon name="close" color="#FFFFFF" size="32"></u-icon>
				</view>
				<view class="share-img">
						<image :src="shareimgurl" mode="aspectFill" ></image>
						<canvas  canvas-id="mycanvas" style="width: 580px;height: 870px;" v-show="canvasShow"></canvas>
				</view>
				<view class="btn-box flex">
					<!-- #ifdef H5 -->
					<view  class="btn" >{{app.langReplace('长按图片保存')}}</view>
					<!-- #endif  -->
					<!-- #ifndef H5 -->
					<view  class="btn" @click="saveImage">{{app.langReplace('保存图片')}}</view>
					<!-- #endif  -->
				</view>
			</view>
		</view>
		
	</view>
</template>

<script>
	import copyright from '@/pages/public/copyright';
	import goodsSpec from '@/pages/shop/goods/spec';
	export default {
		components: {
			copyright,
			goodsSpec,
		},
		data() {
			return {
				shareData: {},
				current:0,
				goods_id: 0,
				cartNum: 0,
				scrollTop: 0,
				tabActive: 0,
				detailDesclTop: 0,
				commentTop: 0,
				baseUrl: this.config.baseUrl,
				specClass: 'none',
				specSelected: [],
				byType: '',
				imgList: [],
				desc: '',
				showGoodsPrice: '0.00',
				showGoodSmarketPrice:'0.00',
				specList: [],
				specChildList: [],
				selectGoods: {}, //选择的商品
				selectSkuId:0,
				selectGoodsPrice: '0.00',
				selectGoodSmarketPrice: '0.00',
				selectGoodsNumber: 0,
				selectGoodsImg: '',
				comment: {
					count: 0,
					list: [],
				},
				isLoading:false,
				isProm:false,
				prom_type:0,
				prom_id:0,
				liveCountTimes:'',
				times:{},
				sound: false,
				mute:'/static/public/images/video_icon/mute.png',
				voice:'/static/public/images/video_icon/voice.png',
				videoPaly:false,
				shareShow:false,
				shareimgurl:"",
				canvasShow:true,
				user_token:uni.getStorageSync('user_token'),
			};
		},
		async onLoad(options) {
			let title = this.app.langReplace('商品信息');
			uni.setNavigationBarTitle({
			　　title:title
			})
			if (options.scene){//获取小程序的场景值，用于获取分享者的token
				let scene = options.scene.split('_');
				uni.setStorageSync("share_token",scene[0]);
				this.goods_id = scene[1];
			}else{
				this.goods_id = options.goods_id;
			}
			this.getGoodsInfo();
		
			
		},
		onShow() {
			let setting = uni.getStorageSync('setting');
			if(setting.shop_force_login==1){
				this.app.isLogin(this); //强制登陆
			}
			this.getComment();
			this.loadCartNum();
		},
		computed: {
			getOpacity() {
				return this.scrollTop > 90 ? 1 : 0;
			}
		},
        onShareAppMessage() {
            return {
                title:this.selectGoods.goods_name,
                imageUrl: this.baseUrl + this.selectGoods.goods_img,
                path: '/pages/shop/goods/info?goods_id='+this.selectGoods.goods_id+'&share_token='+this.user_token
            }
        },
		methods: {
			goUrl(url){
				this.app.goPage(url);
			},
			//获取商品信息
			getGoodsInfo() {
				this.$u.post('shop/api.goods/getGoodsInfo', {
					'goods_id': this.goods_id
				}).then(res => {
					this.selectGoods = res.data;
					this.imgList = res.data.imgList;
					this.desc = res.data.m_goods_desc;
					if (this.selectGoods.is_spec == 0) {
						this.selectGoodsImg = this.config.baseUrl + res.data.goods_thumb;
						this.selectGoodsPrice = res.data.sale_price;
						this.selectGoodSmarketPrice = res.data.market_price;
						this.showGoodsPrice = res.data.sale_price
						this.showGoodSmarketPrice = res.data.market_price;
						this.selectGoodsNumber = res.data.goods_number;
					} else {
						this.specList = res.data.specList;
						this.specChildList = res.data.specChildList;
						this.specSelected = [];
						this.showGoodsPrice = res.data.min_price
						this.showGoodSmarketPrice = res.data.market_price;
						this.showSelectSpec();
					}
					
					this.initActivity(true);
					let setting = uni.getStorageSync('setting');
					this.shareData.title = setting.site_name;
					this.shareData.desc = this.selectGoods.goods_name;
					this.shareData.imageUrl = this.config.baseUrl +this.selectGoods.goods_img;
					this.share();
				})
			},
			//初始化商品活动
			initActivity(type = false){
				if (type == false && this.isProm == false){
					return false;
				}
				this.prom_id = 0;
				this.$u.post('shop/api.goods/checkactivity', {
					'goods_id': this.goods_id,
					'sku_id':this.selectSkuId
				}).then(res => {
					if (res.data.activity_is_on == 1){
						this.isProm = true;
						if (res.data.goods_info){
							this.showGoodsPrice = res.data.goods_info.goods_price;
							this.selectGoodsPrice =  res.data.goods_info.goods_price;
							this.selectGoodSmarketPrice = res.data.goods_info.market_price;
							this.showGoodSmarketPrice = res.data.goods_info.market_price;
							this.prom_type = res.data.prom_type;
							this.prom_id = res.data.prom_id;
						}
						if (this.liveCountTimes == ''){
							this.getLiveTimeCount(res.data.end_time);
						}
						
					}
				})
			},
			getLiveTimeCount(time){
				this.times = {
					day: '00',
					hou: '00',
					min: '00',
					sec: '00'
				};
				if (time == 0) return false; 
				if (typeof(this.liveCountTimes) != 'undefined'){
					clearInterval(this.liveCountTimes);
				}
				this.liveCountTimes=setInterval(()=>{
					let nowTime = new Date().getTime();
					//注：不论安卓还是ios，请将时间如 2020-02-02 20:20:20 转化为 2020/02/02 20:20:20 这种形式后再使用，否则无法转换，如下转换即可↓
					let transedPreTime= time.replace(/-/g,'/') //这里转化时间格式为以/分隔形式
					let preTime = new Date(transedPreTime).getTime()
					if(preTime - nowTime > 0){
						let time = (preTime - nowTime) / 1000;
						if (time > 0){
							let day = parseInt(time / (60 * 60 * 24));
							let hou = parseInt(time % (60 * 60 * 24) / 3600);
							let min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
							let sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
							let msec = Math.floor(time * 1000 % 1000 / 100);
							this.times = {
								day: day<10?'0'+day:day,
								hou: hou<10?'0'+hou:hou,
								min: min<10?'0'+min:min,
								sec: sec<10?'0'+sec:sec
							};
						}else{
							this.prom_id = 0;
						}
					}else{
						this.prom_id = 0;
					}
				},1000)
			},
			//获取评论
			getComment() {
				this.$u.post('shop/api.comment/getListByGoods', {
					'goods_id': this.goods_id,
					'limit': 3
				}).then(res => {
					this.comment.count = res.data.total_count;
					this.comment.list = res.data.list;
					this.$set(this.comment, 'loaded', true);
					this.getSelectorQuery();
				})
			},
			collect() { //收藏
				if (this.isLoading == true){
					return false;
				}
				this.isLoading = true;
				this.$u.post('shop/api.goods/collect', {
					'goods_id': this.goods_id
				}).then(res => {
					this.isLoading = false;
					let is_collect = this.selectGoods.is_collect;
					if (is_collect == 1 ){
						this.selectGoods.is_collect = 0;
						this.selectGoods.collect_count = this.selectGoods.collect_count - 1;
					}else{
						this.selectGoods.is_collect = 1;
						this.selectGoods.collect_count = this.selectGoods.collect_count + 1;
					}
				}).catch(res=>{
					this.isLoading = false;
				})
			},
			onPageScroll(res) {
				this.scrollTop = res.scrollTop;
				if (res.scrollTop >= this.detailDesclTop) {
					this.tabActive = 2;
				} else {
					if (this.selectGoods.shop_goods_comment == 1 && res.scrollTop >= this.commentTop) {
						this.tabActive = 1;
					} else {
						this.tabActive = 0;
					}
				}
			},
			//获取定位
			getSelectorQuery() {
				let that = this;
				const query = uni.createSelectorQuery();
				if (that.selectGoods.shop_goods_comment == 1) {
					query.select('#comment_box').boundingClientRect(function(res) {
						that.commentTop = res.top - 80;
						//console.log('打印demo的元素的信息', res);
					})
				}
				query.select('#detail_desc').boundingClientRect(function(res) {
					that.detailDesclTop = res.top + 100 ;
					//console.log('打印demo的元素的信息', res);
				})
				query.exec()
			},
			pageScrollTo(index) {
				let scrollTop = 0;
				if (index == 1) {
					scrollTop = this.commentTop;
				} else if (index == 2) {
					scrollTop = this.detailDesclTop;
				}
				uni.pageScrollTo({
					duration: 0, //过渡时间必须为0，uniapp bug，否则运行到手机会报错
					scrollTop: scrollTop, //滚动到实际距离是元素距离顶部的距离减去最外层盒子的滚动距离
				})
			},
			//规格弹窗开关
			toggleSpec(type = 'oncart') {
				if (this.specClass === 'show') {
					this.specClass = 'hide';
					setTimeout(() => {
						this.specClass = 'none';
					}, 250);
					this.loadCartNum();
				} else if (this.specClass === 'none') {
					this.specClass = 'show';
				}
				this.byType = type;
			},
			//选择规格
			selectSpec(list) {
				//存储已选择
				/**
				 * 修复选择规格存储错误
				 * 将这几行代码替换即可
				 * 选择的规格存放在specSelected中
				 */
				this.specSelected = [];
				list.forEach(item => {
					if (item.selected === true) {
						this.specSelected.push(item);
					}
				})
				this.showSelectSpec();
			},
			showSelectSpec() {
				this.selectSkuId = 0;
				let selSkuArr = [];
				this.specSelected.forEach(item => {
					selSkuArr.push(item.id);
				})
				let selSku = selSkuArr.join(":");
				Object.keys(this.selectGoods.sub_goods).forEach(sku_id => {
					let item = this.selectGoods.sub_goods[sku_id];
					if (selSku == item.sku_val) {
						this.selectSkuId = parseInt(item.sku_id);
						this.selectGoodsPrice = item.sale_price;
						this.selectGoodSmarketPrice = item.market_price;
						this.selectGoodsNumber = item.goods_number;
						return false;
					}
				})
				if (this.selectSkuId < 1){
					this.selectGoodsImg = this.config.baseUrl + this.selectGoods.goods_thumb;
				}else{
					this.initActivity();
				}
				if (selSkuArr.length < 1) {
					return false;
				}
				let selSkuImgArr = [];
				this.specList.forEach(function(item,index){
					if (item.img_type == 2){
						selSkuImgArr.push(selSkuArr[index]);
					}
				})
				let selSkuImg = selSkuImgArr.join(":");
				this.selectGoods.imgSkuList.forEach(item => {
					if (selSkuImg == item.sku_val) {
						this.selectGoodsImg = this.config.baseUrl + item.goods_thumb;
						return false;
					}
				})
				
			},
			//加载购物车中商品数
			async loadCartNum() {
				this.$u.post('shop/api.flow/getCartInfo',{'showLoading':false}).then(res => {
					this.cartNum = res.data.num;
				})
			},
			changevoice(){
				this.sound = this.sound == false ? true : false;
			},
			nextPage(e){
				if (e.detail.current != 0){
					this.videoPaly = false;
				}
			},
			startplay(){
				this.videoPaly = true;
			},
			showGoodsShare(){
				if (this.shareimgurl == ''){
					this.canvasImage();
				}else{
					 this.shareShow = true;
				}
				
			},
			
			
		   async canvasImage(){
			   let shareInfo = {};
			   let is_wxmp = 0;
			   //#ifdef MP-WEIXIN
			   	is_wxmp = 1;
			   //#endif
			   await this.$u.post('shop/api.goods/shareInfo', {
			   	'goods_id': this.goods_id,
			   	'is_wxmp':is_wxmp
			   }).then(res => {
			   	shareInfo = res.data;
			   })
			   if (shareInfo.length < 1){
				   this.shareShow = false;
			   		return false;
			   }
			   this.shareShow = true;
				uni.showLoading({
					title:'加载中',
					mask:true
				})
				let ImgInfo = {};
				let myCanvas = uni.createCanvasContext('mycanvas', this); 
				this.app.circleImgTwo(myCanvas,'',0,0,580,870,30);
				
				myCanvas.fillStyle = "#FFFFFF";
				myCanvas.fill();
				let goods_img = this.baseUrl+this.selectGoods.goods_img;
				ImgInfo = await this.app.getImgInfo(goods_img);
				myCanvas.drawImage(ImgInfo.path,0,0,ImgInfo.width,ImgInfo.height,0,0,580,580);
				
				myCanvas.fillStyle = "#333333";
				myCanvas.font="28px PingFangSC";
				let goods_name = this.selectGoods.goods_name;
				myCanvas.fillText(goods_name.substring(0,14),30 ,710);
				if (goods_name.length > 14){
					let goods_nameb = goods_name.substring(14,24);
					if (goods_name.length > 24){
						goods_nameb += '...';
					}
					myCanvas.fillText(goods_nameb,30 ,750);
				}
				
				//原价
				myCanvas.fillStyle = '#ccc';
				myCanvas.font = '26px Arial';
				myCanvas.fillText('原价:',200,810);
				myCanvas.fillStyle = "#ccc";
				myCanvas.font="26px Arial";
				myCanvas.fillText('￥',260 ,810);
				myCanvas.font="26px Arial";
				myCanvas.fillText(this.selectGoodSmarketPrice,280,810);
				myCanvas.beginPath();
				const textWidth = myCanvas.measureText(this.selectGoodSmarketPrice).width;
				myCanvas.rect(280, 800, textWidth, 3);
				myCanvas.fillStyle = '#ccc';
				myCanvas.fill();
				//原价end
				
				
				myCanvas.fillStyle = "#FE385E";
				myCanvas.font="26px Arial";
				myCanvas.fillText('￥',30 ,810);
				
				myCanvas.font="36px Arial";
				myCanvas.fillText(this.showGoodsPrice,50,810);
				
				if (shareInfo.share_headimgurl){
					let share_headimgurl = this.baseUrl+shareInfo.share_headimgurl
					ImgInfo = await this.app.getImgInfo(share_headimgurl);
					this.app.circleImg(myCanvas,ImgInfo.path,30,600,30);
				}else{
					this.app.circleImg(myCanvas,'/static/public/images/headimgurl.jpg',30,600,30);
				}
				
				myCanvas.fillStyle = "#333333";
				let nick_name = shareInfo.share_nick_name;
				let nick_name_x = 105;
				let nick_name_y = 638;
				myCanvas.font="30px Georgia";
				myCanvas.fillText(nick_name,nick_name_x,nick_name_y);
				
				let lm = nick_name.length * 30 + 30;
				myCanvas.fillStyle = "#999999";
				myCanvas.font="26px PingFangSC";
				myCanvas.fillText(this.app.langReplace('给您推荐'),nick_name_x + lm ,nick_name_y);
				
				let share_qrcode = this.baseUrl+shareInfo.share_qrcode;
				ImgInfo = await this.app.getImgInfo(share_qrcode);
				myCanvas.drawImage(ImgInfo.path,0,0,ImgInfo.width,ImgInfo.height,420,680,150,150);
				
				//开始绘画，必须调用这一步，才会把之前的一些操作实施
				myCanvas.draw(true,()=>{
					uni.canvasToTempFilePath({
						canvasId: 'mycanvas',
						success: (res) => {
							// 在H5平台下，tempFilePath 为 base64
							this.shareimgurl = res.tempFilePath;
							this.canvasShow = false;
							uni.hideLoading();
							
						  },
						fail: () => {
							uni.showToast({
								title: this.app.langReplace('名片加载失败'),
								duration: 2000 
							});
						}
					});
				});
				
			},
			saveImage(){
				uni.showActionSheet({
					itemList: [this.app.langReplace('保存图片')], 
					success: (res) => {
						if(res.tapIndex == 0){
							uni.saveImageToPhotosAlbum({
								filePath: this.shareimgurl,//    图片文件路径，可以是临时文件路径也可以是永久文件路径，不支持网络图片路径
								success: () => {
									uni.showToast({
										title: this.app.langReplace('保存成功'),
										duration: 2000
									});
								},
								fail: () => {
									uni.showToast({
										title: this.app.langReplace('保存失败'),
										duration: 2000 
									});
								}
							});
						}
					},
					fail: function (res) {
						console.log(res.errMsg);
					}
				});
			},
			
			stopPrevent() {}
		},

	}
</script>

<style lang='scss'>
	@import './info.scss';
	.times{
		padding-right: 10rpx;
		width: 200rpx;
		color: #FE385E;
		text-align: right;
	}
	.times text{
		font-size: 23rpx;
		margin:0rpx 6rpx;
		padding:6rpx;
		background-color: #FFFFFF;
		border-radius: 4rpx;
	}
	.video_cover {
	    width: 100%;
		height: 100%;
	    position: relative;
	}
	
	.video_cover .play {
	    position: absolute;
	    top: 50%;
	    left: 50%;
	    width: 130rpx !important;
	    height: 120rpx !important;
	    margin-top: -60rpx;
	    margin-left: -65rpx;
	}
	.video_cover .bg {
	    position: absolute;
	    left: 0;
	    top: 0;
	}
</style>
