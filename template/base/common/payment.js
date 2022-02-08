export default {
	// 判断是否公众号（微信H5）
	isWechat() {
		// #ifdef H5
		const ua = window.navigator.userAgent.toLowerCase();
		if (ua.match(/micromessenger/i) == 'micromessenger') {
			return true;
		} 
		// #endif
		return false;
	},
	/*
	 *@des 微信支付
	 */
	async weixinPay(_this) {
		// #ifdef MP-QQ
			_this.$u.toast('QQ小程序暂不支持');
			return;
		// #endif
		
		let route = '/pages/public/pay/paySuccess';
		if (_this.options.route){
			route = _this.options.route;
		}
		
		let trade_type = '';
		// #ifdef H5
		trade_type = 'js';
		if (!this.isWechat()) {
			trade_type = 'h5';
		}
		// #endif
		await _this.$u.post('publics/api.pay/getCode', {
				_from:_this.options.from,
				payType: _this.options.payType,
				order_id: _this.options.order_id,
				pay_code:_this.options. pay_code,
				trade_type: trade_type,
				openid: _this.app.getWxOpenId()
			})
			.then(res => {
				// #ifdef APP-PLUS
				uni.requestPayment({
					provider: 'wxpay', // 微信支付
					orderInfo: JSON.stringify(res.data.payConfig),
					success: function() {
						_this.app.showModal('支付成功.',route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id,-1);
					},
					fail: function(err) {
						console.log('err', err);
						_this.app.showModal('支付已取消.',-1);
					}
				});
				// #endif
				// #ifndef APP-PLUS
					// #ifdef H5
					if (trade_type == 'js'){
						//使用原生的，避免初始化appid问题  
						WeixinJSBridge.invoke('getBrandWCPayRequest', {  
							appId:res.data.payConfig['appId'],  
							timeStamp: res.data.payConfig['timeStamp'],  
							nonceStr: res.data.payConfig['nonceStr'], // 支付签名随机串，不长于 32 位  
							package: res.data.payConfig['package'], // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=\*\*\*）  
							signType: res.data.payConfig['signType'], // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'  
							paySign: res.data.payConfig['paySign'], // 支付签名  
						},  
						function(res) {  
							var msg = res.err_msg ?res.err_msg :res.errMsg;  
							//WeixinJSBridge.log(msg);  
							switch (msg) {  
								case 'get_brand_wcpay_request:ok': //支付成功时  
									_this.app.showModal('支付成功.',route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id,-1);
									break;  
								default: //支付失败时  
									var beforeUrl = uni.getStorageSync("beforeUrl");
								   // WeixinJSBridge.log('支付失败!'+msg+',请返回重试.');
									if (msg == 'get_brand_wcpay_request:cancel'){
										_this.app.showModal('支付过程中用户取消.',beforeUrl);
									}else{
										 _this.app.showModal(res.err_code+' - '+res.err_desc+' - '+res.err_msg,beforeUrl);
									}
								break;  
							}  
						})
					}else{
						//微信H5支付
						uni.showModal({
							title: '提示',
							confirmText:'已支付',
							cancelText: '放弃支付',
							content: '正在支付...',
							showCancel: true,
							success: function(res) {
								return _this.app.goPage(route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id);
							}
						})
						window.location.href = res.data.payConfig.result;
					}	
					// #endif
					// #ifdef MP-WEIXIN
					uni.requestPayment({
						...res.data.payConfig,
						timeStamp: res.data.payConfig.timeStamp,
						success: () => {
							_this.app.showModal('支付成功.',route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id,-1);
						},
						fail: res => {
							console.log(res);
							_this.app.showModal('支付已取消.',-1);
						},
						complete: () => {}
					});
					// #endif
				// #endif
			}).catch(res=>{
				_this.app.showModal(res.msg,-1);
			});
		
	},
/*
	 *@des 微信APP支付
	 */
	async appWeixinPay(_this) {
		let route = '/pages/public/pay/paySuccess';
		if (_this.options.route){
			route = _this.options.route;
		}
		await _this.$u.post('publics/api.pay/getCode', {
				_from:_this.options.from,
				payType: _this.options.payType,
				order_id: _this.options.order_id,
				pay_code:_this.options.pay_code,
			})
			.then(res => {
				let platform = uni.getSystemInfoSync().platform;
				if (platform == 'android'){
					window.auc.wxPay(res.data.payConfig.pay_data,route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id)
				}else{
					window.app.wxPay(res.data.payConfig.pay_data,route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id);//IOS调用方式
				}

				return
			}).catch(res=>{
				_this.app.showModal(res.msg,-1);
			})
	},
	/*
	 *@des 支付宝APP支付
	 */
	async alipayApp(_this) {
		let route = '/pages/public/pay/paySuccess';
		if (_this.options.route){
			route = _this.options.route;
		}
		await _this.$u.post('publics/api.pay/getCode', {
			_from:_this.options.from,
			payType: _this.options.payType,
			order_id: _this.options.order_id,
			pay_code:_this.options.pay_code,
		})
		.then(res => {
			let platform = uni.getSystemInfoSync().platform;
			aliPayCallbackUrl = route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id;
			if (platform == 'android') {
				window.auc.AliPay(res.data.payConfig.orderString);
			}else{
				window.app.AliPay(res.data.payConfig.orderString); //ios
			}
		}).catch(res=>{
			_this.app.showModal(res.msg,-1);
		})
	},
	/*
	 *@des 支付宝支付
	 *
	 */
	async aliPay(_this) {
		// #ifdef MP-QQ
		_this.$u.toast('QQ小程序不支持支付宝');
		return;
		// #endif
		// #ifdef MP-WEIXIN
		_this.$u.toast('微信小程序不支持支付宝');
		return;
		// #endif
		let route = '/pages/public/pay/paySuccess';
		if (_this.options.route){
			route = _this.options.route;
		}
		await _this.$u.post('publics/api.pay/getCode', {
				_from:_this.options.from,
				payType: _this.options.payType,
				order_id: _this.options.order_id,
				pay_code:_this.options.pay_code,
			})
			.then(res => {
				// #ifdef H5
				uni.showModal({
					title: '提示',
					confirmText:'已支付',
					cancelText: '放弃支付',
					content: '正在支付...',
					showCancel: true,
					success: function(res) {
						return _this.app.goPage(route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id);
					}
				})
				window.location.href = res.data.payConfig;
				// #endif
				// #ifdef APP-PLUS
				uni.requestPayment({
					provider: 'alipay',
					orderInfo: res.data.payConfig, //微信、支付宝订单数据
					success: function() {
						_this.app.showModal('支付成功.',route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id,-1);
					},
					fail: function(err) {
						_this.app.showModal('支付已取消.',-1);
					}
				});
				// #endif
			}).catch(res=>{
				_this.app.showModal(res.msg,-1);
			})
	},

	/*
	 *@des 余额支付
	 */
	async balancePay(_this) {
		let route = '/pages/public/pay/paySuccess';
		if (_this.options.route){
			route = _this.options.route;
		}
		await _this.$u.post('publics/api.pay/balancePay', {
				_from:_this.options.from,
				payType: _this.options.payType,
				order_id: _this.options.order_id,
				pay_code:_this.options.pay_code,
				pay_password:_this.pay_password,
			})
			.then(res => {
				if (res.code == 0){
					this.$u.toast(res.msg);
					return false;
				}
				_this.app.goPage(route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id,-1);
			})
	},
	/*
	 *@des 货款支付
	 */
	async goodsMoneyPay(_this) {
		let route = '/pages/public/pay/paySuccess';
		if (_this.options.route){
			route = _this.options.route;
		}
		await _this.$u.post('publics/api.pay/goodsMoneyPay', {
				_from:_this.options.from,
				payType: _this.options.payType,
				order_id: _this.options.order_id,
				pay_code:_this.options.pay_code,
				pay_password:_this.pay_password,
			})
			.then(res => {
				if (res.code == 0){
					this.$u.toast(res.msg);
					return false;
				}
				_this.app.goPage(route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id,-1);
			})
	},
	/*
	 *@des 升级货款支付
	 */
	async uplevelGoodsMoneyPay(_this) {
		let route = '/pages/public/pay/paySuccess';
		if (_this.options.route){
			route = _this.options.route;
		}
		await _this.$u.post('publics/api.pay/uplevelGoodsMoneyPay', {
				_from:_this.options.from,
				payType: _this.options.payType,
				order_id: _this.options.order_id,
				pay_code:_this.options.pay_code,
				pay_password:_this.pay_password,
			})
			.then(res => {
				if (res.code == 0){
					this.$u.toast(res.msg);
					return false;
				}
				_this.app.goPage(route+'?from='+_this.options.from+'&payType='+_this.options.payType+'&order_id='+_this.options.order_id,-1);
			})
	}
};
