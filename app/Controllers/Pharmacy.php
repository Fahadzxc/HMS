<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Pharmacy extends Controller
{
	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Pharmacy Dashboard - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function prescriptions()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Prescriptions - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function inventory()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Inventory - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function dispense()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Dispense - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function orders()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Orders - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function reports()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Reports - HMS',
			'user_role' => 'pharmacist',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}
}




