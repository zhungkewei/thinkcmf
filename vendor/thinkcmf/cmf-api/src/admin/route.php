<?php

use think\facade\Route;

Route::get('admin/apps$', 'admin/App/index');
Route::post('admin/apps/:name', 'admin/App/install');
Route::put('admin/apps/:name', 'admin/App/update');
Route::delete('admin/apps/:name', 'admin/App/uninstall');

Route::get('admin/hooks/:hook/plugins', 'admin/Hook/plugins');
Route::get('admin/hooks$', 'admin/Hook/index');
Route::post('admin/hooks/plugins/list/order', 'admin/Hook/pluginListOrder');
Route::post('admin/hooks/sync', 'admin/Hook/sync');

Route::resource('admin/links', 'admin/Link');


Route::get('admin/menus$', 'admin/Menu/menus');

Route::resource('admin/navs', 'admin/Nav');
Route::resource('admin/nav/menus', 'admin/NavMenu');
Route::post('admin/nav/menus/:id/toggle', 'admin/NavMenu/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/nav/menus/:id/status/:status', 'admin/NavMenu/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/nav/menus/list/order', 'admin/NavMenu/listOrder');

Route::get('admin/plugins$', 'admin/Plugin/index');
Route::post('admin/plugins/:id/status/:status', 'admin/Plugin/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::get('admin/plugins/:id/config$', 'admin/Plugin/config')->pattern(['id' => '\d+',]);
Route::put('admin/plugins/:id/config$', 'admin/Plugin/configPut')->pattern(['id' => '\d+',]);
Route::post('admin/plugins/:name$', 'admin/Plugin/install');
Route::put('admin/plugins/:name', 'admin/Plugin/update');
Route::delete('admin/plugins/:id', 'admin/Plugin/uninstall');

Route::get('admin/roles/:id/api/authorize$', 'admin/Role/apiAuthorize');
Route::put('admin/roles/:id/api/authorize$', 'admin/Role/apiAuthorizePut');
Route::get('admin/roles/:id/authorize$', 'admin/Role/authorize');
Route::put('admin/roles/:id/authorize$', 'admin/Role/authorizePut');
Route::resource('admin/roles', 'admin/Role');

Route::delete('admin/setting/cache', 'admin/Setting/clearCache');
Route::put('admin/setting/site', 'admin/Setting/sitePut');
Route::put('admin/setting/upload', 'admin/Setting/uploadPut');
Route::put('admin/setting/storage', 'admin/Setting/storagePut');
Route::put('admin/setting/password', 'admin/Setting/passwordPut');

Route::put('admin/mail/config', 'admin/Mail/configPut');
Route::put('admin/mail/template', 'admin/Mail/templatePut');

Route::resource('admin/slides', 'admin/Slide');
Route::resource('admin/slide/items', 'admin/SlideItem');
Route::post('admin/slide/items/:id/toggle', 'admin/SlideItem/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/slide/items/:id/status/:status', 'admin/SlideItem/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/slide/items/list/order', 'admin/SlideItem/listOrder');

Route::resource('admin/routes', 'admin/Route');
Route::post('admin/routes/:id/toggle', 'admin/Route/toggle')->pattern(['id' => '\d+',]);
Route::post('admin/routes/:id/status/:status', 'admin/Route/status')->pattern(['id' => '\d+', 'status' => '\d+',]);
Route::post('admin/routes/list/order', 'admin/Route/listOrder');
Route::get('admin/routes/app/urls$', 'admin/Route/appUrls');




