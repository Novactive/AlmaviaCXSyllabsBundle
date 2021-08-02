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
     * @param Form $form
     *
     * @return ContentCreateData|ContentUpdateData|null
     */
    protected function getContentData(Form $form)
    {
        $parentForm = $form->getParent();
        if(!$parentForm) {
             return null;
        }
        $parentData = $parentForm ? $parentForm->getData() : null;
        if ($parentData instanceof ContentCreateData || $parentData instanceof ContentUpdateData) {
            return $parentData;
        }
        return $this->getContentData($parentForm);
    }

    protected function getContentTypeIdentifier(Form $form): ?string
    {
        $contentData = $this->getContentData($form);
        if ($contentData instanceof ContentCreateData) {
            return $contentData->contentType->identifier;
        } elseif ($contentData instanceof ContentUpdateData) {
            return $contentData->contentDraft->getContentType()->identifier;
        }
        return null;
    }

    protected function getContentTypeConfiguration(Form $form): ?ContentTypeConfiguration
    {
        $contentTypeIdentifier = $this->getContentTypeIdentifier($form);
        if (!$contentTypeIdentifier) {
            return null;
        }

        return $this->configuration->getContentTypeConfiguration($contentTypeIdentifier);
    }

    protected function getSuggesterConfig(ContentTypeConfiguration $configuration, string $fieldIdentifier): ?array
    {
        $config = [];

        $targetFields = $configuration->getTargetFields();
        foreach ($targetFields as $targetField) {
            if($targetField->getFieldIdentifier() === $fieldIdentifier) {
                $config[$targetField->getType()] = $targetField->getParentTag()->id;
            }
        }

        return !empty($config) ? $config : null;
    }

        /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array                                        $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {

                /** @var Form $form */
                $form = $event->getForm();

                $configuration = $this->getContentTypeConfiguration($form);
                if (!$configuration) {
                    return;
                }

                /** @var FieldData $fieldData */
                $fieldData = $event->getData();
                $suggesterConfig = $this->getSuggesterConfig($configuration, $fieldData->fieldDefinition->identifier);

                if($suggesterConfig) {
                    $form->add(
                        'syllabs_suggester',
                        ButtonType::class,
                        [
                            'attr' => [
                                'class' => 'syllabs-suggester btn btn-primary',
                                'data-suggester-config' => json_encode($suggesterConfig)
                            ]
                        ]
                    );
                }

            }, -10000
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return ContentFieldType::class;
    }
}
