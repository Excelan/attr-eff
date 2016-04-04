<?php
$GLOBALS['NESTING']['Document:Contract:BW'] = ['contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:LC'] = ['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']]; // ['CompanyLegalEntityCounterparty']
$GLOBALS['NESTING']['Document:Contract:LOP'] = ['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:LWP'] = ['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:MT'] = ['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:RSS'] = ['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:SS'] = ['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:TMC'] = ['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:TME'] = ['notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];

$GLOBALS['NESTING']['Document:Regulations:SOP'] = ['DirectoryAdditionalSectionSimple'];
$GLOBALS['NESTING']['Document:Regulations:P'] = ['DirectoryAdditionalSectionSimple'];
$GLOBALS['NESTING']['Document:Regulations:I'] = ['DirectoryAdditionalSectionSimple'];
//$GLOBALS['NESTING']['Document:Regulations:MP'] = ['all']; // !!!!!!!!!!!!!!!
$GLOBALS['NESTING']['Document:Regulations:PV'] = ['DirectoryMaterialbaseSimple', 'DirectoryOptionsSimple', 'DirectoryResponsibleSimple'];
$GLOBALS['NESTING']['Document:Regulations:I'] = ['attachments', 'DirectoryBusinessProcessItem', 'scaleapplication', 'CalendarPeriodMonth', 'boprocedure' ];


$GLOBALS['NESTING']['Document:Protocol:KI'] = ['DocumentCorrectionCapa'];
$GLOBALS['NESTING']['Document:Protocol:SI'] = ['controlriskapproved'];

$GLOBALS['NESTING']['Document:TechnicalTask:forMaterials'] = ['DirectoryTechnicalTaskMaterials'];
$GLOBALS['NESTING']['Document:TechnicalTask:forWorks'] = ['DirectoryTechnicalTaskForWorks'];

//Протоколы Служебного Расследования
#$GLOBALS['NESTING']['Document:Detective:C:IV'] = ['CompanyLegalEntityCounterparty', 'warehouse', 'attachments','object','responsible', 'commissionmember','checkbo', 'complaintstatus', 'CompanyStructureDepartment', 'notapprovedrisks' ];
$GLOBALS['NESTING']['Document:Detective:C_IV'] = ['checkbo_unit', 'responsible_unit', 'internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_LB'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_LC'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_LP'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_LT'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_IS'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_IW'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];

//Протокол Аудита
//$GLOBALS['NESTING']['Document:Protocol:EA'] = [ 'boprocedure','checkbo_unit','commisionhead' => ['PeopleEmployeeInternal'],'commisionmembers' => ['PeopleEmployeeInternal']];
$GLOBALS['NESTING']['Document:Protocol:EA'] = [ 'boprocedure','commisionhead_unit','commisionmembers_unit'];
//Протокол Проверки
//$GLOBALS['NESTING']['Document:Protocol:EC'] = ['commisionhead' => ['PeopleEmployeeInternal'],'commisionmembers' => ['PeopleEmployeeInternal']];
$GLOBALS['NESTING']['Document:Protocol:EC'] = ['boprocedure','commisionhead_unit','commisionmembers_unit'];
//Протокол ТО
//$GLOBALS['NESTING']['Document:Protocol:TM'] = ['bo' => ['MateriallyResponsible', 'ResponsibleMaintenance', 'location', 'boofclient']];
$GLOBALS['NESTING']['Document:Protocol:TM'] = ['CONTEXT_BusinessObjectRecordPolymorph'];
//Протокол Валидации
$GLOBALS['NESTING']['Document:Protocol:VT'] = ['bo' => ['MateriallyResponsible', 'ResponsibleMaintenance', 'location', 'boofclient']];
//$GLOBALS['NESTING']['Document:Protocol:VT'] = ['DirectoryResponsibletwoSimple', 'DirectoryFixedassetSimple'];
//Протокол Калибровки
$GLOBALS['NESTING']['Document:Protocol:CT'] = ['CONTEXT_BusinessObjectRecordPolymorph' => [ 'bo', 'MateriallyResponsible_unit', 'ResponsibleMaintenance'] ];
//$GLOBALS['NESTING']['Document:Protocol:CT'] = ['bo' => ['MateriallyResponsible', 'ResponsibleMaintenance', 'location', 'boofclient']];
//$GLOBALS['NESTING']['Document:Protocol:CT'] = ['bo', 'responsible', 'warehouse', 'client', 'CalendarPeriodMonth', 'upload' ];
//Протокол Поверки
//$GLOBALS['NESTING']['Document:Protocol:MT'] = ['bo' => ['MateriallyResponsible', 'ResponsibleMaintenance', 'location', 'boofclient']];
$GLOBALS['NESTING']['Document:Protocol:MT'] = ['CONTEXT_BusinessObjectRecordPolymorph'];
//Кадровые документы
$GLOBALS['NESTING']['Document:Staffdoc:OF'] = ['CompanyStructureDepartment', 'ManagementPostGroup'];
$GLOBALS['NESTING']['Document:Staffdoc:OF'] = ['employee', 'CompanyStructureDepartment'];

// Inna in progress
// $GLOBALS['NESTING']['Document:Staffdoc:OR'] = [''];
//$GLOBALS['NESTING']['Document:Staffdoc:OF'] = ['ManagementPostIndividual' => ['']];
// $GLOBALS['NESTING']['Document:Staffdoc:SV'] = [''];
// $GLOBALS['NESTING']['Document:Staffdoc:SD'] = [''];

?>
