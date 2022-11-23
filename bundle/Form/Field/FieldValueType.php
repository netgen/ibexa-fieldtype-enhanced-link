<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLinkBundle\Form\Field;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use JMS\TranslationBundle\Annotation\Desc;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Type;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldValueType extends AbstractType
{
    private ContentService $contentService;
    private ContentTypeService $contentTypeService;
    private FieldTypeService $fieldTypeService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FieldTypeService $fieldTypeService
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeService = $fieldTypeService;
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ibexa_fieldtype_ngenhancedlink';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'link_type',
                ChoiceType::class,
                [
                    'choices' => [
                        'field_edit.ngenhancedlink.link_type.' . Type::LINK_TYPE_INTERNAL => Type::LINK_TYPE_INTERNAL,
                        'field_edit.ngenhancedlink.link_type.' . Type::LINK_TYPE_EXTERNAL => Type::LINK_TYPE_EXTERNAL,
                    ],
                    'label' => /* @Desc("Text") */ 'field_edit.ngenhancedlink.link_type',
                    'required' => true,
                    'multiple' => false,
                    'expanded' => true,
                ],
            )
            ->add(
                'id',
                IntegerType::class,
                [
                    'label' => /* @Desc("Text") */ 'field_edit.ngenhancedlink.internal_id',
                    'required' => false,
                ],
            )
            ->add(
                'suffix',
                TextType::class,
                [
                    'label' => $options['enable_query_parameter'] ? /* @Desc("Text") */ 'field_edit.ngenhancedlink.suffix' : false,
                    'required' => false,
                    'attr' => ['hidden' => !$options['enable_query_parameter']],
                    'disabled' => !$options['enable_query_parameter'],
                ],
            )
            ->add(
                'label_internal',
                TextType::class,
                [
                    'label' => /* @Desc("Text") */ 'field_edit.ngenhancedlink.label',
                    'required' => false,
                ],
            )
            ->add(
                'target_internal',
                ChoiceType::class,
                [
                    'choices' => [
                        'field_edit.ngenhancedlink.target.' . Type::TARGET_LINK => Type::TARGET_LINK,
                        'field_edit.ngenhancedlink.target.' . Type::TARGET_LINK_IN_NEW_TAB => Type::TARGET_LINK_IN_NEW_TAB,
                        'field_edit.ngenhancedlink.target.' . Type::TARGET_EMBED => Type::TARGET_EMBED,
                        'field_edit.ngenhancedlink.target.' . Type::TARGET_MODAL => Type::TARGET_MODAL,
                    ],
                    'label' => /* @Desc("Text") */ 'field_edit.ngenhancedlink.target',
                    'required' => true,
                ],
            )
            ->add(
                'url',
                UrlType::class,
                [
                    'label' => /* @Desc("URL") */ 'field_edit.ngenhancedlink.url',
                    'required' => $options['required'],
                ],
            )
            ->add(
                'label_external',
                TextType::class,
                [
                    'label' => /* @Desc("Text") */ 'field_edit.ngenhancedlink.label',
                    'required' => false,
                ],
            )
            ->add(
                'target_external',
                ChoiceType::class,
                [
                    'choices' => [
                        'field_edit.ngenhancedlink.target.' . Type::TARGET_LINK => Type::TARGET_LINK,
                        'field_edit.ngenhancedlink.target.' . Type::TARGET_LINK_IN_NEW_TAB => Type::TARGET_LINK_IN_NEW_TAB,
                        'field_edit.ngenhancedlink.target.' . Type::TARGET_EMBED => Type::TARGET_EMBED,
                        'field_edit.ngenhancedlink.target.' . Type::TARGET_MODAL => Type::TARGET_MODAL,
                    ],
                    'label' => /* @Desc("Text") */ 'field_edit.ngenhancedlink.target',
                    'required' => true,
                ],
            )
            ->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('ngenhancedlink')));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['relations'] = [];
        $view->vars['default_location'] = $options['default_location'];
        $view->vars['root_default_location'] = $options['root_default_location'];

        /** @var \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $data */
        $data = $form->getData();

        if (!$data instanceof Value || null === $data->reference || $data->isTypeExternal()) {
            return;
        }

        $contentId = $data->reference;
        $contentInfo = null;
        $contentType = null;
        $unauthorized = false;

        try {
            $contentInfo = $this->contentService->loadContentInfo($contentId);
            $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
        } catch (UnauthorizedException $e) {
            $unauthorized = true;
        }

        $view->vars['relations'][$data->reference] = [
            'contentInfo' => $contentInfo,
            'contentType' => $contentType,
            'unauthorized' => $unauthorized,
            'contentId' => $contentId,
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'field_edit',
            'attr' => [
                'min' => 1,
                'step' => 1,
            ],
            'default_location' => null,
            'root_default_location' => null,
            'location' => null,
            'enable_query_parameter' => null,
        ]);
        $resolver->setAllowedTypes('default_location', ['null', Location::class]);
        $resolver->setAllowedTypes('root_default_location', ['null', 'bool']);
        $resolver->setAllowedTypes('enable_query_parameter', ['null', 'bool']);
        $resolver->setAllowedTypes('location', ['null', Location::class]);
    }
}
