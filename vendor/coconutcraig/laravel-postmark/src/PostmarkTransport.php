<?php

namespace CraigPaul\Mail;

use Illuminate\Http\Client\Factory as Http;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;

use function array_filter;
use function array_map;
use function array_merge;
use function implode;
use function in_array;
use function json_decode;

use const JSON_OBJECT_AS_ARRAY;

class PostmarkTransport implements TransportInterface
{
    protected const BYPASS_HEADERS = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'content-type',
        'sender',
        'reply-to',
    ];

    public function __construct(
        protected Http $http,
        protected ?string $messageStreamId,
        protected array $options,
        protected string $token,
    ) {
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $envelope = $envelope ?? Envelope::create($message);

        $sentMessage = new SentMessage($message, $envelope);

        $email = MessageConverter::toEmail($sentMessage->getOriginalMessage());

        $response = $this->http
            ->withOptions($this->options)
            ->acceptJson()
            ->withHeaders([
                'X-Postmark-Server-Token' => $this->getServerToken($email),
            ])
            ->post($this->getApiEndpoint($email), $this->getPayload($email, $envelope));

        if ($response->ok()) {
            $sentMessage->setMessageId($response->json('MessageID'));

            return $sentMessage;
        }

        throw new PostmarkTransportException(
            $response->json('Message'),
            $response->json('ErrorCode'),
            $response->toException(),
        );
    }

    protected function getApiEndpoint(Email $email): string
    {
        $url = 'https://api.postmarkapp.com/email';

        if ($this->getTemplatedContent($email) === null) {
            return $url;
        }

        return $url.'/withTemplate';
    }

    protected function getAttachments(Email $email): array
    {
        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();

            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');
            $disposition = $headers->getHeaderBody('Content-Disposition');

            $attributes = [
                'Name' => $filename,
                'Content' => $attachment->bodyToString(),
                'ContentType' => $headers->get('Content-Type')->getBody(),
            ];

            if ($disposition === 'inline') {
                $attributes['ContentID'] = 'cid:'.$filename;
            }

            $attachments[] = $attributes;
        }

        return $attachments;
    }

    protected function getPayload(Email $email, Envelope $envelope): array
    {
        $payload = [
            'From' => $envelope->getSender()->toString(),
            'To' => $this->stringifyAddresses($this->getRecipients($email, $envelope)),
            'Cc' => $this->stringifyAddresses($email->getCc()),
            'Bcc' => $this->stringifyAddresses($email->getBcc()),
            'Subject' => $email->getSubject(),
            'HtmlBody' => $email->getHtmlBody(),
            'TextBody' => $email->getTextBody(),
            'ReplyTo' => $this->stringifyAddresses($email->getReplyTo()),
            'Attachments' => $this->getAttachments($email),
            'MessageStream' => $this->messageStreamId ?? '',
        ];

        foreach ($email->getHeaders()->all() as $name => $header) {
            if (in_array($name, self::BYPASS_HEADERS, true)) {
                continue;
            }

            if ($header instanceof TagHeader) {
                $payload['Tag'] = $header->getValue();

                continue;
            }

            if ($header instanceof MetadataHeader) {
                $payload['Metadata'][$header->getKey()] = $header->getValue();

                continue;
            }

            if ($header instanceof PostmarkServerTokenHeader) {
                continue;
            }

            $payload['Headers'][] = [
                'Name' => $name,
                'Value' => $header->getBodyAsString(),
            ];
        }

        if ($content = $this->getTemplatedContent($email)) {
            $payload['TemplateId'] = $content['id'] ?? null;
            $payload['TemplateAlias'] = $content['alias'] ?? null;
            $payload['TemplateModel'] = $content['model'] ?? null;

            unset($payload['Subject'], $payload['HtmlBody'], $payload['TextBody']);
        }

        return array_filter($payload);
    }

    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        $copies = array_merge($email->getCc(), $email->getBcc());

        return array_filter($envelope->getRecipients(), function (Address $address) use ($copies) {
            return in_array($address, $copies, true) === false;
        });
    }

    protected function getServerToken(Email $email): string
    {
        $header = $email->getHeaders()->get(PostmarkServerTokenHeader::NAME);

        if ($header === null) {
            return $this->token;
        }

        return $header->getBody();
    }

    protected function getTemplatedContent(Email $email): ?array
    {
        return json_decode($email->getHtmlBody(), flags: JSON_OBJECT_AS_ARRAY);
    }

    protected function stringifyAddresses(array $addresses): string
    {
        return implode(',', array_map(fn (Address $address) => $address->toString(), $addresses));
    }

    public function __toString(): string
    {
        return 'postmark';
    }
}
