<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Public / Home
$routes->get('home', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// Auth Routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::processLogin');
$routes->get('dashboard', 'Auth::dashboard');
$routes->get('auth/logout', 'Auth::logout');

// Role dashboards (optional short URLs)
$routes->get('doctor/dashboard', 'Auth::dashboard');
$routes->get('nurse/dashboard', 'Auth::dashboard');

// Temporary role pages (avoid 404s until dedicated controllers exist)
$routes->get('nurse/patients', 'Auth::dashboard');
$routes->get('nurse/tasks', 'Auth::dashboard');
$routes->get('doctor/patients', 'Auth::dashboard');
$routes->get('doctor/appointments', 'Auth::dashboard');

// Admin Routes (expanded, no group)
$routes->get('admin/dashboard', 'Admin\\Dashboard::index');
$routes->get('admin/patients', 'Admin\\Patients::index');
$routes->post('admin/patients/create', 'Admin\\Patients::create');
$routes->get('admin/appointments', 'Admin\\Appointments::index');
$routes->get('admin/billing', 'Admin\\Billing::index');