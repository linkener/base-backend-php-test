<?php


namespace App\Controller;

use App\Entity\Meter;
use App\Exception\DuplicateSerialException;
use App\Repository\MeterRepository;
use App\Service\MeterServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(path="/api/v1/meter")
 */
class MeterApiController
{
    /**
     * @var MeterRepository
     */
    private $meterRepository;

    /**
     * @var MeterServiceInterface
     */
    private $meterService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * MeterApiController constructor.
     *
     * @param MeterRepository $meterRepository
     * @param MeterServiceInterface $meterService
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        MeterRepository $meterRepository,
        MeterServiceInterface $meterService,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    )
    {
        $this->meterRepository = $meterRepository;
        $this->serializer = $serializer;
        $this->meterService = $meterService;
        $this->validator = $validator;
    }

    /**
     * @Route(path="", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        try {
            /** @var Meter $meter */
            $meter = $this->serializer->deserialize(
                $request->getContent(),
                Meter::class,
                'json',
                [
                    'ignored_attributes' => ['created', 'updated'],
                ]
            );
        } catch (NotEncodableValueException $exception) {
            throw new BadRequestHttpException('could not decode JSON', $exception);
        }

        $errors = $this->validator->validate($meter);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        try {
            $this->meterService->save($meter);
        } catch (DuplicateSerialException $exception) {
            throw new BadRequestHttpException(
                sprintf('meter with serial "%s" already exists', $meter->getSerial()),
                $exception
            );
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($meter, 'json')
        );
    }

    /**
     * @Route(path="", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function read(Request $request)
    {
        $limit = intval($request->query->get('limit', 100));
        $offset = intval($request->query->get('offset', 0));

        $totalCount = $this->meterRepository->count([]);
        $meters = $this->meterRepository->findAllPaginated($limit, $offset);

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($meters, 'json'),
            200,
            [
                'X-Total-Count' => $totalCount
            ]
        );
    }

    /**
     * @Route(path="/{serial}", methods={"PUT"})
     * @param Request $request
     * @param string $serial
     *
     * @return JsonResponse
     */
    public function update(Request $request, string $serial)
    {
        /** @var Meter $meter */
        $meter = $this->meterRepository->find($serial);
        if ($meter === null) {
            throw new NotFoundHttpException(
                sprintf('meter "%d" not found', $serial)
            );
        }

        try {
            /** @var Meter $meter */
            $meter = $this->serializer->deserialize(
                $request->getContent(),
                Meter::class,
                'json',
                [
                    'ignored_attributes' => ['created', 'updated'],
                    'object_to_populate' => $meter
                ]
            );
        } catch (NotEncodableValueException $exception) {
            throw new BadRequestHttpException('could not decode JSON', $exception);
        }

        $errors = $this->validator->validate($meter);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        try {
            $this->meterService->save($meter);
        } catch (DuplicateSerialException $exception) {
            throw new BadRequestHttpException(
                sprintf('meter with serial "%s" already exists', $meter->getSerial()),
                $exception
            );
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($meter, 'json')
        );
    }

    /**
     * @Route(path="/{serial}", methods={"DELETE"})
     * @param string $serial
     *
     * @return Response
     */
    public function delete(string $serial)
    {
        /** @var Meter $meter */
        $meter = $this->meterRepository->find($serial);
        if ($meter === null) {
            throw new NotFoundHttpException(
                sprintf('meter "%d" not found', $serial)
            );
        }

        $this->meterService->delete($meter);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
