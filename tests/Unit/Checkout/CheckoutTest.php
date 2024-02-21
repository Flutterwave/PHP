<?php

namespace Unit\Checkout;

use Eloquent\Liberator\Liberator;
use Flutterwave\Config\ForkConfig;
use Flutterwave\Controller\PaymentController;
use Flutterwave\Contract\ConfigInterface;
use Flutterwave\EventHandlers\EventHandlerInterface;
use Flutterwave\EventHandlers\ModalEventHandler;
use Flutterwave\Flutterwave;
use Flutterwave\Library\Modal;
use Flutterwave\Util\Currency;
use PHPUnit\Framework\TestCase;
use DG\BypassFinals;

BypassFinals::enable();
BypassFinals::setWhitelist(
    [
        '*/src/Library/*',
        // '*/src/Entities/*',
        // '*/src/Factories/*',
        // '*/src/HttpAdapter/*',
        '*/src/Controller/*',
    ]
);



class CheckoutTest extends TestCase 
{
    protected Flutterwave $paymentClient;

    protected function setUp(): void
    {
        Flutterwave::bootstrap();
        $this->paymentHandler = new ModalEventHandler();
    }

    /**
	 * Tests Inline Setup.
	 *
	 * @dataProvider checkoutProvider
	 */
    public function testCheckoutProcess(
        string $modalType,
        array $generatedTransactionData, 
        EventHandlerInterface $paymentHandler, 
        ConfigInterface $config,
        array $request
    ){
        $mockClient = $this->createMock(Flutterwave::class);
        $mockModal = $this->createMock(Modal::class);

        $mockModal
        ->expects($this->exactly(1))
        ->method('with')
        ->will($this->returnValue($mockModal));

        if( 'standard' === $modalType ) {
            $mockModal
            ->expects($this->exactly(1))
            ->method('getUrl')
            ->will($this->returnValue(''));
        } else {
            $mockModal
            ->expects($this->exactly(1))
            ->method('getHtml')
            ->will($this->returnValue(''));
        }

        $mockClient
        ->expects($this->exactly(1))
        ->method('render')
        ->with( $modalType )
        ->will($this->returnValue($mockModal));

        $mockClient
        ->expects($this->exactly(1))
        ->method('eventHandler')
        ->will($this->returnValue($mockClient));

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $controller = new PaymentController( $mockClient , $paymentHandler, $modalType );

        $controller->process( $request );
    }

    public function checkoutProvider() {
        return [
            [
                Modal::STANDARD,
                [ 
                    "tx_ref" => 'FLW_TEST|' . random_int(10, 2000) . '|' . uniqid('aMx') 
                ],
                new ModalEventHandler(),
                ForkConfig::setUp(
                    $_ENV['SECRET_KEY'] ?? \getenv('SECRET_KEY'),
                    $_ENV['PUBLIC_KEY'] ?? \getenv('PUBLIC_KEY'),
                    $_ENV['ENCRYPTION_KEY'] ?? \getenv('ENCRYPTION_KEY'),
                    $_ENV['ENV'] ?? \getenv('ENV')
                ),
                [
                    'amount' => 3000,
                    'currency' => Currency::NGN,
                    'phone_number' => '080000000000',
                    'first_name' => 'Abraham',
                    'last_name' => 'Olaobaju',
                    'success_url' => null,
                    'failure_url' => null,
                ]
            ],
            [
                Modal::POPUP,
                [ "tx_ref" => 'FLW_TEST|' . random_int( 10, 2000) . '|' . uniqid('mAx') ],
                new ModalEventHandler(),
                ForkConfig::setUp(
                    $_ENV['SECRET_KEY'] ?? \getenv('SECRET_KEY'),
                    $_ENV['PUBLIC_KEY'] ?? \getenv('PUBLIC_KEY'),
                    $_ENV['ENCRYPTION_KEY'] ?? \getenv('ENCRYPTION_KEY'),
                    $_ENV['ENV'] ?? \getenv('ENV')
                ),
                [
                    'amount' => 1500,
                    'currency' => Currency::NGN,
                    'phone_number' => '08000000000',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'success_url' => null,
                    'failure_url' => null,
                ]
            ]


        ];
    }

}