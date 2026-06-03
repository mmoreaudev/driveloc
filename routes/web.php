<?php
declare(strict_types=1);


$router->get('/',                                     'HomeController',        'index');
$router->get('/login',                                'AuthController',        'loginForm');
$router->post('/login',                               'AuthController',        'login');
$router->get('/register',                             'AuthController',        'registerForm');
$router->post('/register',                            'AuthController',        'register');
$router->get('/logout',                               'AuthController',        'logout');


$router->get('/profile',                              'AuthController',        'profileForm');
$router->post('/profile',                             'AuthController',        'updateProfile');
$router->post('/profile/password',                    'AuthController',        'changePassword');

$router->get('/vehicles',                             'VehicleController',     'index');
$router->get('/vehicles/create',                      'VehicleController',     'createForm');
$router->post('/vehicles/create',                     'VehicleController',     'create');
$router->get('/vehicles/(\d+)',                       'VehicleController',     'show');
$router->get('/vehicles/(\d+)/edit',                  'VehicleController',     'editForm');
$router->post('/vehicles/(\d+)/edit',                 'VehicleController',     'edit');
$router->post('/vehicles/(\d+)/toggle',               'VehicleController',     'toggleStatus');
$router->post('/vehicles/(\d+)/delete',               'VehicleController',     'delete');


$router->post('/reservations/create',                 'ReservationController', 'create');
$router->post('/reservations/(\d+)/cancel',           'ReservationController', 'cancel');


$router->get('/dashboard/client',                     'DashboardController',   'client');


$router->get('/dashboard/owner',                      'DashboardController',   'owner');
$router->get('/dashboard/owner/reservations',         'OwnerController',       'reservations');


$router->get('/dashboard/admin',                      'AdminController',       'index');
$router->get('/dashboard/admin/users',                'AdminController',       'users');
$router->post('/dashboard/admin/users/(\d+)/toggle',  'AdminController',       'toggleUser');
$router->post('/dashboard/admin/users/(\d+)/delete',  'AdminController',       'deleteUser');
$router->get('/dashboard/admin/vehicles',             'AdminController',       'vehicles');
$router->post('/dashboard/admin/vehicles/(\d+)/toggle','AdminController',      'toggleVehicle');
$router->post('/dashboard/admin/vehicles/(\d+)/delete','AdminController',      'deleteVehicle');
$router->get('/dashboard/admin/categories',           'AdminController',       'categories');
$router->post('/dashboard/admin/categories/create',   'AdminController',       'createCategory');
