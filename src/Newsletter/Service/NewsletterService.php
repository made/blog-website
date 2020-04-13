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

namespace App\Newsletter\Service;

use App\Mailer\Model\MailConfiguration;
use App\Mailer\Service\MailService;
use App\Newsletter\Entity\Newsletter;
use App\Newsletter\Exception\EmailAlreadyActivatedException;
use App\Newsletter\Exception\EmailExistsException;
use App\Newsletter\Exception\EmailNotFoundException;
use App\Newsletter\Exception\GenericNewsletterException;
use App\Newsletter\Exception\NewsletterDatabaseException;
use App\Newsletter\Exception\NewsletterMailerException;
use App\Newsletter\Exception\TokenInvalidException;
use App\Newsletter\Model\NewsletterEmail;
use App\Newsletter\Repository\NewsletterRepository;
use App\Util\RandomGenerator;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Class NewsletterService
 * @package App\Newsletter\Service
 */
class NewsletterService
{
    /**
     * @var NewsletterRepository
     */
    private $repository;

    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * NewsletterService constructor.
     * @param NewsletterRepository $repository
     * @param MailService $mailService
     * @param LoggerInterface $logger
     * @param RequestStack $requestStack
     */
    public function __construct(NewsletterRepository $repository, MailService $mailService, LoggerInterface $logger, RequestStack $requestStack)
    {
        $this->repository = $repository;
        $this->mailService = $mailService;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    /**
     * @param NewsletterEmail $newsletterEmail
     * @return void
     * @throws EmailAlreadyActivatedException
     * @throws EmailExistsException
     * @throws EmailNotFoundException
     * @throws NewsletterDatabaseException
     * @throws NewsletterMailerException
     */
    public function registerEmail(NewsletterEmail $newsletterEmail): void
    {
        try {
            $email = $newsletterEmail->getEmail();
            $newsletterEntity = $this->repository->findByEmail($email);

            if ($newsletterEntity && $newsletterEntity->isActivated()) {
                $this->logger->debug("The E-Mail $email is already confirmed.");
                throw new EmailAlreadyActivatedException("The E-Mail $email is already confirmed.");
            }

            if ($newsletterEntity) {
                $this->logger->debug("The E-Mail $email already exists.");
                throw new EmailExistsException("The E-Mail $email already exists.");
            }
            $this->logger->debug("The E-Mail $email is not registered yet.");

            $activationCode = RandomGenerator::generateActivationCode();
            $urlActivationToken = RandomGenerator::generateUrlActivationToken();
            $this->mapToEntityAndPersistToDatabase($newsletterEmail, $activationCode, $urlActivationToken);

            $this->sendConfirmationMail(
                $email,
                $activationCode,
                $this->generateOrProvideActivationUrl($email)
            );

        } catch (NonUniqueResultException|ORMException|OptimisticLockException $exception) {
            throw new NewsletterDatabaseException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (TransportExceptionInterface $exception) {
            throw new NewsletterMailerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param string $email
     * @param string $token
     * @return void
     * @throws NewsletterDatabaseException
     * @throws TokenInvalidException
     */
    public function activateViaToken(string $email, string $token): void
    {
        try {
            $result = $this->repository->findByEmail($email);

            if (!$result || $result->getActivationCode() !== $token) {
                throw new TokenInvalidException('The token you have provided is not valid.');
            }

            $this->repository->activate($result);
        } catch (NonUniqueResultException|OptimisticLockException|ORMException $exception) {
            throw new NewsletterDatabaseException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param string $email
     * @throws EmailNotFoundException
     * @throws NewsletterDatabaseException
     * @throws NewsletterMailerException
     */
    public function resendEmail(string $email)
    {
        try {
            $result = $this->repository->findByEmail($email);

            if (!$result) {
                $this->logger->debug("The E-Mail $email can not be found in the database.");
                throw new EmailNotFoundException("The E-Mail $email can not be found in the database.");
            }

            $this->sendConfirmationMail(
                $result->getEmail(),
                $result->getActivationCode(),
                $this->generateOrProvideActivationUrl($result->getEmail())
            );
        } catch (NonUniqueResultException $exception) {
            throw new NewsletterDatabaseException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (TransportExceptionInterface $exception) {
            throw new NewsletterMailerException($exception->getMessage(), $exception->getCode(), $exception);
        }

    }

    /**
     * @param string $hashedEmail
     * @param string $activationToken
     * @throws EmailAlreadyActivatedException
     * @throws EmailNotFoundException
     * @throws GenericNewsletterException
     * @throws NewsletterDatabaseException
     * @throws TokenInvalidException
     */
    public function activateViaUrl(string $hashedEmail, string $activationToken): void
    {
        try {
            $email = base64_decode($hashedEmail);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->logger->error('Someone tried to manipulate the email: ' . $email);
                throw new GenericNewsletterException("This email can not be found in the database.");
            }

            $result = $this->repository->findByEmail($email);

            if (!$result) {
                $this->logger->debug("The E-Mail $email can not be found in the database.");
                throw new EmailNotFoundException("This email can not be found in the database.");
            }

            if ($result->isActivated()) {
                $this->logger->debug("The E-Mail $email can not be found in the database.");
                throw new EmailAlreadyActivatedException("This email is already activated.");
            }

            if ($activationToken !== $result->getActivationTokenUrl()) {
                $this->logger->warning("The activation token $activationToken is not the same as the expected " . $result->getActivationTokenUrl());
                throw new TokenInvalidException('Unfortunately the Activation Token is invalid.');
            }

            $this->repository->activate($result);
        } catch (NonUniqueResultException|ORMException|OptimisticLockException $exception) {
            throw new NewsletterDatabaseException($exception->getMessage(), $exception->getCode(), $exception);
        }

    }

    /**
     * @param string $email
     * @return string
     * @throws EmailNotFoundException
     * @throws NewsletterDatabaseException
     */
    private function generateOrProvideActivationUrl(string $email): string
    {
        try {
            $result = $this->repository->findByEmail($email);

            if (!$result) {
                $this->logger->debug("The E-Mail $email can not be found in the database.");
                throw new EmailNotFoundException("The E-Mail $email can not be found in the database.");
            }

            // If no url token exists, then create a new one.
            if (empty($result->getActivationTokenUrl())) {
                $urlToken = RandomGenerator::generateUrlActivationToken();
                $result->setActivationTokenUrl($urlToken);
                $this->repository->persist($result);
            } else {
                $urlToken = $result->getActivationTokenUrl();
            }

            $emailBase64 = base64_encode($email);

            // ToDo: This should later be configurable, since the newsletter service will be a standalone service.
            return $this->requestStack->getMasterRequest()->getSchemeAndHttpHost() . "/newsletter/activate/url/$emailBase64/" . $urlToken;

        } catch (NonUniqueResultException|ORMException|OptimisticLockException $exception) {
            throw new NewsletterDatabaseException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param NewsletterEmail $newsletterEmail
     * @param string $token
     * @param string $urlActivationToken
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function mapToEntityAndPersistToDatabase(NewsletterEmail $newsletterEmail, string $token, string $urlActivationToken)
    {
        $newsletter = new Newsletter();
        $newsletter
            ->setEmail($newsletterEmail->getEmail())
            ->setLocale($newsletterEmail->getLocale())
            ->setList(Newsletter::NEWSLETTER_LIST_NAME)
            ->setActivationCode($token)
            ->setActivationTokenUrl($urlActivationToken)
            ->setCreationDate(new DateTime());

        $this->repository->persist($newsletter);
        $this->logger->debug("The E-Mail" . $newsletterEmail->getEmail() . " has been persisted to the database.");
    }

    /**
     * @param string $to
     * @param string $registrationCode
     * @param string $activationUrl
     * @throws TransportExceptionInterface
     */
    private function sendConfirmationMail(string $to, string $registrationCode, string $activationUrl): void
    {
        $config = (new MailConfiguration())
            ->setHtmlTemplate('@newsletter/mail/newsletter_registration.html.twig')
            ->setTextTemplate('@newsletter/mail/newsletter_registration.txt.twig')
            ->setTemplateContext([
                'code' => $registrationCode,
                'activationUrl' => $activationUrl
            ])
            ->setSubject('Made Blog Newsletter Registration: Your Confirmation Token.')
            ->setTo([$to]);

        $this->mailService->send($config);
    }
}
