<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserPoint\UserPointRepository;
use Illuminate\Contracts\Container\Container;

class RechargePremiumCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recharge:premium:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $userPointRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Container $app)
    {
        parent::__construct();
        $this->userPointRepository = $app->make(UserPointRepository::class);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user_ids = $this->userPointRepository->browseUserByRole('premium');
        $this->userPointRepository->rechargeAccount($user_ids, 40);
        \Log::info("All premium user has been re-charged successfully");
    }
}
