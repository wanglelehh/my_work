<template>
	<view :style="{background:diyitem.style.background}">
		<view v-if="diyitem.params.showtype==0">
			<view class="fui-pictures single" :class="'row-'+diyitem.params.rownum" >
				<block v-for="(item, idx) in diyitem.data" :key="idx">
					<view class="item" @click="app.goPage(item.linkurl)" :style="{padding:app.pxToRpx(diyitem.style.paddingtop)+'rpx '+app.pxToRpx(diyitem.style.paddingleft)+'rpx'}">
						<view class="image" :style="{'text-align':diyitem.style.titlealign,color:diyitem.style.titlecolor}">
							<image mode="widthFix" :src="item.imgurl"></image>
							<view class="title" :style="{color:item.titlecolor}">{{item.title}}</view>
						</view>
						<view class="text" :style="{'text-align':diyitem.style.textalign,color:diyitem.style.textcolor}">{{item.text}}</view>
					</view>
				</block>
			</view>
		</view>
		<view v-else>
				<swiper :duration="duration" :interval="interval" :indicator-dots="diyitem.params.showbtn==1?true:false" :style="{height:app.pxToRpx(diyitem.params.height)+'rpx',background:diyitem.style.background}">
					<block v-for="(childitem, childid) in diyitem.data_temp" :key="childid">
						<swiper-item class="fui-pictures" :class="'row-'+diyitem.params.rownum">
							<block v-for="(item, idx) in childitem" :key="idx">
								<view class="item" @click="app.goPage(item.linkurl)" :style="{padding:app.pxToRpx(diyitem.style.paddingtop)+'rpx '+app.pxToRpx(diyitem.style.paddingleft)+'rpx'}">
									<view class="image" :style="{'text-align':diyitem.style.titlealign,color:diyitem.style.titlecolor}">
										<image mode="widthFix" :src="item.imgurl"></image>
										<view class="title" :style="{color:item.titlecolor}">{{item.title}}</view>
									</view>
									<view class="text" :style="{'text-align':diyitem.style.textalign,color:diyitem.style.textcolor}">{{item.text}}</view>
								</view>
							</block>
						</swiper-item>
					</block>
				</swiper>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: "tpl_pictures",
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
				interval: 5e3,
				duration: 500,
			};
		},
		watch: {},
		computed: {},
		methods: {}
	}
</script>

<style lang='scss'>
</style>
