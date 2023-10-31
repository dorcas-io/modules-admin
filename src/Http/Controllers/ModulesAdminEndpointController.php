<?php

namespace Dorcas\ModulesAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Dorcas\ModulesAdmin\Models\ModulesAdmin;
use App\Dorcas\Hub\Utilities\UiResponse\UiResponse;
use Hostville\Dorcas\Sdk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class ModulesAdminEndpointController extends Controller {

    public function __construct()
    {
        parent::__construct();
        $this->data = [
            'status' => false,
            'message' => '',
            'data' => [],
            'feature' => null
        ];
    }

    public function test()
    {
    	$this->data = [
            'status' => true,
            'message' => 'Test Works',
            'data' => [],
            'feature' => 'test'
        ];
        return response()->json($this->data);
    }

}