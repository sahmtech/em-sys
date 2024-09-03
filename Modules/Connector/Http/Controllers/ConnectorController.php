<?php

namespace Modules\Connector\Http\Controllers;

use App\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;

class ConnectorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('connector::index');
    }

    public function user_device()
    {
        $user_devices = UserDevice::with('user');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (request()->ajax()) {
            return DataTables::of($user_devices)
                ->addColumn('name', function ($row) {
                    return $row->user->first_name . ' ' . $row->user->last_name;
                })
                ->editColumn('id_proof_number', function ($row) {
                    return $row->user->id_proof_number;
                })
                ->addColumn('action', function ($row) use ($is_admin) {

                    $html = '';
                    if ($is_admin) {

                        $html .= '<a class="btn btn-xs btn-danger" href="' . route('user_device_delete', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</a>';
                    }

                    return $html;
                })
                ->make(true);
        }
        return view('connector::user_device');
    }


    public function user_device_delete($id)
    {
        try {
            UserDevice::where('id', $id)->delete();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->back()->with('status', $output);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('connector::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return view('connector::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        return view('connector::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request) {}

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy() {}
}
