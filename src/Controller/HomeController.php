<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController.
 */
class HomeController
{
    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HomeController constructor.
     *
     * @param RouteCollectorInterface $routeCollector
     * @param Environment $twig
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(RouteCollectorInterface $routeCollector, Environment $twig, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->routeCollector = $routeCollector;
        $this->twig = $twig;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     *
     * @throws HttpBadRequestException
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->twig->render('home/index.html.twig', [
                'trailers' => $this->fetchData(),
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @Route("/{id}", name="show_post")
     *
     * @param $id
     */

    public function show($id, ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $trailer = $this->em->getRepository(Movie::class)->find($id);

            $data = $this->twig->render('home/post.html.twig', [
                'trailer' => $trailer,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Not show data', ['details' => $e]);

            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @return Collection
     */
    protected function fetchData(): Collection
    {
        $data = $this->em->getRepository(Movie::class)
            ->findBy([], [], 10);

        return new ArrayCollection($data);
    }
}
