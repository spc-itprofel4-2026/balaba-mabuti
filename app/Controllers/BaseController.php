<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 */
abstract class BaseController extends Controller
{
    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Helpers available to every controller that extends BaseController.
        // Must be set before parent::initController() runs.
        $this->helpers = ['form', 'url'];

        parent::initController($request, $response, $logger);
    }

    /**
     * Renders a view inside the shared layout.
     *
     * @param array<string, mixed> $data
     */
    protected function page(string $view, array $data = []): string
    {
        $data['pageTitle'] = $data['pageTitle'] ?? 'Viber Announcements';
        $data['content']   = view($view, $data);

        return view('layout', $data);
    }

    /**
     * The signed-in officer, or null when the session is empty.
     *
     * @return object|null
     */
    protected function currentOfficer()
    {
        $id = session()->get('officerId');

        return $id ? (object) [
            'id'   => $id,
            'name' => session()->get('officerName'),
            'role' => session()->get('officerRole'),
        ] : null;
    }
}
