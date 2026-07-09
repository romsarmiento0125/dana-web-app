<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserAdminController extends BaseController
{
    private const REAUTH_WINDOW_SECONDS = 900;

    private function requireAdminReauth()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Admin access is required for this page.');
        }

        $adminReauthUntil = (int) session()->get('admin_reauth_until');
        if ($adminReauthUntil < time()) {
            return redirect()->to('/useradmin/login')->with('error', 'Please re-enter your admin password.');
        }

        return null;
    }

    private function findUserOrRedirect(int $userId)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            return [null, redirect()->to('/useradmin')->with('error', 'User not found.')];
        }

        return [$user, null];
    }

    private function usernameExists(string $username, ?int $ignoreUserId = null): bool
    {
        $userModel = new UserModel();
        $existingUser = $userModel->findByUsername($username);

        if (! $existingUser) {
            return false;
        }

        return $ignoreUserId === null || (int) $existingUser['id'] !== $ignoreUserId;
    }

    private function adminCount(): int
    {
        return (new UserModel())
            ->where('role', 'admin')
            ->countAllResults();
    }

    public function loginForm()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Admin access is required for this page.');
        }

        return view('useradmin/login', [
            'username' => session()->get('username'),
        ]);
    }

    public function login()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Admin access is required for this page.');
        }

        $password = (string) $this->request->getPost('password');
        if ($password === '') {
            return redirect()->back()->with('error', 'Password is required.');
        }

        $userModel = new UserModel();
        $user = $userModel->find((int) session()->get('user_id'));

        $hashToVerify = $user['password_hash'] ?? '$2y$10$invalidhashpadding00000000000000000000000000000000000000';
        $isAdmin = ($user['role'] ?? '') === 'admin';

        if (! $user || ! $isAdmin || ! password_verify($password, $hashToVerify)) {
            return redirect()->back()->with('error', 'Invalid admin credentials.');
        }

        session()->set('admin_reauth_until', time() + self::REAUTH_WINDOW_SECONDS);

        return redirect()->to('/useradmin');
    }

    public function index()
    {
        if ($redirect = $this->requireAdminReauth()) {
            return $redirect;
        }

        helper('form');

        $userModel = new UserModel();
        $users = $userModel->orderBy('created_at', 'DESC')->findAll();

        return view('useradmin/index', [
            'users' => $users,
            'username' => session()->get('username'),
            'admin_reauth_until' => (int) session()->get('admin_reauth_until'),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdminReauth()) {
            return $redirect;
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'password' => 'required|min_length[8]|max_length[255]',
            'role' => 'required|in_list[admin,user]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $role = (string) $this->request->getPost('role');

        if ($this->usernameExists($username)) {
            return redirect()->back()->withInput()->with('error', 'Username is already taken.');
        }

        $userModel = new UserModel();
        $userModel->insert([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/useradmin')->with('success', 'User created successfully.');
    }

    public function edit(int $userId)
    {
        if ($redirect = $this->requireAdminReauth()) {
            return $redirect;
        }

        helper('form');

        [$user, $redirect] = $this->findUserOrRedirect($userId);
        if ($redirect) {
            return $redirect;
        }

        return view('useradmin/edit', [
            'user' => $user,
            'username' => session()->get('username'),
            'admin_reauth_until' => (int) session()->get('admin_reauth_until'),
        ]);
    }

    public function update(int $userId)
    {
        if ($redirect = $this->requireAdminReauth()) {
            return $redirect;
        }

        [$user, $redirect] = $this->findUserOrRedirect($userId);
        if ($redirect) {
            return $redirect;
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'role' => 'required|in_list[admin,user]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $username = trim((string) $this->request->getPost('username'));
        $role = (string) $this->request->getPost('role');

        if ($this->usernameExists($username, $userId)) {
            return redirect()->back()->withInput()->with('error', 'Username is already taken.');
        }

        $currentUserId = (int) session()->get('user_id');
        if ($userId === $currentUserId && $role !== 'admin') {
            return redirect()->back()->withInput()->with('error', 'You cannot remove your own admin role.');
        }

        $userModel = new UserModel();
        $userModel->update($userId, [
            'username' => $username,
            'role' => $role,
        ]);

        if ($currentUserId === $userId) {
            session()->set('username', $username);
        }

        return redirect()->to('/useradmin/users/' . $userId . '/edit')->with('success', 'User updated successfully.');
    }

    public function changePassword(int $userId)
    {
        if ($redirect = $this->requireAdminReauth()) {
            return $redirect;
        }

        [$user, $redirect] = $this->findUserOrRedirect($userId);
        if ($redirect) {
            return $redirect;
        }

        $rules = [
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $password = (string) $this->request->getPost('password');

        $userModel = new UserModel();
        $userModel->update($userId, [
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        return redirect()->to('/useradmin/users/' . $userId . '/edit')->with('success', 'Password updated successfully.');
    }

    public function delete(int $userId)
    {
        if ($redirect = $this->requireAdminReauth()) {
            return $redirect;
        }

        [$user, $redirect] = $this->findUserOrRedirect($userId);
        if ($redirect) {
            return $redirect;
        }

        $currentUserId = (int) session()->get('user_id');
        if ($userId === $currentUserId) {
            return redirect()->to('/useradmin')->with('error', 'You cannot delete your own account.');
        }

        if (($user['role'] ?? 'user') === 'admin' && $this->adminCount() <= 1) {
            return redirect()->to('/useradmin')->with('error', 'You must keep at least one admin account.');
        }

        $userModel = new UserModel();
        $userModel->delete($userId);

        return redirect()->to('/useradmin')->with('success', 'User deleted successfully.');
    }
}
