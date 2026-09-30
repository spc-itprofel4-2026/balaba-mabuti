<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// --------------------------------------------------------------------
// Public
// --------------------------------------------------------------------

$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::login');
$routes->get('register', 'Register::index');
$routes->post('register', 'Register::store');

// Viber posts to this endpoint; it is exempted from CSRF (see Config\Filters).
$routes->post('viber/webhook', 'Webhook::index');

// --------------------------------------------------------------------
// Officer back office (session required)
// --------------------------------------------------------------------

$routes->group('/', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->post('logout', 'Auth::logout');

    $routes->get('announcements', 'Announcements::index');
    $routes->get('announcements/create', 'Announcements::create');
    $routes->post('announcements', 'Announcements::store');
    $routes->get('announcements/(:num)', 'Announcements::show/$1');
    $routes->post('announcements/(:num)/send', 'Announcements::send/$1');
    $routes->post('announcements/(:num)/delete', 'Announcements::destroy/$1');

    $routes->get('members', 'Members::index');
    $routes->post('members', 'Members::store');
    $routes->post('members/(:num)/toggle', 'Members::toggle/$1');
    $routes->post('members/(:num)/delete', 'Members::destroy/$1');

    // ---------------------------------------------------------------
    // Admin-only: officer account management (role gate = AdminFilter)
    // ---------------------------------------------------------------
    $routes->group('profiles', ['filter' => 'admin'], static function (RouteCollection $routes): void {
        $routes->get('', 'Profiles::index');
        $routes->get('(:num)', 'Profiles::edit/$1');
        $routes->post('(:num)/update', 'Profiles::update/$1');
        $routes->post('(:num)/toggle', 'Profiles::toggle/$1');
        $routes->post('(:num)/delete', 'Profiles::destroy/$1');
    });
});
