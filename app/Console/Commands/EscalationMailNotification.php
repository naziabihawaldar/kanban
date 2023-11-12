<?php

namespace App\Console\Commands;

use App\Mail\EscalationMail;
use App\Models\Card;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EscalationMailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:escalation-mail-notification';

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
        $yesterday = \Carbon\Carbon::yesterday();
        $cards = Card::whereDate('due_date', $yesterday)->get();
        foreach ($cards as $card) {
            $board = $card->board;
            if($board)
            {
                $manager = $board->user;
                $assignee = $card->users()->first();
                try {
                    $mailData = [
                        'username' => $manager->name,
                        'taskTitle' => $card->content,
                        'assignedToName' => optional($assignee)->name,
                    ];
                    Mail::to($manager->email)->send(new EscalationMail($mailData));
                } catch (\Exception $e) {
                    logger($e);
                }
            }
        }
    }
}
