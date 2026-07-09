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
