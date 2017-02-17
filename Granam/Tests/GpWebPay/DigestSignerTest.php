<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\DigestSigner;
use PHPUnit\Framework\TestCase;

class DigestSignerTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_create_signed_digest_and_verify_it()
    {
        $digestSigner = new DigestSigner(
            __DIR__ . '/files/testing_public_key.pub',
            __DIR__ . '/files/testing_private_key.pem',
            '1234567' // password
        );
        $valuesForDigest = ['foo' => 'bar', 'baz' => 'qux', 123 => 456];
        $signedDigest = $digestSigner->createSignedDigest($valuesForDigest);
        self::assertNotEmpty($signedDigest);
        self::assertTrue($digestSigner->verifySignedDigest($signedDigest, $valuesForDigest));
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\DigestCanNotBeVerified
     * @expectedExceptionMessageRegExp ~baz|qux~
     */
    public function I_am_stopped_by_exception_if_digest_can_not_be_verified()
    {
        $digestSigner = new DigestSigner(
            __DIR__ . '/files/testing_public_key.pub',
            __DIR__ . '/files/testing_private_key.pem',
            '1234567' // password
        );
        $digestSigner->verifySignedDigest('SignedInBottomRight', ['foo' => 'bar', 'baz' => 'qux']);
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\PublicKeyFileCanNotBeRead
     * @expectedExceptionMessageRegExp ~in a cloud~
     */
    public function I_can_not_create_signer_with_unreachable_public_key_file()
    {
        $digestSigner = new DigestSigner(
            'in a cloud',
            __DIR__ . '/files/testing_private_key.pem',
            '1234567' // password
        );
        $valuesForDigest = ['foo' => 'bar', 'baz' => 'qux', 123 => 456];
        $signedDigest = $digestSigner->createSignedDigest($valuesForDigest);
        self::assertNotEmpty($signedDigest);
        self::assertTrue($digestSigner->verifySignedDigest($signedDigest, $valuesForDigest));
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\PrivateKeyFileCanNotBeRead
     * @expectedExceptionMessageRegExp ~on keychain~
     */
    public function I_can_not_create_signer_with_unreachable_private_key_file()
    {
        $digestSigner = new DigestSigner(
            __DIR__ . '/files/testing_public_key.pub',
            'on keychain',
            '1234567' // password
        );
        $valuesForDigest = ['foo' => 'bar', 'baz' => 'qux', 123 => 456];
        $signedDigest = $digestSigner->createSignedDigest($valuesForDigest);
        self::assertNotEmpty($signedDigest);
        self::assertTrue($digestSigner->verifySignedDigest($signedDigest, $valuesForDigest));
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     */
    public function I_can_not_create_signer_with_invalid_private_key_password()
    {
        $digestSigner = new DigestSigner(
            __DIR__ . '/files/testing_public_key.pub',
            __DIR__ . '/files/testing_private_key.pem',
            'knock knock' // password
        );
        $valuesForDigest = ['foo' => 'bar', 'baz' => 'qux', 123 => 456];
        $signedDigest = $digestSigner->createSignedDigest($valuesForDigest);
        self::assertNotEmpty($signedDigest);
        self::assertTrue($digestSigner->verifySignedDigest($signedDigest, $valuesForDigest));
    }

}