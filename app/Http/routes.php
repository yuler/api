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

// Mobile verification code
$router->post('send-sms-verification', function(Illuminate\Http\Request $request)
{
	$mobile_number = $request->get('mobile_number');
	
	$users = App\User::where('email','=',$mobile_number)->get();
	if(sizeof($users) > 0){
		return '{"status":"error","message":"手机号码已注册"}';
	}
	// regex valid
	if(! preg_match('/^1[3458][0-9]{9}$/', $mobile_number)){
		return '{"status":"error","message":"手机号码格式不正确"}';
	}
	
	// return '{"phone":"'. $mobile_number .'","checkCode":"123","status":"success"}';

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
	return '{"phone":"'. $mobile_number .'","checkCode":"'. json_decode($result)->v_code .'","status":"success"}';
});

// Register User
$router->post('register', function(Illuminate\Http\Request $request){
	try {
		$validator = Validator::make($request->all(), 
			[
            	'username' => 'unique:users,email',
        	],
        	[
        		'unique' => '手机号码已经存在'
        	]
        );
        if ($validator->fails()) {
            return '{"status":"error","message":"'. $validator->errors()->first() .'"}';
        }
		$user = new App\User;
		$user->email = $request->get('username');
		$user->password = bcrypt($request->get('password'));
		$user->save();
	} catch (Exception $e) {
		return '{"status":"error","message":"用户注册失败"}';
	}
	return '{"status":"success","message":"用户注册成功"}';
});

$router->get('users', function()
{
	return App\User::all();
});
