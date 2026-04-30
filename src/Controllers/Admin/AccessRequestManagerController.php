<?php

declare(strict_types=1);

namespace GatewayAPI\Controllers\Admin;

use GatewayAPI\Core\Controller;
use GatewayAPI\Core\Request;
use GatewayAPI\Helpers\Csrf;
use GatewayAPI\Helpers\Sanitizer;
use GatewayAPI\Models\AccessGrant;
use GatewayAPI\Models\AccessRequest;

final class AccessRequestManagerController extends Controller
{
    public function index(Request $request): \GatewayAPI\Core\Response
    {
        return $this->render('admin/access-requests', [
            'requests' => AccessRequest::pendingList(),
        ], 'admin');
    }

    public function review(Request $request, string $id): \GatewayAPI\Core\Response
    {
        if (!Csrf::validate((string) $request->input('csrf_token'))) {
            flash('error', 'Invalid CSRF token.');
            return $this->redirect('/admin/access-requests');
        }

        $record = AccessRequest::findDetailed((int) $id);

        if ($record === null) {
            return $this->abort(404, 'Access request not found.');
        }

        $status = in_array($request->input('status'), ['approved', 'rejected'], true)
            ? (string) $request->input('status')
            : 'rejected';

        AccessRequest::updateById((int) $id, [
            'status' => $status,
            'reviewed_by' => current_user()['id'] ?? null,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewer_note' => Sanitizer::multiline($request->input('reviewer_note', '')),
        ]);

        if ($status === 'approved' && !AccessGrant::hasGrant((int) $record['user_id'], (int) $record['api_service_id'])) {
            AccessGrant::insert([
                'user_id' => $record['user_id'],
                'api_service_id' => $record['api_service_id'],
                'granted_by' => current_user()['id'] ?? null,
            ]);
        }

        flash('success', 'Access request reviewed.');
        return $this->redirect('/admin/access-requests');
    }
}