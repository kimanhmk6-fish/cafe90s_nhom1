<?php
/** @var App\Core\Router $router */

// ===== Client =====
$router->get('/', 'Client\HomeController@index');
$router->get('/ve-quan', 'Client\HomeController@about');
$router->get('/menu', 'Client\ProductController@menu');

$router->get('/dang-nhap', 'Client\AuthController@showLogin', 'guest');
$router->post('/dang-nhap', 'Client\AuthController@login', 'guest');
$router->get('/dang-ky', 'Client\AuthController@showRegister', 'guest');
$router->post('/dang-ky', 'Client\AuthController@register', 'guest');
$router->post('/dang-xuat', 'Client\AuthController@logout');
$router->get('/quen-mat-khau', 'Client\AuthController@showForgot', 'guest');
$router->post('/quen-mat-khau', 'Client\AuthController@forgot', 'guest');
$router->get('/dat-lai-mat-khau', 'Client\AuthController@showReset', 'guest');
$router->post('/dat-lai-mat-khau', 'Client\AuthController@reset', 'guest');

$router->get('/ho-so', 'Client\ProfileController@showInfo', 'auth');
$router->post('/ho-so', 'Client\ProfileController@update', 'auth');
$router->post('/ho-so/avatar', 'Client\ProfileController@avatar', 'auth');
$router->get('/ho-so/don-hang', 'Client\ProfileController@showOrders', 'auth');
$router->get('/ho-so/doi-mat-khau', 'Client\ProfileController@showPassword', 'auth');
$router->post('/ho-so/doi-mat-khau', 'Client\ProfileController@password', 'auth');
$router->post('/ho-so/don-hang/{id}/huy', 'Client\OrderController@cancel', 'auth');

$router->get('/gio-hang', 'Client\CartController@index', 'auth');
$router->post('/gio-hang/them', 'Client\CartController@add');
$router->post('/gio-hang/cap-nhat', 'Client\CartController@update', 'auth');
$router->post('/gio-hang/xoa', 'Client\CartController@remove', 'auth');
$router->post('/dat-hang', 'Client\OrderController@place', 'auth');
$router->get('/dat-hang/thanh-cong/{code}', 'Client\OrderController@success', 'auth');

// ===== Admin =====
$router->get('/admin/login', 'Admin\AuthController@showLogin');
$router->post('/admin/login', 'Admin\AuthController@login');
$router->post('/admin/logout', 'Admin\AuthController@logout');
$router->get('/admin', 'Admin\DashboardController@index', 'admin');

$router->get('/admin/products', 'Admin\ProductController@index', 'admin');
$router->get('/admin/products/create', 'Admin\ProductController@create', 'admin');
$router->post('/admin/products', 'Admin\ProductController@store', 'admin');
$router->get('/admin/products/{id}/edit', 'Admin\ProductController@edit', 'admin');
$router->post('/admin/products/{id}', 'Admin\ProductController@update', 'admin');
$router->post('/admin/products/{id}/toggle', 'Admin\ProductController@toggle', 'admin');
$router->post('/admin/products/{id}/delete', 'Admin\ProductController@delete', 'admin');

$router->get('/admin/categories', 'Admin\CategoryController@index', 'admin');
$router->post('/admin/categories', 'Admin\CategoryController@store', 'admin');
$router->post('/admin/categories/{id}', 'Admin\CategoryController@update', 'admin');
$router->post('/admin/categories/{id}/toggle', 'Admin\CategoryController@toggle', 'admin');
$router->post('/admin/categories/{id}/delete', 'Admin\CategoryController@delete', 'admin');

$router->get('/admin/orders', 'Admin\OrderController@index', 'admin');
$router->get('/admin/orders/export', 'Admin\OrderController@export', 'admin');
$router->get('/admin/orders/{id}', 'Admin\OrderController@show', 'admin');
$router->post('/admin/orders/{id}/status', 'Admin\OrderController@updateStatus', 'admin');
$router->post('/admin/orders/{id}/payment', 'Admin\OrderController@reconcile', 'admin');

$router->get('/admin/users', 'Admin\UserController@index', 'admin');
$router->post('/admin/users/{id}/toggle', 'Admin\UserController@toggleActive', 'admin');
$router->post('/admin/users/{id}/promote', 'Admin\UserController@promote', 'admin');
