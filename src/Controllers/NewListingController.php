<?php


namespace Startplats\Controllers;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Startplats\Listings;
use Startplats\EmailNewListing;
use Startplats\Utils;
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
            $categories = Utils::get_categories($this->db);
            $subcategories = Utils::get_subcategories($this->db);
            $captcha = new CaptchaBuilder;
            $captcha->build();
            $this->session->set('captcha', $captcha->getPhrase());

            return $this->view->render($response, 'new_listing.html.twig', [
                'categories' => $categories,
                'subcategories' => $subcategories,
                'language' => $this->language,
                'captcha' => $captcha->inline(),
            ]);
        } catch (Exception $e) {
            $this->logger->addError("/listings/new GET threw exception: " . $e);
            return $this->view->render($response, 'error.html.twig', [
                'alert' => ['level' => 'danger', 'text' => $this->language['internal_server_error']],
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
                $alert_text = $this->language['wrong_captcha'];
                $alert = ['level' => 'warning', 'text' => $alert_text];
            } else {
                $removal_code = Utils::generate_removal_code();
                $params['removal_code'] = $removal_code;
                $listings = new Listings($this->db);
                $inserted_id = $listings->insertListing($params);
                $this->logger->addInfo("Parameters inserted:", $params);

                $email_variables = new stdClass;
                $email_variables->insertedId = $inserted_id;
                $email_variables->removal_code = $removal_code;
                // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
                if (getenv('TRAVIS') != 'true') {
                    $this->mailer->setTo($params['email'])->sendMessage(new EmailNewListing($email_variables));
                }

                $alert_text = "<strong>" . $this->language['success'] . "</strong> "
                    . $this->language['new_listing_inserted_message']
                    . " <a href=\"/listings/$inserted_id\">" . $this->language['here'] . "</a>. "
                    .$this->language['removal_code'] . ": $removal_code";
                $alert = ['level' => 'success', 'text' => $alert_text];

                //We only refill form if it was unsuccessful
                $params = null;
            }

            $categories = Utils::get_categories($this->db);
            $subcategories = Utils::get_subcategories($this->db);
            $captcha = new CaptchaBuilder;
            $captcha->build();
            $this->session->set('captcha', $captcha->getPhrase());

            return $this->view->render($response, 'new_listing.html.twig', [
                'categories' => $categories,
                'subcategories' => $subcategories,
                'alert' => $alert,
                'language' => $this->language,
                'captcha' => $captcha->inline(),
                'params' => $params,
            ]);
        } catch (Exception $e) {
            $this->logger->addError("/listings/new POST threw exception: " . $e);
            return $this->view->render($response, 'error.html.twig', [
                'alert' => ['level' => 'danger', 'text' => $this->language['internal_server_error']],
                'language' => $this->language,
            ]);
        }
    }
}
