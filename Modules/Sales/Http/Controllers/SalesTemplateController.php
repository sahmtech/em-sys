<?php

namespace Modules\Sales\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Entities\salesproposaltemplate;
use App\Media;
use DB;
use Illuminate\Http\Response;
use App\Contact;
class SalesTemplateController extends Controller
{
    
    protected $moduleUtil;
   
   public function __construct(ModuleUtil $moduleUtil)
   {
       $this->moduleUtil = $moduleUtil;
   }


    public function first_choice_offer_price_template()
    {
        $business_id = request()->session()->get('user.business_id');
        $proposal_template = salesproposaltemplate::with(['media'])
        ->where('business_id', $business_id)->where('subject','first_choice_offer_price')
        ->first();
   
        if (!$proposal_template) {
            return view('sales::templates.create')
                ->with('status', ['success' => 0,
                    'msg' => __('sales::lang.please_add_template'),
                ]);
        }
        return view('sales::templates.index')->with(compact('proposal_template'));
    }

    public function second_choice_offer_price_template()
    {
        
        $business_id = request()->session()->get('user.business_id');
        $proposal_template = salesproposaltemplate::with(['media'])
        ->where('business_id', $business_id)->where('subject','second_choice_offer_price')
        ->first();
       
        if (!$proposal_template) {
            return view('sales::templates.create')
                ->with('status', ['success' => 0,
                    'msg' => __('sales::lang.please_add_template'),
                ]);
        }

        return view('sales::templates.index')->with(compact('proposal_template'));
    }
    public function first_choice_sales_contract_template()
    {
        $business_id = request()->session()->get('user.business_id');
        $proposal_template = salesproposaltemplate::with(['media'])
        ->where('business_id', $business_id)->where('subject','first_choice_sales_contract')
        ->first();
        if (!$proposal_template) {
            return view('sales::templates.create')
            ->with('status', ['success' => 0,
                'msg' => __('sales::lang.please_add_template'),
            ]);
        }
        return view('sales::templates.index')->with(compact('proposal_template'));
    }

    public function second_choice_sales_contract_template()
    { 
        $business_id = request()->session()->get('user.business_id');
        $proposal_template = salesproposaltemplate::with(['media'])
        ->where('business_id', $business_id)->where('subject','second_choice_sales_contract')
        ->first();
        if (!$proposal_template) {
            return view('sales::templates.create')
                ->with('status', ['success' => 0,
                    'msg' => __('sales::lang.please_add_template'),
                ]);
        }
        return view('sales::templates.index')->with(compact('proposal_template'));
    }
  
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');


        $proposal_template = $this->__getProposalTemplate($business_id);

        return view('sales:templates.index')
            ->with(compact('proposal_template'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('sales.add_proposal_template'))) {
           //temp  abort(403, 'Unauthorized action.');
        }

        $proposal_template = salesproposaltemplate::with(['media'])
        ->where('business_id', $business_id)->where('subject','second_choice_offer_price')
        ->first();
        if ($proposal_template != null) {

            return view('sales::templates.index')
            ->with(compact('proposal_template'))
                ->with('status', ['success' => 0,
                    'msg' => __('sales::lang.template_is_already_created'),
                ]);
        }

        return view('sales::templates.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('sales.add_proposal_template'))) {
           //temp  abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'subject' => 'required',
            'body' => 'required',
        ]);

        try {
            $input = $request->only(['subject', 'body']);
            $input['business_id'] = $business_id;
            $input['created_by'] = request()->session()->get('user.id');

            $attachments = $request->file('attachments');

            DB::beginTransaction();
            $proposal_template = salesproposaltemplate::create($input);
            if (! empty($attachments)) {
                Media::uploadMedia($business_id, $proposal_template, request(), 'attachments');
            }
            DB::commit();
            $output = ['success' => 1,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);

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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
   
     private function __getProposalTemplate($business_id)
     {
         $proposal_template = salesproposaltemplate::with(['media'])
                                 ->where('business_id', $business_id)
                                 ->first();
 
         return $proposal_template;
     }
 
     public function getEdit($id)
     {
         $business_id = request()->session()->get('user.business_id');
         if (! (auth()->user()->can('sales.add_proposal_template'))) {
            //temp  abort(403, 'Unauthorized action.');
         }
 
         $proposal_template = salesproposaltemplate::with(['media'])
         ->where('business_id', $business_id)->where('id',$id)
         ->first();
        
         if (!$proposal_template) {
             return view('sales::templates.create')
                 ->with('status', ['success' => 0,
                     'msg' => __('sales::lang.please_add_template'),
                 ]);
         }
      
 
 
         return view('sales::templates.edit')
             ->with(compact('proposal_template'));
     }
 
     public function postEdit(Request $request)
     {
      
         $business_id = request()->session()->get('user.business_id');
         if (! (auth()->user()->can('sales.add_proposal_template'))) {
            //temp  abort(403, 'Unauthorized action.');
         }
 
         $proposal_template = salesproposaltemplate::with(['media'])
         ->where('business_id', $business_id)->where('id',$request->id)
         ->first();
        
         if (!$proposal_template) {
             return view('sales::templates.create')
                 ->with('status', ['success' => 0,
                     'msg' => __('sales::lang.please_add_template'),
                 ]);
         }
 
         $request->validate([
             'subject' => 'required',
             'body' => 'required',
         ]);
 
         try {
             $input = $request->only(['subject', 'body', 'cc', 'bcc']);
 
             $attachments = $request->file('attachments');
 
             DB::beginTransaction();
         
 
             $proposal_template->subject = $input['subject'];
             $proposal_template->body = $input['body'];
             $proposal_template->cc = $input['cc'];
             $proposal_template->bcc = $input['bcc'];
             $proposal_template->save();
 
             if (! empty($attachments)) {
                 Media::uploadMedia($business_id, $proposal_template, request(), 'attachments');
             }
             DB::commit();
             $output = ['success' => 1,
                 'msg' => __('lang_v1.updated'),
             ];
         } catch (\Exception $e) {
             DB::rollBack();
             \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            error_log('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
             $output = ['success' => 0,
                 'msg' => __('messages.something_went_wrong'),
             ];
         }
 
         return redirect()->back()->with('status', $output);
     }
 
    public function getView()
    {
         $business_id = request()->session()->get('user.business_id');

 
         if (empty($this->__getProposalTemplate($business_id))) {
             return redirect()
                 ->action([\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'create'])
                 ->with('status', ['success' => 0,
                     'msg' => __('sales::lang.please_add_template'),
                 ]);
         }
 
         $proposal_template = $this->__getProposalTemplate($business_id);
 
         return view('sales:templates.view')
             ->with(compact('proposal_template'));
    }
 
     public function send($id)
     {
         $business_id = request()->session()->get('user.business_id');

 
         $proposal_template = salesproposaltemplate::with(['media'])
         ->where('business_id', $business_id)->where('id',$id)
         ->first();
        
         if (!$proposal_template) {
             return view('sales::templates.create')
                 ->with('status', ['success' => 0,
                     'msg' => __('sales::lang.please_add_template'),
                 ]);
         }
 
 
         $contacts = Contact::where('type','qualified')->pluck('name','id');
 
         return view('sales::templates.send')
             ->with(compact('proposal_template', 'contacts'));
     }
 
     public function deleteProposalMedia(Request $request, $id)
     {
         $business_id = request()->session()->get('user.business_id');

 
         if (request()->ajax()) {
             try {
                 Media::deleteMedia($business_id, $id);
 
                 $output = ['success' => true,
                     'msg' => __('lang_v1.success'),
                 ];
             } catch (\Exception $e) {
                 $output = ['success' => false,
                     'msg' => __('messages.something_went_wrong'),
                 ];
             }
 
             return $output;
         }
     }
}
