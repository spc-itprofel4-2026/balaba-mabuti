<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Role gate: only accounts with role "admin" may pass.
 * Runs after AuthFilter, so the session is already established.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = (string) session()->get('officerRole');

        if ($role !== 'admin') {
            session()->setFlashdata('error', 'Admin access required. Your account does not have that role.');

            return redirect()->to('/dashboard');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
