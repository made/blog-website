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

        if ($this->config->hasHtmlTemplate()) {
            $message->htmlTemplate($this->config->getHtmlTemplate());
            $this->logger->debug('HTML template: ' . $this->config->getHtmlTemplate());
        }

        if ($this->config->hasTextTemplate()) {
            $message->textTemplate($this->config->getTextTemplate());
            $this->logger->debug('Text template: ' . $this->config->getHtmlTemplate());
        }

        if ($this->config->hasTemplateContext()) {
            $message->context($this->config->getTemplateContext());
            $this->logger->debug('Template Context: ' . print_r($this->config->getTemplateContext(), true));
        }

        $this->logger->debug('From: ' . $this->config->getFrom());
        $this->logger->debug('To: ' . implode(' ; ', $this->config->getTo()));

        if ($this->config->hasCc()) {
            $message->cc(...$this->config->getCc());
            $this->logger->debug('CC: ' . implode(', ', $this->config->getCc()));
        }

        if ($this->config->hasBcc()) {
            $message->bcc(...$this->config->getBcc());
            $this->logger->debug('BCC: ' . implode(', ', $this->config->getBcc()));
        }

        if ($this->config->hasAttachment()) {
            $message->attachFromPath($this->config->getAttachmentPath());
            $this->logger->debug('Attachment: ' . $this->config->getAttachmentPath());
        }

        return $message;
    }
}
