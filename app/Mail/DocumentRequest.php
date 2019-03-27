<?php

namespace Intranet\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentRequest extends Mailable
{
    use Queueable, SerializesModels;
    
    public $colaboracion;
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($colaboracion,$email)
    {
        $this->colaboracion = $colaboracion;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('jsegura@cipfpbatoi.es')->view('email.documentRequest');
    }
}
