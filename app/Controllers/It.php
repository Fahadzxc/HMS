<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class It extends Controller
{
	public function dashboard()
	{
		// Check session data
		$isLoggedIn = session()->get('isLoggedIn');
		$userRole = session()->get('role');
		$userName = session()->get('name');
		
		// If not logged in, redirect to login
		if (!$isLoggedIn) {
			return redirect()->to('/login')->with('error', 'Please log in first.');
		}
		
		// If role is not IT or admin, redirect to login
		if (!in_array($userRole, ['it','admin'], true)) {
			return redirect()->to('/login')->with('error', 'Unauthorized access. Your role: ' . $userRole);
		}

		$data = [
			'title' => 'IT Dashboard - HMS',
			'user_role' => 'it',
			'user_name' => $userName,
		];

		return view('auth/dashboard', $data);
	}

	public function system()
	{
		if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'System Status - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function users()
	{
		if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'User Management - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function backup()
	{
		if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Backup - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function security()
	{
		if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Security - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}

	public function tickets()
	{
		if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['it','admin'], true)) {
			return redirect()->to('/login');
		}

		$data = [
			'title' => 'Support Tickets - HMS',
			'user_role' => 'it',
			'user_name' => session()->get('name'),
		];

		return view('auth/dashboard', $data);
	}
}




