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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class ModulesAdminEndpointController extends Controller {


    private const FEATURES = ['orders', 'customers'];

    private $partner;

    public function __construct()
    {
        parent::__construct();
        $this->data = [
            'status' => false,
            'message' => '',
            'data' => [],
            'feature' => null
        ];

        $partnerAdmin = DB::connection('core_mysql')->table("companies")->where('id', 1)->first(); 
        $this->partner = $partnerAdmin;
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

    public function features(Request $request,)
    {

        if ( empty($request->feature) || !in_array($request->feature, self::FEATURES) ) {
            return response()->json(['status' => false, 'message' => 'Invalid Feature', 'data' => []], 401);
        }
        $this->data['feature'] = $feature = $request->feature;

        $pre = !empty($request->pre) && ( $request->pre === true || $request->pre === "true" ) ? true : false;
    	
        $data_package = [
            "table" => "",
            "where" => [],
            "select" => [],
            "joins" => []
        ];

        switch ($feature) {

            case "orders":
                $data_package["table"] = "orders as o";
                $data_package["select"] = $pre ? ["uuid", "updated_at"] : [
                    "o.uuid",
                    "o.company_id",
                    "o.updated_at",
                    "o.status",
                    "o.currency",
                    "o.amount",
                    \DB::raw('c.uuid as company_id'),
                ];
                $data_package["join"] = [
                    "table" => "company as c",
                    "column_local" => "company_id",
                    "column_foreign" => "id"
                ];
                break;

        }


        try {

            $data = DB::connection('core_mysql')->table($data_package["table"]);
            if (!empty($data_package["where"])) {
                $data->where($data_package["where"]);
            }
            if (!empty($data_package["select"])) {
                $data->select($data_package["select"]);
            }
            if (!empty($data_package["joins"])) {
                $join = $data_package["joins"][0];
                $data->join($join["table"], $join["column_local"], '=', $join["column_foreign"]);
            }
            $data->get();

        } catch (\Exception $e) {

            $this->data['status'] = false;
            $this->data['message'] = 'Error Querying Database ' . $e->getMessage();
            return response()->json($this->data, 401);

        }

        $this->data['status'] = true;
        $this->data['message'] = 'Data Successfully Queried';
        $this->data['data'] = $data;

        return response()->json($this->data);
    }

}