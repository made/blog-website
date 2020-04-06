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

namespace App\Mailer\Service;

use App\Mailer\Model\MailConfiguration;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Class MailService
 * @package App\Mailer\Service
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
     * @var MailConfiguration
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
    }

    /**
     * @param MailConfiguration $mailConfiguration
     * @throws TransportExceptionInterface
     */
    public function send(MailConfiguration $mailConfiguration)
    {
        $this->config = $mailConfiguration;

        $message = $this->buildEmail();
        $this->mailer->send($message);
    }

    /**
     * @return Email
     */
    private function buildEmail(): Email
    {
        $message = new TemplatedEmail();
        $message
            ->subject($this->config->getSubject())
            ->html($this->config->getBody())
            ->from($this->config->getFrom())
            ->to(...$this->config->getTo());

        if ($this->config->hasTemplate()) {
            $message->htmlTemplate($this->config->getTemplate());
            if ($this->config->hasTemplateContext()) {
                $message->context($this->config->getTemplateContext());
            }
        }


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

        if ($this->config->hasAttachment()) {
            $message->attachFromPath($this->config->getAttachmentPath());
            $this->logger->info('Attachment: ' . $this->config->getAttachmentPath());
        }

        return $message;
    }
}
