<?php

/**
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Controller;

use App\Newsletter\Exception\EmailAlreadyActivatedException;
use App\Newsletter\Exception\EmailExistsException;
use App\Newsletter\Exception\EmailNotFoundException;
use App\Newsletter\Exception\GenericNewsletterException;
use App\Newsletter\Exception\NewsletterDatabaseException;
use App\Newsletter\Exception\TokenInvalidException;
use App\Newsletter\FormType\NewsletterEmailType;
use App\Newsletter\FormType\NewsletterTokenType;
use App\Newsletter\Model\NewsletterEmail;
use App\Newsletter\Model\NewsletterToken;
use App\Newsletter\Service\NewsletterService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NewsletterController
 * @package App\Controller
 *
 * @Route(path="/newsletter", name="newsletter_")
 */
class NewsletterController extends AbstractController
{
    // ToDo: Clean this class and maybe also outsource some things.
    // ToDo: Create an object and pass it to the template

    private const FORM_EMAIL = 'newsletter_email';
    private const FORM_SUBMIT_STEP_1_NAME = 'newsletter_step_1_done';
    private const FORM_SUBMIT_STEP_2_NAME = 'newsletter_step_2_done';

    private const FORWARD_NAME_REGISTER_EMAIL = '::register';
    private const FORWARD_NAME_REGISTER_TOKEN = '::activateWithCode';
    private const FORWARD_NAME_REGISTER_SUCCESS = '::registerSuccess';

    /**
     * @var NewsletterService
     */
    private $newsletterService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NewsletterController constructor.
     * @param NewsletterService $newsletterService
     * @param LoggerInterface $logger
     */
    public function __construct(NewsletterService $newsletterService, LoggerInterface $logger)
    {
        $this->newsletterService = $newsletterService;
        $this->logger = $logger;
    }

    /**
     * @Route(path="/register", name="register", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     * @return string|Response
     */
    public function register(Request $request, SessionInterface $session)
    {
        // After successfully registering the user should not be able to register another email in the current session.
        if ($session->get(static::FORM_SUBMIT_STEP_2_NAME)) {
            return $this->forward(static::class . static::FORWARD_NAME_REGISTER_SUCCESS);
        }

        $form = $this->createForm(NewsletterEmailType::class, new NewsletterEmail());

        try {

            if ($request->isXmlHttpRequest()) {
                $form->handleRequest($request);

                /** @var NewsletterEmail $newsletterEmail */
                $newsletterEmail = $form->getData();
                $newsletterEmail->setLocale($request->getLocale());

                if ($form->isSubmitted()) {
                    $session->set(static::FORM_EMAIL, $newsletterEmail->getEmail());
                    if (!$form->isValid()) {
                        throw new GenericNewsletterException('Please check the format of the e-mail address.');
                    }

                    try {
                        $this->newsletterService->registerEmail($newsletterEmail);
                    } catch (EmailExistsException $exception) {
                        $this->newsletterService->resendEmail($newsletterEmail->getEmail());
                    }
                    $session->set(static::FORM_SUBMIT_STEP_1_NAME, true);

                    return $this->forward(static::class . static::FORWARD_NAME_REGISTER_TOKEN);
                }
            }

        } catch (GenericNewsletterException $exception) {
            $this->logger->error($exception->getMessage());
            $errorMessage = $exception->getMessage();
        } catch (EmailAlreadyActivatedException $exception) {
            $this->logger->error($exception->getMessage());
            $errorMessage = 'The email is already registered and confirmed :)';
        } catch (Exception $exception) {
            $errorMessage = 'A technical problem has occured. Please try again later.';
            $this->logger->error($exception->getMessage());
        }

        return $this->render('@newsletter/web/newsletter_email.html.twig', [
            'form' => $form->createView(),
            'errorMessage' => $errorMessage ?? null
        ]);
    }

    /**
     * @Route(path="/activate/code", name="activate_code", methods={"POST"})
     * @param SessionInterface $session
     * @param Request $request
     * @return Response
     */
    public function activateWithCode(SessionInterface $session, Request $request)
    {
        if (!$session->get(static::FORM_SUBMIT_STEP_1_NAME)) {
            return $this->forward(static::class . static::FORWARD_NAME_REGISTER_EMAIL);
        }

        if ($session->get(static::FORM_SUBMIT_STEP_2_NAME)) {
            return $this->forward(static::class . static::FORWARD_NAME_REGISTER_SUCCESS);
        }

        $form = $this->createForm(NewsletterTokenType::class, new NewsletterToken());
        try {
            /** @var NewsletterToken $token */
            $token = $form->getData();

            if ($request->isXmlHttpRequest()) {
                $form->handleRequest($request);
                if ($form->isSubmitted()) {

                    if (!$form->isValid()) {
                        throw new GenericNewsletterException('Please check the format of the token.');
                    }

                    $email = $session->get(static::FORM_EMAIL);

                    $this->newsletterService->activateViaToken($email, $token->getToken());

                    $session->set(static::FORM_SUBMIT_STEP_2_NAME, true);
                    return $this->forward(static::class . static::FORWARD_NAME_REGISTER_SUCCESS);
                }
            }

        } catch (TokenInvalidException $exception) {
            $errorMessage = $exception->getMessage();
        } catch (Exception $exception) {
            // ToDo: return a general error here later.
            $errorMessage = $exception->getMessage();
        }

        return $this->render('@newsletter/web/newsletter_token.html.twig', [
            'form' => $form->createView(),
            'errorMessage' => $errorMessage ?? null,
            'email' => $session->get(static::FORM_EMAIL) ?? null
        ]);
    }

    /**
     * @Route(path="/activate/url/{hashedEmail}/{activationToken}")
     * @param SessionInterface $session
     * @param string $hashedEmail
     * @param string $activationToken
     * @return Response
     */
    public function activateViaUrl(SessionInterface $session, string $hashedEmail, string $activationToken)
    {
        $feedback = 'E-Mail successfully activated.';
        try {
            $this->newsletterService->activateViaUrl($hashedEmail, $activationToken);
            $session->set(static::FORM_SUBMIT_STEP_2_NAME, true);
        } catch (EmailNotFoundException|EmailAlreadyActivatedException|TokenInvalidException $exception) {
            $feedback = $exception->getMessage();
        } catch (NewsletterDatabaseException|GenericNewsletterException $exception) {
            $this->logger->error($exception->getMessage());
            $feedback = 'A general error has occurred.';
        }

        return $this->render('@newsletter/web/newsletter_activation_url_success.html.twig', [
            "feedback" => $feedback
        ]);
    }

    /**
     * @Route(path="/activate/resend", name="activate_resend", methods={"POST"})
     * @param SessionInterface $session
     * @return Response
     */
    public function resendEmail(SessionInterface $session)
    {
        try {
            $this->newsletterService->resendEmail($session->get(static::FORM_EMAIL));
        } catch (Exception $exception) {
            // ToDo: return a general error here later.
            $this->logger->error($exception->getMessage());
        }

        return $this->render('@newsletter/web/newsletter_resend.html.twig');
    }

    /**
     * @Route(path="/activate/success", name="activate_success", methods={"POST"})
     */
    public function registerSuccess()
    {
        return $this->render('@newsletter/web/newsletter_success.html.twig');
    }
}
