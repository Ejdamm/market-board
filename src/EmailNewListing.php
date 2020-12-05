<?php


namespace MarketBoard;

use Anddye\Mailer\Mailable;

class EmailNewListing extends Mailable
{
    private $emailFields;

    public function __construct($emailFields, $subject)
    {
        $this->emailFields = $emailFields;
        $this->setSubject($subject);
    }

    public function build()
    {
        $this->setView('emails/new_listing_confirmation.html.twig', [
            'params' => $this->emailFields,
            'server_address' => $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'],
        ]);

        return $this;
    }
}
