package erp.process

import com.attracti.app.gates.process.claim.{ClaimStartProcess, Request}
//import domains.document.DocumentQualityClaim


/* задачи
// на основе ролей, роль позже связывается в приложении с конкретными пользователями или должностями
какой узел(авто или экран) первый?
узлы все + внтури их переходы. права
экраны
    действия (complete, reject)
*/
class Claim extends BusinessProcess {
    val name = "CLAIM"
    val nodes = Seq[Stage]() // TODO add Start, Edit
    // acting roles

}

class Start extends Stage {
    val nodetype = Auto()
    // access UserClass, UserType
    val in = new StartIn(this)

    // call process gate (java)
    // TODO path process/document/clientclass/claimtype/create/processing > ClaimStartProcess()
    val claimStartProcess = new ClaimStartProcess()
    val resp = claimStartProcess.process(new Request("urn-startit-1"))
    println("CLAIM STARTED " + resp.i + ", " + resp.s)

    val out = new StartOut(this)
}

class StartIn(stage: Stage) extends StageIn {

    // IN (stage)
    // TX
    // (try to change entity state to be ready for process stage. Process Instance take management on entity in valid state)
    // Entity.stateTransitionTo


    // WEB PROCESS MASTER
    // Web user can M3 init process (init, point to first stage, start(int) first stage) or M123 complete(out)(with_variants) stage, PM transition, start next stage(in)

    // PROCESS MASTER
    // ? all processes in scala level business logic, not gates, not distributed (Process Domains possible)
    // knows each stage timings, subject urn-d-c-t, responsible role,
    // list of stages (stage know next stage)
    // rbac access to init new process instance (!= start first stage its delegated by process stage itself)
    // create instance (save sql)
    // * ManagedProcessExecution.initByOn('QualityControl', 'Claim', userId, 'urn-d-c-t-id') = ManagedProcessInstance (upn-d-c-id) // ManagedTypedProcess('QC','Claim','Internal'..)
    // set first stage as active (but not started). те PMaster перевел указатель на след этап, но этап еще не началасся.
    // инициатор процесс может быть != actor первого этапа, тогда только актер начнет этап, но будет уведомлен
    // Затем явный run stage (in call) (after out sys, ?page entry)

    // ProcessDC?
    // StageDC? by processpackage.stagename в отличие о гейтов и vc у этапов одинаковые параметры вызова

    // PROCESS INSTANCE DATA
    // subject entity urn, returntopmi, желаемый nextstage, current stage (eid+stageid=dsnapshot), current actor, p started, curent stage started, metadata map<String, String> (json object untyped values)
    // metadata. key, stage, author, value
    // * ManagedProcessCentral (parse xml, hold all protos)
    // * ManagedProcessCentral.mastercopyByPrototype(D,C,T) (getInstance /package protected)
    // * ManagedProcessMastercopy (know Stages, single instance)
    // > ManagedProcessExecution.create(proto, user)
    // > ManagedProcessExecution.loadInstance(upn-d-c-id) + make link to MPP  // 'UPN-QualityControl-Claim-123'
    // in, out Class.forName() biocon.process.ChangesManagement.Quolity.Claim.Edit.In
    // > ManagedProcessExecution.completeCurrentStage() = MPC.getProto(d,c,t) get current stage from proto, check role access
    // * ManagedProcessExecution.setStageMetaData('disApproveReason', 'richTxt') - вызывается при завершения этапа
    //   ManagedProcessExecution.completeStage('StartStage') = call current stage, actor; call out (1), PM.transition current state (2), call next(will be current) stage in (3)
    //      if stage is auto, call process after stage in, then recursive call .completeStage()
    //   managedProcessI.transitionFromToBy('StartStage', 'EditStage', userId)
    //      managedProcessI.initProcessAsStageBy('DMS', 'Approve', curStageActor) = stage container, init p, set new pmi.returntopmi = self pmi
    //      managedProcessI.completeProcess() = check delegate need
    // last stage make intent to delegate to new process
    // todo approve/visa complete (different out methods - next, back with comment) route to concrete process/stage
    // journal of stage to stage movement
    // todo back interrupt process, signal to process. interrupt slot (similar to in, out) save nextstage. interrupt = complete or not?
    // todo control slot (timing)

    /**
      * read all processes xmls
      *     new single MPP > MPP static all[each MPP] + new Class.forName[full.path.StageName+In/Out] on each MPI!
      *
     */

    // STAGE
    // сохранение формы идет не через процесс, а напрямую через gate в VC.save, тк менеджер процесса не связан с сутью задачи процесса
    // MPI claimProcessInst.

    // Entity.stateTransitionTo(urn, 'stage')   Entity interface + possible check for state required/values mix fields + dct OR entity?

    // DATA ABSTRACTION LEVEL
    // Entity.load = state, initiator, date  OR  NO  OR  plv8 json build from urn
    // Document.load(class, type, id) = (load only doc level fields - id, dct, vised, initiator, state (entity base),  json rendered?)
    // ? DCT.load(id) = full entity ??? DCT значит там не должно быть других DCT, только urn ссылки + has many + belongsto(non.uo) COMPOUND value load
    // VC.load() = COMPOUND (multy dct, not dct computed) value load

    // CREATE
    // DocumentQualityClaim.create("urn-user-1"); + MQ pub notify // id = (initiator) rbac, sql insert
    // * URN = Entity.create('urn-Document-Quality-Claim', userId)  J sql insert blank, user as object on scala level

    // UPDATE
    // Entity.directUpdateString('u-d-c-t-id', 'field', 'value')  J
    // VC save

    // LISTS
    // Entity.linkWith('urn-d-c-t-id-list', 'linkedURN')
    // Document.linkWith('c-t-id', 'linkedURN')
    // операции над данными, не методы данных ООП
    // EntityDomain level Class functions
    // Document.vised, d.visant, d.class d.type

    // STATE SNAPSHOTS
    // state changed,
    // Journal updates on stage change level.
}

class StartOut(stage: Stage) extends StageOut {
    // TX out
    // set pi.data.nextstage
    // Entity interface dct.stateTransitionTo(Start) possible check for state required/values mix fields
    // Process interface processInstance.route()   ? processAbstractStatic
    // start new process, return of control table (called process as stage). instances
}



class Edit extends Stage {
    val nodetype = Auto()
    // access UserClass, UserType
    // In > load form
        // TODO назначение в форме - это касается процесса, но хранится в документе
    // save form + Out
}

class EditIn extends StageIn {

}

class EditOut extends StageOut {
    // xml stage conf
    // load doc
    // update doc.stage
    // update process
    // set next stage actor
    // journal
    // route process
}
