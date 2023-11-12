<?php

namespace App\Console\Commands;

use App\Mail\DueDateReminderOneDayBeforeMail;
use App\Mail\ManagerDueDateReminder;
use App\Models\Card;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TaskOneDayBeforeReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:task-one-day-before-reminder';

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
        $oneDayAgo = \Carbon\Carbon::tomorrow();
        $cards = Card::whereDate('due_date', $oneDayAgo)->get();
        foreach ($cards as $card) {
            $board = $card->board;
            if ($board) {
                $manager = $board->user;
                $assignee_user = $card->users()->first();
                try {
                    $mailData = [
                        'username' => $manager->name,
                        'taskTitle' => $card->content,
                        'assignedToName' => optional($assignee_user)->name,
                    ];
                    Mail::to($manager->email)->send(new ManagerDueDateReminder($mailData));
                } catch (\Exception $e) {
                    logger($e);
                }
            }

            $assignees = $card->users()->get();
            foreach ($assignees as $assignee) {
                try {
                    $mailData = [
                        'username' => $assignee->name,
                        'taskTitle' => $card->content,
                    ];
                    Mail::to($assignee->email)->send(new DueDateReminderOneDayBeforeMail($mailData));
                } catch (\Exception $e) {
                    logger($e);
                }

            }


        }
    }
}
