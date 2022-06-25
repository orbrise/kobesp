<?php
 
namespace App\Console\Commands;
 
use Illuminate\Console\Command;
use Auth;
use Carbon\Carbon;
use App\Models\Product;
use Mail;
use Log;

class CheckStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:checkstock';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check Stock qty';
 
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
		Log::info('cron started');
		$products = Product::where('minorderqty', '!=', '')->orWhere('minorderqty', '>=', 0)->get();
		
		$n = 1;
		foreach($products as $prod){
			$minqty = $prod->minorderqty;
			if($prod->stockinput <= $minqty){
				$data[] = [
					'no' => $n++,
					'product_id' => $prod->id,
					'product_name' => $prod->title,
					'school' => $prod->schoold->name,
					'class' => $prod->classd->name,
					'category' => $prod->getCat->name,
					'stock' => $prod->stockinput,
				];
			
			}
		}
		 Log::info('mail prepared');
		
		if(!empty($data) and count($data) > 0){
			Mail::send('frontend.mails.stockalert',['data' => $data], function ($message)
             {
                $message->from('info@kobesp.com.my', 'KOBESP');
                $message->subject('KOBESP Stock Alert');
                $message->to('pustaka.mindadexsb@gmail.com');
            });
			Log::info('mail sent');
		}
		
		Log::info('cron end');
    }
}