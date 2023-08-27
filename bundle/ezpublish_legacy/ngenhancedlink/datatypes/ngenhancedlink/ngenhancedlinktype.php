<?php
/**
 * File containing the eZObjectRelationType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 * @package kernel
 */

/*!
  \class ngEnhgancedLinkType ngenhancedlinktype.php
  \ingroup eZDatatype
  \brief A content datatype which handles object relations and urls
*/

class NgEnhancedLinkType extends eZDataType
{
    public const SELECTION_LINK_TYPE_INTERNAL = 0;
    public const SELECTION_LINK_TYPE_EXTERNAL = 1;
    public const LINK_TYPE_ALL = 'all';

    public const LINK_TYPE_INTERNAL = 'internal';
    public const LINK_TYPE_EXTERNAL = 'external';
    public const TARGET_LINK = 'link';
    public const TARGET_LINK_IN_NEW_TAB = 'link_new_tab';
    public const TARGET_EMBED = 'embed';
    public const TARGET_MODAL = 'modal';

    public const LINK_TYPES = [
        'internal',
        'external',
    ];

    public const ALLOWED_LINK_TYPES = [
        'all',
        'internal',
        'external',
    ];

    public const TARGETS = [
        0 => 'link',
        1 => 'link_new_tab',
        2 => 'embed',
        3 => 'modal',
    ];

    public const DATA_TYPE_STRING = "ngenhancedlink";

    public function __construct()
    {
        parent::__construct(
            self::DATA_TYPE_STRING,
            ezpI18n::tr(
                'kernel/classes/datatypes',
                "Enhanced Link",
                'Datatype name'
            ),
            [
                'serialize_supported' => true,
            ],
        );
    }

    /*!
     * Indicates if the datatype handles relations
     */
    public function isRelationType(): bool
    {
        return true;
    }

    /*!
     Initializes the class attribute with some data.
     */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $dataText = $originalContentObjectAttribute->attribute( "data_text" );
            $dataInt = $originalContentObjectAttribute->attribute( "data_int" );
            $data = $originalContentObjectAttribute->attribute( "content" );
            $contentObjectAttribute->setAttribute( "data_text", $dataText );
            $contentObjectAttribute->setAttribute( "data_int", $dataInt );
            $contentObjectAttribute->setContent( $data );
        }
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $linkTypeVariableName = "ContentClass_ngenhancedlink_link_type_" . $contentObjectAttribute->ContentClassAttributeID;

        if(!$http->hasPostVariable($linkTypeVariableName) or !in_array($http->postVariable($linkTypeVariableName), [self::SELECTION_LINK_TYPE_INTERNAL, self::SELECTION_LINK_TYPE_EXTERNAL])){
            $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes', 'Invalid link type input.'));
            return eZInputValidator::STATE_INVALID;
        }

        $linkType = $http->postVariable($linkTypeVariableName);

        if($linkType == $this::SELECTION_LINK_TYPE_INTERNAL){
            $relationVariableName = $base . "_data_object_relation_id_" . $contentObjectAttribute->attribute( "id" );

            if (!$http->hasPostVariable($relationVariableName) or ($contentObjectAttribute->validateIsRequired() and $http->postVariable($relationVariableName) == 0)) {
                $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes',
                    'Missing internal link input.'));
                return eZInputValidator::STATE_INVALID;
            }

        }else if ($linkType == $this::SELECTION_LINK_TYPE_EXTERNAL){
            $urlVariableName = $base . "_ngenhancedlink_url_" . $contentObjectAttribute->attribute( "id" );

            if (!$http->hasPostVariable($urlVariableName) or ($contentObjectAttribute->validateIsRequired() and empty($http->postVariable($urlVariableName)))) {
                $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes',
                    'Missing external link input.'));
                return eZInputValidator::STATE_INVALID;
            }
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var string input and stores it in the data instance.
    */
    /**
     * @throws JsonException
     */
    function fetchObjectAttributeHTTPInput($http, $base, $contentObjectAttribute )
    {
        $linkTypeVariableName = "ContentClass_ngenhancedlink_link_type_" . $contentObjectAttribute->ContentClassAttributeID;
        $linkType = $http->postVariable($linkTypeVariableName);
        $haveData = false;

        if($linkType == $this::SELECTION_LINK_TYPE_INTERNAL){
            $internalLinkIdInputName = $base . "_data_object_relation_id_" . $contentObjectAttribute->attribute( "id" );
            $internalLinkSuffixInputName = $base . "_ngenhancedlink_suffix_" . $contentObjectAttribute->attribute( "id" );
            $internalLinkLabelInputName = $base . "_ngenhancedlink_label_internal_" . $contentObjectAttribute->attribute( "id" );
            $internalLinkTargetInputName = "ContentClass_ngenhancedlink_target_internal_" . $contentObjectAttribute->ContentClassAttributeID;

            $internalLinkId = $http->postVariable($internalLinkIdInputName);
            $internalLinkSuffix = $http->postVariable($internalLinkSuffixInputName);
            $internalLinkLabel = $http->postVariable($internalLinkLabelInputName);
            $internalLinkTarget = $http->postVariable($internalLinkTargetInputName);

            $data = [
                'id' => ((int)$internalLinkId) === 0 ? null : (int)$internalLinkId,
                'label' => $internalLinkLabel,
                'type' => self::LINK_TYPE_INTERNAL,
                'target' => $this::TARGETS[$internalLinkTarget],
                'suffix' => $internalLinkSuffix,
            ];

            $dataText = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

            $contentObjectAttribute->setAttribute('data_text', $dataText);
            $contentObjectAttribute->setAttribute('data_int', (int)$internalLinkId);
            $haveData = true;
        }else if($linkType == 1){
            $externalLinkIdInputName = $base . "_ngenhancedlink_url_" . $contentObjectAttribute->attribute( "id" );
            $externalLinkLabelInputName = $base . "_ngenhancedlink_label_external_" . $contentObjectAttribute->attribute( "id" );
            $externalLinkTargetInputName = "ContentClass_ngenhancedlink_target_external_" . $contentObjectAttribute->ContentClassAttributeID;

            $externalLinkId = $http->postVariable($externalLinkIdInputName);
            $externalLinkLabel = $http->postVariable($externalLinkLabelInputName);
            $externalLinkTarget = $http->postVariable($externalLinkTargetInputName);

            $data = [
                'id' => $externalLinkId,
                'label' => $externalLinkLabel,
                'type' => self::LINK_TYPES[$linkType],
                'target' => $this::TARGETS[$externalLinkTarget],
                'suffix' => null,
            ];

            $dataText = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

            $contentObjectAttribute->setAttribute('data_text', $dataText);

            $haveData = true;
        }
        return $haveData;
    }

    /*!
     Stores relation or url
    */
    function storeObjectAttribute( $contentObjectAttribute )
    {
        $contentClassAttributeID = $contentObjectAttribute->ContentClassAttributeID;
        $contentObjectID = $contentObjectAttribute->ContentObjectID;
        $contentObjectVersion = $contentObjectAttribute->Version;
        $languageCode = $contentObjectAttribute->attribute( 'language_code' );

        /** @var eZContentObject */
        $contentObject = $contentObjectAttribute->object();

        if(trim($contentObjectAttribute->attribute('data_text')) == '' ){
            return false;
        }
        $contentObjectAttributeData = json_decode($contentObjectAttribute->attribute('data_text'), true, 512, JSON_THROW_ON_ERROR);

        if($contentObjectAttributeData['type'] === $this::LINK_TYPE_INTERNAL){
            if ( $contentObjectAttribute->ID !== null )
            {
                // cleanup previous relations
                $contentObject->removeContentObjectRelation( false, $contentObjectVersion, $contentClassAttributeID, eZContentObject::RELATION_ATTRIBUTE );
                // if translatable, we need to re-add the relations for other languages of (previously) published version.
                $publishedVersionNo = $contentObject->publishedVersion();
                if ( $contentObjectAttribute->contentClassAttributeCanTranslate() && $publishedVersionNo > 0 )
                {
                    $existingRelations = array();

                    // get published translations of this attribute
                    $pubAttribute = eZContentObjectAttribute::fetch($contentObjectAttribute->ID, $publishedVersionNo );
                    if ( $pubAttribute )
                    {
                        foreach( $pubAttribute->fetchAttributeTranslations() as $attributeTranslation )
                        {
                            // skip if language is the one being saved
                            if ( $attributeTranslation->LanguageCode === $languageCode )
                                continue;

                            if ( $attributeTranslation->attribute( 'data_int' ) )
                                $existingRelations[$attributeTranslation->LanguageCode] = (int)$attributeTranslation->attribute( 'data_int' );
                        }
                    }

                    // fetch existing attribute translations for current editing version
                    foreach( $contentObjectAttribute->fetchAttributeTranslations() as $attributeTranslation )
                    {
                        if ( $attributeTranslation->LanguageCode === $languageCode )
                            continue;

                        if ( $attributeTranslation->attribute( 'data_int' ) )
                            $existingRelations[$attributeTranslation->LanguageCode] = (int)$attributeTranslation->attribute( 'data_int' );
                    }


                    // re-add existing or new relations for other languages
                    foreach( array_unique($existingRelations) as $existingObjectId )
                    {
                        $contentObject->addContentObjectRelation( $existingObjectId, $contentObjectVersion, $contentClassAttributeID, eZContentObject::RELATION_ATTRIBUTE );
                    }
                }
            }

            $objectID = $contentObjectAttribute->attribute( 'data_int' );
            if ( $objectID )
            {
                $contentObject->addContentObjectRelation( $objectID, $contentObjectVersion, $contentClassAttributeID, eZContentObject::RELATION_ATTRIBUTE );
            }
        }else if($contentObjectAttributeData['type'] === $this::LINK_TYPE_EXTERNAL){
            $urlValue = $contentObjectAttributeData['id'];
            if(is_string($urlValue)){
                $contentObjectAttribute->setContent($urlValue);
            }else{
                $url = eZURL::fetch( $contentObjectAttribute->attribute( 'data_int' ) );
                if ( is_object( $url ) and
                    trim( $url->attribute( 'url' ) ) != '' and
                    $url->attribute( 'is_valid' ) ){
                    $contentObjectAttribute->setContent($url->attribute('url'));
                    $contentObjectAttributeData['id']=$url->attribute('url');
                    $urlValue=$url->attribute('url');
                }
            }
            $contentObjectAttribute->setAttribute( 'data_text', json_encode($contentObjectAttributeData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) );
            if ( trim( $urlValue ) != '' )
            {
                $oldURLID = $contentObjectAttribute->attribute( 'data_int' );
                $urlID = eZURL::registerURL( $urlValue );
                $contentObjectAttribute->setAttribute( 'data_int', $urlID );
                $contentObjectAttributeData['id'] = (int)$urlID;
                $contentObjectAttribute->setAttribute( 'data_text', json_encode($contentObjectAttributeData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) );

                if ( $oldURLID && $oldURLID != $urlID &&
                    !eZURLObjectLink::hasObjectLinkList( $oldURLID ) )
                    eZURL::removeByID( $oldURLID );
            }
            else
            {
                $contentObjectAttribute->setAttribute( 'data_int', 0 );
            }
        }
        return true;
    }

    function postStore($contentObjectAttribute){
        if(trim($contentObjectAttribute->attribute('data_text')) == '' ){
            return false;
        }
        $contentObjectAttributeData = json_decode($contentObjectAttribute->attribute('data_text'), true, 512, JSON_THROW_ON_ERROR);
        if($contentObjectAttributeData['type'] === $this::LINK_TYPE_EXTERNAL){

            $urlValue = $contentObjectAttribute->content();

            if ( trim( $urlValue ) != '' )
            {
                $urlID = eZURL::registerURL( $urlValue );
                $objectAttributeID = $contentObjectAttribute->attribute( 'id' );
                $objectAttributeVersion = $contentObjectAttribute->attribute( 'version' );


                $db = eZDB::instance();
                $db->begin();

                $objectLinkList = eZURLObjectLink::fetchLinkObjectList( $objectAttributeID, $objectAttributeVersion );

                // In order not to have duplicated links, delete existing ones that have been created during the version creation process
                // and create a clean one (we can't update url_id since there's no primary key). This fixes EZP-20988
                if ( !empty( $objectLinkList ) )
                {
                    eZURLObjectLink::removeURLlinkList( $objectAttributeID, $objectAttributeVersion );
                }

                $linkObjectLink = eZURLObjectLink::create( $urlID, $objectAttributeID, $objectAttributeVersion );
                $linkObjectLink->store();

                $db->commit();
            }
        }
    }

    function validateClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $state = eZInputValidator::STATE_ACCEPTED;

        $linkTypeName = 'ContentClass_ngenhancedlink_link_type_' . $classAttribute->attribute( 'id' );

        if(!($http->hasPostVariable($linkTypeName))) {
            $state = eZInputValidator::STATE_INVALID;
        }

        $linkType = $http->postVariable($linkTypeName);
        if($linkType < 0 or $linkType > 2){
            $state = eZInputValidator::STATE_INVALID;
        }

        if(in_array($this::ALLOWED_LINK_TYPES[$linkType], [$this::LINK_TYPE_ALL, $this::LINK_TYPE_INTERNAL])){
            $internalTargetsName = 'ContentClass_ngenhancedlink_internal_target_' . $classAttribute->attribute( 'id' );
            $classConstraintsName = 'ContentClass_ngenhancedlink_class_list_' . $classAttribute->attribute( 'id' );

            if(!($http->hasPostVariable($internalTargetsName) and
                $http->hasPostVariable($classConstraintsName))) {
                $state = eZInputValidator::STATE_INVALID;
            }

            $internalTargetTypes = $http->postVariable($internalTargetsName);
            $diff = array_diff(array_values($internalTargetTypes), array_keys($this::TARGETS));
            if(!empty($diff)){
                $state = eZInputValidator::STATE_INVALID;
            }
        }

        if(in_array($this::ALLOWED_LINK_TYPES[$linkType], [$this::LINK_TYPE_ALL, $this::LINK_TYPE_EXTERNAL])){
            $externalTargetsName = 'ContentClass_ngenhancedlink_external_target_' . $classAttribute->attribute( 'id' );

            if(!$http->hasPostVariable($externalTargetsName)) {
                $state = eZInputValidator::STATE_INVALID;
            }

            $externalTargetTypes = $http->postVariable($externalTargetsName);
            $diff = array_diff(array_values($externalTargetTypes), array_keys($this::TARGETS));
            if(!empty($diff)){
                $state = eZInputValidator::STATE_INVALID;
            }

        }

        return $state;
    }

    function fixupClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
    }

    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $content = $classAttribute->content();

        $linkTypeName = 'ContentClass_ngenhancedlink_link_type_' . $classAttribute->attribute( 'id' );
        if($http->hasPostVariable($linkTypeName)){
            $linkType = $http->postVariable($linkTypeName);
            $content['allowedLinkType'] = $this::ALLOWED_LINK_TYPES[$linkType];
            if(in_array($this::ALLOWED_LINK_TYPES[$linkType], [$this::LINK_TYPE_ALL, $this::LINK_TYPE_INTERNAL])){
                $internalTargetsName = 'ContentClass_ngenhancedlink_internal_target_' . $classAttribute->attribute( 'id' );
                if($http->hasPostVariable($internalTargetsName)){
                    $internalTargets = $http->postVariable($internalTargetsName);
                    $internalTargetsList = array();
                    foreach ($internalTargets as $target){
                        $internalTargetsList[] = $this::TARGETS[$target];
                    }
                    $content['allowedTargetsInternal'] = $internalTargetsList;
                }

                $enableSuffix = 'ContentClass_ngenhancedlink_enable_suffix_' . $classAttribute->attribute( 'id' );
                if($http->hasPostVariable($enableSuffix)){
                    $content['enableSuffix'] = true;
                }

                $classConstraintsName = 'ContentClass_ngenhancedlink_class_list_' . $classAttribute->attribute( 'id' );
                if($http->hasPostVariable($classConstraintsName)){
                    $constrainedList = $http->postVariable( $classConstraintsName );
                    $constrainedClassList = array();
                    foreach ( $constrainedList as $constraint )
                    {
                        if ( trim( $constraint ) != '' )
                            $constrainedClassList[] = $constraint;
                    }
                    $content['selectionContentTypes'] = $constrainedClassList;
                }
            }
            if(in_array($this::ALLOWED_LINK_TYPES[$linkType], [$this::LINK_TYPE_ALL, $this::LINK_TYPE_EXTERNAL])){
                $externalTargetsName = 'ContentClass_ngenhancedlink_external_target_' . $classAttribute->attribute( 'id' );
                if($http->hasPostVariable($externalTargetsName)){
                    $externalTargets = $http->postVariable($externalTargetsName);
                    $externalTargetsList = array();
                    foreach ($externalTargets as $target){
                        $externalTargetsList[] = $this::TARGETS[$target];
                    }
                    $content['allowedTargetsExternal'] = $externalTargetsList;
                }
            }
        }
        $classAttribute->setContent( $content );
        return true;
    }

    function initializeClassAttribute( $classAttribute )
    {
        $dataText = $classAttribute->attribute( 'data_text5' );

        if ( trim( $dataText ) == '' )
        {
            $content = $this->defaultClassAttributeContent();
            $classAttribute->setContent($content);
            return $this->storeClassAttributeContent( $classAttribute, $content );
        }
    }

    function preStoreClassAttribute( $classAttribute, $version )
    {
        $content = $classAttribute->content();
        $classAttributeContent = $this->defaultClassAttributeContent();
        foreach( $content as $key => $value )
        {
            if( isset( $classAttributeContent[$key] ) )
            {
                $classAttributeContent[$key] = $value;
            }
        }

        $this->storeClassAttributeContent( $classAttribute, $classAttributeContent );
    }

    /**
     * @throws JsonException
     */
    function storeClassAttributeContent($classAttribute, $content )
    {
        if(is_array($content)){
            $fieldSettings = json_encode($content, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            $classAttribute->setAttribute( 'data_text5', $fieldSettings );
            return true;
        }
        return false;
    }

    /*!
     \private
     Delete the old version from ezcontentobject_link if count of translations > 1
    */
    function removeContentObjectRelation( $contentObjectAttribute )
    {
        $obj = $contentObjectAttribute->object();
        $atrributeTrans = $contentObjectAttribute->fetchAttributeTranslations( );
        try {
            $contentObjectAttributeData = json_decode($contentObjectAttribute->attribute('data_text'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return;
        }
        if($contentObjectAttributeData['type'] === $this::LINK_TYPE_EXTERNAL) return;
        // Check if current relation exists in ezcontentobject_link
        foreach ( $atrributeTrans as $attrTarns )
        {
            if ( $attrTarns->attribute( 'id' ) != $contentObjectAttribute->attribute( 'id' ) )
                if ( $attrTarns->attribute( 'data_int' ) == $contentObjectAttribute->attribute( 'data_int' ) )
                    return;
        }

        //get eZContentObjectVersion
        $currVerobj = $obj->currentVersion();
        // get array of ezcontentobjecttranslations
        $transList = $currVerobj->translations( false );
        // get count of LanguageCode in transList
        $countTsl = count( $transList );
        // Delete the old version from ezcontentobject_link if count of translations > 1
        if ( $countTsl > 1 )
        {
            $objectID = $contentObjectAttribute->attribute( "data_int" );
            $contentClassAttributeID = $contentObjectAttribute->ContentClassAttributeID;
            $contentObjectID = $contentObjectAttribute->ContentObjectID;
            $contentObjectVersion = $contentObjectAttribute->Version;
            eZContentObject::fetch( $contentObjectID )->removeContentObjectRelation( $objectID, $contentObjectVersion, $contentClassAttributeID, eZContentObject::RELATION_ATTRIBUTE );
        }
    }

    function customObjectAttributeHTTPAction( $http, $action, $contentObjectAttribute, $parameters )
    {
        switch ( $action )
        {
            case "set_object_relation" :
            {

                if ( $http->hasPostVariable( 'BrowseActionName' ) and
                          $http->postVariable( 'BrowseActionName' ) == ( 'AddRelatedObject_' . $contentObjectAttribute->attribute( 'id' ) ) and
                          $http->hasPostVariable( "SelectedObjectIDArray" ) )
                {
                    if ( !$http->hasPostVariable( 'BrowseCancelButton' ) )
                    {
                        $selectedObjectArray = $http->hasPostVariable( "SelectedObjectIDArray" );
                        $selectedObjectIDArray = $http->postVariable( "SelectedObjectIDArray" );

                        // Delete the old version from ezcontentobject_link if count of translations > 1
                        $this->removeContentObjectRelation( $contentObjectAttribute );

                        $objectID = $selectedObjectIDArray[0];
                        $contentObjectAttribute->setAttribute( 'data_int', $objectID );
                        $contentObjectAttribute->store();
                    }
                }
            } break;

            case "browse_object" :
            {
                $module = $parameters['module'];
                $redirectionURI = $parameters['current-redirection-uri'];
                $ini = eZINI::instance( 'content.ini' );

                $browseParameters = array( 'action_name' => 'AddRelatedObject_' . $contentObjectAttribute->attribute( 'id' ),
                                           'type' =>  'AddRelatedObjectToDataType',
                                           'browse_custom_action' => array( 'name' => 'CustomActionButton[' . $contentObjectAttribute->attribute( 'id' ) . '_set_object_relation]',
                                                                            'value' => $contentObjectAttribute->attribute( 'id' ) ),
                                           'persistent_data' => array( 'HasObjectInput' => 0 ),
                                           'from_page' => $redirectionURI );
                $browseTypeINIVariable = $ini->variable( 'ObjectRelationDataTypeSettings', 'ClassAttributeStartNode' );
                foreach( $browseTypeINIVariable as $value )
                {
                    list( $classAttributeID, $type ) = explode( ';',$value );
                    if ( $classAttributeID == $contentObjectAttribute->attribute( 'contentclassattribute_id' ) && strlen( $type ) > 0 )
                    {
                        $browseParameters['type'] = $type;
                        break;
                    }
                }

                $nodePlacementName = $parameters['base_name'] . '_browse_for_object_start_node';
                if ( $http->hasPostVariable( $nodePlacementName ) )
                {
                    $nodePlacement = $http->postVariable( $nodePlacementName );
                    if ( isset( $nodePlacement[$contentObjectAttribute->attribute( 'id' )] ) )
                        $browseParameters['start_node'] = eZContentBrowse::nodeAliasID( $nodePlacement[$contentObjectAttribute->attribute( 'id' )] );
                }

                // Fetch the list of "allowed" classes .
                // A user can select objects of only those allowed classes when browsing.
                $classAttribute = $contentObjectAttribute->attribute( 'contentclass_attribute' );
                $classContent   = $classAttribute->content();
                if ( isset( $classContent['class_constraint_list'] ) )
                {
                    $classConstraintList = $classContent['class_constraint_list'];
                }
                else
                {
                    $classConstraintList = array();
                }

                if ( count($classConstraintList) > 0 )
                {
                    $browseParameters['class_array'] = $classConstraintList;
                }

                eZContentBrowse::browse( $browseParameters,
                                         $module );
            } break;

            case "remove_object" :
            {
                // Delete the old version from ezcontentobject_link if count of translations > 1
                $this->removeContentObjectRelation( $contentObjectAttribute );

                $contentObjectAttribute->setAttribute( 'data_int', 0 );
                $contentObjectAttribute->store();
            } break;

            default :
            {
                eZDebug::writeError( "Unknown custom HTTP action: " . $action, "NgEnhancedLinkType" );
            } break;
        }
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        try {
            $contentObjectAttributeData = json_decode($contentObjectAttribute->attribute('data_text'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return false;
        }
        if($contentObjectAttributeData['type'] === self::LINK_TYPE_INTERNAL){
            $object = $this->objectAttributeContent( $contentObjectAttribute );
            if ( $object )
                return true;
        }else if($contentObjectAttribute['type'] === self::LINK_TYPE_EXTERNAL){
            if ( $contentObjectAttribute->attribute( 'data_int' ) == 0 )
                return false;

            $url = eZURL::fetch( $contentObjectAttribute->attribute( 'data_int' ) );
            if ( is_object( $url ) and
                trim( $url->attribute( 'url' ) ) != '' and
                $url->attribute( 'is_valid' ) )
                return true;
        }
        return false;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $data = $contentObjectAttribute->attribute( "data_text" );
        if(empty(trim($data))) return null;
        $contentObjectAttributeData = json_decode($contentObjectAttribute->attribute('data_text'), true, 512, JSON_THROW_ON_ERROR);
        if($contentObjectAttributeData['type'] === self::LINK_TYPE_INTERNAL){
            $objectID = $contentObjectAttribute->attribute( "data_int" );
            if ( trim($data) != '' and $objectID != 0 )
                //treba dodati i za url
                $object = eZContentObject::fetch( $objectID );
            else
                $object = null;
            return $object;
        }else if($contentObjectAttributeData['type'] === self::LINK_TYPE_EXTERNAL){
            if ( !$contentObjectAttribute->attribute( 'data_int' ) )
            {
                $attrValue = null;
                return $attrValue;
            }
            $url = eZURL::url( $contentObjectAttribute->attribute( 'data_int' ) );

            return $url;
        }
        return null;
    }

    function defaultClassAttributeContent()
    {
        return [
        'selectionContentTypes' => [],
        'allowedLinkType' => $this::LINK_TYPE_ALL,
        'allowedTargetsInternal' => [
            $this::TARGET_LINK,
            $this::TARGET_LINK_IN_NEW_TAB,
            $this::TARGET_EMBED,
            $this::TARGET_MODAL,
        ],
        'allowedTargetsExternal' => [
            $this::TARGET_LINK,
            $this::TARGET_LINK_IN_NEW_TAB,
        ],
        'enableSuffix' => false,
        ];
    }

    /*!
     Sets \c grouped_input to \c true when browse mode is active or
     a dropdown with a fuzzy match is used.
    */
    function objectDisplayInformation( $objectAttribute, $mergeInfo = false )
    {
        $classAttribute = $objectAttribute->contentClassAttribute();
        $content = $this->classAttributeContent( $classAttribute );
        $editGrouped = true;

        $info = array( 'edit' => array( 'grouped_input' => $editGrouped ),
                       'collection' => array( 'grouped_input' => $editGrouped ) );
        return eZDataType::objectDisplayInformation( $objectAttribute, $info );
    }

    function sortKey( $contentObjectAttribute )
    {
        try {
            $contentObjectAttributeData = json_decode($contentObjectAttribute->attribute('data_text'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return '';
        }
        if($contentObjectAttributeData['type']===self::LINK_TYPE_EXTERNAL){
            $url = eZURL::url( $contentObjectAttribute->attribute( 'data_int' ) );
            return (trim($url));
        }
        return $contentObjectAttribute->attribute( 'data_int' );
    }

    function sortKeyType()
    {
        return 'string';
    }

    function classAttributeContent( $classObjectAttribute )
    {
        $attributeContent = $this->defaultClassAttributeContent();
        $encodedContent = $classObjectAttribute->attribute( 'data_text5' );
        if ( trim( $encodedContent ) != '' )
        {
            $attributeContent = json_decode($encodedContent, true, 512, JSON_THROW_ON_ERROR);
        }
        return $attributeContent;
    }

    function deleteNotVersionedStoredClassAttribute( eZContentClassAttribute $classAttribute )
    {
        eZContentObjectAttribute::removeRelationsByContentClassAttributeId( $classAttribute->attribute( 'id' ) );
    }

/*    function customClassAttributeHTTPAction( $http, $action, $classAttribute )
    {
        switch ( $action )
        {
            case 'browse_for_selection_node':
            {
                $module = $classAttribute->currentModule();
                $customActionName = 'CustomActionButton[' . $classAttribute->attribute( 'id' ) . '_browsed_for_selection_node]';
                eZContentBrowse::browse( array( 'action_name' => 'SelectObjectRelationNode',
                                                'content' => array( 'contentclass_id' => $classAttribute->attribute( 'contentclass_id' ),
                                                                    'contentclass_attribute_id' => $classAttribute->attribute( 'id' ),
                                                                    'contentclass_version' => $classAttribute->attribute( 'version' ),
                                                                    'contentclass_attribute_identifier' => $classAttribute->attribute( 'identifier' ) ),
                                                'persistent_data' => array( $customActionName => '',
                                                                            'ContentClassHasInput' => false ),
                                                'description_template' => 'design:class/datatype/browse_ngenhancedlink_placement.tpl',
                                                'from_page' => $module->currentRedirectionURI() ),
                                         $module );
            } break;
            case 'browsed_for_selection_node':
            {
                $nodeSelection = eZContentBrowse::result( 'SelectObjectRelationNode' );
                if ( count( $nodeSelection ) > 0 )
                {
                    $nodeID = $nodeSelection[0];
                    $content = $classAttribute->content();
                    $content['default_selection_node'] = $nodeID;
                    $classAttribute->setContent( $content );
                }
            } break;
            case 'disable_selection_node':
            {
                $content = $classAttribute->content();
                $content['default_selection_node'] = false;
                $classAttribute->setContent( $content );
            } break;
            default:
            {
                eZDebug::writeError( "Unknown objectrelationlist action '$action'", __METHOD__ );
            } break;
        }
    }*/

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        $object = $this->objectAttributeContent( $contentObjectAttribute );
        if ( $object )
        {
            try {
                $contentObjectAttributeData = json_decode($contentObjectAttribute->attribute('data_text'), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                return false;
            }
            if($contentObjectAttributeData['type']===self::LINK_TYPE_EXTERNAL){
                return $object;
            }
            // Does the related object exist in the same language as the current content attribute ?
            if ( in_array( $contentObjectAttribute->attribute( 'language_code' ), $object->attribute( 'current' )->translationList( false, false ) ) )
            {
                $attributes = $object->attribute( 'current' )->contentObjectAttributes( $contentObjectAttribute->attribute( 'language_code' ) );
            }
            else
            {
                $attributes = $object->contentObjectAttributes();
            }

            return eZContentObjectAttribute::metaDataArray( $attributes, true );
        }
        return false;
    }
    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function fromString( $contentObjectAttribute, $string )
    {
        if ( !is_numeric( $string ) || !eZContentObject::fetch( $string ) )
            return false;

        $contentObjectAttribute->setAttribute( 'data_int', $string );
        return true;
    }

    function isIndexable()
    {
        return true;
    }

    /*!
     Returns the content of the string for use as a title
    */
    function title( $contentObjectAttribute, $name = null )
    {
        $contentObjectAttributeData = json_decode($contentObjectAttribute->attribute('data_text'), true, 512, JSON_THROW_ON_ERROR);
        $object = $this->objectAttributeContent( $contentObjectAttribute );
        if ( $object )
        {
            if($contentObjectAttributeData['type'] === $this::LINK_TYPE_EXTERNAL){
                $url = eZURL::url( $contentObjectAttribute->attribute( 'data_int' ) );
                return (trim($url));
            }
            return $object->attribute( 'name' );
        }
        return false;
    }

    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        $content = $classAttribute->content();
        $dom = $attributeParametersNode->ownerDocument;
        $selectionTypeNode = $dom->createElement( 'selection-type' );
        $selectionTypeNode->setAttribute( 'id', $content['selection_type'] );
        $attributeParametersNode->appendChild( $selectionTypeNode );
        $fuzzyMatchNode = $dom->createElement( 'fuzzy-match' );
        $fuzzyMatchNode->setAttribute( 'id', $content['fuzzy_match'] );
        $attributeParametersNode->appendChild( $fuzzyMatchNode );
        if ( $content['default_selection_node'] )
        {
            $defaultSelectionNode = $dom->createElement( 'default-selection' );
            $defaultSelectionNode->setAttribute( 'node-id', $content['default_selection_node'] );
            $attributeParametersNode->appendChild( $defaultSelectionNode );
        }
    }

    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
        //ok, selection-type moze ostati, osim toga moze, ost
        $content = $classAttribute->content();
        $selectionTypeNode = $attributeParametersNode->getElementsByTagName( 'selection-type' )->item( 0 );
        $content['selection_type'] = 0;
        if ( $selectionTypeNode )
            $content['selection_type'] = $selectionTypeNode->getAttribute( 'id' );

        $fuzzyMatchNode = $attributeParametersNode->getElementsByTagName( 'fuzzy-match' )->item( 0 );
        $content['fuzzy_match'] = false;
        if ( $fuzzyMatchNode )
            $content['fuzzy_match'] = $fuzzyMatchNode->getAttribute( 'id' );

        $defaultSelectionNode = $attributeParametersNode->getElementsByTagName( 'default-selection' )->item( 0 );
        $content['default_selection_node'] = false;
        if ( $defaultSelectionNode )
            $content['default_selection_node'] = $defaultSelectionNode->getAttribute( 'node-id' );

        $classAttribute->setContent( $content );
        $classAttribute->store();
    }

    /*!
     Export related object's remote_id.
    */
    function serializeContentObjectAttribute( $package, $objectAttribute )
    {
        $node = $this->createContentObjectAttributeDOMNode( $objectAttribute );
        $relatedObjectID = $objectAttribute->attribute( 'data_int' );

        if ( $relatedObjectID !== null )
        {
            $relatedObject = eZContentObject::fetch( $relatedObjectID );
            if ( !$relatedObject )
            {
                eZDebug::writeNotice( 'Related object with ID: ' . $relatedObjectID . ' does not exist.' );
            }
            else
            {
                $relatedObjectRemoteID = $relatedObject->attribute( 'remote_id' );
                $dom = $node->ownerDocument;
                $relatedObjectRemoteIDNode = $dom->createElement( 'related-object-remote-id' );
                $relatedObjectRemoteIDNode->appendChild( $dom->createTextNode( $relatedObjectRemoteID ) );
                $node->appendChild( $relatedObjectRemoteIDNode );
            }
        }

        return $node;
    }

    function unserializeContentObjectAttribute( $package, $objectAttribute, $attributeNode )
    {
        $relatedObjectRemoteIDNode = $attributeNode->getElementsByTagName( 'related-object-remote-id' )->item( 0 );
        $relatedObjectID = null;

        if ( $relatedObjectRemoteIDNode )
        {
            $relatedObjectRemoteID = $relatedObjectRemoteIDNode->textContent;
            $object = eZContentObject::fetchByRemoteID( $relatedObjectRemoteID );
            if ( $object )
            {
                $relatedObjectID = $object->attribute( 'id' );
            }
            else
            {
                // store remoteID so it can be used in postUnserialize
                $objectAttribute->setAttribute( 'data_text', $relatedObjectRemoteID );
            }
        }

        $objectAttribute->setAttribute( 'data_int', $relatedObjectID );
    }

    function postUnserializeContentObjectAttribute( $package, $objectAttribute )
    {
        $attributeChanged = false;
        $relatedObjectID = $objectAttribute->attribute( 'data_int' );

        if ( !$relatedObjectID )
        {
            // Restore cross-relations using preserved remoteID
            $relatedObjectRemoteID = $objectAttribute->attribute( 'data_text' );
            if ( $relatedObjectRemoteID)
            {
                $object = eZContentObject::fetchByRemoteID( $relatedObjectRemoteID );
                $relatedObjectID = ( $object !== null ) ? $object->attribute( 'id' ) : null;

                if ( $relatedObjectID )
                {
                    $objectAttribute->setAttribute( 'data_int', $relatedObjectID );
                    $attributeChanged = true;
                }
            }
        }

        return $attributeChanged;
    }

    function supportsBatchInitializeObjectAttribute()
    {
        return true;
    }

    /// \privatesection
}

eZDataType::register( NgEnhancedLinkType::DATA_TYPE_STRING, "NgEnhancedLinkType" );
