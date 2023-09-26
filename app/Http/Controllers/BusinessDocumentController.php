<?php

namespace App\Http\Controllers;

use App\BusinessDocument;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
class BusinessDocumentController extends Controller
{
    protected $moduleUtil;
   

     public function __construct(ModuleUtil $moduleUtil)
     {
         $this->moduleUtil = $moduleUtil;
     }
    public function show($business_id){
    
  
        if (! auth()->user()->can('business_documents.view') ) {
            abort(403, 'Unauthorized action.');
        }
      
       $business_id = request()->session()->get('user.business_id');
       $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

       if (request()->ajax()) {  
         
            $business = BusinessDocument::where('business_id', $business_id)
            ->select(['id','licence_type','licence_number','licence_date','renew_date','expiration_date','issuing_location','details','path_file']);

            
            return Datatables::of($business)
            
          
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->removeColumn('id')
            ->rawColumns(['action'])
            ->make(true);
         
        }
     
    
        return view('essentials::bussines_manage.docsView')->with(compact('business_id'));



    }

    public function store(Request $request)
    {

    $businessDocument = new BusinessDocument();
    $businessDocument->licence_type = $request->licence_type;
    $businessDocument->licence_number = $request->licence_number;
    $businessDocument->business_id = $request->business_id;
    $businessDocument->licence_date = $request->licence_date;
    $businessDocument->renew_date = $request->renew_date;
    $businessDocument->expiration_date = $request->expiration_date;
    $businessDocument->issuing_location = $request->issuing_location;
    $businessDocument->details = $request->details;
    
        
    if ($request->hasFile('file')) {
        error_log("11111111111");
        $file = $request->file('file');
        $filePath = $file->store('/public/business_documents'); 
        $businessDocument->path_file = $filePath;
    }
 

    $businessDocument->save();

    return redirect()->route('business_documents.view', ['id' => $request->business_id])->with('success', 'Business document added successfully');

}

}
