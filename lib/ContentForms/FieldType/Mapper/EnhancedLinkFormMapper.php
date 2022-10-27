<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\FieldType\Mapper\AbstractRelationFormMapper;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Netgen\IbexaFieldTypeEnhancedLink\ContentForms\Form\Type\FieldType\EnhancedLinkFieldType;
use Symfony\Component\Form\FormInterface;

class EnhancedLinkFormMapper extends AbstractRelationFormMapper
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $fieldSettings = $fieldDefinition->getFieldSettings();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        EnhancedLinkFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired,
                            'label' => $fieldDefinition->getName(),
                            'default_location' => $this->loadDefaultLocationForSelection(
                                $fieldSettings['selectionRoot'],
                                $fieldForm->getConfig()->getOption('location'),
                            ),
                            'root_default_location' => $fieldSettings['rootDefaultLocation'] ?? false,
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
