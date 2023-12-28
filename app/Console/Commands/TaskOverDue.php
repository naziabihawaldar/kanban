<?php

namespace App\Console\Commands;

use App\Models\Card;
use Illuminate\Console\Command;

class TaskOverDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:task-over-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = \Carbon\Carbon::yesterday()->toDateString();        
        $tasks = Card::whereDate('due_date',$yesterday)->whereNull('task_status')->get();
        if($tasks)
        {
            foreach ($tasks as $task) 
            {
                $task->task_status = 'overdue';
                $task->save();
            }
        }

    }
}
