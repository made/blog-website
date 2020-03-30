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

use App\Newsletter\Model\NewsletterEmail;
use App\Newsletter\FormType\NewsletterEmailType;
use App\Newsletter\FormType\NewsletterTokenType;
use App\Newsletter\Model\NewsletterToken;
use App\Newsletter\Service\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NewsletterController
 * @package App\Controller
 */
class NewsletterController extends AbstractController
{
    // ToDo: Clean this class and maybe also outsource some things.

    private const FORM_EMAIL = 'newsletter_email';
    private const FORM_SUBMIT_STEP_1_NAME = 'newsletter_step_1_done';
    private const FORM_SUBMIT_STEP_2_NAME = 'newsletter_step_2_done';

    /**
     * @var NewsletterService
     */
    private $newsletterService;

    public function __construct(NewsletterService $newsletterService)
    {
        $this->newsletterService = $newsletterService;
    }

    /**
     * @Route(path="/newsletter_register", name="newsletter_register", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     * @return string|Response
     */
    public function register(Request $request, SessionInterface $session)
    {
        // Make sure to redirect to the second step, if step1 is already done
        if ($session->get(static::FORM_SUBMIT_STEP_1_NAME) || $session->get(static::FORM_SUBMIT_STEP_2_NAME)) {
            return $this->forward('App\Controller\NewsletterController::registerToken');
        }

        $form = $this->createForm(NewsletterEmailType::class, new NewsletterEmail());
        try {

            if ($request->isXmlHttpRequest()) {
                $form->handleRequest($request);

                /** @var NewsletterEmail $newsletterEmail */
                $newsletterEmail = $form->getData();

                if ($form->isSubmitted()) {
                    if (!$form->isValid()) {
                        throw new \Exception('form invalid');
                    }

                    $this->newsletterService->registerEmail($newsletterEmail);

                    $session->set(static::FORM_SUBMIT_STEP_1_NAME, true);
                    $session->set(static::FORM_EMAIL, $newsletterEmail->getEmail());

                    return $this->forward('App\Controller\NewsletterController::registerToken');
                }
            }

        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
            $error = true;
            // ToDo: define a custom exception with the error message, then pass this to the template
            //  Technical problems for example doctrine stuff need a general error message
        }

        return $this->render('elements/newsletter/newsletter_email.html.twig', [
            'form' => $form->createView(),
            'error' => $error ?? false
        ]);
    }

    /**
     * @Route(path="/newsletter_register_token", name="newsletter_register_token", methods={"POST"})
     * @param SessionInterface $session
     * @param Request $request
     * @return Response
     */
    public function registerToken(SessionInterface $session, Request $request)
    {
        if (!$session->get(static::FORM_SUBMIT_STEP_1_NAME)) {
            return $this->forward('App\Controller\NewsletterController::register');
        }

        if ($session->get(static::FORM_SUBMIT_STEP_2_NAME)) {
            return $this->forward('App\Controller\NewsletterController::registerSuccess');
        }

        $form = $this->createForm(NewsletterTokenType::class, new NewsletterToken());
        try {
            /** @var NewsletterToken $token */
            $token = $form->getData();

            if ($request->isXmlHttpRequest()) {
                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    if ($form->isValid()) {

                        $email = $session->get(static::FORM_EMAIL);

                        $this->newsletterService->confirmToken($email, $token);

                        $session->set(static::FORM_SUBMIT_STEP_2_NAME, true);

                        return $this->forward('App\Controller\NewsletterController::registerSuccess');
                    } else {
                        $error = true;
                    }
                }
            }

        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
            $error = true;
            // ToDo: also define custom exceptions here
        }
        return $this->render('elements/newsletter/newsletter_token.html.twig', [
            'form' => $form->createView(),
            'error' => $error ?? false,
            'email' => $session->get(static::FORM_EMAIL) ?? null
        ]);
    }

    /**
     * @Route(path="/newsletter_register_success", name="newsletter_register_success", methods={"POST"})
     */
    public
    function registerSuccess()
    {
        return $this->render('elements/newsletter/newsletter_success.html.twig');
    }
}
