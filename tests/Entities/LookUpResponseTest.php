<?php


class LookUpResponseTest extends BaseTestCase
{

    public function testItParsesASuccessfulRequest()
    {
        $data = $this->unserialize('YTozOntzOjg6ImVucm9sbGVkIjtzOjE6IlkiO3M6MTI6InJlZGlyZWN0X3VybCI7czoxMTE6Imh0dHBzOi8vZGV2LnBsYWNldG9wYXkuZWMvM2RzLW1waS9hdXRoZW50aWNhdGUvMDE2ZjlmNTU4NzIzOGE1MjRhODcwNzZiZTExNDFmMDI5NTNjYzFlMTQzZDI0YjEwNWJjZTMwYzYzNDM5Yzg4YyI7czoxNDoidHJhbnNhY3Rpb25faWQiO2k6MTt9');
        $response = \PlacetoPay\MPI\Messages\LookUpResponse::loadFromResult($data);

        $this->assertTrue($response->canAuthenticate());
        $this->assertEquals('Y', $response->enrolled());
        $this->assertEquals(1, $response->identifier());
        $this->assertEquals('https://dev.placetopay.ec/3ds-mpi/authenticate/016f9f5587238a524a87076be1141f02953cc1e143d24b105bce30c63439c88c', $response->processUrl());
    }

    /**
     * @expectedException \PlacetoPay\MPI\Exceptions\ErrorResultMPI
     */
    public function testItHandlesTheErrorResult()
    {
        $data = $this->unserialize('YTozOntzOjEyOiJlcnJvcl9udW1iZXIiO2k6MTAwMjtzOjE3OiJlcnJvcl9kZXNjcmlwdGlvbiI7czozNzoiSW52YWxpZCBhcmd1bWVudHMgdG8gaW5pdGlhdGUgcmVxdWVzdCI7czo2OiJlcnJvcnMiO2E6MTp7czozOiJwYW4iO2E6MTp7aTowO3M6NDk6IlRoZSBjYXJkIG51bWJlciBkb2Vzbid0IG1hdGNoIHRoZSBleHBlY3RlZCB2YWx1ZXMiO319fQ==');
        \PlacetoPay\MPI\Messages\LookUpResponse::loadFromResult($data);
    }
    
}