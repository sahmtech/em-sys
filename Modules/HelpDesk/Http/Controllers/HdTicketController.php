<?php

namespace Modules\HelpDesk\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HelpDesk\Entities\HdAttachment;
use Modules\HelpDesk\Entities\HdTicket;
use Modules\HelpDesk\Entities\HdTicketReply;
use Yajra\DataTables\Facades\DataTables;

class HdTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $tickets = HdTicket::query();

        if (!$is_admin ||  $user->user_type != 'admin') {
            $tickets =  $tickets->where('user_id',  $user->id);
        }
        if (request()->ajax()) {
            return Datatables::of($tickets)
                ->addColumn('ticket_number', function ($row) {
                    return $row->ticket_number;
                })
                ->addColumn('title', function ($row) {
                    return $row->title;
                })
                ->addColumn('status', function ($row) {
                    return $row->hdTicketStatus->title ?? '';
                })
                ->addColumn('last_update_date', function ($row) {
                    return $row->last_update_date ?? $row->created_at;
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="' . route('tickets.show', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye"></i> ' . __('messages.view') . '</a>';

                    return $html;
                })
                ->rawColumns(['title', 'status', 'last_update_date', 'action'])
                ->make(true);
        }
        $urgencies = [
            'low' => __('helpdesk::lang.low'),
            'mid' => __('helpdesk::lang.mid'),
            'high' => __('helpdesk::lang.high'),
            'urgent' => __('helpdesk::lang.urgent')
        ];
        return view('helpdesk::tickets.index')->with(compact('urgencies'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('helpdesk::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        try {
            $input = $request->all();
            $data = [
                'user_id' => auth()->user()->id,
                'title' =>  $input['title'],
                'message' => $input['message'],
                'status_id' => 1,
                'urgency' => $input['urgency'],
            ];
            $ticket = HdTicket::create($data);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {

                    $path = $attachment->store('/tickets_attachments');
                    HdAttachment::create([
                        'ticket_id' => $ticket->id,
                        'type' => 'ticket',
                        'path' => $path,
                    ]);
                }
            }
            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('tickets.index')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $ticket = HdTicket::with('attachments', 'replies', 'user', 'hdTicketStatus')->find($id);
        $urgencies = [
            'low' => __('helpdesk::lang.low'),
            'mid' => __('helpdesk::lang.mid'),
            'high' => __('helpdesk::lang.high'),
            'urgent' => __('helpdesk::lang.urgent')
        ];
        return view('helpdesk::tickets.show')->with(compact('ticket', 'urgencies'));;
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('helpdesk::edit');
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
