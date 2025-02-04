<?php
namespace Modules\HelpDesk\Http\Controllers;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsDepartment;
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

        $user     = User::where('id', auth()->user()->id)->first();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $departments = EssentialsDepartment::where('is_main', 1)->pluck('name', 'id');

        $tickets = HdTicket::query()->orderBy('created_at', 'desc');

        if (request()->input('select_department_id') && request()->input('select_department_id') != 'all') {
            $select_department_id = request()->input('select_department_id');

            $tickets = $tickets->whereHas('user', function ($query) use ($select_department_id) {
                $query->where('essentials_department_id', $select_department_id);
            });
        }

        if (! $is_admin || $user->user_type != 'admin') {
            $tickets = $tickets->where('user_id', $user->id);
        }
        if (request()->ajax()) {
            return Datatables::of($tickets)
                ->addColumn('ticket_number', function ($row) {
                    return $row->ticket_number;
                })
                ->addColumn('title', function ($row) {
                    return $row->title;
                })

                ->addColumn('user', function ($row) {
                    return trim(implode(' ', array_filter([$row->user->first_name, $row->user->mid_name, $row->user->last_name])));
                })
                ->addColumn('department', function ($row) {
                    return $row->user?->essentialsDepartment?->name ?? '';
                })

                ->addColumn('user_reply', function ($row) {
                    $reply = HdTicketReply::where('ticket_id', $row->id)->orderBy('created_at', 'desc')->first();

                    return $reply->user?->first_name ?? '';
                })

                ->addColumn('status', function ($row) {
                    $status = $row->hdTicketStatus;

                    if ($status) {
                        // Assuming 'status_id' holds the ticket status (1 for open, 2 for closed)
                        if ($row->status_id == 1) {
                            $title = 'مفتوحة'; // Open status
                            $color = '#28a745';      // Green color for open
                        } elseif ($row->status_id == 2) {
                            $title = 'مغلقة'; // Closed status
                            $color = '#dc3545';    // Red color for closed
                        } else {
                            $title = $status->title;
                            $color = $status->color; // Fallback to the original color if other status exists
                        }

                        return "<span style='color: {$color}; font-weight: bold;'>{$title}</span>";
                    }

                    return '';
                })

                ->addColumn('priority', function ($row) {
                    $priorityMapping = [
                        'low'    => ['label' => 'منخفضة', 'color' => '#28a745'], // Green
                        'mid'    => ['label' => 'متوسطة', 'color' => '#ffc107'], // Yellow
                        'high'   => ['label' => 'عالية', 'color' => '#fd7e14'],   // Orange
                        'urgent' => ['label' => 'عاجلة', 'color' => '#dc3545'],   // Red
                    ];

                    $priority = $priorityMapping[$row->urgency] ?? ['label' => 'غير محدد', 'color' => '#6c757d']; // 'غير محدد' = Not Specified

                    return "<span style='background-color: {$priority['color']}; color: white; padding: 2px 6px; border-radius: 8px; font-size: 10px; font-weight: bold; border: 1px solid {$priority['color']};'>{$priority['label']}</span>";
                })

                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d');
                })
                ->addColumn('updated_at', function ($row) {
                    $reply = HdTicketReply::query()
                        ->where('ticket_id', $row->id)
                        ->latest('created_at')
                        ->first();

                    return optional($reply)->updated_at?->format('m-d-Y h:i A') ?? __('helpdesk::lang.no_replies_yet');

                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="' . route('tickets.show', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye"></i> ' . __('messages.view') . '</a>';

                    return $html;
                })
                ->rawColumns(['title', 'user', 'department', 'user_reply', 'priority', 'status', 'created_at', 'updated_at', 'action'])
                ->make(true);
        }
        $urgencies = [
            'low'    => __('helpdesk::lang.low'),
            'mid'    => __('helpdesk::lang.mid'),
            'high'   => __('helpdesk::lang.high'),
            'urgent' => __('helpdesk::lang.urgent'),
        ];
        return view('helpdesk::tickets.index')->with(compact('urgencies', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function updateStatus($id)
    {
        $ticket = HdTicket::find($id);

        // Check if the ticket exists
        if (! $ticket) {
            return redirect()->route('tickets.index')->with('error', 'التذكرة غير موجودة');
        }

        // Update the ticket status
        $ticket->update([
            'status_id' => 2, //  closed  status
        ]);

        $output = [
            'success' => true,
            'msg'     => __('lang_v1.close_ticket'),
        ];

        return redirect()->route('tickets.index')->with('status', $output);
    }

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
            $data  = [
                'user_id'   => auth()->user()->id,
                'title'     => $input['title'],
                'message'   => $input['message'],
                'status_id' => 1,
                'urgency'   => $input['urgency'],
            ];
            $ticket = HdTicket::create($data);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {

                    $path = $attachment->store('/tickets_attachments');
                    HdAttachment::create([
                        'ticket_id' => $ticket->id,
                        'type'      => 'ticket',
                        'path'      => $path,
                    ]);
                }
            }
            $output = [
                'success' => true,
                'msg'     => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg'     => __('messages.something_went_wrong'),
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
        $ticket    = HdTicket::with('attachments', 'replies', 'user', 'hdTicketStatus')->find($id);
        $urgencies = [
            'low'    => __('helpdesk::lang.low'),
            'mid'    => __('helpdesk::lang.mid'),
            'high'   => __('helpdesk::lang.high'),
            'urgent' => __('helpdesk::lang.urgent'),
        ];
        return view('helpdesk::tickets.show')->with(compact('ticket', 'urgencies'));
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
