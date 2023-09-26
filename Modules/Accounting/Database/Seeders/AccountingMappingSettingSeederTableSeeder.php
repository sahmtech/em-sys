<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\AccountingMappingSetting;

class AccountingMappingSettingSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccountingMappingSetting::query()->truncate();
        $data = [
            [
                'sub_type' => 'sell',
                'type' => 'credit',
                'map_type' => 'payment_account',
                'accounting_account_id'=> 49,
                'method' => 'cash'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 20,
                'method' => 'cash'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 5,
                'method' => 'cash'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'credit',
                'map_type' => 'payment_account',
                'accounting_account_id'=> 49,
                'method' => 'card'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 20,
                'method' => 'card'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 5,
                'method' => 'card'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'credit',
                'map_type' => 'payment_account',
                'accounting_account_id'=> 49,
                'method' => 'cheque'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 20,
                'method' => 'cheque'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 5,
                'method' => 'cheque'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'credit',
                'map_type' => 'payment_account',
                'accounting_account_id'=> 49,
                'method' => 'bank_transfer'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 20,
                'method' => 'bank_transfer'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 5,
                'method' => 'bank_transfer'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'credit',
                'map_type' => 'payment_account',
                'accounting_account_id'=> 49,
                'method' => 'other'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 20,
                'method' => 'other'
            ],
            [
                'sub_type' => 'sell',
                'type' => 'debit',
                'map_type' => 'deposit_to',
                'accounting_account_id'=> 5,
                'method' => 'other'
            ],
        ];
        foreach ($data as $d){
            AccountingMappingSetting::query()->create($d);
        }
    }
}
