<?php

namespace Xguard\LaravelKanban\Commands;

use Illuminate\Console\Command;
use Xguard\LaravelKanban\Actions\Notifications\NotifyOfTasksWithDeadlineInNext24Action;

class NotifyOfTasksWithDeadlineInNext24 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kanban:notify-tasks-with-incoming-deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify slack channel of tasks with deadline in the next 24 hours';

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
        app(NotifyOfTasksWithDeadlineInNext24Action::class)->run();
    }
}
