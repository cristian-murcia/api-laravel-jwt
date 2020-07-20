<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Carga de clases
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

//Prueba a Orm
Route::get('/testOrm', 'PruebasController@testOrm');

//Rutas de Controlador de Usuario
Route::post('/api/login', 'UserController@login');
Route::post('/api/register', 'UserController@register');
Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);

Route::put('/api/user/update', 'UserController@update');

Route::get('/api/user/avatar/{filename}', 'UserController@getimage');
Route::get('/api/user/detail/{id}', 'UserController@details');

//Rutas del Controlador de Categorias
Route::resource('/api/category', 'CategoryController');

//Rutas del Controlador de Post
Route::resource('/api/post', 'PostController');
Route::post('/api/post/upload', 'PostController@upload');

Route::get('/api/post/image/{filename}', 'PostController@getImage');
Route::get('/api/post/category/{id}', 'PostController@getPostByCategory');
Route::get('/api/post/user/{id}', 'PostController@getPostByUser');

