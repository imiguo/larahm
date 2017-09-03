<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService
{
    public static function send($user, $view, $subject, $data)
    {
        Mail::to($user)->send(app('App\Mail\CommonMail', [
            'view' => $view,
            'subject'=> $subject,
            'data' => $data,
        ]));
    }

    public static function templateSend($user, $templateId, $data)
    {
        Mail::to($user)->send(app('App\Mail\CommonMail', [
            'view' => 'emails.'.$templateId,
            'subject' => config("mail_template.{$templateId}.subject", 'notification'),
            'data' => $data,
        ]));
    }

    public static function RawSend($to, $subject, $text)
    {
        Mail::raw($text, function ($message) use ($subject, $to) {
            $message->setSubject($subject);
            $message->setTo($to);
        });
    }
}
