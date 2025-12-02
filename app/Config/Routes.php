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
$routes->get('nurse/treatment-updates', 'Nurse::treatmentUpdates');
$routes->post('nurse/updateVitals', 'Nurse::updateVitals');
$routes->get('nurse/reports', 'Nurse::reports');
$routes->post('nurse/saveVitalSigns', 'Nurse::updateVitals'); // New simple endpoint
$routes->post('nurse/updateTreatment', 'Nurse::updateTreatment');
$routes->post('nurse/assignPatient', 'Nurse::assignPatient');
$routes->post('nurse/markPrescriptionAsGiven', 'Nurse::markPrescriptionAsGiven');

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
$routes->get('reception/reports', 'Reception::reports');
$routes->get('reception/schedule', 'Reception::schedule');

// Lab Routes
$routes->get('lab/requests', 'Lab::requests');
$routes->post('lab/createRequest', 'Lab::createRequest');
$routes->post('lab/updateRequestStatus', 'Lab::updateRequestStatus');
$routes->get('lab/results', 'Lab::results');
$routes->post('lab/results/save', 'Lab::saveResult');
$routes->get('lab/equipment', 'Lab::equipment');
$routes->get('lab/inventory', 'Lab::inventory');
$routes->get('lab/reports', 'Lab::reports');

// Pharmacy Routes
$routes->get('pharmacy/prescriptions', 'Pharmacy::prescriptions');
$routes->get('pharmacy/prescriptions/view/(:num)', 'Pharmacy::viewPrescription/$1');
$routes->post('pharmacy/dispensePrescription', 'Pharmacy::dispensePrescription');
$routes->get('pharmacy/inventory', 'Pharmacy::inventory');
$routes->post('pharmacy/inventory/save', 'Pharmacy::saveInventory');
$routes->get('pharmacy/inventory/get/(:num)', 'Pharmacy::getInventory/$1');
$routes->post('pharmacy/inventory/adjustStock', 'Pharmacy::adjustStock');
$routes->get('pharmacy/dispense', 'Pharmacy::dispense');
$routes->get('pharmacy/stock-movement', 'Pharmacy::stockMovement');
$routes->get('pharmacy/stockMovement', 'Pharmacy::stockMovement');
$routes->get('pharmacy/orders', 'Pharmacy::orders');
$routes->post('pharmacy/orders/create', 'Pharmacy::createOrder');
$routes->post('pharmacy/orders/updateStatus', 'Pharmacy::updateOrderStatus');
$routes->get('pharmacy/reports', 'Pharmacy::reports');

// Accounts Routes
$routes->get('accounts/billing', 'Accounts::billing');
$routes->post('accounts/createBill', 'Accounts::createBill');
$routes->post('accounts/recordPayment', 'Accounts::recordPayment');
$routes->post('accounts/voidPayment', 'Accounts::voidPayment');
$routes->get('accounts/getBillDetails/(:num)', 'Accounts::getBillDetails/$1');
$routes->get('accounts/getPrescriptionDetails/(:num)', 'Accounts::getPrescriptionDetails/$1');
$routes->get('accounts/getPatientBillableItems/(:num)', 'Accounts::getPatientBillableItems/$1');
$routes->get('accounts/getPatientInsuranceDiscount/(:num)', 'Accounts::getPatientInsuranceDiscount/$1');
$routes->get('accounts/payments', 'Accounts::payments');
$routes->get('accounts/reports', 'Accounts::reports');
$routes->get('accounts/financial', 'Accounts::financial');
$routes->get('accounts/settings', 'Accounts::settings');
$routes->post('accounts/settings/save', 'Accounts::saveSettings');
$routes->get('nurse/settings', 'Nurse::settings');
$routes->post('nurse/settings/save', 'Nurse::saveSettings');
$routes->get('reception/settings', 'Reception::settings');
$routes->post('reception/settings/save', 'Reception::saveSettings');
$routes->get('doctor/settings', 'Doctor::settings');
$routes->post('doctor/settings/save', 'Doctor::saveSettings');
$routes->get('lab/settings', 'Lab::settings');
$routes->post('lab/settings/save', 'Lab::saveSettings');

// IT Routes
$routes->get('it/system', 'It::system');
$routes->get('it/users', 'It::users');
$routes->get('it/backup', 'It::backup');
$routes->get('it/security', 'It::security');
$routes->get('it/tickets', 'It::tickets');


// Doctor Routes
$routes->get('doctor/schedule', 'Doctor::schedule');
$routes->post('doctor/updateSchedule', 'Doctor::updateSchedule');
$routes->get('doctor/getRecurringSchedules', 'Doctor::getRecurringSchedules');
$routes->post('doctor/updateRecurringSchedule', 'Doctor::updateRecurringSchedule');
$routes->get('doctor/getAvailableSlots', 'Doctor::getAvailableSlots');
$routes->get('doctor/appointments', 'Doctor::appointments');
$routes->get('doctor/consultations', 'Doctor::consultations');
$routes->get('doctor/inpatients', 'Doctor::inpatients');
$routes->get('doctor/consultations/view/(:num)', 'Doctor::viewConsultation/$1');
$routes->get('doctor/patients', 'Doctor::patients');
$routes->get('doctor/patients/show/(:num)', 'Doctor::getPatientDetails/$1');
$routes->get('doctor/prescriptions', 'Doctor::prescriptions');
$routes->post('doctor/prescriptions/create', 'Doctor::createPrescription');
$routes->get('doctor/labs', 'Doctor::labRequests');
$routes->get('doctor/labRequests', 'Doctor::labRequests');
$routes->post('doctor/createLabRequest', 'Doctor::createLabRequest');
$routes->get('doctor/reports', 'Doctor::reports');

// Admin Routes (expanded, no group)
$routes->get('admin/dashboard', 'Admin\\Dashboard::index');
$routes->get('admin/patients', 'Admin\\Patients::index');
$routes->post('admin/patients/create', 'Admin\\Patients::create');
$routes->get('admin/patients/view/(:num)', 'Admin\\Patients::view/$1');
$routes->get('admin/patients/edit/(:num)', 'Admin\\Patients::edit/$1');
$routes->post('admin/patients/update/(:num)', 'Admin\\Patients::update/$1');
$routes->post('admin/patients/delete/(:num)', 'Admin\\Patients::delete/$1');
$routes->get('admin/doctors', 'Admin\\Doctors::index');
$routes->get('admin/nurses', 'Admin\\Nurses::index');
$routes->post('admin/nurses/createSchedule', 'Admin\\Nurses::createSchedule');
$routes->get('admin/nurses/getSchedule/(:num)', 'Admin\\Nurses::getSchedule/$1');
$routes->post('admin/nurses/deleteSchedule', 'Admin\\Nurses::deleteSchedule');
$routes->get('admin/nurses/getAvailableNurses', 'Admin\\Nurses::getAvailableNurses');
$routes->get('admin/lab', 'Admin\\Lab\\Dashboard::index');
$routes->get('admin/lab/requests', 'Admin\\Lab\\Requests::index');
$routes->get('admin/pharmacy-inventory', 'Admin\\Pharmacy::index');
$routes->get('admin/pharmacy-inventory/details/(:num)', 'Admin\\Pharmacy::getMedicineDetails/$1');
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
$routes->post('admin/appointments/update', 'Admin\\Appointments::update');
$routes->post('admin/appointments/delete/(:num)', 'Admin\\Appointments::delete/$1');

// Admin Admissions
$routes->get('admin/admissions', 'Admin\\Admissions::index');
$routes->post('admin/admissions/update', 'Admin\\Admissions::update');
$routes->post('admin/admissions/discharge/(:num)', 'Admin\\Admissions::discharge/$1');
$routes->post('admin/admissions/delete/(:num)', 'Admin\\Admissions::delete/$1');

$routes->get('admin/billing', 'Admin\\Billing::index');
$routes->post('admin/billing/createBillsForCompleted', 'Admin\\Billing::createBillsForCompletedPrescriptions');
$routes->post('admin/billing/createBillForPrescription/(:num)', 'Admin\\Billing::createBillForPrescription/$1');
$routes->get('admin/users', 'Admin\\Users::index');
$routes->get('admin/reports', 'Admin\\Reports::index');
$routes->post('admin/users/create', 'Admin\\Users::create');
$routes->post('admin/users/update', 'Admin\\Users::update');
$routes->post('admin/users/delete', 'Admin\\Users::delete');

// Admin Settings
$routes->get('admin/settings', 'Admin\\Settings::index');
$routes->post('admin/settings/save', 'Admin\\Settings::save');
$routes->get('accounts/settings', 'Accounts::settings');
$routes->post('accounts/settings/save', 'Accounts::saveSettings');
$routes->get('nurse/settings', 'Nurse::settings');
$routes->post('nurse/settings/save', 'Nurse::saveSettings');
$routes->get('reception/settings', 'Reception::settings');
$routes->post('reception/settings/save', 'Reception::saveSettings');
$routes->get('doctor/settings', 'Doctor::settings');
$routes->post('doctor/settings/save', 'Doctor::saveSettings');
$routes->get('lab/settings', 'Lab::settings');
$routes->post('lab/settings/save', 'Lab::saveSettings');
$routes->get('pharmacy/settings', 'Pharmacy::settings');
$routes->post('pharmacy/settings/save', 'Pharmacy::saveSettings');

// Reception Appointment Routes
$routes->post('reception/createAppointment', 'Reception::createAppointment');
$routes->post('reception/checkInPatient', 'Reception::checkInPatient');
$routes->get('reception/getDoctorSchedule/(:num)', 'Reception::getDoctorSchedule/$1');
$routes->get('reception/getDoctorUnavailableDates/(:num)', 'Reception::getDoctorUnavailableDates/$1');
$routes->get('reception/rooms', 'Reception::getRoomsByType');
$routes->get('reception/doctors', 'Reception::getDoctors');