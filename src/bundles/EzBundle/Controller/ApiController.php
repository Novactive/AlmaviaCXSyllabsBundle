<?php

namespace AlmaviaCX\Bundle\Syllabs\EzBundle\Controller;

use AlmaviaCX\Syllabs\API\Service\ProcessService;
use AlmaviaCX\Syllabs\API\Value\Document;
use AlmaviaCX\Syllabs\Ez\Service\SuggestionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /** @var ProcessService */
    protected $processService;

    /** @var SuggestionService $suggestionService */
    protected $suggestionService;

    /**
     * ApiController constructor.
     *
     * @param ProcessService    $processService
     * @param SuggestionService $suggestionService
     */
    public function __construct(ProcessService $processService, SuggestionService $suggestionService)
    {
        $this->processService    = $processService;
        $this->suggestionService = $suggestionService;
    }

    /**
     * @param Request     $request
     * @Route("/syllabs/process", methods={"POST"}, name="syllabs_process", options={"expose": true})
     */
    public function processAction(Request $request)
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $docProp = json_decode($request->getContent(), true);
        } else {
            $docProp = [
                'id'    => $request->get('id'),
                'title' => $request->get('title'),
                'text'  => $request->get('text')
            ];
        }

        $doc = new Document($docProp);

        $syllabsDocs = $this->processService->process([$docProp['id'] => $doc]);

        return new JsonResponse($syllabsDocs);
    }

    /**
     * @param Request     $request
     * @Route("/create/tags", methods={"GET"}, name="create_tags")
     */
    public function tagsAction(Request $request)
    {
        $newTags = [];
        $parentTagId = 345;
        $keywords = ["Ambition", 'Inégalité'];
        foreach ($keywords as $keyword) {
            $newTags[] = $this->suggestionService->createTag($keyword, 'fre-FR', $parentTagId);
        }

        return new JsonResponse($newTags);
    }
}
