<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Mobile verification code
$router->post('send-sms-verification', function(Illuminate\Http\Request $request)
{
	$mobile_number = $request->get('mobile_number');

	// regex valid
	if(! preg_match('/^1[3458][0-9]{9}$/', $mobile_number)){
		return "error";
	}
	

	$url = 'http://www.zuulee.com/cgi-bin/user/sendVerifyCode.do';
	$data = [
		'phone' => $mobile_number,
		'sendMode' => 2 ,
		'codeType' => 1 ,
		'location' => 10 
	];
	$postdata = http_build_query($data);
	$opts = array('http' => [
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata
		]
	);
	$context = stream_context_create($opts);
	$result = file_get_contents($url, false, $context);
	// $result = '{"v_code":"8447","errcode":0}';
	return json_decode($result)->v_code;
});

$router->get('users', function()
{
	return App\User::all();
});
