<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers\Admin;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Helpers\Csrf;
use GatewayAPI\Models\Role;
use GatewayAPI\Models\User;

final class UserManagerController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        return $this->render('admin/user-list', [
            'users' => User::allWithRoles(),
            'roles' => Role::all('id ASC'),
        ], 'admin');
    }

    public function updateRole(Request $request, string $id): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect('/admin/users');
        }

        User::updateById((int) $id, ['role_id' => (int) $request->input('role_id', 2)]);
        flash('success', 'User role updated.');
        return $this->redirect('/admin/users');
    }
}