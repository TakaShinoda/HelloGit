


<?php
/****************************************************


PageRankを用いたTwitterアカウントの影響力


*****************************************************/


$fst_id = " ";	//評価対象のTwitterアカウントのユーザID


set_time_limit(0);
echo str_repeat(' ', 1024);

//////***評価対象のアカウントのフォロワー数***///////

	// 設定
	$api_key = '' ;		// APIキー
	$api_secret = '' ;		// APIシークレット
	$access_token = '' ;		// アクセストークン
	$access_token_secret = '' ;		// アクセストークンシークレット
	$request_url = 'https://api.twitter.com/1.1/users/show.json' ;		// エンドポイント
	$request_method = 'GET' ;

	// パラメータA (オプション)
	$params_a = array(
		"user_id" => $fst_id,
	) ;

	// キーを作成する (URLエンコードする)
	$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;

	// パラメータB (署名の材料用)
	$params_b = array(
		'oauth_token' => $access_token ,
		'oauth_consumer_key' => $api_key ,
		'oauth_signature_method' => 'HMAC-SHA1' ,
		'oauth_timestamp' => time() ,
		'oauth_nonce' => microtime() ,
		'oauth_version' => '1.0' ,
	) ;



	// パラメータAとパラメータBを合成してパラメータCを作る
	$params_c = array_merge( $params_a , $params_b ) ;

	// 連想配列をアルファベット順に並び替える
	ksort( $params_c ) ;

	// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
	$request_params = http_build_query( $params_c , '' , '&' ) ;

	// 一部の文字列をフォロー
	$request_params = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , $request_params ) ;

	// 変換した文字列をURLエンコードする
	$request_params = rawurlencode( $request_params ) ;

	// リクエストメソッドをURLエンコードする
	// ここでは、URL末尾の[?]以下は付けないこと
	$encoded_request_method = rawurlencode( $request_method ) ;
 
	// リクエストURLをURLエンコードする
	$encoded_request_url = rawurlencode( $request_url ) ;
 
	// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
	$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;

	// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
	$hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;

	// base64エンコードして、署名[$signature]が完成する
	$signature = base64_encode( $hash ) ;

	// パラメータの連想配列、[$params]に、作成した署名を加える
	$params_c['oauth_signature'] = $signature ;

	// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
	$header_params = http_build_query( $params_c , '' , ',' ) ;

	// リクエスト用のコンテキスト
	$context = array(
		'http' => array(
			'method' => $request_method , // リクエストメソッド
			'header' => array(			  // ヘッダー
				'Authorization: OAuth ' . $header_params ,
			) ,
		) ,
	) ;

	// パラメータがある場合、URLの末尾に追加
	if( $params_a ) {
		$request_url .= '?' . http_build_query( $params_a ) ;
	}

	// オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
//	if( $params_a ) {
//		$context['http']['content'] = http_build_query( $params_a ) ;
//	}

	// cURLを使ってリクエスト
	$curl = curl_init() ;
	curl_setopt( $curl, CURLOPT_URL , $request_url ) ;
	curl_setopt( $curl, CURLOPT_HEADER, 1 ) ; 
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;	// メソッド
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false ) ;	// 証明書の検証を行わない
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true ) ;	// curl_execの結果を文字列で返す
	curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] ) ;	// ヘッダー
//	if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) ) {		// GETの場合は不要
//		curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;	// リクエストボディ
//	}
	curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;	// タイムアウトの秒数
	$res1 = curl_exec( $curl ) ;
	$res2 = curl_getinfo( $curl ) ;
	curl_close( $curl ) ;

	// 取得したデータ
	$json = substr( $res1, $res2['header_size'] ) ;		// 取得したデータ(JSONなど)
	$header = substr( $res1, 0, $res2['header_size'] ) ;	// レスポンスヘッダー (検証に利用したい場合にどうぞ)

	// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
	// $json = file_get_contents( $request_url , false , stream_context_create( $context ) ) ;

	// JSONをオブジェクトに変換
	$obj = json_decode( $json ) ;

	// HTML用
	$html = '' ;

	// タイトル
	$html .= '<h1 style="text-align:center; border-bottom:1px solid #555; padding-bottom:12px; margin-bottom:48px; color:#D36015;">フォロワー数取得</h1>' ;

	// エラー判定
	if( !$json || !$obj ) {
		$html .= '<h2>エラー内容</h2>' ;
		$html .= '<p>データを取得することができませんでした…。設定を見直して下さい。</p>' ;
	}



$a = strpos($json, "followers_count");
$b =  substr($json,$a + 17);
$count = 0;
$count_up = $a + 17;
$z = 0;

$a_num = "";

while (1) {
	
	$a_num .= $json[$count_up];

	$count_up++;
	if(strcmp($json[$count_up], ',') == 0)
		break;


}



echo "Aのフォロワー数";

echo $a_num;

	// 出力 (本稼働時はHTMLのヘッダー、フッターを付けよう)
	echo $html ;


//////////////////////////////////////////////////////////////////


 $loop_num = 3;
// pagerank関数
function pagerank($usr_id,$flw_num){
global $loop_num;
	$loop_num--;
	

/////////////***フォロワーのユーザID***///////////////

	// 設定
	$api_key = '' ;		// APIキー
	$api_secret = '' ;		// APIシークレット
	$access_token = '' ;		// アクセストークン
	$access_token_secret = '' ;		// アクセストークンシークレット
	$request_url = 'https://api.twitter.com/1.1/followers/ids.json' ;		// エンドポイント
	$request_method = 'GET' ;



	// パラメータA (オプション)
	$params_a = array(
		"user_id" => $usr_id,
	) ;

	// キーを作成する (URLエンコードする)
	$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;

	// パラメータB (署名の材料用)
	$params_b = array(
		'oauth_token' => $access_token ,
		'oauth_consumer_key' => $api_key ,
		'oauth_signature_method' => 'HMAC-SHA1' ,
		'oauth_timestamp' => time() ,
		'oauth_nonce' => microtime() ,
		'oauth_version' => '1.0' ,
	) ;

	// パラメータAとパラメータBを合成してパラメータCを作る
	$params_c = array_merge( $params_a , $params_b ) ;

	// 連想配列をアルファベット順に並び替える
	ksort( $params_c ) ;

	// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
	$request_params = http_build_query( $params_c , '' , '&' ) ;

	// 一部の文字列をフォロー
	$request_params = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , $request_params ) ;

	// 変換した文字列をURLエンコードする
	$request_params = rawurlencode( $request_params ) ;

	// リクエストメソッドをURLエンコードする
	// ここでは、URL末尾の[?]以下は付けないこと
	$encoded_request_method = rawurlencode( $request_method ) ;
 
	// リクエストURLをURLエンコードする
	$encoded_request_url = rawurlencode( $request_url ) ;
 
	// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
	$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;

	// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
	$hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;

	// base64エンコードして、署名[$signature]が完成する
	$signature = base64_encode( $hash ) ;

	// パラメータの連想配列、[$params]に、作成した署名を加える
	$params_c['oauth_signature'] = $signature ;

	// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
	$header_params = http_build_query( $params_c , '' , ',' ) ;

	// リクエスト用のコンテキスト
	$context = array(
		'http' => array(
			'method' => $request_method , // リクエストメソッド
			'header' => array(			  // ヘッダー
				'Authorization: OAuth ' . $header_params ,
			) ,
		) ,
	) ;

	// パラメータがある場合、URLの末尾に追加
	if( $params_a ) {
		$request_url .= '?' . http_build_query( $params_a ) ;
	}

	// オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
//	if( $params_a ) {
//		$context['http']['content'] = http_build_query( $params_a ) ;
//	}

	// cURLを使ってリクエスト
	$curl = curl_init() ;
	curl_setopt( $curl, CURLOPT_URL , $request_url ) ;
	curl_setopt( $curl, CURLOPT_HEADER, 1 ) ; 
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;	// メソッド
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false ) ;	// 証明書の検証を行わない
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true ) ;	// curl_execの結果を文字列で返す
	curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] ) ;	// ヘッダー
//	if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) ) {		// GETの場合は不要
//		curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;	// リクエストボディ
//	}
	curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;	// タイムアウトの秒数
	$res1 = curl_exec( $curl ) ;
	$res2 = curl_getinfo( $curl ) ;
	curl_close( $curl ) ;

	// 取得したデータ
	$json = substr( $res1, $res2['header_size'] ) ;		// 取得したデータ(JSONなど)
	$header = substr( $res1, 0, $res2['header_size'] ) ;	// レスポンスヘッダー (検証に利用したい場合にどうぞ)

	// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
	// $json = file_get_contents( $request_url , false , stream_context_create( $context ) ) ;

	// JSONをオブジェクトに変換
	$obj = json_decode( $json ) ;

	// HTML用
	$html = '' ;

	// タイトル
	$html .= '<h1 style="text-align:center; border-bottom:1px solid #555; padding-bottom:12px; margin-bottom:48px; color:#D36015;">GET followers/ids</h1>' ;

	// エラー判定
	if( !$json || !$obj ) {
		$html .= '<h2>エラー内容</h2>' ;
		$html .= '<p>データを取得することができませんでした…。設定を見直して下さい。</p>' ;
	}


	$len = 0;
	$u2_id_tmp = '';
	$s = 0;

	while (1) {
		$i = 0;
		$json_tmp = substr($json, 8 + $len + $s);

		while (strcmp($json_tmp[$i], ',') != 0) {
			$u2_id_tmp .= $json_tmp[$i];
			$i++;
				if($i > 5000){
					
					break;	
				}
			if (strcmp($json_tmp[$i], ']') == 0) {
				goto a;
			}
		}

		$u2_id[$s] = $u2_id_tmp;
		$len += strlen($u2_id_tmp);
		$u2_id_tmp = '';
		$json_tmp = '';
		$s++;
	}


a:	//goto

	$u2_id[$s] = $u2_id_tmp;

	// 検証用
	$html .= '<h2>取得したデータ</h2>' ;
	$html .= '<p>下記のデータを取得できました。</p>' ;
	$html .= '<h3>対象アカウントのフォロワーのユーザID一覧</h3>' ;

	for ($t=0; $t < $s + 1; $t++) { 
		$html .= '<p>' .$u2_id[$t].'</p>';
	}


	$cnt = $s + 1;	//test 
	$html .= "aa" . $len;  //test
	$html .= '<p>'. '<h3>' ."フォロワーID取得終了（最大5000）".'</h3>'.'</p>';   //test

	//$html .= 	'<h3>ボディ(JSON)</h3>' ;
	//$html .= 	'<p><textarea style="width:80%" rows="8">' . $json . '</textarea></p>' ;
	//$html .= 	'<h3>レスポンスヘッダー</h3>' ;
	//$html .= 	'<p><textarea style="width:80%" rows="8">' . $header . '</textarea></p>' ;


	// 出力 (本稼働時はHTMLのヘッダー、フッターを付けよう)
	echo $html ;

	@ob_flush();
	@flush();

echo $cnt;


/*プロフィール情報よりフォロー数取得*/

$uuu = '<p>'.  '</p>';
echo $uuu; 

/*乱数*/
for($w=0; $w < 5; $w++){

	$rand_n[$w]=rand(0,$cnt-1);
	$ransu = $rand_n[$w];
	// 設定
	$api_key = '' ;		// APIキー
	$api_secret = '' ;		// APIシークレット
	$access_token = '' ;		// アクセストークン
	$access_token_secret = '' ;		// アクセストークンシークレット
	$request_url = 'https://api.twitter.com/1.1/followers/ids.json' ;		// エンドポイント
	$request_method = 'GET' ;




	// パラメータA (オプション)
	$params_a = array(
		"user_id" => $u2_id[$ransu],
	) ;

	// キーを作成する (URLエンコードする)
	$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;

	// パラメータB (署名の材料用)
	$params_b = array(
		'oauth_token' => $access_token ,
		'oauth_consumer_key' => $api_key ,
		'oauth_signature_method' => 'HMAC-SHA1' ,
		'oauth_timestamp' => time() ,
		'oauth_nonce' => microtime() ,
		'oauth_version' => '1.0' ,
	) ;

	// パラメータAとパラメータBを合成してパラメータCを作る
	$params_c = array_merge( $params_a , $params_b ) ;

	// 連想配列をアルファベット順に並び替える
	ksort( $params_c ) ;

	// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
	$request_params = http_build_query( $params_c , '' , '&' ) ;

	// 一部の文字列をフォロー
	$request_params = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , $request_params ) ;

	// 変換した文字列をURLエンコードする
	$request_params = rawurlencode( $request_params ) ;

	// リクエストメソッドをURLエンコードする
	// ここでは、URL末尾の[?]以下は付けないこと
	$encoded_request_method = rawurlencode( $request_method ) ;
 
	// リクエストURLをURLエンコードする
	$encoded_request_url = rawurlencode( $request_url ) ;
 
	// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
	$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;

	// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
	$hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;

	// base64エンコードして、署名[$signature]が完成する
	$signature = base64_encode( $hash ) ;

	// パラメータの連想配列、[$params]に、作成した署名を加える
	$params_c['oauth_signature'] = $signature ;

	// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
	$header_params = http_build_query( $params_c , '' , ',' ) ;

	// リクエスト用のコンテキスト
	$context = array(
		'http' => array(
			'method' => $request_method , // リクエストメソッド
			'header' => array(			  // ヘッダー
				'Authorization: OAuth ' . $header_params ,
			) ,
		) ,
	) ;

	// パラメータがある場合、URLの末尾に追加
	if( $params_a ) {
		$request_url .= '?' . http_build_query( $params_a ) ;
	}

	// オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
//	if( $params_a ) {
//		$context['http']['content'] = http_build_query( $params_a ) ;
//	}

	// cURLを使ってリクエスト
	$curl = curl_init() ;
	curl_setopt( $curl, CURLOPT_URL , $request_url ) ;
	curl_setopt( $curl, CURLOPT_HEADER, 1 ) ; 
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;	// メソッド
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false ) ;	// 証明書の検証を行わない
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true ) ;	// curl_execの結果を文字列で返す
	curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] ) ;	// ヘッダー
//	if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) ) {		// GETの場合は不要
//		curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;	// リクエストボディ
//	}
	curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;	// タイムアウトの秒数
	$res1 = curl_exec( $curl ) ;
	$res2 = curl_getinfo( $curl ) ;
	curl_close( $curl ) ;

	// 取得したデータ
	$json = substr( $res1, $res2['header_size'] ) ;		// 取得したデータ(JSONなど)
	$header = substr( $res1, 0, $res2['header_size'] ) ;	// レスポンスヘッダー (検証に利用したい場合にどうぞ)

	// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
	// $json = file_get_contents( $request_url , false , stream_context_create( $context ) ) ;

	// JSONをオブジェクトに変換
	$obj = json_decode( $json ) ;

	// HTML用
	$html = '' ;

	// タイトル
	$html .= '<h1 style="text-align:center; border-bottom:1px solid #555; padding-bottom:12px; margin-bottom:48px; color:#D36015;">GET followers/ids</h1>' ;

	// エラー判定
	if( !$json || !$obj ) {
		$html .= '<h2>エラー内容</h2>' ;
		$html .= '<p>データを取得することができませんでした…。設定を見直して下さい。</p>' ;
	}

	//鍵垢判定
if(strpos($json, 'Not authorized') !== false){
	$w--;
	continue;
}
/////////////////////////////////////////////////



}

///////////////////////////////////////////////////////
	echo "乱数";
	$rand_debug = '';
	for ($t=0; $t < 5; $t++) { 
		$rand_debug .= '<p>' .$rand_n[$t].'</p>';
	}

	echo $rand_debug;


sleep(930);
/////////////***ここからフォロー数を求める***////////////////

for ($k=0; $k< 5; $k++) { 

	// 設定
	$api_key = '' ;		// APIキー
	$api_secret = '' ;		// APIシークレット
	$access_token = '' ;		// アクセストークン
	$access_token_secret = '' ;		// アクセストークンシークレット
	$request_url = 'https://api.twitter.com/1.1/users/show.json' ;		// エンドポイント
	$request_method = 'GET' ;

 

//////////***乱数***//////////

$ransu = $rand_n[$k];
//////////////////////////////

$u3_id[$k] = $u2_id[$ransu];

//$u3_id[$k] = $rand_n[$k];


////////////////
//	echo "$u2_id[$ransu]"$u2_id[$ransu];
	echo "u3_id[$k]";
	echo $u3_id[$k];
	
	// パラメータA (オプション)
	$params_a = array(
		"user_id" => $u3_id[$k],
	) ;

	// キーを作成する (URLエンコードする)
	$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;

	// パラメータB (署名の材料用)
	$params_b = array(
		'oauth_token' => $access_token ,
		'oauth_consumer_key' => $api_key ,
		'oauth_signature_method' => 'HMAC-SHA1' ,
		'oauth_timestamp' => time() ,
		'oauth_nonce' => microtime() ,
		'oauth_version' => '1.0' ,
	) ;

	// パラメータAとパラメータBを合成してパラメータCを作る
	$params_c = array_merge( $params_a , $params_b ) ;

	// 連想配列をアルファベット順に並び替える
	ksort( $params_c ) ;

	// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
	$request_params = http_build_query( $params_c , '' , '&' ) ;

	// 一部の文字列をフォロー
	$request_params = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , $request_params ) ;

	// 変換した文字列をURLエンコードする
	$request_params = rawurlencode( $request_params ) ;

	// リクエストメソッドをURLエンコードする
	// ここでは、URL末尾の[?]以下は付けないこと
	$encoded_request_method = rawurlencode( $request_method ) ;
 
	// リクエストURLをURLエンコードする
	$encoded_request_url = rawurlencode( $request_url ) ;
 
	// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
	$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;

	// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
	$hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;

	// base64エンコードして、署名[$signature]が完成する
	$signature = base64_encode( $hash ) ;

	// パラメータの連想配列、[$params]に、作成した署名を加える
	$params_c['oauth_signature'] = $signature ;

	// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
	$header_params = http_build_query( $params_c , '' , ',' ) ;

	// リクエスト用のコンテキスト
	$context = array(
		'http' => array(
			'method' => $request_method , // リクエストメソッド
			'header' => array(			  // ヘッダー
				'Authorization: OAuth ' . $header_params ,
			) ,
		) ,
	) ;

	// パラメータがある場合、URLの末尾に追加
	if( $params_a ) {
		$request_url .= '?' . http_build_query( $params_a ) ;
	}

	// オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
//	if( $params_a ) {
//		$context['http']['content'] = http_build_query( $params_a ) ;
//	}

	// cURLを使ってリクエスト
	$curl = curl_init() ;
	curl_setopt( $curl, CURLOPT_URL , $request_url ) ;
	curl_setopt( $curl, CURLOPT_HEADER, 1 ) ; 
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;	// メソッド
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false ) ;	// 証明書の検証を行わない
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true ) ;	// curl_execの結果を文字列で返す
	curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] ) ;	// ヘッダー
//	if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) ) {		// GETの場合は不要
//		curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;	// リクエストボディ
//	}
	curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;	// タイムアウトの秒数
	$res1 = curl_exec( $curl ) ;
	$res2 = curl_getinfo( $curl ) ;
	curl_close( $curl ) ;

	// 取得したデータ
	$json = substr( $res1, $res2['header_size'] ) ;		// 取得したデータ(JSONなど)
	$header = substr( $res1, 0, $res2['header_size'] ) ;	// レスポンスヘッダー (検証に利用したい場合にどうぞ)

	// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
	// $json = file_get_contents( $request_url , false , stream_context_create( $context ) ) ;

	// JSONをオブジェクトに変換
	$obj = json_decode( $json ) ;

	// HTML用
	$html = '' ;

	// タイトル
	$html .= '<h1 style="text-align:center; border-bottom:1px solid #555; padding-bottom:12px; margin-bottom:48px; color:#D36015;">フォロー数取得</h1>' ;

	// エラー判定
	if( !$json || !$obj ) {
		$html .= '<h2>エラー内容</h2>' ;
		$html .= '<p>データを取得することができませんでした…。設定を見直して下さい。</p>' ;
	}




	// 検証用
	$html .= '<h2>取得したデータ</h2>' ;
	$html .= '<p>下記のデータを取得できました。</p>' ;
	$html .= 	'<h3>ボディ(JSON)</h3>' ;
	$html .= 	'<p><textarea style="width:80%" rows="8">' . $json . '</textarea></p>' ;
	$html .= 	'<h3>レスポンスヘッダー</h3>' ;
	$html .= 	'<p><textarea style="width:80%" rows="8">' . $header . '</textarea></p>' ;

	// 検証用
	$html .= '<h2>リクエストしたデータ</h2>' ;
	$html .= '<p>下記内容でリクエストをしました。</p>' ;
	$html .= 	'<h3>URL</h3>' ;
	$html .= 	'<p><textarea style="width:80%" rows="8">' . $context['http']['method'] . ' ' . $request_url . '</textarea></p>' ;
	$html .= 	'<h3>ヘッダー</h3>' ;
	$html .= 	'<p><textarea style="width:80%" rows="8">' . implode( "\r\n" , $context['http']['header'] ) . '</textarea></p>' ;

	// フッター
	$html .= '<small style="display:block; border-top:1px solid #555; padding-top:12px; margin-top:72px; text-align:center; font-weight:700;">プログラムの説明: <a href="https://syncer.jp/Web/API/Twitter/REST_API/GET/users/show/" target="_blank">SYNCER</a></small>' ;






/////***フォロー数***/////
//echo strpos($json, "friends_count");
$a = strpos($json, "friends_count");
$b =  substr($json,$a + 15);
$count_up2 = 0;
$count_up2 = $a + 15;
$z = 0;

$friend_count_num = "";

while (1) {
	
	$friend_count_num .= $json[$count_up2];

	$count_up2++;
	if(strcmp($json[$count_up2], ',') == 0)
		break;


}
;

echo "フォロー数";

echo $friend_count_num;

	// 出力 (本稼働時はHTMLのヘッダー、フッターを付けよう)
	echo $html ;

$follow[$k] = $friend_count_num;



///////////////***フォロワー数求める***///////////////////


	// 設定
	$api_key = '' ;		// APIキー
	$api_secret = '' ;		// APIシークレット
	$access_token = '' ;		// アクセストークン
	$access_token_secret = '' ;		// アクセストークンシークレット
	$request_url = 'https://api.twitter.com/1.1/users/show.json' ;		// エンドポイント
	$request_method = 'GET' ;

    // パラメータA (オプション)
    $params_a = array(

      "user_id" => $u3_id[$k], 


    // キーを作成する (URLエンコードする)
    $signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;

    // パラメータB (署名の材料用)
    $params_b = array(
        'oauth_token' => $access_token ,
        'oauth_consumer_key' => $api_key ,
        'oauth_signature_method' => 'HMAC-SHA1' ,
        'oauth_timestamp' => time() ,
        'oauth_nonce' => microtime() ,
        'oauth_version' => '1.0' ,
    ) ;

    // パラメータAとパラメータBを合成してパラメータCを作る
    $params_c = array_merge( $params_a , $params_b ) ;

    // 連想配列をアルファベット順に並び替える
    ksort( $params_c ) ;

    // パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
    $request_params = http_build_query( $params_c , '' , '&' ) ;

    // 一部の文字列をフォロー
    $request_params = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , $request_params ) ;

    // 変換した文字列をURLエンコードする
    $request_params = rawurlencode( $request_params ) ;

    // リクエストメソッドをURLエンコードする
    // ここでは、URL末尾の[?]以下は付けないこと
    $encoded_request_method = rawurlencode( $request_method ) ;
 
    // リクエストURLをURLエンコードする
    $encoded_request_url = rawurlencode( $request_url ) ;
 
    // リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
    $signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;

    // キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
    $hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;

    // base64エンコードして、署名[$signature]が完成する
    $signature = base64_encode( $hash ) ;

    // パラメータの連想配列、[$params]に、作成した署名を加える
    $params_c['oauth_signature'] = $signature ;

    // パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
    $header_params = http_build_query( $params_c , '' , ',' ) ;

    // リクエスト用のコンテキスト
    $context = array(
        'http' => array(
            'method' => $request_method , // リクエストメソッド
            'header' => array(            // ヘッダー
                'Authorization: OAuth ' . $header_params ,
            ) ,
        ) ,
    ) ;

    // パラメータがある場合、URLの末尾に追加
    if( $params_a ) {
        $request_url .= '?' . http_build_query( $params_a ) ;
    }

    // オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
//  if( $params_a ) {
//      $context['http']['content'] = http_build_query( $params_a ) ;
//  }

    // cURLを使ってリクエスト
    $curl = curl_init() ;
    curl_setopt( $curl, CURLOPT_URL , $request_url ) ;
    curl_setopt( $curl, CURLOPT_HEADER, 1 ) ; 
    curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;  // メソッド
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false ) ;  // 証明書の検証を行わない
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true ) ;   // curl_execの結果を文字列で返す
    curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] ) ; // ヘッダー
//  if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) ) {       // GETの場合は不要
//      curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;   // リクエストボディ
//  }
    curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;    // タイムアウトの秒数
    $res1 = curl_exec( $curl ) ;
    $res2 = curl_getinfo( $curl ) ;
    curl_close( $curl ) ;

    // 取得したデータ
    $json = substr( $res1, $res2['header_size'] ) ;     // 取得したデータ(JSONなど)
    $header = substr( $res1, 0, $res2['header_size'] ) ;    // レスポンスヘッダー (検証に利用したい場合にどうぞ)

    // [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
    // $json = file_get_contents( $request_url , false , stream_context_create( $context ) ) ;

    // JSONをオブジェクトに変換
    $obj = json_decode( $json ) ;

    // HTML用
    $html = '' ;

    // タイトル
    $html .= '<h1 style="text-align:center; border-bottom:1px solid #555; padding-bottom:12px; margin-bottom:48px; color:#D36015;">フォロワー数取得</h1>' ;

    // エラー判定
    if( !$json || !$obj ) {
        $html .= '<h2>エラー内容</h2>' ;
        $html .= '<p>データを取得することができませんでした…。設定を見直して下さい。</p>' ;
    }

$a = strpos($json, "followers_count");
$b =  substr($json,$a + 17);
$count_up3 = 0;
$count_up3 = $a + 17;
$z = 0;

$followers_count_num = "";

while (1) {
    
    $followers_count_num .= $json[$count_up3];

    $count_up3++;
    if(strcmp($json[$count_up3], ',') == 0)
        break;


}

echo "フォロワー数";

echo $followers_count_num;

    // 出力 (本稼働時はHTMLのヘッダー、フッターを付けよう)
    echo $html ;

$follower[$k] = $followers_count_num;



}


/////////////////////////////////////////////////////////////




		/*usr_num[i] <- usr_id[i]のフォロー数*/

		$pr_tmp = 0;
		$d = 0.5;	//減衰係数
	

		if($loop_num > 0){

			for($j=0;$j<5;$j++){
					$usr_id=$u3_id[$j];
					$usr_num=$follow[$j];
					$flw_num=$follower[$j];
					$pr_tmp=(pagerank($usr_id,$flw_num)/$usr_num);
				}			
			
		$pr_tmp = $pr_tmp  / 5 * $flw_num;
		return ($d*$pr_tmp) + (1-$d);

		} else{
			//loop_num = 3;
			return 1;
		}


	}

//関数の呼び出し

$PR_result = "RESULT:" . '<p>' . pagerank($fst_id,$a_num) . '</p>';

echo "result_";
echo $PR_result;

