<?php


namespace App\Tests;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Note: using this base class will recreate the database before running the test
 */
abstract class BaseIntegrationTest extends WebTestCase
{
    /**
     * boot kernel
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::bootKernel();

        // create/reset test database
        $cliApplication = new Application(self::$kernel);
        $cliApplication->setAutoExit(false);

        $cliApplication->run(
            new ArrayInput(
                [
                    'command' => 'doctrine:database:drop',
                    '--force' => true
                ]
            ),
            new NullOutput()
        );

        $cliApplication->run(
            new ArrayInput(
                [
                    'command' => 'doctrine:database:create',
                ]
            ),
            new NullOutput()
        );

        $cliApplication->run(
            new ArrayInput(
                [
                    'command' => 'doctrine:schema:create',
                ]
            ),
            new NullOutput()
        );
    }

    /* API helpers */

    /**
     * @param string $uri
     * @param array $getParameters will be added to the URI
     * @param int $expectedStatusCode
     *
     * @return Response
     */
    protected function apiGet(string $uri, array $getParameters, int $expectedStatusCode = 200)
    {
        /** @var Request $request */
        /** @var Response $response */
        [$request, $response] = $this->apiRequest(
            'GET',
            sprintf(
                '%s?%s',
                $uri,
                http_build_query($getParameters)
            )
        );

        $this->assertResponse($request, $response, $expectedStatusCode);

        return $response;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param int $expectedStatusCode
     *
     * @return Response
     */
    protected function apiPost(string $uri, array $data, int $expectedStatusCode = 200)
    {
        /** @var Request $request */
        /** @var Response $response */
        [$request, $response] = $this->apiRequest('POST', $uri, $data);

        $this->assertResponse($request, $response, $expectedStatusCode);

        return $response;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param int $expectedStatusCode
     *
     * @return Response
     */
    protected function apiPut(string $uri, array $data, int $expectedStatusCode = 200)
    {
        /** @var Request $request */
        /** @var Response $response */
        [$request, $response] = $this->apiRequest('PUT', $uri, $data);

        $this->assertResponse($request, $response, $expectedStatusCode);

        return $response;
    }

    /**
     * @param string $uri
     * @param int $expectedStatusCode
     *
     * @return Response
     */
    protected function apiDelete(string $uri, int $expectedStatusCode = 204)
    {
        /** @var Request $request */
        /** @var Response $response */
        [$request, $response] = $this->apiRequest('DELETE', $uri);

        $this->assertResponse($request, $response, $expectedStatusCode);

        return $response;
    }

    /* DB helpers */

    /**
     * @param string $entityClass
     * @param $id
     *
     * @return object|null
     */
    protected function dbReadEntity(string $entityClass, $id)
    {
        if (self::$container === null) {
            self::bootKernel();
        }

        /** @var EntityManagerInterface $em */
        $em = self::$container->get('doctrine.orm.entity_manager');

        $entity = $em->find($entityClass, $id);
        $em->clear(); // clear entity manager state to not influence tests

        return $entity;
    }

    private function assertResponse(
        Request $request,
        Response $response,
        int $expectedStatusCode
    )
    {
        self::assertSame(
            $expectedStatusCode,
            $response->getStatusCode(),
            sprintf(
                '"%s %s" returned unexpected status code %d instead of %d (body: %s)',
                $request->getMethod(),
                $request->getUri(),
                $response->getStatusCode(),
                $expectedStatusCode,
                $response->getContent()
            )
        );
    }

    private function apiRequest(string $method, string $uri, array $data = null)
    {
        $client = self::createClient();

        $client->request(
            $method,
            $uri,
            [],
            [],
            [],
            $data === null
                ? null
                : json_encode($data)
        );

        return [$client->getRequest(), $client->getResponse()];
    }
}
