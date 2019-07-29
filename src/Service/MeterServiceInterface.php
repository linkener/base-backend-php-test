<?php


namespace App\Service;


use App\Entity\Meter;
use App\Exception\DuplicateSerialException;

interface MeterServiceInterface
{
    /**
     * Inserts or updates one or more meters to the database
     *
     * @param Meter|Meter[] $meters
     *
     * @throws DuplicateSerialException if a serial already exists in the database
     */
    public function save(Meter... $meters) : void;

    /**
     * Deletes one or more meters from the database
     *
     * @param Meter|Meter[] $meters
     */
    public function delete(Meter... $meters) : void;
}
