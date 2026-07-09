<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ---------------------------------------------------------------------------
// Public routes
// ---------------------------------------------------------------------------
$routes->get('/login', 'AuthController::index');
$routes->post('/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');

// ---------------------------------------------------------------------------
// Protected page routes
// ---------------------------------------------------------------------------
$routes->get('/dashboard', 'ChatController::dashboard', ['filter' => 'auth']);

// ---------------------------------------------------------------------------
// User admin routes (direct URL access only)
// ---------------------------------------------------------------------------
$routes->group('useradmin', ['filter' => ['auth', 'adminrole']], static function (RouteCollection $routes) {
    $routes->get('login', 'UserAdminController::loginForm');
    $routes->post('login', 'UserAdminController::login');
});

$routes->group('useradmin', ['filter' => ['auth', 'adminrole', 'adminreauth']], static function (RouteCollection $routes) {
    $routes->get('/', 'UserAdminController::index');
    $routes->post('users', 'UserAdminController::create');
    $routes->get('users/(:num)/edit', 'UserAdminController::edit/$1');
    $routes->post('users/(:num)', 'UserAdminController::update/$1');
    $routes->post('users/(:num)/password', 'UserAdminController::changePassword/$1');
    $routes->post('users/(:num)/delete', 'UserAdminController::delete/$1');
});

// ---------------------------------------------------------------------------
// Protected API routes
// ---------------------------------------------------------------------------
$routes->group('api', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('conversations/create', 'ChatController::createConversation');
    $routes->get('conversations/list', 'ChatController::listConversations');
    $routes->get('conversations/messages/(:any)', 'ChatController::getMessages/$1');
    $routes->post('chat/send', 'ChatController::send');
});

// ---------------------------------------------------------------------------
// Root redirect
// ---------------------------------------------------------------------------
$routes->get('/', static function () {
    return redirect()->to('/dashboard');
});
