<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Lab extends Controller
{
	public function dashboard()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Laboratory Dashboard - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function requests()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Test Requests - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function results()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Test Results - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function equipment()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Equipment - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function inventory()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Inventory - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function reports()
	{
		if (!session()->get('isLoggedIn') || session()->get('role') !== 'lab') {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Reports - HMS',
			'user_role' => 'lab',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}
}




