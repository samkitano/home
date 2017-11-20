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

Route::resource('/', 'ProjectsController')->only(['index', 'store',]);
Route::post('fix', 'ProjectsController@fixPermissions');
Route::post('destroy', 'ProjectsController@destroyProject');
Route::get('can-create-project/{name}', 'ProjectsController@canCreateProject');

Route::get('t', 'TestController@index');