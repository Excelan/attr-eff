<?php

$GLOBALS['ENTITY']['VIEW']['Directory:TenderBidder:Simple']['passedToSecondTour'] = ['biddersolution' => 'yes'];

$GLOBALS['NESTING']['Document:Contract:BW'] = [ 'CompanyLegalEntityCounterparty', 'owncomany', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:LC'] = ['CompanyLegalEntityCounterparty', 'owncomany','notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']]; // ['CompanyLegalEntityCounterparty']
$GLOBALS['NESTING']['Document:Contract:LOP'] = ['CompanyLegalEntityCounterparty', 'owncomany','notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:LWP'] = ['CompanyLegalEntityCounterparty', 'owncomany','notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:MT'] = ['CompanyLegalEntityCounterparty', 'owncomany','notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:RSS'] = ['CompanyLegalEntityCounterparty', 'owncomany','notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:SS'] = ['CompanyLegalEntityCounterparty', 'owncomany','notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:TMC'] = ['CompanyLegalEntityCounterparty', 'owncomany','notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];
$GLOBALS['NESTING']['Document:Contract:TME'] = ['CompanyLegalEntityCounterparty', 'owncomany','notifyusercompany_unit', 'contractapplication' => ['MediaAttributed']];

$GLOBALS['NESTING']['Document:Regulations:SOP'] = ['DirectoryAdditionalSectionSimple'];
$GLOBALS['NESTING']['Document:Regulations:P'] = ['DirectoryAdditionalSectionSimple'];
$GLOBALS['NESTING']['Document:Regulations:I'] = ['DirectoryAdditionalSectionSimple'];
$GLOBALS['NESTING']['Document:Regulations:AO'] = ['userprocedure'];
$GLOBALS['NESTING']['Document:Regulations:JD'] = ['userprocedure'];
//$GLOBALS['NESTING']['Document:Regulations:MP'] = ['all']; // !!!!!!!!!!!!!!!
$GLOBALS['NESTING']['Document:Regulations:PV'] = ['DirectoryMaterialbaseSimple'=>['BusinessObjectRecordPolymorph'], 'DirectoryOptionsSimple', 'DirectoryResponsibleSimple'=>['ManagementPostIndividual']];
$GLOBALS['NESTING']['Document:Regulations:I'] = ['attachments', 'DirectoryBusinessProcessItem', 'scaleapplication', 'CalendarPeriodMonth', 'boprocedure' ];


$GLOBALS['NESTING']['Document:Protocol:KI'] = ['DocumentCorrectionCapa'];
$GLOBALS['NESTING']['Document:Protocol:SI'] = ['controlriskapproved'];

$GLOBALS['NESTING']['Document:TechnicalTask:forMaterials'] = ['DirectoryTechnicalTaskMaterials'];
$GLOBALS['NESTING']['Document:TechnicalTask:forWorks'] = ['DirectoryTechnicalTaskForWorks'];

//Протоколы Служебного Расследования
#$GLOBALS['NESTING']['Document:Detective:C:IV'] = ['CompanyLegalEntityCounterparty', 'warehouse', 'attachments','object','responsible', 'commissionmember','checkbo', 'complaintstatus', 'CompanyStructureDepartment', 'notapprovedrisks' ];
$GLOBALS['NESTING']['Document:Detective:C_IV'] = ['DocumentComplaintC_IV','checkbo_unit', 'responsible', 'internaldocuments_unit','commissionmember','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_LB'] = ['DocumentComplaintC_LB','checkbo_unit', 'responsible', 'internaldocuments_unit','commissionmember','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_LC'] = ['DocumentComplaintC_LC','checkbo_unit', 'responsible', 'internaldocuments_unit','commissionmember','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_LP'] = ['DocumentComplaintC_LP','checkbo_unit', 'responsible', 'internaldocuments_unit','commissionmember','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_LT'] = ['DocumentComplaintC_LT','checkbo_unit', 'responsible', 'internaldocuments_unit','commissionmember','deviations'=>['approvedrisks_unit','notapprovedrisks']];
//$GLOBALS['NESTING']['Document:Detective:C_LB'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
//$GLOBALS['NESTING']['Document:Detective:C_LC'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
//$GLOBALS['NESTING']['Document:Detective:C_LP'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
//$GLOBALS['NESTING']['Document:Detective:C_LT'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_IS'] = ['DocumentComplaintC_IS','checkbo_unit', 'responsible', 'internaldocuments_unit','commissionmember','deviations'=>['approvedrisks_unit','notapprovedrisks']];
$GLOBALS['NESTING']['Document:Detective:C_IW'] = ['DocumentComplaintC_IW','checkbo_unit', 'responsible', 'internaldocuments_unit','commissionmember','deviations'=>['approvedrisks_unit','notapprovedrisks']];
//$GLOBALS['NESTING']['Document:Detective:C_IS'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];
//$GLOBALS['NESTING']['Document:Detective:C_IW'] = ['checkbo_unit','internaldocuments_unit','commissionmember_unit','deviations'=>['approvedrisks_unit','notapprovedrisks']];

//Протокол Аудита
$GLOBALS['NESTING']['Document:Protocol:EA'] = [ 'boprocedure','commisionhead','commisionmembers'];
//Протокол Проверки
$GLOBALS['NESTING']['Document:Protocol:EC'] = ['boprocedure','commisionhead','commisionmembers'];
//Протокол ТО
$GLOBALS['NESTING']['Document:Protocol:TM'] = ['bo'=> [ 'MateriallyResponsible_unit', 'ResponsibleMaintenance_unit']];
//Протокол Валидации
$GLOBALS['NESTING']['Document:Protocol:VT'] = ['CONTEXT_BusinessObjectRecordPolymorph' => ['bo_unit', 'MateriallyResponsible_unit', 'ResponsibleMaintenance_unit'], 'DirectoryResponsibletwoSimple' => ['ManagementPostIndividual'], 'DirectoryFixedassetSimple' => ['equipment_unit'] ];
//$GLOBALS['NESTING']['Document:Protocol:VT'] = ['bo' => ['MateriallyResponsible', 'ResponsibleMaintenance', 'MateriallyResponsible']];
//$GLOBALS['NESTING']['Document:Protocol:VT'] = ['DirectoryResponsibletwoSimple', 'DirectoryFixedassetSimple'];
//Протокол Калибровки
$GLOBALS['NESTING']['Document:Protocol:CT'] = ['bo' => [ 'MateriallyResponsible_unit', 'ResponsibleMaintenance_unit','ResponsibleCalibration_unit'] ];
//Протокол Поверки
$GLOBALS['NESTING']['Document:Protocol:MT'] = ['bo' => [ 'MateriallyResponsible_unit', 'ResponsibleMaintenance_unit','ResponsibleVerification_unit'] ];
//Кадровые документы
//$GLOBALS['NESTING']['Document:Staffdoc:OF'] = ['CompanyStructureDepartment', 'ManagementPostGroup'];
//$GLOBALS['NESTING']['Document:Staffdoc:OF'] = ['employee', 'CompanyStructureDepartment'];
$GLOBALS['NESTING']['Document:Staffdoc:SV'] = ['employee'];
$GLOBALS['NESTING']['Document:Staffdoc:SD'] = ['employee', 'based'];
$GLOBALS['NESTING']['Document:Staffdoc:OF'] = ['ManagementPostIndividual'];
//Capa
$GLOBALS['NESTING']['Document:Capa:Deviation'] = ['RiskManagementRiskApproved', 'RiskManagementRiskNotApproved', 'DocumentCorrectionCapa'=>['eventplace', 'controlresponsible','DocumentSolutionCorrection', 'selectedsolution']];
//TechTasks
 $GLOBALS['NESTING']['Document:TechnicalTask:ForMaterials'] =['DirectoryTechnicalTaskMaterials', 'personreceive', 'contactperson'];
 $GLOBALS['NESTING']['Document:TechnicalTask:ForWorks'] =['DirectoryTechnicalTaskForWorks', 'personreceive', 'contactperson'];
//Claims
 $GLOBALS['NESTING']['Document:Claim:R_RDC'] =['DirectoryBusinessProcessItem'];
 $GLOBALS['NESTING']['Document:Claim:R_UPL'] =['recipient'];
 $GLOBALS['NESTING']['Document:Claim:R_UPK'] =['printuser'];
 $GLOBALS['NESTING']['Document:Claim:R_UPE'] =['mailusernew', 'mailuserold'];
 $GLOBALS['NESTING']['Document:Claim:R_QDE'] =['student'];
 $GLOBALS['NESTING']['Document:Claim:R_PAT'] =['hardwareuser'];
 $GLOBALS['NESTING']['Document:Claim:R_UPI'] =['internetuser'];
 $GLOBALS['NESTING']['Document:Claim:R_OQR'] =['purchaseuser'];
 $GLOBALS['NESTING']['Document:Claim:R_RDD'] =['DocumentCopyControled', 'purchaseuser'];
