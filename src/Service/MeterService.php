<?php


namespace App\Service;


use App\Entity\Meter;
use App\Exception\DuplicateSerialException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class MeterService implements MeterServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * MeterService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function save(Meter... $meters) : void {
        foreach ($meters as $meter) {
            $this->em->persist($meter);
        }

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new DuplicateSerialException($exception);
        }
    }

    public function delete(Meter ...$meters): void
    {
        foreach ($meters as $meter) {
            $this->em->remove($meter);
        }

        $this->em->flush();
    }
}
