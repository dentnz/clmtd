<?php
	
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->post('auth/login', 'App\Api\V1\Controllers\AuthController@login');
    $api->post('auth/signup', 'App\Api\V1\Controllers\AuthController@signup');

    // Password reset/recovery is disabled for now...
    //$api->post('auth/recovery', 'App\Api\V1\Controllers\AuthController@recovery');
    //$api->post('auth/reset', 'App\Api\V1\Controllers\AuthController@reset');

    $api->get('user', ['middleware' => ['api.auth'], function () {		
        // @todo put this into a proper controller
        $user = JWTAuth::parseToken()->authenticate();
        return $user;
    }]);
});