<?php

namespace Sabre\CalDAV\Schedule;

use Sabre\DAV;
use Sabre\VObject\ITip;

/**
 * iMIP handler.
 *
 * This class is responsible for sending out iMIP messages. iMIP is the
 * email-based transport for iTIP. iTIP deals with scheduling operations for
 * iCalendar objects.
 *
 * If you want to customize the email that gets sent out, you can do so by
 * extending this class and overriding the sendMessage method.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class IMipPlugin extends DAV\ServerPlugin {

    /**
     * Email address used in From: header.
     *
     * @var string
     */
    protected $senderEmail;

    /**
     * ITipMessage
     *
     * @var ITip\Message
     */
    protected $itipMessage;

    /**
     * Creates the email handler.
     *
     * @param string $senderEmail. The 'senderEmail' is the email that shows up
     *                             in the 'From:' address. This should
     *                             generally be some kind of no-reply email
     *                             address you own.
     */
    function __construct($senderEmail) {

        $this->senderEmail = $senderEmail;

    }

    /*
     * This initializes the plugin.
     *
     * This function is called by Sabre\DAV\Server, after
     * addPlugin is called.
     *
     * This method should set up the required event subscriptions.
     *
     * @param DAV\Server $server
     * @return void
     */
    function initialize(DAV\Server $server): void {

        $server->on('schedule', [$this, 'schedule'], 120);

    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using \Sabre\DAV\Server::getPlugin
     *
     * @return string
     */
    function getPluginName() {

        return 'imip';

    }

    /**
     * Event handler for the 'schedule' event.
     *
     * @param ITip\Message $iTipMessage
     * @return void
     */
    function schedule(ITip\Message $iTipMessage): void {

        // Not sending any emails if the system considers the update
        // insignificant.
        if (!$iTipMessage->significantChange) {
            if (!$iTipMessage->scheduleStatus) {
                $iTipMessage->scheduleStatus = '1.0;We got the message, but it\'s not significant enough to warrant an email';
            }
            return;
        }

        $summary = $iTipMessage->message->VEVENT->SUMMARY;

        if (parse_url($iTipMessage->sender, PHP_URL_SCHEME) !== 'mailto')
            return;

        if (parse_url($iTipMessage->recipient, PHP_URL_SCHEME) !== 'mailto')
            return;

        $sender = substr($iTipMessage->sender, 7);
        $recipient = substr($iTipMessage->recipient, 7);

        if ($iTipMessage->senderName) {
            $sender = $iTipMessage->senderName . ' <' . $sender . '>';
        }
        if ($iTipMessage->recipientName) {
            $recipient = $iTipMessage->recipientName . ' <' . $recipient . '>';
        }

        $subject = 'SabreDAV iTIP message';
        switch (strtoupper($iTipMessage->method)) {
            case 'REPLY' :
                $subject = 'Re: ' . $summary;
                break;
            case 'REQUEST' :
                $subject = $summary;
                break;
            case 'CANCEL' :
                $subject = 'Cancelled: ' . $summary;
                break;
        }

        $headers = [
            'Reply-To: ' . $sender,
            'From: ' . $this->senderEmail,
            'Content-Type: text/calendar; charset=UTF-8; method=' . $iTipMessage->method,
        ];
        if (DAV\Server::$exposeVersion) {
            $headers[] = 'X-Sabre-Version: ' . DAV\Version::VERSION;
        }
        $this->mail(
            $recipient,
            $subject,
            $iTipMessage->message->serialize(),
            $headers
        );
        $iTipMessage->scheduleStatus = '1.1; Scheduling message is sent via iMip';

    }

    // @codeCoverageIgnoreStart
    // This is deemed untestable in a reasonable manner

    /**
     * This function is responsible for sending the actual email.
     *
     * @param string $to Recipient email address
     * @param string $subject Subject of the email
     * @param string $body iCalendar body
     * @param array $headers List of headers
     * @return void
     */
    protected function mail($to, $subject, $body, array $headers) {

        mail($to, $subject, $body, implode("\r\n", $headers));

    }

    // @codeCoverageIgnoreEnd

    /**
     * Returns a bunch of meta-data about the plugin.
     *
     * Providing this information is optional, and is mainly displayed by the
     * Browser plugin.
     *
     * The description key in the returned array may contain html and will not
     * be sanitized.
     *
     * @return array
     */
    function getPluginInfo() {

        return [
            'name'        => $this->getPluginName(),
            'description' => 'Email delivery (rfc6037) for CalDAV scheduling',
            'link'        => 'http://sabre.io/dav/scheduling/',
        ];

    }

}
