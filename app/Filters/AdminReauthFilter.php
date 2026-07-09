<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminReauthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Admin access is required for this page.');
        }

        $adminReauthUntil = (int) session()->get('admin_reauth_until');
        if ($adminReauthUntil < time()) {
            return redirect()->to('/useradmin/login')->with('error', 'Please re-enter your admin password.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }
}
