<?php

namespace Modules\CyberFranco\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AddFrancoBackpackRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'franco:add-backpack-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Franco backpack route if needed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('backpack:add-custom-route',['code' => "Route::crud('pdf-request', 'PdfRequestCrudController');"]);
        $this->comment("Pdf Request Backpack toure added successfully");
    }
}