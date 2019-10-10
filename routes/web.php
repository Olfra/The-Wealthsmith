<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->post('/register', 'UserController@register');
//$router->post('/test', 'UserController@test');
$router->post('/login', 'UserController@authenticate');
$router->get('/redirect/{service}', 'UserController@redirect' );
$router->get( '/callback/{service}', 'UserController@callback' );
/**
 * Protected driver routes
 */
$router->group(
    ['middleware' => 'jwt.auth',], 
    function($router)  {
        //User profile
        $router->post('/userprofile', 'UserController@update_profile');
    }
);

