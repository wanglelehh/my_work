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
			<swiper indicator-dots circular=true duration="400">
				<swiper-item v-if="selectGoods.video_position == 1 && selectGoods.video_url != ''" class="swiper-item">
					<view class="image-wrapper">
						<video controls  autoplay="false" :muted="sound" :src="baseUrl+selectGoods.video_url" style="width: 100%;height: 100%;" >
							<cover-image @click="changevoice" class="voice" :src="sound?mute:voice"></cover-image>
						</video>
					</view>
				</swiper-item>
				<swiper-item class="swiper-item" v-for="(item,index) in imgList" :key="index">
					<view class="image-wrapper">
						<image :src="baseUrl+item.goods_img" class="loaded" mode="aspectFill"></image>
					</view>
				</swiper-item>
				
			</swiper>
		</view>

		<view class="introduce-section">
			<text class="title">{{selectGoods.goods_name}}</text>
			<view class="smll">
				<view class="price-box base-color flex_bd">
					<text class="price ff">{{showGoodsPrice}}</text>
					<text class="price-tip fs24">{{app.langReplace('积分')}}</text>
					<text v-if="selectGoods.is_spec == 1" class="font-w700 ml10">{{app.langReplace('起')}}</text>
					<text class="m-price fs26 ml20" v-if="selectGoods.show_market_price == 1">{{app.langReplace('原价')}}: {{showGoodSmarketPrice}}</text>
				</view>
			</view>
			
			<view class="bot-row">
				<text v-if="selectGoods.show_stock_num == 1 && selectGoods.is_spec == 0">{{app.langReplace('库存')}}: {{selectGoodsNumber}}</text> 
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
			<u-parse :content="desc" :imageProp="{'mode':'widthFix'}" noData="" @navigate="goUrl" ></u-parse>
		</view>
		

		<!-- 底部操作菜单 -->
		<view class="page-bottom flex">
			<view @click="app.goPage('/pagesA/integral/index')" class="p-b-btn ">
				<u-icon  name="home"  size="46" color="#000"></u-icon>
				<text>{{app.langReplace('首页')}}</text>
			</view>
			<view @click="app.goPage('/pagesA/integral/cart')" class="p-b-btn relative">
				<u-icon name="shopping-cart" size="46" color="#000"></u-icon>
				<text>{{app.langReplace('购物车')}}</text>
				<view class="cartNum bg-base">{{cartNum}}</view>
			</view>

			<view class="action_btn_group primarybtnB flex_bd">
				<view class=" action_btn no-border " @click="toggleSpec('oncart','integral')">{{app.langReplace('加入购物车')}}</view>
				<view class=" action_btn no-border " @click="toggleSpec('onbuy','integral')">{{app.langReplace('立即兑换')}}</view>
			</view>
		</view>
		<!--end 底部按钮 -->
		<goodsSpec :specClass="specClass" @selectSpec="selectSpec" @toggleSpec="toggleSpec" :specSelected="specSelected"
		 :selectGoods="selectGoods" :selectGoodsPrice="selectGoodsPrice" :selectGoodSmarketPrice="selectGoodSmarketPrice"
		 :selectGoodsNumber="selectGoodsNumber" :selectGoodsImg="selectGoodsImg" :specList="specList" :specChildList="specChildList"
		:selectSkuId="selectSkuId" :prom_id="prom_id" :prom_type="prom_type" :byType="byType" :exType="exType"></goodsSpec>

	</view>
</template>

<script>
	import goodsSpec from '@/pages/shop/goods/spec';
	export default {
		components: {
			goodsSpec,
		},
		data() {
			return {
				ig_id:0,
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
				exType:'integral',
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
				times:{},
				sound: false,
				mute:'/static/public/images/video_icon/mute.png',
				voice:'/static/public/images/video_icon/voice.png',
			};
		},
		async onLoad(options) {
			let title = this.app.langReplace('积分商品');
			uni.setNavigationBarTitle({
			　　title:title
			})
			this.ig_id = options.ig_id;
			this.getGoodsInfo();
			this.loadCartNum();
		},
		onShow() {},
		computed: {
			getOpacity() {
				return this.scrollTop > 60 ? 1 : 0;
			}
		},
		methods: {
			goUrl(url){
				this.app.goPage(url);
			},
			//获取商品信息
			getGoodsInfo() {
				this.$u.post('integral/api.goods/info', {
					'ig_id': this.ig_id
				}).then(res => {
					this.selectGoods = res.data.goods;
					this.goods_id = this.selectGoods.goods_id;
					this.imgList = this.selectGoods.imgList;
					this.desc = this.selectGoods.m_goods_desc;
					if (this.selectGoods.is_spec == 0) {
						this.selectGoodsImg = this.config.baseUrl + this.selectGoods.goods_thumb;
						this.selectGoodsPrice = this.selectGoods.show_integral;
						this.selectGoodSmarketPrice = this.selectGoods.sale_price;
						this.showGoodsPrice = this.selectGoods.show_integral;
						this.showGoodSmarketPrice = this.selectGoods.sale_price;
						this.selectGoodsNumber = this.selectGoods.goods_number;
					} else {
						this.specList = this.selectGoods.specList;
						this.specChildList = this.selectGoods.specChildList;
						this.specSelected = [];
						this.showGoodsPrice = this.selectGoods.show_integral
						this.showGoodSmarketPrice = this.selectGoods.sale_price;
						this.showSelectSpec();
					}
					this.getComment();
				})
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
						that.commentTop = res.top - 50;
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
					this.loadCartNum();
					this.specClass = 'hide';
					setTimeout(() => {
						this.specClass = 'none';
					}, 250);
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
						if (item.sale_price > 0){
							this.selectSkuId = parseInt(item.sku_id);
							this.selectGoodsPrice = item.integral;
							this.selectGoodSmarketPrice = item.sale_price;
							this.selectGoodsNumber = item.goods_number;
						}
						return false;
					}
				})
				if (this.selectSkuId < 1){
					this.selectGoodsImg = this.config.baseUrl + this.selectGoods.goods_thumb;
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
				this.$u.post('integral/api.flow/getCartInfo').then(res => {
					this.cartNum = res.data.num;
				})
			},
			changevoice(){
				this.sound = this.sound == false ? true : false;
			},
			stopPrevent() {}
		},

	}
</script>

<style lang='scss'>
	@import '@/pages/shop/goods/info.scss';
	.times text{
		font-size: 23rpx;
		margin:0rpx 6rpx;
		padding:4rpx;
		background-color: #FF0000;
		color: #FFFFFF;
	}
</style>
