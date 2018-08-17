<?php

function smarty_prefilter_strip($source, $template)
{
    return '{{strip}}'.$source.'{{/strip}}';
}

