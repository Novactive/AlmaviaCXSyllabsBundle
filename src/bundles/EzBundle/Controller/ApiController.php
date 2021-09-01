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
        $this->processService = $processService;
        $this->suggestionService = $suggestionService;
    }

    /**
     * @param Request $request
     * @Route("/syllabs/process", methods={"POST"}, name="syllabs_process", options={"expose": true})
     */
    public function processAction(Request $request)
    {
        $docProp = $this->processRequest(
            $request,
            [
                'id',
                'title',
                'text',
            ]
        );

        $doc = new Document($docProp);

        $syllabsDocs = $this->processService->process([$docProp['id'] => $doc]);

        return new JsonResponse($syllabsDocs);
    }

    protected function processRequest(Request $request, array $keys): ?array
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            return json_decode($request->getContent(), true);
        } else {
            $values = [];
            foreach ($keys as $key) {
                $values[$key] = $request->get($key);
            }

            return $values;
        }
    }

    /**
     * @param Request $request
     * @Route("/syllabs/create-suggestions", methods={"POST"}, name="syllabs_create_suggestions",
     *     options={"expose": true})
     */
    public function tagsAction(Request $request)
    {
        $params = $this->processRequest(
            $request,
            [
                'suggestions',
                'languageCode',
            ]
        );

        $newTags = [];
        foreach ($params['suggestions'] as $suggestion) {
            $tag = $this->suggestionService->createTag(
                $suggestion['text'],
                $suggestion['parentTagId'],
                $params['languageCode']
            );

            $newTags[] = [
                'id'          => $tag->id,
                'parentTagId' => $tag->parentTagId,
                'keywords'    => $tag->keywords,
            ];
        }

        return new JsonResponse($newTags);
    }
}
