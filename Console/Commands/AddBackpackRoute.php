<?php

namespace modules\CyberFranco\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AddCustomRouteContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:add-custom-route
                                {code : HTML/PHP code that registers a route. Use either single quotes or double quotes. Never both. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add HTML/PHP code to the routes/backpack/custom.php file';

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
        Artisan::call('backpack:add-custom-route',['code' => "Route::crud('pdf-request', 'PdfRequestCrudController')"]);
        $this->comment("Pdf Request Backpack toure added successfully");
    }
}