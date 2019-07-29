<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/system")
 */
class SystemController
{
    /**
     * @Route(path="/health")
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function health()
    {
        return new JsonResponse(
            [
                'systemTime' => (new \DateTime("now"))->format(\DateTime::ISO8601),
            ]
        );
    }

    /**
     * @Route(path="/phpInfo")
     */
    public function phpInfo()
    {
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_get_clean();

        return new Response($info);
    }
}
