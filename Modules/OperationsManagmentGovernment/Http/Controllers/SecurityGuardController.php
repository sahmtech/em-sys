<?php
namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            // Find the security guard
            $securityGuard = User::findOrFail($id);

            $rules = [
                'first_name'      => 'required|string|max:50',
                'mid_name'        => 'required|string|max:50',
                'last_name'       => 'required|string|max:50',
                'profession'      => 'required|exists:essentials_professions,id',
                'id_proof_number' => 'required|numeric|min:10',
                'fingerprint_no'  => 'required|max:50',
            ];
            $message = [
                'first_name.required'      => 'الرجاء ادخال :attribute ',
                'first_name.max'           => ' الحد الاعلى المسموح به 5 خانات في :attribute',
                'mid_name.required'        => 'الرجاء ادخال :attribute ',
                'last_name.required'       => 'الرجاء ادخال :attribute ',
                'profession.required'      => 'الرجاء تحديد :attribute ',
                'profession.exists'        => 'ناسف لا يوجد :attribute من خلال هذا الرقم ',
                'fingerprint_no.required'  => 'الرجاء ادخال :attribute',
                'id_proof_number.required' => 'الرجاء ادخال رقم الهوية :attribute',
                'id_proof_number.numeric'  => 'رقم الهوية يجب ان يكون ارقام  :attribute',
                'id_proof_number.min'      => 'رقم الهوية يجب ان يكون من 10 ارقام',
            ];

            $validator = Validator::make($request->all(), $rules, $message);

            if ($validator->fails()) {
                return redirect()->route('security_guards')->withErrors($validator)->withInput();
            }

            // Update the user data
            $securityGuard->update([
                'first_name'      => $request->first_name,
                'mid_name'        => $request->mid_name,
                'last_name'       => $request->last_name,
                'id_proof_number' => $request->id_proof_number,
                'custom_field_1'  => $request->profession, // profession id
            ]);

            // Redirect with success message
            return redirect()->route('security_guards')->with('success', __('messages.updated_success'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch validation exception and redirect back with error messages
            return redirect()->route('security_guards')
                ->withErrors($e->errors()) // Return the validation errors
                ->withInput();             // Retain old input values
        } catch (\Exception $e) {
            // Log the error and show a generic error message
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            // Return a generic error message
            return redirect()->route('security_guards')->withErrors([__('messages.something_went_wrong')]);
        }
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
