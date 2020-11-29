<?php


namespace MarketBoard\Controllers;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use MarketBoard\EmailSeller;
use MarketBoard\Listings;
use MarketBoard\Utils;
use stdClass;

class SingleListingController extends BaseController
{
    private $listings;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->listings = new Listings($this->db);
    }

    public function get($request, $response, $args): ResponseInterface
    {
        try {
            $listing = $this->listings->getSingleListing($args['id']);
            if ($listing == null) {
                $this->logger->addInfo("/listings/" . $args['id'] . " 404 Tried to access non-existing listing");
                return $this->view->render($response->withStatus(404), 'errors/error404.html.twig', [
                    'language' => $this->language,
                ]);
            }

            return $this->view->render($response, 'single_listing.html.twig', [
                'listing' => $listing,
                'language' => $this->language,
                'captcha' => Utils::createCaptcha($this->session),
            ]);
        } catch (Exception $e) {
            $this->logger->addError("/listings/" . $args['id'] . " GET threw exception: " . $e);
            return $this->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                'language' => $this->language,
            ]);
        }
    }

    public function post($request, $response, $args): ResponseInterface
    {
        try {
            $params = $request->getParams();

            if (array_key_exists("removal_form", $params)) {
                $responseParams = $this->processRemove($args['id'], $params['removal_code']);
                return $this->view->render($response, 'remove_listing.html.twig', $responseParams);
            } elseif (array_key_exists("email_form", $params)) {
                $responseParams = $this->processContact($args['id'], $params);
                return $this->view->render($response, 'single_listing.html.twig', $responseParams);
            } else {
                throw new Exception("Neither email_form nor removal_form was set.");
            }
        } catch (Exception $e) {
            $this->logger->addError("/listings/" . $args['id'] . " POST threw exception: " . $e);
            return $this->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                'language' => $this->language,
            ]);
        }
    }

    private function processContact($listingId, $params)
    {
        $listing = $this->listings->getSingleListing($listingId);

        if (!$params['captcha'] || $this->session->get('captcha') != $params['captcha']) {
            $alert_text = $this->language['wrong_captcha'];
            $alert = ['level' => 'warning', 'text' => $alert_text];
        } else {
            $this->sendEmail($listingId, $params['email_from'], $listing['email'], $params["email_text"]);
            $alert_text = "<strong>" . $this->language['success'] . "</strong> " . $this->language['your_email_was_sent'];
            $alert = ['level' => 'info', 'text' => $alert_text];

            //We only refill form if it was unsuccessful
            $params = null;
        }

        return [
            'alert' => $alert,
            'listing' => $listing,
            'language' => $this->language,
            'captcha' =>Utils::createCaptcha($this->session),
            'params' => $params,
        ];
    }

    private function processRemove($listingId, $removalCode)
    {
        $affected_rows = $this->listings->removeListing($listingId, $removalCode);

        $alert = [];
        if ($affected_rows >= 1) {
            $alert['text'] = "<strong>" . $this->language['success'] . "</strong> "
                . $this->language['listing_removed_success'] . " <a href=\"/\">" . $this->language['go_back_to_start'] . "</a>";
            $alert['level'] = "success";
            $this->logger->addInfo("Listing removed: " . $listingId);
        } else {
            $alert['text'] = "<strong>" . $this->language['warning'] . "</strong> "
                . $this->language['listing_removed_failed']
                . " <a href=\"/listings/" . $listingId . "\">" . $this->language['go_back_to_listing'] . "</a>";
            $alert['level'] = "warning";
            $this->logger->addWarning("Listing was not removed: " . $listingId);
        }

        return [
            'alert' => $alert,
            'language' => $this->language,
            'captcha' => Utils::createCaptcha($this->session),
        ];
    }

    private function sendEmail($listingId, $sender, $receiver, $text)
    {
        $this->logger->addInfo("Sending email from: " . $sender . " to: " . $receiver);
        $message = new stdClass;
        $message->listingId = $listingId;
        $message->message = $text;

        // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
        if (getenv('TRAVIS') != 'true') {
            $emailSeller = new EmailSeller($message, $this->language['email_contact_seller_subject']);
            $emailSeller->setFrom($sender);
            $this->mailer->setTo($receiver)->sendMessage($emailSeller);
        }
    }
}
