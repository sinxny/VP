<?php include __DIR__ . "/_inc.php";
$isLogin = @$_SESSION["user"]["user_id"];

// 도메인
$domain = strtoupper($_SERVER["HTTP_HOST"]);
if($domain == "DOCS.HTENC.CO.KR" || $domain == "DOCS.SEPARK2111" || $domain == "DOCS.SEPARK2111.HTENC.CO.KR") {
	$menuRight = "all";
} else if($domain == "VP.HTENC.CO.KR" || $domain == "VP.SEPARK2111.HTENC.CO.KR") {
	$menuRight = "vp";
} else if($domain == "CM.HTENC.CO.KR" || $domain == "CM.SEPARK2111.HTENC.CO.KR") {
	$menuRight = "cm";
}

if(isset($_REQUEST) && array_key_exists("sid", $_REQUEST) && isset($_REQUEST["sid"]) && is_null($_REQUEST["sid"]) != true && $_REQUEST["sid"] != ""){
	if(isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221")
	{
		//echo "aaa";
		//exit;
	}
    if(!$isLogin) //isset($_SERVER) && $_SERVER["REMOTE_ADDR"] == "10.10.103.221" && $_REQUEST["sid"] == "62810vlt58ssctcdode2s6au93"
	{
		//echo $_REQUEST["sid"];
		//exit;
		$curl = curl_init();
        $url = "https://gw.htenc.co.kr/api/json_session.php";
        $param = array(
            'sid' => $_REQUEST["sid"]
			, 'agent' => $_SERVER['HTTP_USER_AGENT']
			, 'ip' => $_SERVER["REMOTE_ADDR"]
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                'Accept: application/json',
                //'Content-Type: application/json'
            ),
        ));
        $result = curl_exec($curl);
		curl_close($curl);
		
		if(isset($result) && !is_null($result) && $result != "")
		{
			@session_start();
			$contents = json_decode($result, true);
			if(isset($contents) && !is_null($contents) && is_array($contents) && array_key_exists("user", $contents) )
			{
				foreach($contents["user"] as $_key => $_val)
				{
					$_SESSION["user"][$_key] = $_val;
				}
				//$_SESSION["user"] = $contents;
				//echo '<pre>';
				//print_r($_SESSION);
				//echo '</pre>';
				
			}
			
		}
		$isLogin = @$_SESSION["user"]["user_id"];
		
		if($isLogin)
		{
			if(isset($_SERVER) && $_SERVER["HTTPS"] == "on")
			{
				$location = "https://";
			}
			else
			{
				$location = "http://";
			}
			$location .= $_SERVER['HTTP_HOST'];
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $location);
		}
	}
}

require_once "header.php";
if(!isset($isLogin)){
    //로그인
    require_once "account/login_view.php";
}