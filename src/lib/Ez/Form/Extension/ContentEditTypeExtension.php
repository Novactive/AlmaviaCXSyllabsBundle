<?php

/**
 * @copyright Novactive
 * Date: 30/07/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\Form\Extension;

use AlmaviaCX\Syllabs\Ez\Config\SyllabsConfiguration;
use AlmaviaCX\Syllabs\Ez\Event\Subscriber\ContentEditEventSubscriber;
use AlmaviaCX\Syllabs\Ez\Value\Configuration\ContentTypeConfiguration;
use EzSystems\EzPlatformWorkflow\Form\Type\EditorialWorkflowType;
use EzSystems\RepositoryForms\Data\Content\ContentCreateData;
use EzSystems\RepositoryForms\Data\Content\ContentUpdateData;
use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\Form\Type\Content\ContentEditType;
use EzSystems\RepositoryForms\Form\Type\Content\ContentFieldType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContentEditTypeExtension extends AbstractTypeExtension
{
    /** @var SyllabsConfiguration */
    protected $configuration;

    /**
     * ContentEditTypeExtension constructor.
     *
     * @param SyllabsConfiguration $configuration
     */
    public function __construct(SyllabsConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array                                        $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {

                /** @var Form $form */
                $form = $event->getForm();

                $contentTypeIdentifier = $this->getContentTypeIdentifier($form);
                $languageCode = $this->getContentLanguageCode($form);
                $form->add(
                    'contentTypeIdentifier',
                    HiddenType::class,
                    [
                        'data' => $contentTypeIdentifier,
                        'mapped' => false
                    ]
                );$form->add(
                    'languageCode',
                    HiddenType::class,
                    [
                        'data' => $languageCode,
                        'mapped' => false
                    ]
                );
            },
            -10000
        );
    }

    protected function getContentTypeIdentifier(Form $form): ?string
    {
        $contentData = $form->getData();
        if ($contentData instanceof ContentCreateData) {
            return $contentData->contentType->identifier;
        } elseif ($contentData instanceof ContentUpdateData) {
            return $contentData->contentDraft->getContentType()->identifier;
        }

        return null;
    }

    protected function getContentLanguageCode(Form $form): ?string
    {
        $contentData = $form->getData();
        if ($contentData instanceof ContentCreateData) {
            return $contentData->mainLanguageCode;
        } elseif ($contentData instanceof ContentUpdateData) {
            return $contentData->contentDraft->contentInfo->mainLanguageCode;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return ContentEditType::class;
    }
}
