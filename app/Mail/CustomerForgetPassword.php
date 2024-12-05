<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\User;

class CustomerForgetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
     public function __construct(User $customer)
     {
       $this->customer = $customer;
     }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      return $this->markdown('emails.customer.forget-password')->with([
        'customer' => $this->customer,
        'url' => url('/customer/forget-password/'.$this->customer->token_email_verification)
      ]);
    }
}
