<?php

namespace AlmaviaCX\Bundle\Syllabs\EzBundle\Controller;

use AlmaviaCX\Syllabs\API\Service\ProcessService;
use AlmaviaCX\Syllabs\API\Value\Document;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /** @var ProcessService */
    protected $processService;

    /** @var TagsService $tagsService */
    protected $tagsService;

    /**
     * ApiController constructor.
     *
     * @param ProcessService $processService
     */
    public function __construct(ProcessService $processService, TagsService $tagsService)
    {
        $this->processService = $processService;
        $this->tagsService    = $tagsService;
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
            $newTag = null;

            $tags = $this->tagsService->loadTagsByKeyword($keyword, 'fre-FR');

            foreach ($tags as $tag) {
                if ($tag->parentTagId == $parentTagId) {
                    $newTag = $tag;
                }
            }

            if (is_null($newTag)) {
                $tagCreateStruct = new TagCreateStruct();
                $tagCreateStruct->setKeyword($keyword, 'fre-FR');
                $tagCreateStruct->parentTagId      = $parentTagId;
                $tagCreateStruct->mainLanguageCode = 'fre-FR';
                $newTag = $this->tagsService->createTag($tagCreateStruct);
            }

            $newTags[] = [
                'id'          => $newTag->id,
                'parentTagId' => $newTag->parentTagId,
                'keywords'    => $newTag->keywords
            ];
        }

        echo"<pre>";print_r($newTags);echo"</pre>";die;
        return new JsonResponse(true);

    }
}
