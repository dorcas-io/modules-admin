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


    private const FEATURES = ['businesses', 'orders', 'customers'];

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

    public function permissions(Request $request)
    {

        try {

            $partnerCompanyConfig = (array) $this->partner->extra_data;
            
            if (isset($partnerCompanyConfig["bridge_settings"]["permissions"]) && !empty($partnerCompanyConfig["bridge_settings"]["permissions"]) ) {
                $bridgePermissions = $partnerCompanyConfig["bridge_settings"]["permissions"];
            } else {
                $bridgePermissions = [
                    "meta" => [
                        "businesses" => true,
                        "orders" => true,
                        "customers" => true
                    ]
                ];
            }

        } catch (\Exception $e) {

            $this->data['status'] = false;
            $this->data['message'] = 'Error Getting Permissions: ' . $e->getMessage();
            return response()->json($this->data, 500);

        }

        $this->data['status'] = true;
        $this->data['message'] = 'Permissions Successfully Fetched';
        $this->data['data'] = $bridgePermissions;

        return response()->json($this->data);
    }

    public function feature(Request $request)
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
            "joins" => [],
            "group_by" => []
        ];

        switch ($feature) {

            case "businesses":
                $data_package["table"] = "companies as c";
                $data_package["joins"] = [];
                $data_package["select"] = $pre ? ["c.uuid", "c.updated_at"] : [
                    "c.uuid",
                    "c.name",
                    "c.phone",
                    "c.email",
                    "c.logo_url",
                    "c.extra_data",
                    //DB::raw('CONCAT("{\"prefix\":", prefix, ",\"reg_number\":", reg_number, ",\"website\":", website, "}") as other_data'),
                    DB::raw('JSON_OBJECT("prefix", c.prefix, "reg_number", c.reg_number, "website", c.website) as other_data'),
                    "c.created_at",
                    "c.updated_at",
                ];


                break;

            case "orders":
                $data_package["table"] = "orders as o";
                $data_package["joins"] = [
                    [
                        "table" => "companies as c",
                        "column_local" => "o.company_id",
                        "column_foreign" => "c.id"
                    ],
                    [
                        "table" => "customer_order as co",
                        "column_local" => "o.id",
                        "column_foreign" => "co.order_id"
                    ],
                    [
                        "table" => "customers as cc",
                        "column_local" => "co.customer_id",
                        "column_foreign" => "cc.id"
                    ],
                    [
                        "table" => "order_items as i",
                        "column_local" => "o.id",
                        "column_foreign" => "i.order_id"
                    ],
                    [
                        "table" => "products as p",
                        "column_local" => "i.product_id",
                        "column_foreign" => "p.id"
                    ]
                ];
                $data_package["select"] = $pre ? ["o.uuid", "o.updated_at"] : [
                    "o.uuid",
                    DB::raw('c.uuid as company_id'),
                    DB::raw('cc.uuid as customer_id'),
                    "o.status",
                    "o.currency",
                    "o.amount",
                    "co.is_paid",
                    "co.paid_at",
                    DB::raw('JSON_OBJECT("due_at", o.due_at, "reminder_on", o.reminder_on, "is_quote", o.is_quote) as other_data'),
                    DB::raw('JSON_OBJECTAGG(i.quantity, i.unit_price, p.name) as order_items'),
                    //JSON_OBJECTAGG(First_Name, Socre_In_Exhibiiton_match)
                    "o.created_at",
                    "o.updated_at",
                ];
                $data_package["group_by"] = "o.id";
                break;

            case "customers":
                $data_package["table"] = "customers as cs";
                $data_package["joins"][] = [
                    "table" => "companies as c",
                    "column_local" => "cs.company_id",
                    "column_foreign" => "c.id"
                ];
                $data_package["select"] = $pre ? ["cs.uuid", "cs.updated_at"] : [
                    "cs.uuid",
                    \DB::raw('c.uuid as company_id'),
                    "cs.firstname",
                    "cs.lastname",
                    "cs.phone",
                    "cs.email",
                    "cs.created_at",
                    "cs.updated_at",

                ];
                break;

        }


        try {

            $data = DB::connection('core_mysql')->table($data_package["table"]);

            if (!$pre && !empty($data_package["joins"])) {
                for ($i=0; $i < count($data_package["joins"]); $i++) { 
                    $join = $data_package["joins"][$i];
                    $data->join($join["table"], $join["column_local"], '=', $join["column_foreign"]);
                }
            }
            if (!empty($data_package["where"])) {
                $data->where($data_package["where"]);
            }
            if (!empty($data_package["select"])) {
                $data->select($data_package["select"]);
            }
            if (!empty($data_package["select"])) {
                $data->groupBy($data_package["group_by"][0]);
            }

            $results = $data->get();

        } catch (\Exception $e) {

            $this->data['status'] = false;
            $this->data['message'] = 'Error Querying Database: ' . $e->getMessage();
            return response()->json($this->data, 500);

        }

        $this->data['status'] = true;
        $this->data['message'] = 'Data Successfully Queried';
        $this->data['data'] = $results;

        return response()->json($this->data);
    }

}