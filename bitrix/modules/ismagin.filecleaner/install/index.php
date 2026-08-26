<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

IncludeModuleLangFile(__FILE__);

class ismagin_filecleaner extends CModule
{
    var $MODULE_ID = 'ismagin.filecleaner';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;


    public function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__."/version.php");
        
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("ismagin.filecleaner_MODULE_TITLE");
		$this->MODULE_DESCRIPTION = GetMessage("ismagin.filecleaner_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = GetMessage("ismagin.filecleaner_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("ismagin.filecleaner_PARTNER_URI");

			$this->MODULE_ID            = str_replace("_", ".", get_class($this));
			$this->MODULE_NAME          = Loc::getMessage("ismagin.filecleaner_MODULE_NAME");
			$this->MODULE_DESCRIPTION	= Loc::getMessage("ismagin.filecleaner_MODULE_DESC");
			$this->PARTNER_NAME			= Loc::getMessage("ismagin.filecleaner_PARTNER_NAME");
			$this->PARTNER_URI			= Loc::getMessage("ismagin.filecleaner_PARTNER_URI");
    }

 public function DoInstall()
    {
        $this->InstallFiles();
        RegisterModule($this->MODULE_ID);
    }

 public function DoUninstall()
    {
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
    }

public function InstallFiles(): bool
	{
		CopyDirFiles( __DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);
		
		return true;
	}

	/**
	 * @return bool
	 */
	public function UnInstallFiles(): bool
	{
		DeleteDirFiles( __DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
		
		return true;
	}


}