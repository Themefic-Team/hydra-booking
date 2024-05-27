<?php
namespace HydraBooking\Hooks;

class Mailer
{
    public static function send($to, $subject, $body, $headers = [], $attachments = [])
    {
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        return wp_mail($to, $subject, $body, $headers, $attachments);
    }
}