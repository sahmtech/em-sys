<?php

namespace Modules\Sales\Entities;
use Modules\Sales\Entities\salesContract;
use App\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesOrdersOperation extends Model
{
    protected $guarded = ['id'];
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    public function project()
    {
        return $this->belongsTo(SalesProject::class, 'sales_project_id');
    }


    public function salesContract()
    {
        return $this->belongsTo(salesContract::class, 'sale_contract_id');
    }
}