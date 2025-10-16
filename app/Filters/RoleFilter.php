<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		$session = session();

		if (!$session->get('isLoggedIn')) {
			return redirect()->to('/login');
		}

		if (empty($arguments)) {
			return null;
		}

		$allowedRoles = is_array($arguments) ? $arguments : [$arguments];
		$userRole = (string) $session->get('role');

		if (!in_array($userRole, $allowedRoles, true)) {
			return redirect()->to('/login')->with('error', 'Unauthorized.');
		}

		return null;
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
	}
}



