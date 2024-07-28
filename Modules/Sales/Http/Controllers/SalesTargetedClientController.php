<?php

namespace Modules\Sales\Http\Controllers;

use App\Product;
use App\TransactionSellLine;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Sales\Entities\salesService;
use Yajra\DataTables\Facades\DataTables;
use Modules\Sales\Entities\salesTargetedClient;

class SalesTargetedClientController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
    }
    public function clientAdd()
    {

        if (!auth()->user()->can('product.create')) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        //  $specializations=EssentialsProfession::where('type','academic')->pluck('name','id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        return view('sales::targetedClient.client_add')->with(compact('specializations', 'professions', 'nationalities'));
    }

    public function editClient($id)
    {
        if (!auth()->user()->can('product.create')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $client = SalesService::find($id);
            if (!$client) {
                return response()->json(['success' => false, 'message' => 'Client not found']);
            }

            $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
            $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
            $nationalities = EssentialsCountry::nationalityForDropdown();
            $additionalAllowances = [];

            if (!empty($client->additional_allwances)) {
                $additionalAllowances = json_decode($client->additional_allwances, true);
            }

            error_log(json_encode($additionalAllowances));

            return response()->json([
                'success' => true,
                'client' => $client,
                'specializations' => $specializations,
                'professions' => $professions,
                'nationalities' => $nationalities,
                'additional_allowances' => $additionalAllowances
            ]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('messages.something_went_wrong')
            ]);
        }
    }

    // public function saveQuickClient(Request $request)
    // {
    //     error_log(json_encode($request->all()));
    //     try {

    //         $business_id = $request->session()->get('user.business_id');
    //         $input = $request->only(['profession', 'specialization', 'nationality', 'selectedData', 'gender', 'monthly_cost', 'number', 'essentials_salary']);

    //         $input2['profession_id'] = $input['profession'];
    //         // $input2['specialization_id'] = $input['specialization'];
    //         $input2['nationality_id'] = $input['nationality'];
    //         $input2['gender'] = $input['gender'];
    //         $input2['service_price'] = $input['essentials_salary'];
    //         $input2['monthly_cost_for_one'] = $input['monthly_cost'];
    //         $input2['business_id'] = $business_id;
    //         $input2['quantity'] = $input['number'];

    //         $input2['created_by'] = $request->session()->get('user.id');

    //         $productData = json_decode($input['selectedData'], true);
    //         $input2['additional_allwances'] = json_encode($productData);

    //         $client = salesService::create($input2);
    //         $profession = DB::table('essentials_professions')->where('id', $client->profession_id)->first()->name;
    //         $nationality = DB::table('essentials_countries')->where('id', $client->nationality_id)->first()->nationality;
    //         error_log($client);

    //         $output = [
    //             'success' => 1,
    //             'client' => $client,
    //             'profession' => $profession,
    //             //   'specialization' => $specialization,
    //             'nationality' => $nationality,
    //             'quantity' => request()->number,
    //             'selectedData' => json_decode(request()->selectedData, true)

    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = [
    //             'success' => 0,
    //             'msg' => __('messages.something_went_wrong'),
    //         ];
    //     }

    //     return $output;
    // }
    public function saveQuickClient(Request $request)
    {
        error_log(json_encode($request->all()));
        try {
            $business_id = $request->session()->get('user.business_id');
            $input = $request->all();

            $input2['profession_id'] = $input['profession'];
            $input2['nationality_id'] = $input['nationality'];
            $input2['gender'] = $input['gender'];
            $input2['service_price'] = $input['essentials_salary'];
            $input2['monthly_cost_for_one'] = $input['monthly_cost'];
            $input2['business_id'] = $business_id;
            $input2['quantity'] = $input['number'];
            $input2['created_by'] = $request->session()->get('user.id');
            $input2['gosi_amount'] = $input['gosiAmount'];
            $input2['vacation_amount'] = $input['vacationAmount'];
            $input2['end_service_amount'] = $input['endServiceAmount'];
            $productData = json_decode($input['selectedData'], true);
            $input2['additional_allwances'] = json_encode($productData);

            if (!empty($input['client_id'])) {
                // Update existing client
                error_log($input['client_id']);
                $client = SalesService::find($input['client_id']);
                if ($client) {
                    $client->update($input2);
                }
            } else {
                // Create new client
                $client = SalesService::create($input2);
            }

            $profession = DB::table('essentials_professions')->where('id', $client->profession_id)->first()->name;
            $nationality = DB::table('essentials_countries')->where('id', $client->nationality_id)->first()->nationality;
            error_log($client);

            $output = [
                'success' => 1,
                'client' => $client,
                'profession' => $profession,
                'nationality' => $nationality,
                'quantity' => $input['number'],
                'selectedData' => $productData,
                'action' => !empty($input['client_id']) ? 'edit' : 'add'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }
    public function fetchSpecializations(Request $request)
    {
        $professionId = $request->input('profession_id');

        $specializations = EssentialsSpecialization::where('profession_id', $professionId)->pluck('name', 'id');

        return response()->json($specializations);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('sales::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function deleteClient(Request $request)
    {
        $clientId = $request->input('id');
        error_log($clientId);
        try {
            // Find the client by ID and delete it
            $client = SalesService::find($clientId);

            if ($client) {
                $client->delete();
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'Client not found']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the client']);
        }
    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
