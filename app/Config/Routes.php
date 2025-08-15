<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


// Auth routes
$routes->post('register', 'Auth::register');
$routes->post('login', 'Auth::login');
$routes->post('logout', 'Auth::logout', ['filter' => 'jwt']);
$routes->post('refresh', 'Auth::refreshToken', ['filter' => 'jwt']);

// User management (protected by JWT)
$routes->group('users', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'User::index');
    $routes->get('(:num)', 'User::show/$1');
    $routes->post('/', 'User::create');
    $routes->post('(:num)', 'User::update/$1');
    $routes->delete('(:num)', 'User::delete/$1');
});
