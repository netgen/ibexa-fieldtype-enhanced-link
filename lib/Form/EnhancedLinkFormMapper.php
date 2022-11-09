<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Form;

use Ibexa\AdminUi\FieldType\Mapper\AbstractRelationFormMapper;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\Form\Type\RelationType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnhancedLinkFormMapper extends AbstractRelationFormMapper
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add('selectionRoot', RelationType::class, [
                'required' => true,
                'property_path' => 'fieldSettings[selectionRoot]',
                'label' => /* @Desc("Starting Location") */ 'field_definition.ngenhancedlink.selection_root',
            ])
            ->add('rootDefaultLocation', CheckboxType::class, [
                'required' => false,
                'label' => /* @Desc("Root Default Location") */ 'field_definition.ngenhancedlink.root_default_location',
                'property_path' => 'fieldSettings[rootDefaultLocation]',
            ])
            ->add('selectionContentTypes', ChoiceType::class, [
                'choices' => $this->getContentTypesHash(),
                'expanded' => false,
                'multiple' => true,
                'required' => false,
                'property_path' => 'fieldSettings[selectionContentTypes]',
                'label' => /* @Desc("Allowed Content Types") */ 'field_definition.ngenhancedlink.selection_content_types',
                'disabled' => $isTranslation,
            ])
            ->add('allowedTargets', ChoiceType::class, [
                'choices' => ['Link' => 'link', 'Link in new tab' => 'link_new_tab', 'Embed / in_place' => 'in_place', 'Modal' => 'modal'],
                'property_path' => 'fieldSettings[allowedTargets]',
                'label' => /* @Desc("Allowed Targets") */ 'field_definition.ngenhancedlink.selection_allowed_targets',
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    /**
     * Fake method to set the translation domain for the extractor.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'content_type',
            ]);
    }
}
