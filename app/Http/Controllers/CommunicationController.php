<?php

namespace App\Http\Controllers;

use App\CommunicationMessage;
use App\CommunicationReplie;
use App\CommunicationAttachment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Yajra\DataTables\Facades\DataTables;

class CommunicationController extends Controller
{
    public function index($from)
    {
        $departmentIds = [];
        $route = route('Communication', ['from' => $from]);

        if ($from == 'hrm') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%بشرية%')
                ->pluck('id')->toArray();
        } else if ($from == 'employee_affairs') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%موظف%')
                ->pluck('id')->toArray();
        } else if ($from == 'ceomanagment') {
            $departmentIds = EssentialsDepartment::where(function ($query) {
                $query->where('name', 'LIKE', '%تنفيذ%');
            })->pluck('id')->toArray();
        } else if ($from == 'medicalInsurance') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%تأمين%')
                ->pluck('id')->toArray();
        } else if ($from == 'payrolls') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%رواتب%')
                ->pluck('id')->toArray();
        } else if ($from == 'followup') {
            $departmentIds = EssentialsDepartment::where(function ($query) {
                $query->where('name', 'LIKE', '%متابعة%')
                    ->orWhere(function ($query) {
                        $query->where('name', 'LIKE', '%تشغيل%')
                            ->where('name', 'LIKE', '%أعمال%');
                    })->orWhere(function ($query) {
                        $query->where('name', 'LIKE', '%تشغيل%')
                            ->where('name', 'LIKE', '%شركات%');
                    });
            })->pluck('id')->toArray();
        } else if ($from == 'generalmanagement') {
            $departmentIds = EssentialsDepartment::where(function ($query) {
                $query->where('name', 'LIKE', '%مجلس%')
                    ->orWhere('name', 'LIKE', '%عليا%')
                    ->orWhere('name', 'LIKE', '%عام%');
            })->pluck('id')->toArray();
        } else if ($from == 'generalmanagmentoffice') {
            $departmentIds = EssentialsDepartment::where(function ($query) {
                $query->where('name', 'LIKE', '%مكتب%');
            })->pluck('id')->toArray();
        } else if ($from == 'housingmovements') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%سكن%')
                ->pluck('id')->toArray();
        } else if ($from == 'internationalrelations') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%دولي%')
                ->pluck('id')->toArray();
        } else if ($from == 'legalaffairs') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%قانوني%')
                ->pluck('id')->toArray();
        } else if ($from == 'sales') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%مبيعات%')
                ->pluck('id')->toArray();
        } else if ($from == 'operationsmanagmentgovernment') {
            $departmentIds = EssentialsDepartment::where(function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'LIKE', '%تشغيل%')
                        ->where('name', 'LIKE', '%حكوم%');
                });
            })->pluck('id')->toArray();
        } else if ($from == 'movment') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%حرك%')
                ->pluck('id')->toArray();
        } else if ($from == 'work_cards') {
            $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%حكومي%')
                ->pluck('id')->toArray();
        }

        $user = User::where('id', auth()->user()->id)->first();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $messages = CommunicationMessage::query();

        $departments = EssentialsDepartment::whereNotIn('id', $departmentIds)->pluck('name', 'id');
        $users = User::select(
            'users.id',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''), ' ', COALESCE(users.last_name, '')) as name")
        )->pluck('name', 'id')->toArray();

        $messages = $messages->whereIn('sender_department_id', $departmentIds);
        $sentMessages = CommunicationMessage::whereIn('sender_department_id', $departmentIds)->with('replies', 'attachments')->get();

        $receivedMessages = CommunicationMessage::whereIn('reciever_department_id', $departmentIds)->with('replies', 'attachments')->get();
        if (request()->ajax()) {
            return Datatables::of($messages)
                ->editColumn('sender_id', function ($row) use ($users) {
                    return $row->sender_id ? $users[$row->sender_id] : '';
                })
                ->editColumn('reciever_department_id', function ($row) use ($departments) {
                    return $row->reciever_department_id ? $departments[$row->reciever_department_id] : '';
                })
                ->rawColumns(['sender_id', 'reciever_department_id'])
                ->make(true);
        }

        $urgencies = [
            'low' => __('helpdesk::lang.low'),
            'mid' => __('helpdesk::lang.mid'),
            'high' => __('helpdesk::lang.high'),
            'urgent' => __('helpdesk::lang.urgent')
        ];
        return view('communication_messages')->with(compact('from', 'route', 'sentMessages', 'receivedMessages', 'departments', 'users', 'urgencies'));
    }


    public function send_communication_message(Request $request)
    {

        $validatedData = $request->validate([
            'from' => 'required|string',
            'department' => 'required|integer|exists:essentials_departments,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'urgency' => 'required|in:low,mid,high,urgent',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        $sender_department_id = null;
        switch ($validatedData['from']) {
            case 'hrm':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%بشرية%')->pluck('id')->first();
                break;
            case 'employee_affairs':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%موظف%')->pluck('id')->first();
                break;
            case 'ceomanagment':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%تنفيذ%')->pluck('id')->first();
                break;
            case 'medicalInsurance':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%تأمين%')->pluck('id')->first();
                break;
            case 'payrolls':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%رواتب%')->pluck('id')->first();
                break;
            case 'followup':
                $sender_department_id = EssentialsDepartment::where(function ($query) {
                    $query->where('name', 'LIKE', '%متابعة%')
                        ->orWhere(function ($query) {
                            $query->where('name', 'LIKE', '%تشغيل%')->where('name', 'LIKE', '%أعمال%');
                        })->orWhere(function ($query) {
                            $query->where('name', 'LIKE', '%تشغيل%')->where('name', 'LIKE', '%شركات%');
                        });
                })->pluck('id')->first();
                break;
            case 'generalmanagement':
                $sender_department_id = EssentialsDepartment::where(function ($query) {
                    $query->where('name', 'LIKE', '%مجلس%')
                        ->orWhere('name', 'LIKE', '%عليا%')
                        ->orWhere('name', 'LIKE', '%عام%');
                })->pluck('id')->first();
                break;
            case 'generalmanagmentoffice':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%مكتب%')->pluck('id')->first();
                break;
            case 'housingmovements':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%سكن%')->pluck('id')->first();
                break;
            case 'internationalrelations':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%دولي%')->pluck('id')->first();
                break;
            case 'legalaffairs':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%قانوني%')->pluck('id')->first();
                break;
            case 'sales':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%مبيعات%')->pluck('id')->first();
                break;
            case 'operationsmanagmentgovernment':
                $sender_department_id = EssentialsDepartment::where(function ($query) {
                    $query->where('name', 'LIKE', '%تشغيل%')->where('name', 'LIKE', '%حكوم%');
                })->pluck('id')->first();
                break;
            case 'movment':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%حرك%')->pluck('id')->first();
                break;
            case 'work_cards':
                $sender_department_id = EssentialsDepartment::where('name', 'LIKE', '%حكومي%')->pluck('id')->first();
                break;
        }

        $message = CommunicationMessage::create([
            'sender_department_id' => $sender_department_id,
            'reciever_department_id' => $validatedData['department'],
            'sender_id' =>  auth()->user()->id,
            'title' => $validatedData['title'],
            'message' => $validatedData['message'],
            'urgency' => $validatedData['urgency'],
        ]);
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('/communticaton_attachments');
                CommunicationAttachment::create([
                    'communication_message_id' => $message->id,
                    'type' => 'message',
                    'path' => $path,
                ]);
            }
        }
        return redirect()->back()->with('status', [
            'success' => true,
            'msg' => __('lang_v1.sending_success'),
        ]);
    }
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        $reply = CommunicationReplie::create([
            'communication_message_id' => $id,
            'replay' => $request->input('reply'),
            'replied_by' =>  auth()->user()->id,
        ]);
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('/communticaton_attachments');

                CommunicationAttachment::create([
                    'communication_reply_id' => $reply->id,
                    'type' => 'reply',
                    'path' => $path,
                ]);
            }
        }
        return redirect()->back()->with('status', [
            'success' => true,
            'msg' => __('lang_v1.sending_success'),
        ]);
    }
}
