<template>
	<block v-if="diyitem.params.row==1">
		<view class="fui-cube" :style="{background:diyitem.style.background}">
			<view class="fui-cube-left">
				<view class="navigator" @click="app.goPage(diyitem.data[0].linkurl)" :style="getStyle">
					<image :src="diyitem.data[0].imgurl"></image>
				</view>
			</view>
			<view class="fui-cube-right">
				<block v-if="diyitem.data.length==2">
					<view class="navigator" @click="app.goPage(diyitem.data[1].linkurl)" :style="getStyle">
						<image :src="diyitem.data[1].imgurl"></image>
					</view>
				</block>
				<block v-if="diyitem.data.length>2">
					<view class="fui-cube-right1">
						<view class="navigator" @click="app.goPage(diyitem.data[1].linkurl)" :style="getStyle">
							<image :src="diyitem.data[1].imgurl"></image>
						</view>
					</view>
					<view class="fui-cube-right2">
						<block v-if="diyitem.data.length==3">
							<view class="navigator"  @click="app.goPage(diyitem.data[2].linkurl)" :style="getStyle">
								<image :src="diyitem.data[2].imgurl"></image>
							</view>
						</block>
						<view v-if="diyitem.data.length>3">
							<view class="left">
								<view class="navigator"  @click="app.goPage(diyitem.data[2].linkurl)" :style="getStyle">
									<image :src="diyitem.data[2].imgurl"></image>
								</view>
							</view>
						</view>
						<block v-if="diyitem.data.length==4">
							<view class="right">
								<view class="navigator"  @click="app.goPage(diyitem.data[3].linkurl)" :style="getStyle">
									<image :src="diyitem.data[3].imgurl"></image>
								</view>
							</view>
						</block>
					</view>
				</block>
			</view>
		</view>
	</block>
	<view v-else class="fui-picturew" :class="'row-'+diyitem.params.row" :style="{background:diyitem.style.background}">
		<block v-if="diyitem.params.showtype == 0">
			<block v-for="(childitem, childid) in diyitem.data" :key="childid">
				<view class="item" :style="{padding:app.pxToRpx(diyitem.style.paddingtop)+'rpx '+app.pxToRpx(diyitem.style.paddingleft)+'rpx','box-sizing':'border-box'}">
					<view class="navigator" @click="app.goPage(childitem.linkurl)" :style="{padding:app.pxToRpx(diyitem.style.paddingtop)+'rpx '+app.pxToRpx(diyitem.style.paddingleft)+'rpx'}">
						<image mode="widthFix" :src="childitem.imgurl" style="padding:0;margin:0"></image>
					</view>
				</view>
			</block>
		</block>
		<block v-else>
			<swiper :duration="duration" :interval="interval" :indicator-dots="diyitem.style.showdot==1?true:false" :style="{height:app.pxToRpx(diyitem.style.height)+'rpx',background:diyitem.style.background,padding:'20rpx 0'}">
				<block v-for="(childitem, childid) in diyitem.data_temp" :key="childid">
					<swiper-item class="fui-picturew">
						<block  v-for="(pic_item, pic_index) in childitem" :key="pic_index">
							<view class="item">
								<view  class="navigator" @click="app.goPage(pic_item.linkurl)" :style="{padding:app.pxToRpx(diyitem.style.paddingtop)+'rpx '+app.pxToRpx(diyitem.style.paddingleft)+'rpx'}">
									<image mode="widthFix" :src="pic_item.imgurl"></image>
								</view>
							</view>
						</block>
					</swiper-item>
				</block>
			</swiper>
		</block>
	</view>
</template>

<script>
	export default {
		name: "tpl_picturew",
		props: {
			diyitem: {
				type: Object,
				default: function() {
					return {};
				}
			},
		},
		data() {
			return {
				getStyle: this.evelStyle(),
				interval: 5e3,
				duration: 500,
			};
		},
		watch: {},
		computed: {

		},
		methods: {
			evelStyle() {
				let padding = 'padding:';
				if (this.diyitem.style.paddingtop == 0) {
					padding += '0 ';
				} else {
					padding += this.app.pxToRpx(this.diyitem.style.paddingtop) + 'rpx ';
				}
				if (this.diyitem.style.paddingleft == 0) {
					padding += '0 ';
				} else {
					padding += this.app.pxToRpx(this.diyitem.style.paddingleft) + 'rpx ';
				}
				return padding;
			}
		}
	}
</script>

<style lang='scss'>
</style>
