<?php

namespace Modules\FollowUp\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\FollowUp\Entities\followupRecruitmentRequest;

class FollowUpRecruitmentRequestController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_followup_crud_recruitmentRequests = auth()->user()->can('followup.crud_recruitmentRequests');
        if (!($is_admin || $can_followup_crud_recruitmentRequests)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        if (request()->ajax()) {

            $recruitmentRequests = followupRecruitmentRequest::select([
                'id',
                'quantity',
                'nationality_id',
                'profession_id',
                'specialization_id',
                'date',
                'note',
                'status',
                'attachment',
                'assigned_to',
            ])->where('assigned_to', '=', 0);



            return Datatables::of($recruitmentRequests)
                ->editColumn('status', function ($row) {
                    if ($row->assigned_to != 0) {
                        return '';
                    }

                    $assignedRows = followupRecruitmentRequest::where('assigned_to', $row->id)->get();

                    $statusesAndQuantities = $assignedRows->map(function ($assignedRow) {
                        return trans('followup::lang.' . $assignedRow->status) . ': ' . $assignedRow->quantity;
                    })->prepend(trans('followup::lang.' . $row->status) . ': ' . $row->quantity)->implode(', ');

                    return $statusesAndQuantities;
                })

                ->editColumn('nationality_id', function ($row) use ($nationalities) {
                    $item = $nationalities[$row->nationality_id] ?? '';

                    return $item;
                })
                ->editColumn('profession_id', function ($row) use ($professions) {
                    $item = $professions[$row->profession_id] ?? '';

                    return $item;
                })
                ->editColumn('specialization_id', function ($row) use ($specializations) {
                    $item = $specializations[$row->specialization_id] ?? '';

                    return $item;
                })


                ->addColumn(
                    'attachments',
                    function ($row) {
                        $html = '';
                        if (!empty($row->attachment)) {
                            $html .= '<button class="btn btn-xs btn-info btn-modal" data-dismiss="modal" onclick="window.location.href = \'/uploads/' . $row->attachment . '\'"><i class="fa fa-eye"></i> ' . __('followup::lang.attachment_view') . '</button>';
                            '&nbsp;';
                        } else {
                            $html .= '<span class="text-warning">' . __('followup::lang.no_attachment_to_show') . '</span>';
                        }

                        //    $html .= '<a  href="'. route('req.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> '.__('messages.edit').'</a>';
                        //   $html .= '<button class="btn btn-xs btn-danger delete_req_button" data-href="' . route('req.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> '.__('messages.delete').'</button>';

                        return $html;
                    }
                )

                ->removeColumn('id')
                ->rawColumns(['attachments'])
                ->make(true);
        }


        return view('followup::requests.recruitmentRequests')->with(compact('specializations', 'professions', 'nationalities'));
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('followup::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['nationlity', 'quantity', 'date', 'profession', 'specialization', 'note']);

            $input2['nationality_id'] = $input['nationlity'];
            $input2['date'] = $input['date'];
            $input2['note'] = $input['note'];
            $input2['quantity'] = $input['quantity'];
            $input2['profession_id'] = $input['profession'];
            $input2['assigned_to'] = 0;
            $input2['specialization_id'] = $input['specialization'];


            if (isset($request->attachment) && !empty($request->attachment)) {
                $attachmentPath = $request->attachment->store('/recruitmentRequests');
                $input2['attachment'] = $attachmentPath;
            }

            followupRecruitmentRequest::create($input2);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }




        return redirect()->route('recruitmentRequests');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('followup::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('followup::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
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
