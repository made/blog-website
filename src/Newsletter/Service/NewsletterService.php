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

use App\Entity\Newsletter;
use App\Mail\Service\MailService;
use App\Newsletter\Exception\EmailAlreadyConfirmedException;
use App\Newsletter\Exception\EmailExistsException;
use App\Newsletter\Exception\GenericNewsletterException;
use App\Newsletter\Exception\InvalidTokenException;
use App\Newsletter\Model\NewsletterEmail;
use App\Newsletter\Model\NewsletterToken;
use App\Repository\NewsletterRepository;
use App\Util\RandomTokenGenerator;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
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
     * NewsletterService constructor.
     * @param NewsletterRepository $repository
     * @param MailService $mailService
     * @param LoggerInterface $logger
     */
    public function __construct(NewsletterRepository $repository, MailService $mailService, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->mailService = $mailService;
        $this->logger = $logger;
    }

    /**
     * @param NewsletterEmail $newsletterEmail
     * @return void
     * @throws EmailAlreadyConfirmedException
     * @throws EmailExistsException
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransportExceptionInterface
     */
    public function registerEmail(NewsletterEmail $newsletterEmail): void
    {
        $newsletterEntity = $this->repository->findByEmail($newsletterEmail->getEmail());

        if ($newsletterEntity && $newsletterEntity->isConfirmed()) {
            $this->logger->debug('email already confirmed.');
            throw new EmailAlreadyConfirmedException('email already confirmed :)');
        }

        if ($newsletterEntity) {
            $this->logger->debug('email already exists.');
            throw new EmailExistsException('email already exists bruh');
        }
        $this->logger->debug('email is new.');

        $token = RandomTokenGenerator::generate();

        $newsletter = new Newsletter();
        $newsletter
            ->setEmail($newsletterEmail->getEmail())
            ->setLocale($newsletterEmail->getLocale())
            ->setList(Newsletter::NEWSLETTER_LIST_NAME)
            ->setToken($token)
            ->setCreationDate(new DateTime());

        $this->repository->persist($newsletter);
        $this->logger->debug('email is persisted.');
        // ToDo: An url must be generated here and also sent to the user to manually activate the newsletter
        $this->sendConfirmationMail($newsletterEmail->getEmail(), $token);
    }

    /**
     * @param string $email
     * @param string $token
     * @return void
     * @throws InvalidTokenException
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function confirmToken(string $email, string $token)
    {
        /** @var Newsletter $result */
        $result = $this->repository->findByEmail($email);

        if (!$result || $result->getToken() !== $token) {
            throw new InvalidTokenException('The token you have provided is not valid.');
        }

        $result
            ->setConfirmed(true)
            ->setConfirmationDate(new DateTime());

        return $this->repository->persist($result);
    }

    /**
     * @param string $email
     * @throws GenericNewsletterException
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    public function resendEmail(string $email)
    {
        /** @var Newsletter $result */
        $result = $this->repository->findByEmail($email);

        if (!$result) {
            throw new GenericNewsletterException('Email not found');
        }

        $this->sendConfirmationMail(
            $result->getEmail(),
            $result->getToken()
        );
    }

    /**
     * @param string $newsletterEmail
     * @param string $token
     * @throws TransportExceptionInterface
     */
    private function sendConfirmationMail(string $newsletterEmail, string $token): void
    {
        $this->mailService
            ->withBody($token)
            ->withSubject('Made Blog Newsletter Registration: Your Confirmation Token.')
            ->setTo([$newsletterEmail])
            ->send();
    }
}