<?php

namespace App\Http\Controllers;


use App\Report;

use App\Utils\ModuleUtil;



class BusinessSectorController extends Controller
{
    protected $moduleUtil;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function landing()
    {

        return view('business_sector.index');
    }
}
