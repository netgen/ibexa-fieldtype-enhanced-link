<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Form;

use Ibexa\AdminUi\FieldType\Mapper\AbstractRelationFormMapper;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\Form\Type\RelationType;
use JMS\TranslationBundle\Annotation\Desc;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Type;
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
            ->add('allowedLinkType', ChoiceType::class, [
                'choices' => [
                    'field_definition.ngenhacnedlink.allowed_link_type.' . Type::ALLOWED_LINK_TYPE_INTERNAL => Type::ALLOWED_LINK_TYPE_INTERNAL,
                    'field_definition.ngenhacnedlink.allowed_link_type.' . Type::ALLOWED_LINK_TYPE_EXTERNAL => Type::ALLOWED_LINK_TYPE_EXTERNAL,
                    'field_definition.ngenhacnedlink.allowed_link_type.' . Type::ALLOWED_LINK_TYPE_ALL => Type::ALLOWED_LINK_TYPE_ALL,
                ],
                'property_path' => 'fieldSettings[allowedLinkType]',
                'label' => /* @Desc("Allowed link type") */ 'field_definition.ngenhancedlink.selection_allowed_link_type',
                'multiple' => false,
                'expanded' => true,
            ])
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
            ->add('enableQueryParameter', CheckboxType::class, [
                'required' => false,
                'label' => /* @Desc("Enable query parameter") */ 'field_definition.ngenhancedlink.enable_query_parameter',
                'property_path' => 'fieldSettings[enableQueryParameter]',
            ])
            ->add('allowedTargets', ChoiceType::class, [
                'choices' => [
                    'field_definition.ngenhacnedlink.allowed_target.' . Type::ALLOWED_TARGET_LINK => Type::ALLOWED_TARGET_LINK,
                    'field_definition.ngenhacnedlink.allowed_target.' . Type::ALLOWED_TARGET_LINK_IN_NEW_TAB => Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                    'field_definition.ngenhacnedlink.allowed_target.' . Type::ALLOWED_TARGET_IN_PLACE => Type::ALLOWED_TARGET_IN_PLACE,
                    'field_definition.ngenhacnedlink.allowed_target.' . Type::ALLOWED_TARGET_MODAL => Type::ALLOWED_TARGET_MODAL,
                ],
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
