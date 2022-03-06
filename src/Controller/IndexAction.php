<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Type\FilesType;
use App\Handler\YamlDiffExtractHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @Route("/{_locale}/",
 *     name="app_index",
 *     methods={"GET","POST"},
 *     requirements={"_locale": "en|fr"}
 * )
 * @Route("/",
 *     name="app_index_default",
 *     methods={"GET","POST"},
 *     defaults={"_locale": "en"}
 * )
 */
class IndexAction extends AbstractController
{
    public function __invoke(
        Request                $request,
        TranslatorInterface    $translator,
        YamlDiffExtractHandler $handler
    ): Response
    {
        $form = $this->createForm(FilesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $fileA */
            $fileA = $form->get('file_a')->getData();
            /** @var UploadedFile $fileB */
            $fileB = $form->get('file_b')->getData();

            try {
                $file = $handler->extract($fileA->getPathname(), $fileB->getPathname());
                $stream = new Stream($file->getPathname());
                $response = new BinaryFileResponse($stream);
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'diff.yaml');
                $response->deleteFileAfterSend(true);

                return $response;
            } catch (Throwable $e) {
                $this->addFlash('error', $translator->trans('error.process'));
                return $this->redirectToRoute('app_index', [
                    '_locale' => $request->getLocale()
                ]);
            }
        }

        return $this->render('index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
