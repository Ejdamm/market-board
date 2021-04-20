<?php


namespace MarketBoard\Controllers;

use Exception;
use MarketBoard\Categories;
use MarketBoard\EmailNewListing;
use MarketBoard\Listings;
use MarketBoard\Utils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class NewListingController extends BaseController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function get($request, $response, $args) : ResponseInterface
    {
        $categories = new Categories($this->db);
        return $this->render($response, 'new_listing.html.twig', [
            'categories' => $categories->getMainCategories(),
            'subcategories' => $categories->getSubcategories(),
            'captcha' => Utils::createCaptcha($this->session),
        ]);
    }

    public function post($request, $response, $args) : ResponseInterface
    {
        $params = $request->getParams();

        if (array_key_exists("new_listing_form", $params)) {
            $responseParams = $this->processNewListing($params);
        } elseif (array_key_exists("refresh_captcha", $params)) {
            $categories = new Categories($this->db);
            $responseParams = [
                'categories' => $categories->getMainCategories(),
                'subcategories' => $categories->getSubcategories(),
                'captcha' => Utils::createCaptcha($this->session),
                'params' => $params,
            ];
        } else {
            throw new Exception("Unknown post request was sent.");
        }
        return $this->render($response, 'new_listing.html.twig', $responseParams);
    }

    private function processNewListing($params)
    {
        $this->logger->addDebug("Received post params:" . print_r($params, true));
        $removalCode = Utils::generateRemovalCode();
        $insertedId = null;

        if (!$params['captcha'] || $this->session->get('captcha') != $params['captcha']) {
            $alert = 'alerts/wrong_captcha.html.twig';
        } else {
            $insertedId = $this->insertNewListing($params, $removalCode);
            $this->sendEmail($insertedId, $removalCode, $params['email']);
            $alert = 'alerts/listing_added.html.twig';

            // We only refill form if it was unsuccessful
            $params = null;
        }

        $categories = new Categories($this->db);
        return [
            'categories' => $categories->getMainCategories(),
            'subcategories' => $categories->getSubcategories(),
            'alert' => $alert,
            'captcha' => Utils::createCaptcha($this->session),
            'removalCode' => $removalCode,
            'listingId' => $insertedId,
            'params' => $params,
        ];
    }

    private function insertNewListing($params, $removalCode)
    {
        $listings = new Listings($this->db);
        $insertedId = $listings->insertListing($params, $removalCode);
        $this->logger->addDebug("Parameters inserted:", $params);
        return $insertedId;
    }

    private function sendEmail($insertedId, $removalCode, $address)
    {
        $emailParams = new stdClass;
        $emailParams->insertedId = $insertedId;
        $emailParams->removalCode = $removalCode;
        $emailParams->language = $this->language;
        // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
        if (getenv('TRAVIS') != 'true') {
            $newListingEmail = new EmailNewListing($emailParams, $this->language['email_new_listing_subject'], $this->settings['domain']);
            $this->mailer->setTo($address)->sendMessage($newListingEmail);
            $this->logger->addDebug("Sent email to " . $address);
        }
    }
}
