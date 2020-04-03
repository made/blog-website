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

namespace App\Mail\Service;

use App\Mail\Model\MailConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Class MailService
 * @package App\Mail\Service
 */
class MailService
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $to;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $attachmentPath;

    /**
     * @var MailConfig
     */
    private $config;

    /**
     * MailService constructor.
     * @param MailerInterface $mailer
     * @param LoggerInterface $logger
     */
    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;

        // This should be injected as a config object later :)
        $this->config = (new MailConfig())
            ->setFrom('test@made.dev')
            ->setTo(['dennis@made.dev'])
            ->setBcc(['haha@made.dev'])
            ->setSubject('Hello matey');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send()
    {
        $message = $this->buildEmail();

        $this->mailer->send($message);
    }

    /**
     * @return Email
     */
    private function buildEmail(): Email
    {
        $message = new Email();
        $message
            ->subject($this->config->getSubject())
            ->html($this->config->getBody() ?? 'no body specified.')
            ->from($this->config->getFrom())
            ->to(...$this->config->getTo());

        $this->logger->info('From: ' . $this->config->getFrom());
        $this->logger->info('To: ' . implode(' ; ', $this->config->getTo()));

        if ($this->config->hasCc()) {
            $message->cc(...$this->config->getCc());
            $this->logger->info('CC: ' . implode(', ', $this->config->getCc()));
        }

        if ($this->config->hasBcc()) {
            $message->bcc(...$this->config->getBcc());
            $this->logger->info('BCC: ' . implode(', ', $this->config->getBcc()));
        }

        if ($this->hasAttachment()) {
            $message->attachFromPath($this->attachmentPath);
            $this->logger->info('Attachment: ' . $this->attachmentPath);
        }

        return $message;
    }

    /**
     * @param string $body
     * @return MailService
     */
    public function withBody(string $body): MailService
    {
        $this->config->setBody($body);
        return $this;
    }

    /**
     * @param string $subject
     * @return MailService
     */
    public function withSubject(string $subject): MailService
    {
        $this->config->setSubject($subject);
        return $this;
    }

    /**
     * @param string $attachmentPath
     * @return MailService
     */
    public function withAttachment(string $attachmentPath): MailService
    {
        $this->attachmentPath = $attachmentPath;
        return $this;
    }


    public function setTo(array $to): MailService
    {
        $this->config->setTo($to);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachmentPath);
    }

}
