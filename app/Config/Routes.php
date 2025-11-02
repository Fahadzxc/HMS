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
$routes->post('reception/patients/store', 'Reception::store');
$routes->post('reception/patients/update/(:num)', 'Reception::update/$1');
$routes->post('reception/patients/delete/(:num)', 'Reception::delete/$1');
$routes->get('reception/patients/show/(:num)', 'Reception::show/$1');
// Legacy route fallback
$routes->post('reception/createPatient', 'Reception::store');
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
$routes->get('admin/doctors', 'Admin\\Doctors::index');
$routes->get('admin/nurses', 'Admin\\Nurses::index');
$routes->get('admin/lab', 'Admin\\Lab\\Dashboard::index');
$routes->get('admin/lab/requests', 'Admin\\Lab\\Requests::index');
$routes->get('admin/lab/results', 'Admin\\Lab\\Results::index');
$routes->get('admin/lab/staff', 'Admin\\Lab\\Staff::index');
$routes->get('admin/lab/departments', 'Admin\\Lab\\Departments::index');
$routes->get('admin/lab/equipment', 'Admin\\Lab\\Equipment::index');
$routes->get('admin/lab/inventory', 'Admin\\Lab\\Inventory::index');
$routes->get('admin/lab/reports', 'Admin\\Lab\\Reports::index');
$routes->get('laboratory', static function () {
    return redirect()->to('/admin/lab');
});
$routes->post('admin/lab/requests/reassign/(:num)', 'Admin\\Lab\\Requests::reassign/$1');
$routes->post('admin/lab/requests/change-priority/(:num)', 'Admin\\Lab\\Requests::changePriority/$1');
$routes->post('admin/lab/requests/force-complete/(:num)', 'Admin\\Lab\\Requests::forceComplete/$1');
$routes->post('admin/lab/results/audit/(:num)', 'Admin\\Lab\\Results::audit/$1');
$routes->post('admin/lab/staff/create', 'Admin\\Lab\\Staff::create');
$routes->post('admin/lab/staff/update/(:num)', 'Admin\\Lab\\Staff::update/$1');
$routes->post('admin/lab/staff/delete/(:num)', 'Admin\\Lab\\Staff::delete/$1');
$routes->post('admin/lab/departments/create', 'Admin\\Lab\\Departments::create');
$routes->post('admin/lab/departments/update/(:num)', 'Admin\\Lab\\Departments::update/$1');
$routes->post('admin/lab/departments/delete/(:num)', 'Admin\\Lab\\Departments::delete/$1');
$routes->post('admin/lab/equipment/create', 'Admin\\Lab\\Equipment::create');
$routes->post('admin/lab/equipment/update/(:num)', 'Admin\\Lab\\Equipment::update/$1');
$routes->post('admin/lab/equipment/log/(:num)', 'Admin\\Lab\\Equipment::log/$1');
$routes->post('admin/lab/equipment/delete/(:num)', 'Admin\\Lab\\Equipment::delete/$1');
$routes->post('admin/lab/inventory/create', 'Admin\\Lab\\Inventory::create');
$routes->post('admin/lab/inventory/update/(:num)', 'Admin\\Lab\\Inventory::update/$1');
$routes->post('admin/lab/inventory/log/(:num)', 'Admin\\Lab\\Inventory::log/$1');
$routes->post('admin/lab/inventory/delete/(:num)', 'Admin\\Lab\\Inventory::delete/$1');
$routes->post('admin/lab/reports/download', 'Admin\\Lab\\Reports::download');
$routes->get('admin/appointments', 'Admin\\Appointments::index');
$routes->get('admin/billing', 'Admin\\Billing::index');
$routes->get('admin/users', 'Admin\\Users::index');
$routes->post('admin/users/create', 'Admin\\Users::create');
$routes->post('admin/users/update', 'Admin\\Users::update');
$routes->post('admin/users/delete', 'Admin\\Users::delete');

// Reception Appointment Routes
$routes->post('reception/createAppointment', 'Reception::createAppointment');
$routes->post('reception/checkInPatient', 'Reception::checkInPatient');