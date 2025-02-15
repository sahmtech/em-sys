<?php
namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Modules\Essentials\Entities\EssentialsProfession;
use Yajra\DataTables\Facades\DataTables;

class SecurityGuardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $is_admin    = auth()->user()->hasRole('Admin#1') ? true : false;
        $guard_users = User::where('user_type', 'guard')
            ->orderBy('id', 'desc')
            ->get();

        if ($request->ajax()) {

            return DataTables::of($guard_users)
                ->editColumn('full_name', function ($row) {
                    return $row->first_name ? trim($row->first_name . ' ' . $row->mid_name . ' ' . $row->last_name) : '';

                })
                ->editColumn('id_proof_number', function ($row) {
                    return $row->id_proof_number ?? '';
                })

                ->editColumn('fingerprint_no', function ($row) {
                    return $row->fingerprint_no ?? '';
                })

                ->editColumn('profession', function ($row) {
                    return $row->guardProfession->name ?? '';
                })

                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';

                        // Edit Button
                        if (($is_admin || auth()->user()->can('operationsmanagmentgovernment.delete_project_report'))) {
                            $html .= '
                                <a href="' . route('security_guards.edit', ['id' => $row->id]) . '"
                                   class="btn btn-sm btn-info action-button edit_security_guard_button"
                                   data-container="#edit_security_guards_model">
                                   <i class="fas fa-edit"></i> ' . __("messages.edit") . '
                                </a>
                            ';
                        }

                        // Delete Button
                        if ($is_admin) {
                            $html .= '
                                <button class="btn btn-sm btn-danger action-button delete_security_guard_button"
                                        data-href="' . route('security_guards.destroy', ['id' => $row->id]) . '">
                                    <i class="fas fa-trash"></i> ' . __("messages.delete") . '
                                </button>
                            ';
                        }

                        return $html;
                    }
                )

                ->rawColumns(['action', 'profession', 'fingerprint_no', 'full_name', 'id_proof_number'])
                ->make(true);
        }

        return view('operationsmanagmentgovernment::security_guards.index'); // Return the view
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $professions = EssentialsProfession::all()->pluck('name', 'id');

        return view('operationsmanagmentgovernment::security_guards.create', compact('professions'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $input = $request->only(['first_name', 'mid_name', 'last_name', 'fingerprint_no', 'id_proof_number', 'profession']);

            $user = User::create([
                'user_type'       => 'guard',
                'fingerprint_no'  => $input['fingerprint_no'],
                'id_proof_number' => $input['id_proof_number'],
                'first_name'      => $input['first_name'],
                'mid_name'        => $input['mid_name'],
                'last_name'       => $input['last_name'],
                'custom_field_1'  => $input['profession'], // Store profession id
            ]);

            // return $user;

            $output = [
                'success' => true,
                'msg'     => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg'     => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('success', $output['msg']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        $security_guard = User::findOrFail($id);
        $professions    = EssentialsProfession::all()->pluck('name', 'id');
        return view('operationsmanagmentgovernment::security_guards.edit', compact('security_guard', 'professions'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // return $request->all();
            $securityGuard = User::findOrFail($id);

            $request->validate([
                'first_name'      => 'required|string|max:255',
                'mid_name'        => 'required|string|max:255',
                'last_name'       => 'required|string|max:255',
                'profession'      => 'required',
                'id_proof_number' => 'required:integer',
                'fingerprint_no'  => 'required|string|max:255',
            ]);

            $securityGuard->update([
                'first_name'      => $request->first_name,
                'mid_name'        => $request->mid_name,
                'last_name'       => $request->last_name,
                'id_proof_number' => $request->id_proof_number,
                'custom_field_1'  => $request->profession, //profession id
            ]);

            return redirect()->route('security_guards')->with('success', __('messages.updated_success'));

        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg'     => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('security_guards')->withErrors([$output['msg']]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['success' => false, 'msg' => __('messages.user_not_found')]);
        }

        $user->delete();

        return response()->json(['success' => true, 'msg' => __('messages.user_deleted')]);
    }
}
