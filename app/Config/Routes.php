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
$routes->get('doctor/dashboard', 'Doctor::dashboard');
$routes->get('nurse/dashboard', 'Nurse::dashboard');
$routes->get('reception/dashboard', 'Reception::dashboard');
$routes->get('lab/dashboard', 'Lab::dashboard');
$routes->get('pharmacy/dashboard', 'Pharmacy::dashboard');
$routes->get('accounts/dashboard', 'Accounts::dashboard');
$routes->get('it/dashboard', 'It::dashboard');

// Nurse Routes
$routes->get('nurse/patients', 'Nurse::patients');
$routes->get('nurse/tasks', 'Nurse::tasks');
$routes->get('nurse/schedule', 'Nurse::schedule');
$routes->get('nurse/appointments', 'Nurse::appointments');
$routes->post('nurse/updateVitals', 'Nurse::updateVitals');
$routes->post('nurse/updateTreatment', 'Nurse::updateTreatment');
$routes->post('nurse/assignPatient', 'Nurse::assignPatient');
$routes->post('nurse/updateSchedule', 'Nurse::updateSchedule');
$routes->post('nurse/requestScheduleChange', 'Nurse::requestScheduleChange');
$routes->post('nurse/addAppointment', 'Nurse::addAppointment');
$routes->post('nurse/updateAppointment', 'Nurse::updateAppointment');
$routes->get('nurse/checkDoctorAvailability', 'Nurse::checkDoctorAvailability');

// Reception Routes
$routes->get('reception/patients', 'Reception::patients');
$routes->post('reception/createPatient', 'Reception::createPatient');
$routes->get('reception/appointments', 'Reception::appointments');
$routes->get('reception/checkin', 'Reception::checkin');
$routes->get('reception/billing', 'Reception::billing');
$routes->get('reception/schedule', 'Reception::schedule');

// Lab Routes
$routes->get('lab/requests', 'Lab::requests');
$routes->get('lab/results', 'Lab::results');
$routes->get('lab/equipment', 'Lab::equipment');
$routes->get('lab/inventory', 'Lab::inventory');
$routes->get('lab/reports', 'Lab::reports');

// Pharmacy Routes
$routes->get('pharmacy/prescriptions', 'Pharmacy::prescriptions');
$routes->get('pharmacy/inventory', 'Pharmacy::inventory');
$routes->get('pharmacy/dispense', 'Pharmacy::dispense');
$routes->get('pharmacy/orders', 'Pharmacy::orders');
$routes->get('pharmacy/reports', 'Pharmacy::reports');

// Accounts Routes
$routes->get('accounts/billing', 'Accounts::billing');
$routes->get('accounts/payments', 'Accounts::payments');
$routes->get('accounts/insurance', 'Accounts::insurance');
$routes->get('accounts/reports', 'Accounts::reports');
$routes->get('accounts/financial', 'Accounts::financial');

// IT Routes
$routes->get('it/system', 'It::system');
$routes->get('it/users', 'It::users');
$routes->get('it/backup', 'It::backup');
$routes->get('it/security', 'It::security');
$routes->get('it/tickets', 'It::tickets');


// Doctor Routes
$routes->get('doctor/schedule', 'Doctor::schedule');
$routes->post('doctor/updateSchedule', 'Doctor::updateSchedule');
$routes->get('doctor/getAvailableSlots', 'Doctor::getAvailableSlots');
$routes->get('doctor/appointments', 'Doctor::appointments');
$routes->get('doctor/patients', 'Auth::dashboard');

// Admin Routes (expanded, no group)
$routes->get('admin/dashboard', 'Admin\\Dashboard::index');
$routes->get('admin/patients', 'Admin\\Patients::index');
$routes->post('admin/patients/create', 'Admin\\Patients::create');
$routes->get('admin/appointments', 'Admin\\Appointments::index');
$routes->post('admin/appointments/create', 'Admin\\Appointments::create');
$routes->post('admin/appointments/update/(:num)', 'Admin\\Appointments::update/$1');
$routes->post('admin/appointments/delete/(:num)', 'Admin\\Appointments::delete/$1');
$routes->get('admin/appointments/get/(:num)', 'Admin\\Appointments::getAppointment/$1');
$routes->get('admin/appointments/checkAvailability', 'Admin\\Appointments::checkAvailability');
$routes->get('admin/billing', 'Admin\\Billing::index');
$routes->get('admin/users', 'Admin\\Users::index');
$routes->post('admin/users/create', 'Admin\\Users::create');
$routes->post('admin/users/update', 'Admin\\Users::update');
$routes->post('admin/users/delete', 'Admin\\Users::delete');

// Reception Appointment Routes
$routes->post('reception/createAppointment', 'Reception::createAppointment');
$routes->post('reception/checkInPatient', 'Reception::checkInPatient');