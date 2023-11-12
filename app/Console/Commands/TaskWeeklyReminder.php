<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDueDateReminderMail;
use App\Models\Card;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TaskWeeklyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:task-weekly-reminder';

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
        $oneWeekAgo = \Carbon\Carbon::now()->subWeek()->startOfDay();
        logger($oneWeekAgo);
        $cards = Card::whereDate('due_date', $oneWeekAgo)->get();
        logger($cards);
        foreach ($cards as $card) 
        {
            $assignees = $card->users()->get();
            foreach ($assignees as $assignee) 
            {
                try
                {
                    $mailData = [
                        'username' => $assignee->name,
                        'taskTitle' => $card->content,
                    ];

                    Mail::to($assignee->email)->send(new WeeklyDueDateReminderMail($mailData));
                }catch(\Exception $e){
                    logger($e);
                }
            }
        }
    }
}
