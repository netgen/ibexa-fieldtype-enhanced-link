{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{let content=$class_attribute.content
     class_list=$content.selectionContentTypes
     all_class_list=fetch( 'class', 'list', hash( 'sort_by', array( 'name', true() ) ) )}

<div class="block">
    <label for="eccael_types_{$class_attribute.id}">{'Allowed link types'|i18n( 'design/standard/class/datatype' )}:</label>
    <select id="eccael_types_{$class_attribute.id}" name="ContentClass_ngenhancedlink_link_type_{$class_attribute.id}">
        <option value="0" data-template-id="{$class_attribute.id}" {eq( $content.allowedLinkType, 'all' )|choose( '', 'selected="selected"' )}>{'All'|i18n( 'design/standard/class/datatype' )}</option>
        <option value="1" data-template-id="{$class_attribute.id}" {eq( $content.allowedLinkType, 'internal' )|choose( '', 'selected="selected"' )}>{'Internal'|i18n( 'design/standard/class/datatype' )}</option>
        <option value="2" data-template-id="{$class_attribute.id}" {eq( $content.allowedLinkType, 'external' )|choose( '', 'selected="selected"' )}>{'External'|i18n( 'design/standard/class/datatype' )}</option>
        {* Commented out because somebody forgot to implement this functionality... *}
        {*    <option value="2" {eq( $content.selection_type, 2 )|choose( '', 'selected="selected"' )}>{'Drop-down tree'|i18n( 'design/standard/class/datatype' )}</option> *}
    </select>
</div>

    <div class="block internal-link-options-block-{$class_attribute.id}">
        <h2>{'Internal link type options'|i18n( 'design/standard/class/datatype' )}:</h2>

        <div class="block">
            <label for="eccael_allowed_link_type_{$class_attribute.id}">{'Allowed classes'|i18n( 'design/standard/class/datatype' )}:</label>
            <select id="eccael_allowed_link_type_{$class_attribute.id}" name="ContentClass_ngenhancedlink_class_list_{$class_attribute.id}[]" multiple="multiple" title="{'Select which classes user can create'|i18n( 'design/standard/class/datatype' )}" size="{min( 8, count( $all_class_list ) )}">
                <option value="" {if $class_list|not}selected="selected"{/if}>{'Any'|i18n( 'design/standard/class/datatype' )}</option>
                {section name=Class loop=$all_class_list}
                    <option value="{$:item.identifier|wash}" {if $class_list|contains($:item.identifier)}selected="selected"{/if}>{$:item.name|wash}</option>
                {/section}
            </select>
        </div>

        <div class="block">
            <label for="eccael_enable_suffix_{$class_attribute.id}">{'Allow link suffix'|i18n( 'design/standard/class/datatype' )}:</label>
            <input id="eccael_enable_suffix_{$class_attribute.id}" type="checkbox" name="ContentClass_ngenhancedlink_enable_suffix_{$class_attribute.id}" {if $content.enableSuffix}checked="checked"{/if} />
        </div>

        <div class="block">
            <select id="eccael_allowed_internal_target_{$class_attribute.id}" name="ContentClass_ngenhancedlink_internal_target_{$class_attribute.id}[]" multiple="multiple" title="{'Allowed internal targets'|i18n( 'design/standard/class/datatype' )}" size="4">
                <option value="0" {if $content.allowedTargetsInternal|contains( 'link' )}selected="selected"{/if}>{'Link'|i18n( 'design/standard/class/datatype' )}</option>
                <option value="1" {if $content.allowedTargetsInternal|contains( 'link_new_tab' )}selected="selected"{/if}>{'Link in new tab'|i18n( 'design/standard/class/datatype' )}</option>
                <option value="2" {if $content.allowedTargetsInternal|contains( 'embed' )}selected="selected"{/if}>{'Embed'|i18n( 'design/standard/class/datatype' )}</option>
                <option value="3" {if $content.allowedTargetsInternal|contains( 'modal' )}selected="selected"{/if}>{'Modal'|i18n( 'design/standard/class/datatype' )}</option>
            </select>
        </div>
    </div>

    <div class="block external-link-options-block-{$class_attribute.id}">
        <h2>{'External link type options'|i18n( 'design/standard/class/datatype' )}:</h2>
        <div class="block">
            <select id="eccael_allowed_external_target_{$class_attribute.id}" name="ContentClass_ngenhancedlink_external_target_{$class_attribute.id}[]" multiple="multiple" title="{'Allowed external targets'|i18n( 'design/standard/class/datatype' )}" size="4">
                <option value="0" {if $content.allowedTargetsExternal|contains( 'link' )}selected="selected"{/if}>{'Link'|i18n( 'design/standard/class/datatype' )}</option>
                <option value="1" {if $content.allowedTargetsExternal|contains( 'link_new_tab' )}selected="selected"{/if}>{'Link in new tab'|i18n( 'design/standard/class/datatype' )}</option>
                <option value="2" {if $content.allowedTargetsExternal|contains( 'embed' )}selected="selected"{/if}>{'Embed'|i18n( 'design/standard/class/datatype' )}</option>
                <option value="3" {if $content.allowedTargetsExternal|contains( 'modal' )}selected="selected"{/if}>{'Modal'|i18n( 'design/standard/class/datatype' )}</option>
            </select>
        </div>
    </div>
{/let}