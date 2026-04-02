{*
 Front template for Design Cart pHTML
*}

{assign var=wrapperStyles value=[]}
{if isset($dc_phtml_bg_color) && $dc_phtml_bg_color}
  {assign var=wrapperStyles value=array_merge($wrapperStyles, ["background-color:`$dc_phtml_bg_color`"])}
{/if}

{assign var=titleStyles value=[]}
{if isset($dc_phtml_title_font_size) && $dc_phtml_title_font_size}
  {assign var=titleStyles value=array_merge($titleStyles, ["font-size:`$dc_phtml_title_font_size`"])}
{/if}
{if isset($dc_phtml_title_color) && $dc_phtml_title_color}
  {assign var=titleStyles value=array_merge($titleStyles, ["color:`$dc_phtml_title_color`"])}
{/if}
{if isset($dc_phtml_title_font_weight) && $dc_phtml_title_font_weight}
  {assign var=titleStyles value=array_merge($titleStyles, ["font-weight:`$dc_phtml_title_font_weight`"])}
{/if}
{if isset($dc_phtml_title_align) && $dc_phtml_title_align}
  {assign var=titleStyles value=array_merge($titleStyles, ["text-align:`$dc_phtml_title_align`"])}
{/if}
{if !empty($dc_phtml_title_uppercase)}
  {assign var=titleStyles value=array_merge($titleStyles, ['text-transform:uppercase'])}
{/if}

{assign var=contentStyles value=[]}
{if isset($dc_phtml_content_font_size) && $dc_phtml_content_font_size}
  {assign var=contentStyles value=array_merge($contentStyles, ["font-size:`$dc_phtml_content_font_size`"])}
{/if}
{if isset($dc_phtml_content_color) && $dc_phtml_content_color}
  {assign var=contentStyles value=array_merge($contentStyles, ["color:`$dc_phtml_content_color`"])}
{/if}
{if !empty($dc_phtml_content_center)}
  {assign var=contentStyles value=array_merge($contentStyles, ['text-align:center'])}
{/if}
{if !empty($dc_phtml_content_uppercase)}
  {assign var=contentStyles value=array_merge($contentStyles, ['text-transform:uppercase'])}
{/if}

{assign var=wrapperStyleString value=implode(';', $wrapperStyles)}
{assign var=titleStyleString value=implode(';', $titleStyles)}
{assign var=contentStyleString value=implode(';', $contentStyles)}

<section class="dc-phtml-block" {if $wrapperStyleString}style="{$wrapperStyleString|escape:'htmlall':'UTF-8'}"{/if}>
  <div class="container">
    <div class="row">
      <div class="col-12">
        {if $dc_phtml_title}
          {if $dc_phtml_title_tag == 'h1'}
            <h1 class="dc-phtml-title" {if $titleStyleString}style="{$titleStyleString|escape:'htmlall':'UTF-8'}"{/if}>
              {$dc_phtml_title nofilter}
            </h1>
          {elseif $dc_phtml_title_tag == 'h3'}
            <h3 class="dc-phtml-title" {if $titleStyleString}style="{$titleStyleString|escape:'htmlall':'UTF-8'}"{/if}>
              {$dc_phtml_title nofilter}
            </h3>
          {elseif $dc_phtml_title_tag == 'h4'}
            <h4 class="dc-phtml-title" {if $titleStyleString}style="{$titleStyleString|escape:'htmlall':'UTF-8'}"{/if}>
              {$dc_phtml_title nofilter}
            </h4>
          {elseif $dc_phtml_title_tag == 'h5'}
            <h5 class="dc-phtml-title" {if $titleStyleString}style="{$titleStyleString|escape:'htmlall':'UTF-8'}"{/if}>
              {$dc_phtml_title nofilter}
            </h5>
          {elseif $dc_phtml_title_tag == 'h6'}
            <h6 class="dc-phtml-title" {if $titleStyleString}style="{$titleStyleString|escape:'htmlall':'UTF-8'}"{/if}>
              {$dc_phtml_title nofilter}
            </h6>
          {else}
            <h2 class="dc-phtml-title" {if $titleStyleString}style="{$titleStyleString|escape:'htmlall':'UTF-8'}"{/if}>
              {$dc_phtml_title nofilter}
            </h2>
          {/if}
        {/if}

        {if $dc_phtml_content}
          <div class="dc-phtml-content" {if $contentStyleString}style="{$contentStyleString|escape:'htmlall':'UTF-8'}"{/if}>
            {$dc_phtml_content nofilter}
          </div>
        {/if}
      </div>
    </div>
  </div>
</section>

