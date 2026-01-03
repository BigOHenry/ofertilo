<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Wood\Command\CreateWood\CreateWoodCommand;
use App\Application\Wood\Command\DeleteWood\DeleteWoodCommand;
use App\Application\Wood\Command\EditWood\EditWoodCommand;
use App\Application\Wood\Query\GetWoodFormData\GetWoodFormDataQuery;
use App\Application\Wood\Query\GetWoodsPaginatedGrid\GetWoodsPaginatedGridQuery;
use App\Domain\User\ValueObject\Role;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodException;
use App\Infrastructure\Web\Form\WoodFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class WoodController extends BaseController
{
    #[Route('/woods', name: 'wood_index')]
    #[IsGranted(Role::READER->value)]
    public function index(): Response
    {
        return $this->render('wood/index.html.twig');
    }

    #[Route('/wood/new', name: 'wood_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function new(Request $request): Response
    {
        $form = $this->createForm(WoodFormType::class, data: $this->formHelper->prepareFormData(Wood::class), options: [
            'action' => $this->generateUrl('wood_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateWoodCommand::createFromForm($form));
                $this->addFlash('success', $this->translator->trans('message.item_created'));

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('color_index', [], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => CreateWoodCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'woodModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'wood-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/wood/{id}/edit', name: 'wood_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function woodEdit(Request $request, Wood $wood): Response
    {
        $envelope = $this->bus->dispatch(new GetWoodFormDataQuery((int) $wood->getId()));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(WoodFormType::class, data: $formData, options: [
            'action' => $this->generateUrl('wood_edit', ['id' => $wood->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditWoodCommand::createFromForm($form));
                $this->addFlash('success', $this->translator->trans('message.item_updated'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                $referer = $request->headers->get('referer') ?? '';
                $isDetailPage = str_contains($referer, '/wood/');

                if ($isDetailPage) {
                    return $this->render('wood/_streams/_card.stream.html.twig', [
                        'wood' => $wood,
                    ]);
                }

                if ($frameId === 'woodModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => EditWoodCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'woodModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'wood-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/api/woods', name: 'api_woods')]
    #[IsGranted(Role::READER->value)]
    public function woodsApi(Request $request): JsonResponse
    {
        try {
            $envelope = $this->bus->dispatch(GetWoodsPaginatedGridQuery::createFormRequest($request));

            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/wood/{id}', name: 'wood_detail', methods: ['GET'])]
    #[IsGranted(Role::READER->value)]
    public function detail(Wood $wood): Response
    {
        return $this->render('wood/detail.html.twig', [
            'wood' => $wood,
        ]);
    }

    #[Route('/wood/{id}', name: 'wood_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function delete(Wood $wood): JsonResponse
    {
        try {
            $this->bus->dispatch(DeleteWoodCommand::create((int) $wood->getId()));

            return new JsonResponse(['success' => true]);
        } catch (WoodException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
