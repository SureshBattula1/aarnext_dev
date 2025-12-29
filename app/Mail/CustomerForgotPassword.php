<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $customer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $customer = null)
    {
        $this->token = $token;
        $this->customer = $customer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reset Password Notification')
                    ->view('emails.customer_forgot_password');
    }
}
