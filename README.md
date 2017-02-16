# GPWebPay interface
[![Build Status](https://travis-ci.org/jaroslavtyc/granam-gpwebpay.svg?branch=master)](https://travis-ci.org/jaroslavtyc/granam-gpwebpay)

GPWebPay is a PHP library for online payments via [GPWebPay service](http://www.gpwebpay.cz/)
![GPWEbPay logo](http://www.gpwebpay.cz/Content/imgs/logo_header@2.png | width=100)

If your are using [Nette framework](https://nette.org/en/), you may want
[Pixidos/GPWebPay](https://github.com/Pixidos/GPWebPay) Nette extension instead.

## Quickstart

This extension is here to provide [GP WebPay](http://www.gpwebpay.cz) system for Nette Framework.

and setting

```yml
gpwebpay:
    privateKey: < your private certificate path >
    privateKeyPassword: < private certificate password >
    publicKey: < gateway public certificate path (you will probably get this by email) > //gpe.signing_prod.pem
    url: <url of gpwabpay system gateway > //example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
    merchantNumber: <your merechant number >
```

or if you need more then one gateway
```yml
gpwebpay:
	privateKey:
		czk: < your CZK private certificate path .pem>
		eur: < your EUR private certificate path .pem>
	privateKeyPassword:
		czk: < private CKZ certificate password >
		eur: < private EUR certificate password >
	publicKey: < gateway public certificate path (you will probably get this by email) > //gpe.signing_prod.pem
	url: <url of gpwebpay system gateway > //example: https://test.3dsecure.gpwebpay.com/unicredit/order.do
	merchantNumber:
		czk: <your CZK merechant number >
		eur: <your EUR merechant number >
```

## Usage


```php
use Pixidos\GPWebPay\Exceptions\GPWebPayException;
use Pixidos\GPWebPay\Request;
use Pixidos\GPWebPay\Response;
use Pixidos\GPWebPay\Operation;

class MyPresenter extends Nette\Application\UI\Presenter
{

	/** @var \Pixidos\GPWebPay\Components\GPWebPayControlFactory @inject */
	public $gpWebPayFactory;

	/**
     * @return GPWebPayControl
     * @throws InvalidArgumentException
     */
    public function createComponentWebPayButton()
    {
        $operation = new Operation(int $orderId, int $totalPrice, int $curencyCode);
        // if you use more than one gateway use gatewayKey - same as in config
        // $operation = new Operation(int $orderId, int $totalPrice, int $curencyCode, string $gatewayKey);

        // if you need to switch gateway lang
        // $operation->setLang('cs');

        /**
         * you can set Response URL. In default will be used handelSuccess() in component
         * https://github.com/Pixidos/GPWebPay/blob/master/src/Pixidos/GPWebPay/Components/GPWebPayControl.php#L93
         * $operation->setResponseUrl($url);
         */

        $control = $this->gpWebPayFactory->create($operation);

        # Run before redirect to webpay gateway
        $control->onCheckout[] = function (GPWebPayControl $control, Request $request){

            //...

        }


        # On success response
        $control->onSuccess[] = function(GPWebPayControl $control, Response $response) {

            //....

        };

        # On Error
        $control->onError[] = function(GPWebPayControl $control, GPWebPayException $exception)
        {

            //...

        };

        return $control;

    }
}
```

## Templates

```smarty
{var $attrs = array(class => 'btn btn-primary')}
{control webPayButton $attrs, 'text on button'}
```

Installation
------------

```sh
composer require granam/gpwebpay
```
*(requires PHP 5.6+)*

## Credits
This library originates from [Pixidos/GPWebPay](https://github.com/Pixidos/GPWebPay) library, which has same
functionality but can be used **only** as a [Nette framework](https://nette.org/en/) extension.
All credits belongs to the author Ondra Votava from Pixidos.

Nevertheless I am grateful to him for sharing that library publicly. Please more of such people.
