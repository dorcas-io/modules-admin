<?php

namespace Dorcas\ModulesAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Dorcas\ModulesAdmin\Models\ModulesAdmin;
use App\Dorcas\Hub\Utilities\UiResponse\UiResponse;
use App\Http\Controllers\HomeController;
use Hostville\Dorcas\Sdk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class ModulesAdminController extends Controller {

    public function __construct()
    {
        parent::__construct();
        $this->data = [
            'page' => ['title' => config('modules-admin.title')],
            'header' => ['title' => 'Administration'],
            'selectedMenu' => 'modules-admin',
            'submenuConfig' => 'navigation-menu.modules-admin.sub-menu',
            'submenuAction' => ''
        ];
    }

    public function index()
    {
    	return view('modules-admin::index', $this->data);
    }

}