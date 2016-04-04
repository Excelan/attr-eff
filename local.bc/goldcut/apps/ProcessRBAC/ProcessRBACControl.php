<?php

class ProcessRBACControl extends AjaxApplication implements ApplicationAccessManaged
{
    public function processesICanStart()
    {
        //$m = json_decode($this->message, true);
        //Log::info($m['json'], 'rbac');
        //Log::info($m['json']['class'], 'rbac');
        //["ClaimsManagement:Claims:Claim" => "Complain2"]
        $m = $this->message;
        Log::info($m->class, 'rbac');
        Log::info($m->urn, 'rbac');

        /**
         * управляющий процесс / управляемый объекты (прототипы)
         */

        $d = [];
        if ($m->class == null) {
            // manual for client / employee?
            // TODO list of classes? D:C / ClassTitle
            $d = [
                //["Document:Complaint" => "Жалобы"], ["Document:Claim" => "Заявки"]
            ];

            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Definition:DocumentClass:ForPrototype';
            $m->order = 'title asc';
            $psps = $m->deliver();

            foreach ($psps as $psp) {
                try {
                    // TODO FILTER BY RBAC START
                    array_push($d, [(string) $psp->urn => $psp->title]);
                } catch (Exception $er) {
                    Log::error($psp->subjectprototype, 'errors');
                    Log::error($er, 'errors');
                    array_push($d, ["ERROR" => "ERROR ".$er->getMessage()]);
                }
            }
        } else {
            //if ($m->class == 'Document:Complaint')

            $selected1levelClass = $m->class;
            /**
            if ($this->externalrole) {
                $d = [
                    // TODO all types
                    ["ClaimsManagement:Claims:Claim/Document:Complaint:C_IS" => "MANUAL Жалоба на работу инженерных систем / оборудование, состояние помещений"]
                ];
              */
            //} else {
            // $m->managementrole = (string) $this->managementrole->urn;
            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:Definition:Prototype:Document';
            $m->DefinitionDocumentClassForPrototype = $selected1levelClass; // URN
            $psps = $m->deliver();

            foreach ($psps as $docProto) {
                $systemProto = $docProto->DefinitionPrototypeSystem;
                $processProto = $docProto->processprototype;
                if (!count($processProto)) {
                    throw new Exception("$docProto->title has no processprototype");
                }
                try {
                    //$E = Entity::ref("{$docProto->indomain}:{$docProto->ofclass}:{$docProto->oftype}");
                        $key = "{$processProto->indomain}:{$processProto->target}:{$processProto->way}".'/'."{$docProto->indomain}:{$docProto->ofclass}:{$docProto->oftype}";
                    $Title = $docProto->title;
                        // $E->title['ru']
                        array_push($d, [$key => $Title]); // {$psp->processprototype->adminview}/{$psp->subjectprototype->adminview}
                } catch (Exception $er) {
                    //Log::error($psp->subjectprototype, 'errors');
                        Log::error($er->getMessage().' in '.$docProto, 'errors');
                    array_push($d, ["ERROR" => "ERROR ".$er->getMessage().' in '.$docProto]);
                }
            }
            //}
        }
        /*
        else
        {
            $d = [
                ["ERROR" => "NOTHING IN CLASS ".$m->class]
            ];
        }
        */

        return json_encode($d);
    }
}
