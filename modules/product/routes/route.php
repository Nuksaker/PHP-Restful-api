<?php

use Core\Route;

Route::setCurrentModule('product');

// Product Get Data
Route::get('product', 'ProductController@getDataList');
Route::get('product/{id}', 'ProductController@getDataListById');

// Product Event
Route::post('product', 'ProductController@create');
Route::put('product', 'ProductController@update');
Route::delete('product/{id}', 'ProductController@delete');

return Route::getRoutes();
