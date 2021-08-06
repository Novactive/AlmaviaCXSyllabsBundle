<?php

namespace AlmaviaCX\Bundle\Syllabs\EzBundle\Controller;

use AlmaviaCX\Syllabs\API\Service\ProcessService;
use AlmaviaCX\Syllabs\API\Value\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/syllabs/process", methods={"POST"}, name="syllabs_process")
     */
    public function processAction(Request $request, ProcessService $processService)
    {
        $docProp = [
            'id'    => $request->get('id'),
            'title' => $request->get('title'),
            'text'  => $request->get('text')
        ];
        $doc = new Document($docProp);

        $syllabsDocs = $processService->process([$docProp['id'] => $doc]);

        $tags = [];
        foreach ($syllabsDocs as $syllabsDoc) {

            foreach ($syllabsDoc->entities as $entity) {
                $tags[] = $entity->text;
            }
            foreach ($syllabsDoc->themes as $theme) {
                $tags[] = $theme->text;
            }
            foreach ($syllabsDoc->wikitags as $wikitag) {
                $tags[] = $wikitag->text;
            }
        }

        return new JsonResponse($tags);
    }

}