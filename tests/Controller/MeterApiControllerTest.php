<?php


namespace App\Tests\Controller;


use App\Entity\Meter;
use App\Tests\BaseIntegrationTest;
use Doctrine\ORM\EntityManagerInterface;

class MeterApiControllerTest extends BaseIntegrationTest
{
    private const SERIAL = '81627cca-def7-4c5e-a998-58bd66098999';

    public function testCreate()
    {
        $response = $this->apiPost(
            '/api/v1/meter',
            [
                'serial' => self::SERIAL,
                'name' => 'test1'
            ]
        );

        // check response
        $data = json_decode($response->getContent(), true);
        self::assertInternalType('array', $data);

        self::assertArrayHasKey('serial', $data);
        self::assertSame(self::SERIAL, $data['serial']);

        self::assertArrayHasKey('name', $data);
        self::assertSame('test1', $data['name']);

        self::assertArrayHasKey('created', $data);
        self::assertNotFalse(
            \DateTime::createFromFormat(\DateTime::ISO8601, $data['created'])
        );

        self::assertArrayHasKey('updated', $data);
        self::assertNotFalse(
            \DateTime::createFromFormat(\DateTime::ISO8601, $data['created'])
        );

        // check database after
        /** @var Meter $meter */
        self::assertNotNull(
            $meter = $this->dbReadEntity(Meter::class, self::SERIAL)
        );

        self::assertSame(self::SERIAL, $meter->getSerial());
        self::assertSame('test1', $meter->getName());
        self::assertInstanceOf(\DateTime::class, $meter->getCreated());
        self::assertInstanceOf(\DateTime::class, $meter->getUpdated());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        // fetch state before update
        /** @var Meter $meterBefore */
        $meterBefore = $this->dbReadEntity(Meter::class, self::SERIAL);

        $response = $this->apiPut(
            '/api/v1/meter/' . self::SERIAL,
            [
                'name' => 'changed'
            ]
        );

        // check response
        $data = json_decode($response->getContent(), true);
        self::assertInternalType('array', $data);

        self::assertArrayHasKey('serial', $data);
        self::assertSame(self::SERIAL, $data['serial']);

        self::assertArrayHasKey('name', $data);
        self::assertSame('changed', $data['name']);

        // check database after
        /** @var Meter $meterAfter */
        $meterAfter = $this->dbReadEntity(Meter::class, self::SERIAL);

        self::assertSame('changed', $meterAfter->getName());
        self::assertTrue($meterAfter->getUpdated() > $meterBefore->getUpdated());
        self::assertTrue($meterAfter->getCreated() == $meterBefore->getCreated());
    }

    /**
     * @depends testCreate
     */
    public function testRead()
    {
        // create 2 new meters to test pagination
        /** @var EntityManagerInterface $em */
        if (self::$container === null) {
            self::bootKernel();
        }
        $em = self::$container->get('doctrine.orm.entity_manager');

        // disable @ORM\PrePersist, ... to prevent override of updated and created fields
        $em->getClassMetadata(Meter::class)->setLifecycleCallbacks([]);

        $em->persist(
            $meter2 = (new Meter())
                ->setSerial('888c0d77-0947-4dc8-9326-87d967b14b8e')
                ->setName('test2')
                ->setCreated(new \DateTime('now -1 hour'))
                ->setUpdated(new \DateTime('now -1 hour'))
        );

        $em->persist(
            $meter3 = (new Meter())
                ->setSerial('502b5a93-2643-45d5-919e-e5209e197aa4')
                ->setName('test3')
                ->setCreated(new \DateTime('now -2 hours'))
                ->setUpdated(new \DateTime('now -2 hours'))
        );

        $em->flush();

        $response = $this->apiGet(
            '/api/v1/meter',
            [
                'limit' => 2,
                'offset' => 0
            ]
        );

        // check response
        self::assertTrue($response->headers->has('X-Total-Count'));
        self::assertSame(3, $response->headers->get('X-Total-Count'));

        $data = json_decode($response->getContent(), true);
        self::assertInternalType('array', $data);
        self::assertCount(2, $data);

        self::assertSame('502b5a93-2643-45d5-919e-e5209e197aa4', $data[0]['serial']); // oldest first
        self::assertSame('888c0d77-0947-4dc8-9326-87d967b14b8e', $data[1]['serial']);
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        $this->apiDelete('/api/v1/meter/' . self::SERIAL);

        // check database
        self::assertNull(
            $this->dbReadEntity(Meter::class, self::SERIAL)
        );
    }
}
