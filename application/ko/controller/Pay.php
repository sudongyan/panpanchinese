<?php
namespace app\ko\controller;
use think\Controller;
use Hooklife\ThinkphpWechat\Wechat;
use EasyWeChat\Payment\Order;
use Gaoming13\HttpCurl\HttpCurl;
use think\Log;
class Pay extends \think\Controller {
	/**
	 * 自己的paypal账号
	 */
	private $account = '598572331@qq.com';

	/**
	 * paypal支付网关地址
	 */
	private $gateway = 'https://www.paypal.com/cgi-bin/webscr?';

	/**
	 * 生成订单并跳转到Paypal进行支付
	 */
	public function index() {
		/**
		 * 自己的逻辑代码
		 * 判断是否登录、购买的哪个商品、购物车等等逻辑
		 * 当然可以调用Model更简单点
		 * 这里不在赘述
		 */
		if ( request() -> isPost()) {
			$data = input('post.');
			$price = model('Price') -> getOne($data['price_id']);
			if (session('ko_id') == null) {
				return 'error';
			}
			$ExchangeRate = model('ExchangeRate') -> getOne('KRW', 'USD');
			if ($ExchangeRate['time'] < time() - 3600 / 2) {
				$thedata['from'] = 'KRW';
				$thedata['to'] = 'USD';
				$thedata['key'] = config("JUHE_KEY");
				list($body) = HttpCurl::request('http://op.juhe.cn/onebox/exchange/currency', 'get', $thedata);
				$bodys = json_decode($body, true);
				if ($bodys['reason'] == 'successed') {
					$result = $bodys['result'];
					for ($i = 0; $i < count($result); $i++) {
						if ($result[$i]['currencyF'] == $thedata['from'] && $result[$i]['currencyT'] == $thedata['to']) {
							$info['price'] = $result[$i]['result'];
							$ExchangeRate = model('ExchangeRate') -> updateData('KRW', 'USD', $info);
						}
					}
				}
			}
			$order['order_id'] = time() . rand(1000, 9999);
			$order['student_id'] = session('ko_id');
			$order['cover'] = $price['cover'];
			$order['name'] = $price['name'];
			$order['day'] = $price['day'];
			$order['class'] = $price['class'];
			$order['money'] = $price['money'];
			$order['discount'] = $price['discount'];
			$order['type'] = $data['type'];
			$order['discount_money'] = $order['money'] - $price['dmoney'];
			$order['usd_discount_money'] = ($price['usd_money']*100 - $price['usd_dmoney']*100)/100;
			$order['USD'] = $order['usd_discount_money'];
			if($data['coupon_id'] != 0){
				$coupon = model('PriceCoupon') -> getOne($data['coupon_id']);
				if($coupon && $coupon['min_usd_money'] < $order['USD']){
					$order['usd_discount_money'] = $order['USD'] - $coupon['usd_money'];
					$order['USD'] = $order['USD'] - $coupon['usd_money'];
					$order['coupon_id'] = $data['coupon_id'];
				}
			}
			if ($data['type'] == 1) {
				$order['payer_name'] = $data['payer_name'];
				$order['payer_info'] = $data['payer_info'];
			}
			model('PriceOrder') -> addData($order);
			if ($data['type'] == 1) {
				$kakao['orderid'] = $order['order_id'];
				$kakao['type'] = 0;
				action('ko/Base/kakaopriceorder', $kakao);
				$this -> redirect('my/index', array('new' => 1));
			}
			/**
			 * 订单包含哪几种商品、谁买的、什么时间、几件
			 */
			$pp_info = array();
			// 初始化准备提交到Paypal的数据
			$pp_info['cmd'] = '_xclick';
			// 告诉Paypal，我的网站是用的我自己的购物车系统
			$pp_info['business'] = $this -> account;
			// 告诉paypal，我的（商城的商户）Paypal账号，就是这钱是付给谁的
			$pp_info['item_name'] = $price['name'];
			// 用户将会在Paypal的支付页面看到购买的是什么东西，只做显示，没有什么特殊用途，如果是多件商品，则直接告诉用户，只支付某个订单就可以了
			$pp_info['amount'] = $order['USD'];
			// 告诉Paypal，我要收多少钱
			$pp_info['currency_code'] = 'USD';
			// 告诉Paypal，我要用什么货币。这里需要注意的是，由于汇率问题，如果网站提供了更改货币的功能，那么上面的amount也要做适当更改，paypal是不会智能的根据汇率更改总额的
			$pp_info['return'] = 'http://' . $_SERVER["HTTP_HOST"] . url('pay/finish');
			// 当用户成功付款后paypal会将用户自动引导到此页面。如果为空或不传递该参数，则不会跳转
			$pp_info['invoice'] = $order['order_id'];
			$pp_info['charset'] = 'utf-8';
			$pp_info['no_shipping'] = '1';
			$pp_info['no_note'] = '1';
			$pp_info['cancel_return'] = 'http://' . $_SERVER["HTTP_HOST"] . url('pay/not_completed');
			// 当跳转到paypal付款页面时，用户又突然不想买了。则会跳转到此页面
			$pp_info['notify_url'] = 'http://' . $_SERVER["HTTP_HOST"] . url('pay/notify', array('orderid' => $order['order_id']));
			// Paypal会将指定 invoice 的订单的状态定时发送到此URL(Paypal的此操作，是paypal的服务器和我方商城的服务器点对点的通信，用户感觉不到）
			$pp_info['rm'] = '2';

			$paypal_payment_url = $this -> gateway . http_build_query($pp_info);
			header("location: {$paypal_payment_url}");
			unset($pp_info);
		} else {
			return 'error';
		}
	}

	public function not_completed() {
		$this -> redirect('price/index');
	}

	public function finish() {
		$this -> redirect('my/index');
	}

	public function notify() {
		// 由于这个文件只有被Paypal的服务器访问，所以无需考虑做什么页面什么的，这个页面不是给人看的，是给机器看的
		$data = input('param.');
		$order_id = $data['orderid'];
		$order_info = model('PriceOrder') -> getOne($order_id);
		// 由于该URL不仅仅只有Paypal的服务器能访问，其他任何服务器都可以向该方法发起请求。所以要判断请求发起的合法性，也就是要判断请求是否是paypal官方服务器发起的

		// 拼凑 post 请求数据
		$req = 'cmd=_notify-validate';
		// 验证请求
		foreach ($_POST as $k => $v) {
			$v = urlencode(stripslashes($v));
			$req .= '&' . $k . '=' . $v;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/cgi-bin/webscr');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		$res = curl_exec($ch);
		curl_close($ch);

		if ($res && !empty($order_info)) {
			// 本次请求是否由Paypal官方的服务器发出的请求
			if (strcmp($res, 'VERIFIED') == 0) {
				/**
				 * 判断订单的状态
				 * 判断订单的收款人
				 * 判断订单金额
				 * 判断货币类型
				 */
				if (($_POST['payment_status'] != 'Completed' && $_POST['payment_status'] != 'Pending') OR ($_POST['receiver_email'] != $this -> account) OR ($_POST['mc_gross'] != $order_info['USD']) OR ('USD' != $_POST['mc_currency'])) {
					// 如果有任意一项成立，则终止执行。由于是给机器看的，所以不用考虑什么页面。直接输出即可
					exit('fail');
				} else {// 如果验证通过，则证明本次请求是合法的
					$order_status['state'] = 1;
					$order_status['payer_email'] = $_POST['payer_email'];
					$order_status['payment_time'] = time();
					$order_status['end_time'] = time() + $order_info['day'] * 86400;
					model('PriceOrder') -> updateData($order_id, $order_status);
					$kakao['orderid'] = $order_id;
					$kakao['type'] = 1;
					action('ko/Base/kakaopriceorder', $kakao);
					// 更改订单状态
					exit('success');
				}
			} else {
				exit('fail');
			}
		}
	}

	public function wxindex() {
		if ( request() -> isPost()) {
			$data = input('post.');
			$price = model('Price') -> getOne($data['price_id']);
			if (session('ko_id') == null) {
				return 'error';
			}
			$ExchangeRate = model('ExchangeRate') -> getOne('KRW', 'USD');
			if ($ExchangeRate['time'] < time() - 3600 / 2) {
				$thedata['from'] = 'KRW';
				$thedata['to'] = 'USD';
				$thedata['key'] = config("JUHE_KEY");
				list($body) = HttpCurl::request('http://op.juhe.cn/onebox/exchange/currency', 'get', $thedata);
				$bodys = json_decode($body, true);
				if ($bodys['reason'] == 'successed') {
					$result = $bodys['result'];
					for ($i = 0; $i < count($result); $i++) {
						if ($result[$i]['currencyF'] == $thedata['from'] && $result[$i]['currencyT'] == $thedata['to']) {
							$info['price'] = $result[$i]['result'];
							$ExchangeRate = model('ExchangeRate') -> updateData('KRW', 'USD', $info);
						}
					}
				}
			}
			$Student = model('Student') -> getOne(session('ko_id'));
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false && $Student['openid'] == null) {
				
				session('ko_id', null);
				session('ko_openid', null);
				$this -> redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx94cd5f68fa803bb2&redirect_uri=http://m.panpanchinese.cn/ko/my&response_type=code&scope=snsapi_base&state=1#wechat_redirect');
			}
			$order['order_id'] = time() . rand(1000, 9999);
			$order['student_id'] = session('ko_id');
			$order['cover'] = $price['cover'];
			$order['name'] = $price['name'];
			$order['day'] = $price['day'];
			$order['class'] = $price['class'];
			$order['money'] = $price['money'];
			$order['cn_money'] = $price['cn_money'];
			$order['discount'] = $price['discount'];
			$order['discount_money'] = $price['money'] - $price['dmoney'];
			$order['type'] = $data['type'];
			$order['cn_discount_money'] = $price['cn_money']*100 - $price['cn_dmoney']*100;
			$order['USD'] = round(($price['money'] - $price['dmoney']) * $ExchangeRate['price'], 2);
			if($data['coupon_id'] != 0){
				$coupon = model('PriceCoupon') -> getOne($data['coupon_id']);
				if($coupon && $coupon['min_cny_money']*100 < $order['cn_discount_money']){
					$order['cn_discount_money'] = $order['cn_discount_money'] - $coupon['cny_money']*100;
					$order['coupon_id'] = $data['coupon_id'];
				}
			}
			model('PriceOrder') -> addData($order);
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
					$app = Wechat::app();
					$payment = $app -> payment;
					$openid = $Student['openid'];
					$total_money = $order['cn_discount_money'];
					$attributes = [  
      			      'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...  
     			       'body'             => '订单号:'.$order['order_id'],  
      			      'detail'           => '订单号:'.$order['order_id'],  
      			      'out_trade_no'     => $order['order_id'],  
      			      'total_fee'        => $total_money, // 单位：分  
      			      'notify_url'       => 'http://'.$_SERVER['HTTP_HOST'].'/ko/pay/order_notify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址  
      			      'openid'           => $openid, // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，  
     			   ];  
					$Order = new Order($attributes);
					$result = $payment -> prepare($Order);
					$config = array();
					if ($result -> return_code == 'SUCCESS' && $result -> result_code == 'SUCCESS') {
						$prepayId = $result -> prepay_id;
						$config = $payment -> configForJSSDKPayment($prepayId);
					}
					$arr = array('timeStamp' => $config['timestamp'], 'nonceStr' => $config['nonceStr'], 'package' => $config['package'], 'signType' => 'MD5', 'paySign' => $config['paySign'], );
					$this -> assign('data', $arr);
					$this -> assign('config', $config);
					return $this -> fetch();
			}else{
					$app = Wechat::app();
					$payment = $app -> payment;
					$openid = $Student['openid'];
					$total_money = $order['cn_discount_money'];
					$attributes = [  
      			      'trade_type'       => 'NATIVE', // JSAPI，NATIVE，APP...  
     			       'body'             => '订单号:'.$order['order_id'],  
      			      'detail'           => '订单号:'.$order['order_id'],  
      			      'out_trade_no'     => $order['order_id'],  
      			      'total_fee'        => $total_money, // 单位：分  
      			      'notify_url'       => 'http://'.$_SERVER['HTTP_HOST'].'/ko/pay/order_notify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址  
      			      'openid'           => $openid, 
     			   ];  
					$Order = new Order($attributes);
					$result = $payment -> prepare($Order);
					$config = array();
					if ($result -> return_code == 'SUCCESS' && $result -> result_code == 'SUCCESS') {
						$order_status['prepay_id'] = $result['prepay_id'];
						model('PriceOrder') -> updateData($order['order_id'], $order_status);
					}
					$this -> assign('data', $result);
					return $this -> fetch('webwxindex');
			}
		} else {
			$return['result'] = FALSE;
			$return['msg'] = '提交方式错误';
			return json($return);
		}
	}

	public function order_notify() {
		$app = Wechat::app();
		$response = $app -> payment -> handleNotify(function($notify, $successful) {
			$Order = model('PriceOrder') -> getOne($notify['out_trade_no']);
			if (!$Order) {
				return 'Order not exist.';
			}
			if ($Order['state'] != 0) {
				return true;
			}
			if ($successful) {
					$order_status['state'] = 1;
					$order_status['payment_time'] = time();
					$order_status['end_time'] = time() + $Order['day'] * 86400;
					model('PriceOrder') -> updateData($notify['out_trade_no'], $order_status);
					$kakao['orderid'] = $notify['out_trade_no'];
					$kakao['type'] = 1;
					action('ko/Base/kakaopriceorder', $kakao);
			}
			return true;
		});
		$response -> send();
	}
	
	public function validate_wxid() {
		$data = input('param.');
		$Order = model('PriceOrder') -> getWxId($data['prepay_id']);
		if($Order && $Order['state'] == 1){
			$return['result'] = TRUE;
			$return['msg'] = '支付成功';
			return json($return);
		}else{
			$return['result'] = FALSE;
			$return['msg'] = '暂未支付';
			return json($return);
		}
	}
	
	public function kindex() {
		if ( request() -> isPost()) {
			$data = input('post.');
			$price = model('Price') -> getOne($data['price_id']);
			if (session('ko_id') == null) {
				return 'error';
			}
			$ExchangeRate = model('ExchangeRate') -> getOne('KRW', 'USD');
			if ($ExchangeRate['time'] < time() - 3600 / 2) {
				$thedata['from'] = 'KRW';
				$thedata['to'] = 'USD';
				$thedata['key'] = config("JUHE_KEY");
				list($body) = HttpCurl::request('http://op.juhe.cn/onebox/exchange/currency', 'get', $thedata);
				$bodys = json_decode($body, true);
				if ($bodys['reason'] == 'successed') {
					$result = $bodys['result'];
					for ($i = 0; $i < count($result); $i++) {
						if ($result[$i]['currencyF'] == $thedata['from'] && $result[$i]['currencyT'] == $thedata['to']) {
							$info['price'] = $result[$i]['result'];
							$ExchangeRate = model('ExchangeRate') -> updateData('KRW', 'USD', $info);
						}
					}
				}
			}
			$Student = model('Student') -> getOne(session('ko_id'));
			$order['order_id'] = time() . rand(1000, 9999);
			$order['student_id'] = session('ko_id');
			$order['cover'] = $price['cover'];
			$order['name'] = $price['name'];
			$order['day'] = $price['day'];
			$order['class'] = $price['class'];
			$order['money'] = $price['money'];
			$order['cn_money'] = $price['cn_money'];
			$order['discount'] = $price['discount'];
			$order['discount_money'] = $price['money'] - $price['dmoney'];
			$order['type'] = $data['type'];
			$order['cn_discount_money'] = $price['cn_money']*100 - $price['cn_dmoney']*100;
			$order['USD'] = round(($price['money'] - $price['dmoney']) * $ExchangeRate['price'], 2);
			if($data['coupon_id'] != 0){
				$coupon = model('PriceCoupon') -> getOne($data['coupon_id']);
				if($coupon && $coupon['min_money'] < $order['discount_money']){
					$order['discount_money'] = $order['discount_money'] - $coupon['money'];
					$order['coupon_id'] = $data['coupon_id'];
				}
			}
			model('PriceOrder') -> addData($order);
			$thedata['buyer_tel'] = $Student['phone'];
			$thedata['merchant_uid'] = $order['order_id'];
			$thedata['name'] = $order['name'];
			$thedata['amount'] = $order['discount_money'];
			$thedata['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/ko/pay/k_order_notify_ok';
			$this -> assign('data', $thedata);
			return $this -> fetch();
		} else {
			$return['result'] = FALSE;
			$return['msg'] = '提交方式错误';
			return json($return);
		}
	}

	public function k_order_notify() {
		import('iamport-rest-client-php-master.src.iamport', EXTEND_PATH);
		$iamport = new \Iamport('4039066762010540', 'xtb5fyVIAfIBWnjDBoOBUgHPAdXThrOOU0U9yPS84AK0Y3DzssanISci59bnB0bv06FzVyI09s17vFGY');
		$data = input('param.');
#2. merchant_uid 로 주문정보 찾기(가맹점의 주문번호) 用merchant_uid查询订单信息（加盟店的订单号码）
		$result = $iamport->findByMerchantUID($data['merchant_uid']); //IamportResult 를 반환(success, data, error) 返还IamportResult（success,data, error）
		if ( $result->success ) {
	/**
	*	IamportPayment 를 가리킵니다. __get을 통해 API의 Payment Model의 값들을 모두 property처럼 접근할 수 있습니다. 指着IamportPayment。 通过__get，API的所有Payment Model数值可以像property一样接近。
	*	참고 : https://api.iamport.kr/#!/payments/getPaymentByMerchantUid 의 Response Model 参考 : https://api.iamport.kr/#!/payments/getPaymentByMerchantUid 的 Response Model
	*/
			$payment_data = $result->data;
	
			$Order = model('PriceOrder') -> getOne($data['merchant_uid']);
			if (!$Order) {
				return 'Order not exist.';
			}
			if ($Order['state'] != 0) {
				return true;
			}
			# 내부적으로 결제완료 처리하시기 위해서는 (1) 결제완료 여부 (2) 금액이 일치하는지 확인을 해주셔야 합니다.
			$amount_should_be_paid = $Order['discount_money'];
			if ( $payment_data->status == 'paid' && $payment_data->amount == $amount_should_be_paid ) {
			//TODO : 결제성공 처리
					$order_status['state'] = 1;
					$order_status['payment_time'] = time();
					$order_status['end_time'] = time() + $Order['day'] * 86400;
					$order_status['prepay_id'] = $payment_data->imp_uid;
					model('PriceOrder') -> updateData($data['merchant_uid'], $order_status);
					$kakao['orderid'] = $data['merchant_uid'];
					$kakao['type'] = 1;
					action('ko/Base/kakaopriceorder', $kakao);
			$return['result'] = TRUE;
			return json($return);
			}
		} else {
			$return['result'] = FALSE;
			$return['msg'] = '订单错误';
			return json($return);
		}
	}

	public function k_order_notify_ok() {
		import('iamport-rest-client-php-master.src.iamport', EXTEND_PATH);
		$iamport = new \Iamport('4039066762010540', 'xtb5fyVIAfIBWnjDBoOBUgHPAdXThrOOU0U9yPS84AK0Y3DzssanISci59bnB0bv06FzVyI09s17vFGY');
		$data = input('param.');
#2. merchant_uid 로 주문정보 찾기(가맹점의 주문번호) 用merchant_uid查询订单信息（加盟店的订单号码）
		$result = $iamport->findByMerchantUID($data['merchant_uid']); //IamportResult 를 반환(success, data, error) 返还IamportResult（success,data, error）
		if ( $result->success ) {
	/**
	*	IamportPayment 를 가리킵니다. __get을 통해 API의 Payment Model의 값들을 모두 property처럼 접근할 수 있습니다. 指着IamportPayment。 通过__get，API的所有Payment Model数值可以像property一样接近。
	*	참고 : https://api.iamport.kr/#!/payments/getPaymentByMerchantUid 의 Response Model 参考 : https://api.iamport.kr/#!/payments/getPaymentByMerchantUid 的 Response Model
	*/
			$payment_data = $result->data;
			$Order = model('PriceOrder') -> getOne($data['merchant_uid']);
			if (!$Order) {
				return 'Order not exist.';
			}
			if ($Order['state'] != 0) {
				$this -> redirect('my/price_order');
			}
			# 내부적으로 결제완료 처리하시기 위해서는 (1) 결제완료 여부 (2) 금액이 일치하는지 확인을 해주셔야 합니다.
			$amount_should_be_paid = $Order['discount_money'];
			if ( $payment_data->status == 'paid' && $payment_data->amount == $amount_should_be_paid ) {
			//TODO : 결제성공 처리
					$order_status['state'] = 1;
					$order_status['payment_time'] = time();
					$order_status['end_time'] = time() + $Order['day'] * 86400;
					$order_status['prepay_id'] = $payment_data->imp_uid;
					model('PriceOrder') -> updateData($data['merchant_uid'], $order_status);
					$kakao['orderid'] = $data['merchant_uid'];
					$kakao['type'] = 1;
					action('ko/Base/kakaopriceorder', $kakao);
			$this -> redirect('my/price_order');
			}
		} else {
			$return['result'] = FALSE;
			$return['msg'] = '订单错误';
			return json($return);
		}
	}

	public function order() {
		if ( request() -> isGet()) {
			$data = input('param.');
			$price = model('Price') -> getOne($data['price_id']);
			if (session('ko_id') == null) {
				return 'error';
			}
			$ExchangeRate = model('ExchangeRate') -> getOne('KRW', 'USD');
			if ($ExchangeRate['time'] < time() - 3600 / 2) {
				$thedata['from'] = 'KRW';
				$thedata['to'] = 'USD';
				$thedata['key'] = config("JUHE_KEY");
				list($body) = HttpCurl::request('http://op.juhe.cn/onebox/exchange/currency', 'get', $thedata);
				$bodys = json_decode($body, true);
				if ($bodys['reason'] == 'successed') {
					$result = $bodys['result'];
					for ($i = 0; $i < count($result); $i++) {
						if ($result[$i]['currencyF'] == $thedata['from'] && $result[$i]['currencyT'] == $thedata['to']) {
							$info['price'] = $result[$i]['result'];
							$ExchangeRate = model('ExchangeRate') -> updateData('KRW', 'USD', $info);
						}
					}
				}
			}
			switch ($data['type']) {
				case 0 :
					$type['name'] = 'PayPal';
					$type['currency'] = 'USD';
					$price['themoney'] = $price['usd_money'];
					$price['thedmoney'] = $price['usd_dmoney'];
					break;
				case 1 :
					$type['name'] = '무통장 입금(입금자 성명)';
					$type['currency'] = 'KRW';
					$price['themoney'] = $price['money'];
					$price['thedmoney'] = $price['dmoney'];
					break;
				case 2 :
					$type['name'] = 'wechat';
					$type['currency'] = 'CNY'; 
					$price['themoney'] = $price['cn_money'];
					$price['thedmoney'] = $price['cn_dmoney'];
					break;
				case 3 :
					$type['name'] = '신용/체크카드(페이팔)';
					$type['currency'] = 'KRW';
					$price['themoney'] = $price['money'];
					$price['thedmoney'] = $price['dmoney'];
					break;
			}
			date_default_timezone_set('UTC');
			$my = model('Student') -> getOne(session('ko_id'));
			$timezone = model('Timezone') -> getOne($my['time_zone']);
			$time = $timezone['time'] * 3600;
			$price['the_start_time'] = date("Y-m-d", time() + $time);
			$price['the_end_time'] = date("Y-m-d", time() + $time + $price['day'] * 86400);
			$price['paymoney'] = ($price['themoney']*100 - $price['thedmoney']*100)/100;
			$this -> assign('type', $type);
			$this -> assign('thetype', $data['type']);
			$this -> assign('price', $price);
			return $this -> fetch();
		} else {
			$return['result'] = FALSE;
			$return['msg'] = '提交方式错误';
			return json($return);
		}
	}

	public function get_coupon() {
		if ( request() -> isGet()) {
			$data = input('param.');
			$list = model('PriceCoupon') -> getCodeList($data['code'],$data['type'],$data['paymoney']);
			if (session('ko_id') == null) {
				return 'error';
			}
			if($data['type'] == 1 || $data['type'] == 3){
				$last_names = array_column($list,'min_money');
				array_multisort($last_names,SORT_DESC,$list);
			}else if($data['type'] == 0){
				$last_names = array_column($list,'min_usd_money');
				array_multisort($last_names,SORT_DESC,$list);
			}else{
				$last_names = array_column($list,'min_cny_money');
				array_multisort($last_names,SORT_DESC,$list);
			}
			$coupon = FALSE;
			for ($i = 0; $i < count($list); $i++) {
				if($i == 0){
					$coupon = $list[$i];
				}
			}
			if(!$coupon || $coupon['end_time'] < time() ||$coupon['state'] != 1){
				$return['result'] = FALSE;
				$return['msg'] = '优惠码错误或已失效';
				return json($return);
			}else{
				$return['result'] = TRUE;
				$return['data'] = $coupon;
				return json($return);
			}
		} else {
			$return['result'] = FALSE;
			$return['msg'] = '提交方式错误';
			return json($return);
		}
	}
}
