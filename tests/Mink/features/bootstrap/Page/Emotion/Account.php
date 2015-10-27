<?php
namespace Shopware\Tests\Mink\Page\Emotion;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\WebAssert;
use Shopware\Tests\Mink\Element\Emotion\AccountOrder;
use Shopware\Tests\Mink\Element\Emotion\AccountPayment;
use Shopware\Tests\Mink\Element\Emotion\AddressBox;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Account extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/account';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'identifierDashboard' => 'div#content > div > div.account',
            'identifierLogin' => 'div#login',
            'identifierRegister' => 'div#content > div > div.register',
            'payment' => 'div#selected_payment strong',
            'logout' => 'div.adminbox a.logout',
            'registrationForm' => 'div.register > form',
            'billingForm' => 'div.change_billing > form',
            'shippingForm' => 'div.change_shipping > form',
            'paymentForm' => 'div.change_payment > form',
            'passwordForm' => 'div.password > form',
            'emailForm' => 'div.email > form',
            'esdDownloads' => 'div.downloads div.table_row',
            'esdDownloadName' => '.grid_7 > strong'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'loginButton'           => ['de' => 'Anmelden',                 'en' => 'Login'],
            'forgotPasswordLink'    => ['de' => 'Passwort vergessen?',      'en' => 'Forgot your password?'],
            'registerButton'        => ['de' => 'Neuer Kunde',              'en' => 'New customer'],
            'sendButton'            => ['de' => 'Registrierung abschließen', 'en' => 'Complete registration'],

            'myAccountLink'         => ['de' => 'Mein Konto',               'en' => 'My account'],
            'myOrdersLink'          => ['de' => 'Meine Bestellungen',       'en' => 'My orders'],
            'myEsdDownloadsLink'    => ['de' => 'Meine Sofortdownloads',    'en' => 'My instant downloads'],
            'changeBillingLink'     => ['de' => 'Rechnungsadresse ändern',  'en' => 'Change billing address'],
            'changeShippingLink'    => ['de' => 'Lieferadresse ändern',     'en' => 'Change shipping address'],
            'changePaymentLink'     => ['de' => 'Zahlungsart ändern',       'en' => 'Change payment method'],
            'noteLink'              => ['de' => 'Merkzettel',               'en' => 'Wish list'],
            'logoutLink'            => ['de' => 'Abmelden Logout',          'en' => 'Logout'],

            'changePaymentButton'   => ['de' => 'Ändern',                   'en' => 'Change'],
            'changeBillingButton'   => ['de' => 'Ändern',                   'en' => 'Change'],
            'changeShippingButton'  => ['de' => 'Ändern',                   'en' => 'Change'],
            'changePasswordButton'  => ['de' => 'Passwort ändern',          'en' => 'Change password'],
            'changeEmailButton'     => ['de' => 'E-Mail ändern',            'en' => 'Change email']
        ];
    }

    /**
     * @param string $action
     * @return bool
     * @throws \Exception
     */
    public function verifyPage($action = '')
    {
        $language = Helper::getCurrentLanguage($this);

        if ($action === 'Dashboard' || empty($action)) {
            if ($this->verifyPageDashboard($language)) {
                return true;
            }
        }

        if ($action === 'Login' || empty($action)) {
            if ($this->verifyPageLogin($language)) {
                return true;
            }
        }

        if ($action === 'Register' || empty($action)) {
            if ($this->verifyPageRegister($language)) {
                return true;
            }
        }

        if ($action) {
            return false;
        }

        $message = ['You are not on Account page!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
        Helper::throwException($message);
    }

    /**
     * @param string $language
     * @return bool
     */
    protected function verifyPageDashboard($language)
    {
        return (
            Helper::hasNamedLink($this, 'myAccountLink', $language) &&
            Helper::hasNamedLink($this, 'myOrdersLink', $language) &&
            Helper::hasNamedLink($this, 'myEsdDownloadsLink', $language) &&
            Helper::hasNamedLink($this, 'changeBillingLink', $language) &&
            Helper::hasNamedLink($this, 'changeShippingLink', $language) &&
            Helper::hasNamedLink($this, 'changePaymentLink', $language) &&
            Helper::hasNamedLink($this, 'noteLink', $language) &&
            Helper::hasNamedLink($this, 'logoutLink', $language)
        );
    }

    /**
     * @param string $language
     * @return bool
     */
    protected function verifyPageLogin($language)
    {
        return (
            $this->hasField('email') &&
            $this->hasField('password') &&
            Helper::hasNamedLink($this, 'forgotPasswordLink', $language) &&
            Helper::hasNamedButton($this, 'loginButton', $language)
        );
    }

    /**
     * @param string $language
     * @return bool
     */
    protected function verifyPageRegister($language)
    {
        return (
            $this->hasSelect('register[personal][customer_type]') &&
            $this->hasSelect('register[personal][salutation]') &&
            $this->hasField('register[personal][firstname]') &&
            $this->hasField('register[personal][lastname]') &&
            $this->hasField('register[personal][email]') &&
            $this->hasField('register[personal][password]') &&

            $this->hasField('register[billing][company]') &&
            $this->hasField('register[billing][department]') &&
            $this->hasField('register[billing][ustid]') &&

            $this->hasField('register[billing][street]') &&
            $this->hasField('register[billing][zipcode]') &&
            $this->hasField('register[billing][city]') &&
            $this->hasSelect('register[billing][country]') &&
            $this->hasField('register[billing][shippingAddress]') &&

            $this->hasSelect('register[shipping][salutation]') &&
            $this->hasField('register[shipping][company]') &&
            $this->hasField('register[shipping][department]') &&
            $this->hasField('register[shipping][firstname]') &&
            $this->hasField('register[shipping][lastname]') &&
            $this->hasField('register[shipping][street]') &&
            $this->hasField('register[shipping][zipcode]') &&
            $this->hasField('register[shipping][city]') &&
            $this->hasSelect('register[shipping][country]') &&

            Helper::hasNamedButton($this, 'sendButton', $language)
        );
    }

    /**
     * Logins a user
     * @param string $email
     * @param string $password
     */
    public function login($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);

        $this->pressButton('Anmelden');
    }

    /**
     * Check if the user was successfully logged in
     * @param string $username
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function verifyLogin($username)
    {
        $assert = new WebAssert($this->getSession());
        $assert->pageTextContains(
            'Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen'
        );
        $assert->pageTextContains('Willkommen, ' . $username);
    }

    /**
     * Logout a customer (important when using the Selenium driver)
     * @return bool
     */
    public function logout()
    {
        if ($this->verifyPage('Dashboard') === true) {
            Helper::clickNamedLink($this, 'logoutLink');

            return true;
        }

        return false;
    }

    /**
     * Changes the password of the user
     * @param string $currentPassword
     * @param string $password
     * @param string $passwordConfirmation
     */
    public function changePassword($currentPassword, $password, $passwordConfirmation = null)
    {
        $data = [
            [
                'field' => 'currentPassword',
                'value' => $currentPassword
            ],
            [
                'field' => 'password',
                'value' => $password
            ],
            [
                'field' => 'passwordConfirmation',
                'value' => ($passwordConfirmation !== null) ? $passwordConfirmation : $password
            ]
        ];

        Helper::fillForm($this, 'passwordForm', $data);
        Helper::pressNamedButton($this, 'changePasswordButton');
    }

    /**
     * Changes the email address of the user
     * @param string $password
     * @param string $email
     * @param string $emailConfirmation
     */
    public function changeEmail($password, $email, $emailConfirmation = null)
    {
        $data = [
            [
                'field' => 'emailPassword',
                'value' => $password
            ],
            [
                'field' => 'email',
                'value' => $email
            ],
            [
                'field' => 'emailConfirmation',
                'value' => ($emailConfirmation !== null) ? $emailConfirmation : $email
            ]
        ];

        Helper::fillForm($this, 'emailForm', $data);
        Helper::pressNamedButton($this, 'changeEmailButton');
    }

    /**
     * Changes the billing address of the user
     * @param array $values
     */
    public function changeBillingAddress($values)
    {
        Helper::fillForm($this, 'billingForm', $values);
        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     * Changes the shipping address of the user
     * @param array $values
     */
    public function changeShippingAddress($values)
    {
        Helper::fillForm($this, 'shippingForm', $values);
        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     * Changes the payment method
     * @param array $data
     */
    public function changePaymentMethod($data = [])
    {
        $element = $this->getElement('AccountPayment');
        $language = Helper::getCurrentLanguage($this);
        Helper::clickNamedLink($element, 'changeButton', $language);

        Helper::fillForm($this, 'paymentForm', $data);
        Helper::pressNamedButton($this, 'changePaymentButton', $language);
    }

    /**
     * @param string $paymentMethod
     * @throws \Exception
     */
    public function checkPaymentMethod($paymentMethod)
    {
        /** @var AccountPayment $element */
        $element = $this->getElement('AccountPayment');

        $properties = [
            'paymentMethod' => $paymentMethod
        ];

        $result = Helper::assertElementProperties($element, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The current payment method is "%s" (should be "%s")',
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * @param AccountOrder $order
     * @param string $orderNumber
     * @param array $articles
     */
    public function checkOrder(AccountOrder $order, $orderNumber, array $articles)
    {
        $date = $order->getDateProperty();
        $this->checkOrderNumber($order, $orderNumber);
        $this->checkOrderPositions($order, $articles);
        $this->checkEsdArticles($date, $articles);
    }

    /**
     * Helper method checks the order number
     * @param AccountOrder $order
     * @param string $orderNumber
     */
    private function checkOrderNumber(AccountOrder $order, $orderNumber)
    {
        $properties = [
            'number' => $orderNumber
        ];

        $result = Helper::assertElementProperties($order, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The order number is "%s" (should be "%s")',
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * Helper method checks the order positions
     * @param AccountOrder $order
     * @param array $articles
     * @throws \Exception
     */
    private function checkOrderPositions(AccountOrder $order, array $articles)
    {
        $positions = $order->getPositions(['product', 'quantity', 'price', 'sum']);

        $data = [];

        foreach ($articles as $key => $article) {
            $data[$key] = Helper::floatArray([
                'quantity' => $article['quantity'],
                'price' => $article['price'],
                'sum' => $article['sum']
            ]);

            $data[$key]['product'] = $article['product'];
        }

        $result = Helper::compareArrays($positions, $data);

        if ($result === true) {
            return;
        }

        $message = sprintf('The %s of a position is different! (is "%s", should be "%s")', $result['key'],
            $result['value'], $result['value2']);
        Helper::throwException($message);
    }

    /**
     * Helper method checks the ESD articles
     * @param string $date
     * @param array $articles
     * @throws \Exception
     */
    private function checkEsdArticles($date, array $articles)
    {
        $esd = [];

        foreach ($articles as $key => $article) {
            if (empty($article['esd'])) {
                continue;
            }

            $esd[] = $article['product'];
        }

        if (empty($esd)) {
            return;
        }

        $language = Helper::getCurrentLanguage($this);
        Helper::clickNamedLink($this, 'myEsdDownloadsLink', $language);

        $elements = Helper::findAllOfElements($this, ['esdDownloads']);
        $locator = Helper::getRequiredSelector($this, 'esdDownloadName');
        $downloads = [];

        /** @var NodeElement $esdDownload */
        foreach ($elements['esdDownloads'] as $esdDownload) {
            if (strpos($esdDownload->getText(), $date) !== false) {
                $downloads[] = $this->find('css', $locator)->getText();
            }
        }

        foreach ($esd as $givenEsd) {
            foreach ($downloads as $download) {
                if ($givenEsd === $download) {
                    break;
                }

                if ($download === end($downloads)) {
                    $message = sprintf('ESD-Article "%s" not found in account!', $givenEsd);
                    Helper::throwException($message);
                }
            }
        }
    }

    /**
     * @param string $type
     * @param string $address
     */
    public function checkAddress($type, $address)
    {
        $this->open();

        $testAddress = explode(', ', $address);
        $testAddress = array_filter($testAddress);
        $testAddress = array_values($testAddress);

        $type = strtolower($type);
        $type = ucfirst($type);

        $addressBox = $this->getElement('Account' . $type);
        $addressData = Helper::getElementProperty($addressBox, 'address');

        $givenAddress = [];

        /** @var Element $data */
        foreach ($addressData as $data) {
            $part = $data->getHtml();
            $parts = explode('<br />', $part);
            foreach ($parts as &$part) {
                $part = strip_tags($part);
                $part = str_replace([chr(0x0009), '  '], ' ', $part);
                $part = str_replace([chr(0x0009), '  '], ' ', $part);
                $part = trim($part);
            }
            unset($part);

            $givenAddress = array_merge($givenAddress, $parts);
        }

        $result = Helper::compareArrays($givenAddress, $testAddress);

        if ($result === true) {
            return;
        }

        $message = sprintf('The addresses are different! ("%s" not was found in "%s")', $result['value2'],
            $result['value']);
        Helper::throwException($message);
    }

    /**
     * @param array $data
     */
    public function register($data)
    {
        if ($this->verifyPage('Login') === true) {
            Helper::pressNamedButton($this, 'registerButton');
        }

        Helper::fillForm($this, 'registrationForm', $data);
        Helper::pressNamedButton($this, 'sendButton');
    }

    /**
     * @param AddressBox $addresses
     * @param string $name
     */
    public function chooseAddress(AddressBox $addresses, $name)
    {
        $name = str_replace(', ', ',', $name);
        $this->searchAddress($addresses, $name);
    }

    /**
     * @param AddressBox $addresses
     * @param string $name
     * @throws \Exception
     */
    protected function searchAddress(AddressBox $addresses, $name)
    {
        /** @var AddressBox $address */
        foreach ($addresses as $address) {
            if (strpos($address->getProperty('title'), $name) === false) {
                continue;
            }

            $language = Helper::getCurrentLanguage($this);
            Helper::pressNamedButton($address, 'chooseButton', $language);

            return;
        }

        $messages = ['The address "' . $name . '" is not available. Available are:'];

        /** @var AddressBox $address */
        foreach ($addresses as $address) {
            $messages[] = $address->getProperty('title');
        }

        Helper::throwException($messages);
    }
}
