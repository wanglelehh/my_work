<template>
	<view class="category-search" @click="">
	    <view class="weui-flex">
	        <view class="weui-flex__item category-search-form" @click="app.goPage('/pages/shop/goods/search')">
	            <view class="flex-head-search"  :style="{background:diyitem.style.background,padding:diyitem.style.paddingtop+'px '+ diyitem.style.paddingleft+'px'}">
	                <view  class="weui-search-bar__form" :class="diyitem.style.searchstyle"  :style="{background:diyitem.style.inputbackground,color:diyitem.style.color}">
	                    <view class="weui-search-bar__box category-search-box">
	                        <view class="flex-icon-search">
	                            <text class="icon icon-search" :style="{color:diyitem.style.iconcolor}"></text>
	                        </view>
	                        <view class="weui-search-bar__input flex-input" :style="{'text-align':diyitem.style.textalign}">
	                              {{diyitem.params.placeholder}}
	                        </view>
	                    </view>
	                </view>
	            </view>
	        </view>
	    </view>
	</view>
</template>

<script>
	export default {
		name: "tpl_search",
		props: {
			diyitem: {
				type: Object,
				default:function(){
					return {};
				}
			},
		},
		data() {
			return {
			};
		},
		watch: {},
		computed: {},
		methods: {
			
		}
	}
</script>

<style lang='scss'>
</style>
