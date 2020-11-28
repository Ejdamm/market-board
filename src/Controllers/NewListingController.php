<?php


namespace MarketBoard\Controllers;

use Exception;
use MarketBoard\Categories;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use MarketBoard\Listings;
use MarketBoard\EmailNewListing;
use MarketBoard\Utils;
use stdClass;
use Gregwar\Captcha\CaptchaBuilder;

class NewListingController extends BaseController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function get($request, $response, $args) : ResponseInterface
    {
        try {
            $categories = new Categories($this->db);
            return $this->view->render($response, 'new_listing.html.twig', [
                'categories' => $categories->getMainCategories(),
                'subcategories' => $categories->getSubcategories(),
                'language' => $this->language,
                'captcha' => $this->createCaptcha(),
            ]);
        } catch (Exception $e) {
            $this->logger->addError("/listings/new GET threw exception: " . $e);
            return $this->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                'language' => $this->language,
            ]);
        }
    }

    public function post($request, $response, $args) : ResponseInterface
    {
        try {
            $params = $request->getParams();
            $this->logger->addInfo("Received post params:" . print_r($params, true));

            if (!$params['captcha'] || $this->session->get('captcha') != $params['captcha']) {
                $alertText = $this->language['wrong_captcha'];
                $alertLevel =  'warning';
            } else {
                $removalCode = Utils::generateRemovalCode();
                $insertedId = $this->insertNewListing($params, $removalCode);
                $this->sendEmail($insertedId, $removalCode, $params['email']);
                $alertText = $this->getSuccessHtmlMessage($insertedId, $removalCode);
                $alertLevel = 'success';

                // We only refill form if it was unsuccessful
                $params = null;
            }

            $categories = new Categories($this->db);

            return $this->view->render($response, 'new_listing.html.twig', [
                'categories' => $categories->getMainCategories(),
                'subcategories' => $categories->getSubcategories(),
                'alert' => ['level' => $alertLevel, 'text' => $alertText],
                'language' => $this->language,
                'captcha' => $this->createCaptcha(),
                'params' => $params,
            ]);
        } catch (Exception $e) {
            $this->logger->addError("/listings/new POST threw exception: " . $e);
            return $this->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                'language' => $this->language,
            ]);
        }
    }

    private function insertNewListing($params, $removalCode)
    {
        $listings = new Listings($this->db);
        $insertedId = $listings->insertListing($params, $removalCode);
        $this->logger->addInfo("Parameters inserted:", $params);
        return $insertedId;
    }

    private function getSuccessHtmlMessage($insertedId, $removalCode)
    {
        return "<strong>" . $this->language['success'] . "</strong> "
            . $this->language['new_listing_inserted_message']
            . " <a href=\"/listings/$insertedId\">" . $this->language['here'] . "</a>. "
            .$this->language['removal_code'] . ": $removalCode";
    }

    private function sendEmail($insertedId, $removalCode, $address)
    {
        $emailParams = new stdClass;
        $emailParams->insertedId = $insertedId;
        $emailParams->removalCode = $removalCode;
        // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
        if (getenv('TRAVIS') != 'true') {
            $this->mailer->setTo($address)->sendMessage(new EmailNewListing($emailParams));
            $this->logger->addInfo("Sent email to " . $address);
        }
    }

    private function createCaptcha()
    {
        $captcha = new CaptchaBuilder;
        $captcha->build();
        $this->session->set('captcha', $captcha->getPhrase());
        return $captcha->inline();
    }
}
