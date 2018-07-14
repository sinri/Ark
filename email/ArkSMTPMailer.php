<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 17:09
 */

namespace sinri\ark\email;


use PHPMailer\PHPMailer\PHPMailer;
use sinri\ark\core\ArkHelper;

//use sinri\smallphpmailer\library\PHPMailer;

class ArkSMTPMailer implements ArkMailer
{
    private $phpMailerInstance;
    private $smtpInfo;
    protected $availableAddressList = null;

    /**
     * LibMail constructor.
     * @param $params
     *
     * host,smtp_auth,username,password,smtp_secure,port,display_name
     */
    public function __construct($params = [])
    {
        $this->smtpInfo = [];

        $this->setUpSMTP($params);

        $this->phpMailerInstance = new PHPMailer();
    }

    /**
     * @param string[] $emails
     * @return void
     */
    public function setReceiverLimitation($emails)
    {
        $this->availableAddressList = $emails;
    }

    /**
     * @param array $params
     */
    public function setUpSMTP($params)
    {
        $this->smtpInfo['host'] = ArkHelper::readTarget($params, 'host', '');
        $this->smtpInfo['smtp_auth'] = ArkHelper::readTarget($params, 'smtp_auth', '');
        $this->smtpInfo['username'] = ArkHelper::readTarget($params, 'username', '');
        $this->smtpInfo['password'] = ArkHelper::readTarget($params, 'password', '');
        $this->smtpInfo['smtp_secure'] = ArkHelper::readTarget($params, 'smtp_secure', '');
        $this->smtpInfo['port'] = ArkHelper::readTarget($params, 'port', '');
        $this->smtpInfo['display_name'] = ArkHelper::readTarget($params, 'display_name', '');
    }

    /**
     * @return PHPMailer
     */
    public function getPhpMailerInstance()
    {
        return $this->phpMailerInstance;
    }

    /**
     * @param int $target 0 for no debug, 4 for full debug
     * @return ArkMailer
     */
    public function setDebug($target = 0)
    {
        $this->phpMailerInstance->SMTPDebug = $target;
        return $this;
    }

    /**
     * If you are using OSX and PHP 5.6 and find error in debug, you might try on this.
     * This is the solution given by PHPMail Official GitHub Developer.
     *
     * 2017-07-18 06:00:18     Connection failed. Error #2: stream_socket_client(): SSL operation failed with code 1. OpenSSL Error messages:
     * error:14090086:SSL routines:ssl3_get_server_certificate:certificate verify failed [~/enoch/SmallPHPMail/SMTP.php line 294]
     * 2017-07-18 06:00:18     Connection failed. Error #2: stream_socket_client(): Failed to enable crypto [~/enoch/SmallPHPMail/SMTP.php line 294]
     * 2017-07-18 06:00:18     Connection failed. Error #2: stream_socket_client(): unable to connect to ssl://smtp.exmail.qq.com:465 (Unknown error) [~/enoch/SmallPHPMail/SMTP.php line 294]
     *
     * @return ArkMailer
     */
    public function stopSSLVerify()
    {
        $this->phpMailerInstance->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        return $this;
    }


    /**
     * @deprecated use method `prepare` instead
     * @param null $error
     * @return ArkMailer
     */
    public function prepareSMTP(&$error = null)
    {
        return $this->prepare($error);
    }

    /**
     * @param null $error
     * @return ArkMailer
     */
    public function prepare(&$error = null)
    {
        try {
            $this->phpMailerInstance = new PHPMailer();
            $this->phpMailerInstance->Host = $this->smtpInfo['host'];// Specify main and backup SMTP servers
            $this->phpMailerInstance->SMTPAuth = $this->smtpInfo['smtp_auth'];// Enable SMTP authentication
            $this->phpMailerInstance->Username = $this->smtpInfo['username'];// SMTP username
            $this->phpMailerInstance->Password = $this->smtpInfo['password'];// SMTP password
            $this->phpMailerInstance->SMTPSecure = $this->smtpInfo['smtp_secure'];// Enable TLS encryption, `ssl` also accepted
            $this->phpMailerInstance->Port = $this->smtpInfo['port'];// TCP port to connect to

            $this->phpMailerInstance->setFrom($this->smtpInfo['username'], $this->smtpInfo['display_name']);

            $this->phpMailerInstance->isSMTP();
        } catch (\Exception $exception) {
            // who care?
            $error = $exception->getMessage();
        }
        return $this;
    }

    private function turnHTML2TEXT($html)
    {
        $html = preg_replace('/\<[Bb][Rr] *\/?\>/', PHP_EOL, $html);
        $html = strip_tags($html);
        return $html;
    }

    /**
     * @param $address
     * @param string $name
     * @return ArkMailer
     */
    public function addReceiver($address, $name = '')
    {
        if ($this->availableAddressList === null || !in_array($address, $this->availableAddressList))
            $this->phpMailerInstance->addAddress($address, $name);
        return $this;
    }

    /**
     * @param $address
     * @param $name
     * @return ArkMailer
     */
    public function addReplyAddress($address, $name)
    {
        $this->phpMailerInstance->addReplyTo($address, $name);
        return $this;
    }

    /**
     * @param $address
     * @param $name
     * @return ArkMailer
     */
    public function addCCAddress($address, $name)
    {
        if ($this->availableAddressList === null || !in_array($address, $this->availableAddressList))
            $this->phpMailerInstance->addCC($address, $name);
        return $this;
    }

    /**
     * @param $address
     * @param $name
     * @return ArkMailer
     */
    public function addBCCAddress($address, $name)
    {
        if ($this->availableAddressList === null || !in_array($address, $this->availableAddressList))
            $this->phpMailerInstance->addBCC($address, $name);
        return $this;
    }

    /**
     * @param $attachmentFile
     * @param string $name
     * @return ArkMailer
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function addAttachment($attachmentFile, $name = '')
    {
        $this->phpMailerInstance->addAttachment($attachmentFile, $name);
        return $this;
    }

    /**
     * @param $subject
     * @return ArkMailer
     */
    public function setSubject($subject)
    {
        $this->phpMailerInstance->Subject = $subject;
        return $this;
    }

    /**
     * @param $text
     * @return ArkMailer
     */
    public function setTextBody($text)
    {
        $this->phpMailerInstance->Body = $text;
        return $this;
    }

    /**
     * @param $htmlCode
     * @return ArkMailer
     */
    public function setHTMLBody($htmlCode)
    {
        $this->phpMailerInstance->isHTML(true);// Set email format to HTML
        $this->phpMailerInstance->Body = $htmlCode;
        $this->phpMailerInstance->AltBody = $this->turnHTML2TEXT($htmlCode);
        return $this;
    }

    /**
     * @param null $error
     * @return bool
     */
    public function finallySend(&$error = null)
    {
        try {
            $done = $this->phpMailerInstance->send();
        } catch (\Exception $exception) {
            $done = false;
        }
        $error = $this->phpMailerInstance->ErrorInfo;
        return $done;
    }


}