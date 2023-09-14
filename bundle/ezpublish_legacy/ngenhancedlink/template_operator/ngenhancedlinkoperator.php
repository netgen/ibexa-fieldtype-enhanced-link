<?php

class NgEnhancedLinkOperator
{

    /**
     * @return array
     */
    function operatorList()
    {
        return array(
            'ng_json_decode',
        );
    }

    /**
     * @return bool
     */
    function namedParameterPerOperator()
    {
        return true;
    }

    /**
     * @return array
     */
    function namedParameterList()
    {
        return array(
            'ng_json_decode' => array(
                'json' => array(
                    'type' => 'string',
                    'required' => true
                )
            ),
        );
    }
    function modify(
        $tpl,
        $operatorName,
        $operatorParameters,
        $rootNamespace,
        $currentNamespace,
        &$operatorValue,
        $namedParameters,
        $placement
    ) {
        if ($operatorName === 'ng_json_decode') {
            $operatorValue = $this->jsonDecode($namedParameters['json']);
        }
    }

    function jsonDecode($json): array
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return [];
        }
    }
}