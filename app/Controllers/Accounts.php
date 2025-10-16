<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Accounts extends Controller
{
	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Accounts Dashboard - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function billing()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Billing - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function payments()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Payments - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function insurance()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Insurance - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function reports()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Reports - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function financial()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'accountant') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Financial - HMS',
			'user_role' => 'accountant',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}
}




