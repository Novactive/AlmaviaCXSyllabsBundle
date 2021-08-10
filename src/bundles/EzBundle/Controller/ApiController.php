<?php

namespace AlmaviaCX\Bundle\Syllabs\EzBundle\Controller;

use AlmaviaCX\Syllabs\API\Service\ProcessService;
use AlmaviaCX\Syllabs\API\Value\Document;
use Netgen\TagsBundle\API\Repository\TagsService;
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
     * @Route("/create/tags", methods={"POST"}, name="create_tags")
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
                $tagCreateStruct->setKeyword($keyword);
                $tagCreateStruct->parentTagId      = $parentTagId;
                $tagCreateStruct->mainLanguageCode = 'fre-FR';
                $newTag = $this->tagsService->createTag($tagCreateStruct);
                $newTags[] = [
                    'id' => $newTag->id,
                    'parentTagId' => 
                ];
            }


            // Boucler sur tags
            // Vérifier le parent, si tag avec bon partent $tag = ce tag
            // Sinon $tag = null

            // Après la boucle, si tag null, alors on le crée

            // Ajouter tag dans newTags


            // Dans newtags, ajouter tableau associatif avec "id / parentTagId / keywords"

            if (empty($tags)) {
                /* @todo créer le tag sous le*/
                $tagCreateStruct = new TagCreateStruct();
                $tagCreateStruct->setKeyword($keyword);
                $tagCreateStruct->parentTagId      = $parentTagId;
                $tagCreateStruct->mainLanguageCode = 'fre-FR';
                $newTag = $this->tagsService->createTag($tagCreateStruct);
                $newTags[] = $newTag;
            } else {
                // Vérifier le parent
                // Si différent, créer un nouveau
            }

        }

        exit("tags créés");
        echo"<pre>";print_r($tags);echo"</pre>";die;


        echo"<pre>";print_r($request->get('entities'));echo"</pre>";die;


        //        entities:
        //        parent_tag: 123
        //  keywords:
        //     - Test 1
        //            - Test 2
        return new JsonResponse(true);

    }
}
