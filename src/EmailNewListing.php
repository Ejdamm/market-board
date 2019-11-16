<?php


namespace Startplats;


use Anddye\Mailer\Mailable;

class EmailNewListing extends Mailable
{
    protected $email_variables;

    public function __construct($email_variables)
    {
        $this->email_variables = $email_variables;
    }

    public function build()
    {
        $this->setSubject('Startplats - Your removal code');
        $this->setView('emails/new_listing_confirmation.html.twig', [
            'params' => $this->email_variables
        ]);

        return $this;
    }
}