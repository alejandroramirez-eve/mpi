<?php

namespace PlacetoPay\MPI\Tests\Functionality;

use PlacetoPay\MPI\Clients\MockClientVersionTwo;
use PlacetoPay\MPI\Constants\MPI;
use PlacetoPay\MPI\Contracts\MPIException;
use PlacetoPay\MPI\Exceptions\ErrorResultMPI;
use PlacetoPay\MPI\Messages\LookupResponseVersionTwo;
use PlacetoPay\MPI\MPIService;
use PlacetoPay\MPI\Tests\BaseTestCase;

class LookUpProcessTest extends BaseTestCase
{
    public function create($overwrite = [])
    {
        return new MPIService(array_merge([
            'url' => getenv('MPI_URL'),
            'apiKey' => getenv('MPI_API_KEY'),
            'client' => \PlacetoPay\MPI\Clients\MockClientVersionOne::instance(),
        ], $overwrite));
    }

    public function testItConstructTheEntityCorrectly()
    {
        $mpi = $this->create();

        $response = $mpi->lookUp([
            'card' => [
                'number' => '4532840681197602',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
        ]);

        $this->assertTrue($response->canAuthenticate());
        $this->assertEquals(1, $response->identifier());
        $this->assertEquals('https://dnetix.co/ping/3ds', $response->processUrl());
    }

    public function testItFailsIfNotURLProvided()
    {
        $this->expectException(\PlacetoPay\MPI\Contracts\MPIException::class);
        $this->create(['url' => null]);
    }

    public function testItInstantiateTheGuzzleLibrary()
    {
        $mpi = $this->create(['client' => null]);
        $this->assertNotNull($mpi);
    }

    public function testItSendsTheInstallmentsCorrectly()
    {
        $mpi = $this->create();

        $response = $mpi->lookUp([
            'card' => [
                'number' => '4716036206946551',
                'expirationYear' => '20',
                'expirationMonth' => '12',
                'installments' => 3,
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
        ]);

        $this->assertFalse($response->canAuthenticate(), 'Card is not registered');
    }

    public function testItValidatesTheInstallmentsCorrectly()
    {
        $this->expectException(\PlacetoPay\MPI\Contracts\MPIException::class);
        $mpi = $this->create();

        $response = $mpi->lookUp([
            'card' => [
                'number' => '4716036206946551',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
        ]);

        $this->assertFalse($response->canAuthenticate(), 'Card is not registered');
    }

    public function testItChangesTheApiKeyOnDemand()
    {
        $mpi = $this->create();

        $mpi->setApiKey('VALID_ONE');

        $response = $mpi->lookUp([
            'card' => [
                'number' => '6011499026766178',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
        ]);

        $this->assertFalse($response->canAuthenticate());
    }

    public function testItChangesTheApiKeyOnDemandInvalid()
    {
        $this->expectException(\PlacetoPay\MPI\Contracts\MPIException::class);
        $mpi = $this->create();

        $mpi->setApiKey('INVALID_ONE');

        $response = $mpi->lookUp([
            'card' => [
                'number' => '6011499026766178',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
        ]);

        $this->assertFalse($response->canAuthenticate());
    }

    public function testItSendsCorrectlyTheRedirectUrl()
    {
        $mpi = $this->create();

        $response = $mpi->lookUp([
            'card' => [
                'number' => '5554575520765108',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://example.com/return',
        ]);

        $this->assertFalse($response->canAuthenticate());
        $this->assertEquals('07', $response->eci());
        $this->assertIsArray($response->toArray());
    }

    public function testItValidatesCorrectlyTheRedirectUrl()
    {
        $this->expectException(\PlacetoPay\MPI\Contracts\MPIException::class);
        $mpi = $this->create();

        $response = $mpi->lookUp([
            'card' => [
                'number' => '5554575520765108',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://other.com/return',
        ]);

        $this->assertFalse($response->canAuthenticate());
    }

    public function testItConstructTheVersionTwoEntityCorrectly()
    {
        $mpi = $this->create([
            '3dsVersion' => MPI::VERSION_TWO,
            'client' => \PlacetoPay\MPI\Clients\MockClientVersionTwo::instance(),
        ]);

        $response = $mpi->lookUp([
            'card' => [
                'number' => '4532840681197602',
                'expirationYear' => '20',
                'expirationMonth' => '01',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
        ]);

        $this->assertInstanceOf(LookupResponseVersionTwo::class, $response);
        $this->assertTrue($response->canAuthenticate());
        $this->assertEquals('https://dnetix.co/ping/3ds', $response->processUrl());

        $this->assertEquals('COP', MockClientVersionTwo::instance()->lastData()['purchaseCurrency']);
    }

    public function testThrowExceptionItInvalidResponse()
    {
        $mpi = $this->create([
            '3dsVersion' => MPI::VERSION_TWO,
            'client' => \PlacetoPay\MPI\Clients\MockClientVersionTwo::instance(),
        ]);

        $this->expectException(ErrorResultMPI::class);
        $mpi->lookUp([
            'card' => [
                'number' => '4716036206946551',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
        ]);
    }

    public function testThrowExceptionWhenDoesntHasRecurringFrecuency()
    {
        $mpi = $this->create([
            '3dsVersion' => MPI::VERSION_TWO,
            'client' => \PlacetoPay\MPI\Clients\MockClientVersionTwo::instance(),
        ]);

        $this->expectException(MPIException::class);
        $mpi->lookUp([
            'card' => [
                'number' => '4716036206946551',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
            'threeDSAuthenticationInd' => 03,
            'recurringExpiry' => '15',
        ]);
    }

    public function testThrowExceptionWhenDoesntHasRecurringExpiry()
    {
        $mpi = $this->create([
            '3dsVersion' => MPI::VERSION_TWO,
            'client' => \PlacetoPay\MPI\Clients\MockClientVersionTwo::instance(),
        ]);

        $this->expectException(MPIException::class);
        $mpi->lookUp([
            'card' => [
                'number' => '6011499026766178',
                'expirationYear' => '20',
                'expirationMonth' => '12',
            ],
            'amount' => 1200,
            'currency' => 'COP',
            'redirectUrl' => 'https://dnetix.co/ping/3ds',
            'threeDSAuthenticationInd' => 03,
            'recurringFrequency' => '15',
        ]);
    }
}
