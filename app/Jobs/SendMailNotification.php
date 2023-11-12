<?php

namespace App\Jobs;

use App\Mail\AssignedCardMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $mail_type,$data,$send_email_to;
    /**
     * Create a new job instance.
     */
    public function __construct($data,$mailType=null,$to_email)
    {
        $this->mail_type = $mailType;
        $this->data = $data;
        $this->send_email_to = $to_email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mail_code = 'customer_send_ticket_created';
        if($this->mail_type != '')
        {
            $mail_code = $this->mail_type;
        }
        // Mail::to('your_email@gmail.com')->send(new DemoMail($mailData));
        Mail::to($this->send_email_to)->send(new AssignedCardMail($this->data));
    }
}
