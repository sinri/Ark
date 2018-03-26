<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/22
 * Time: 14:24
 */

namespace sinri\ark\email;

interface ArkMailer
{
    /**
     * @param null $error
     * @return ArkMailer
     */
    public function prepare(&$error = null);

    /**
     * This should be set before adding methods.
     * NULL for no limit and array of email address strings as limitation
     * @param string[]|null $emails
     * @return void
     */
    public function setReceiverLimitation($emails);

    /**
     * @param $address
     * @param string $name
     * @return ArkMailer
     */
    public function addReceiver($address, $name = '');

    /**
     * @param $address
     * @param $name
     * @return ArkMailer
     */
    public function addReplyAddress($address, $name);

    /**
     * @param $address
     * @param $name
     * @return ArkMailer
     */
    public function addCCAddress($address, $name);

    /**
     * @param $address
     * @param $name
     * @return ArkMailer
     */
    public function addBCCAddress($address, $name);

    /**
     * @param $attachmentFile
     * @param string $name
     * @return ArkMailer
     * @throws \Exception
     */
    public function addAttachment($attachmentFile, $name = '');

    /**
     * @param $subject
     * @return ArkMailer
     */
    public function setSubject($subject);

    /**
     * @param $text
     * @return ArkMailer
     */
    public function setTextBody($text);

    /**
     * @param $htmlCode
     * @return ArkMailer
     */
    public function setHTMLBody($htmlCode);

    /**
     * @param null $error
     * @return bool
     */
    public function finallySend(&$error = null);
}