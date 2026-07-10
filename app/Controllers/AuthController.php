<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    /**
     * Show the login form. Redirect if already authenticated.
     */
    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Process login credentials.
     */
    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Basic presence validation
        if (empty($username) || empty($password)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username and password are required.');
        }

        $userModel = new UserModel();

        try {
            $user = $userModel->findByUsername($username);
        } catch (\Throwable $e) {
            log_message('critical', '[Auth] Login query failed: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Login is temporarily unavailable. Please contact support or try again later.');
        }

        // Use constant-time comparison to prevent timing attacks
        $hashToVerify = $user['password_hash'] ?? '$2y$10$invalidhashpadding00000000000000000000000000000000000000';

        if (! $user || ! password_verify($password, $hashToVerify)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid username or password.');
        }

        // Regenerate session ID to prevent session fixation
        session()->regenerate(true);

        session()->set([
            'user_id'            => $user['id'],
            'username'           => $user['username'],
            'user_role'          => $user['role'] ?? 'user',
            'logged_in'          => true,
            'admin_reauth_until' => null,
        ]);

        return redirect()->to('/dashboard');
    }

    /**
     * Destroy the session and redirect to login.
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
