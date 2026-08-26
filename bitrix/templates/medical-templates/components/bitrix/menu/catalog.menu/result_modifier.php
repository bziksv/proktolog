<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$arResult['PROPERTIES'] = [
	'UF_DELETE_INDEX' => [],
];

$page = $APPLICATION->GetCurPage();
$code = explode('/', $page);
if (!empty($code[2])) {
	$dbRes = CIBlockSection::GetList(array(), ["IBLOCK_ID" => IBLOCK_CATALOG, "CODE" => $code[2]], false, array("ID", "UF_*"));
	if ($arCurSection = $dbRes->Fetch()) {
		$arResult['PROPERTIES'] = $arCurSection;
		if (!is_array($arResult['PROPERTIES']['UF_DELETE_INDEX'])) {
			$arResult['PROPERTIES']['UF_DELETE_INDEX'] = [];
		}
	}
}
//determine if child selected

$bWasSelected = false;
$arParents = array();
$depth = 1;
foreach($arResult as $i=>$arMenu)
{
	$depth = $arMenu['DEPTH_LEVEL'];

	if($arMenu['IS_PARENT'] == true)
	{
		$arParents[$arMenu['DEPTH_LEVEL']-1] = $i;
	}
	elseif($arMenu['SELECTED'] == true)
	{
		$bWasSelected = true;
		break;
	}
}

if($bWasSelected)
{
	for($i=0; $i<$depth-1; $i++)
		$arResult[$arParents[$i]]['CHILD_SELECTED'] = true;
}
?>
