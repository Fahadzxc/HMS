<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::index');
$routes->get('/about', 'Home::about');
$routes->get('/contact', 'Home::contact');

// Auth Routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::processLogin');
$routes->get('dashboard', 'Auth::dashboard');
$routes->get('auth/logout', 'Auth::logout');

// Admin Routes
$routes->get('admin/dashboard', 'Admin\Dashboard::index');
$routes->get('patients', 'Admin\Patients::index');
$routes->get('appointments', 'Admin\Appointments::index');
$routes->get('billing', 'Admin\Billing::index');