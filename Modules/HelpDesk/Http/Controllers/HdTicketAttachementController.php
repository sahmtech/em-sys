<?php

namespace Modules\HelpDesk\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HelpDesk\Entities\HdAttachment;

class HdTicketAttachementController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
     
      
            $attachments = HdAttachment::where('ticket_id',request()->id)->get();
    
            if (! $attachments){
                return response()->json(['error' => 'attachment not found'], 404);
            }
    

            return response()->json($attachments);
        }
    

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function replyAttachIndex()
    {
        $attachments = HdAttachment::where('reply_id',request()->id)->get();
    
        if (! $attachments){
            return response()->json(['error' => 'attachment not found'], 404);
        }


        return response()->json($attachments);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('helpdesk::show');
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
