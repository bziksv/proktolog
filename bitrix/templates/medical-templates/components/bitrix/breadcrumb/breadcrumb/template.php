<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;


//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '';

$strReturn .= '<div class="breadcrumb"><div class="container"><ol class="breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
	$name = htmlspecialcharsex($arResult[$index]["TITLE"]);
	$title = ($index > 0) ? $name : "";
	$icon = (!$index) ? "icon-home" : "";
	$position = $index + 1;

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
	{
		$strReturn .= '
			<li class="breadcrumb__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="'.$arResult[$index]["LINK"].'" title="'.$title.'" itemprop="item" class="'.$icon.'">
					'.($index > 0 ? '<span itemprop="name">'.$title.'</span>' : '<span></span>').'
				</a>
				'.($index > 0 ? '' : '<meta itemprop="name" content="'.$name.'" />').'
				<meta itemprop="position" content="'.$position.'" />
			</li>';
	}
	else
	{
		$strReturn .= '
			<li class="breadcrumb__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">'.($title !== '' ? $title : $name).'</span>
				<meta itemprop="position" content="'.$position.'" />
			</li>';
	}
}

$strReturn .= '</ol></div></div>';

return $strReturn;

?>
