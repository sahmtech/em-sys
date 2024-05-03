<?php

namespace App\Http\Controllers;

use App\BankAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Yajra\DataTables\Facades\DataTables;

class BankAccountsController extends Controller
{


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $banks = EssentialsBankAccounts::forDropdown();
       
        return view('business.partials.settings_add_bank_account',compact('banks'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        try {
            DB::beginTransaction();
            BankAccount::create([
                'bank_id'=>$request->bnak_id,
                'account_number'=>$request->account_number,
                'ibn'=>$request->ibn,
                'account_name'=>$request->account_name,
                'business_id'=>$business_id,
                'company_id'=>$company_id,
               
            ]);
            
            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.added_success')
            ];
            return redirect()->back()->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
        }
    }


    public function edit($id){
        $banks = EssentialsBankAccounts::forDropdown();
       $bank_account = BankAccount::find($id);
        return view('business.partials.settings_edit_bank_accounts',compact('banks','bank_account'));
    }


    public function update(Request $request,$id){
        try {
            DB::beginTransaction();
            $bank_account = BankAccount::find($id);
            $bank_account->update([
                'bank_id'=>$request->bnak_id,
                'account_number'=>$request->account_number,
                'ibn'=>$request->ibn,
                'account_name'=>$request->account_name,
               
            ]);
            
            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('housingmovements::lang.updated_success')
            ];
            return redirect()->back()->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
        }
    }
    
    public function delete($id){
        try {
            BankAccount::find($id)->delete();
          
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.deleted_success')
            ];
            return redirect()->back()->with('status', $output);
        } catch (Exception $e) {
            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ]);
        }
        return $output;
    }
    
}