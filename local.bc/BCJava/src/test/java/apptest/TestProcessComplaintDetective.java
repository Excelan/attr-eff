package apptest;

import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.symbol.URN;
//import org.junit.Assert;
//import org.junit.Ignore;
//import org.junit.Test;
//import org.junit.runners.MethodSorters;
//import org.junit.FixMethodOrder;
import org.testng.annotations.*;
import org.testng.Assert;

import digital.erp.process.ManagedProcessExecution;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.process.ManagedProcessesCentral;

//@FixMethodOrder(MethodSorters.NAME_ASCENDING)
public class TestProcessComplaintDetective extends Assert {

    static UPN upn;
    static String ofType = "C_IS";

    @Test(enabled = false)
    public void all() throws Exception
    {
        ManagedProcessExecution.clearAll();


        ManagedProcessesCentral mpc = ManagedProcessesCentral.getInstance();
        //mpc.availableProcessMastercopies.forEach((name, mpp) -> System.out.println(mpp));
        //assertEquals(mpc.availableProcessMastercopies.size(), 4); // Approvement, Visa, Claim, Detective



        // B
        URN initiator = new URN("URN:Management:Post:Individual:1118804000");
        Prototype subjectProto = Prototype.fromString("Document:Complaint:"+this.ofType);
        this.upn = ManagedProcessesCentral.getInstance().startProcess(Prototype.fromString("DMS:Complaints:Complaint"), initiator, null, subjectProto, null);
        //

        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        assertFalse(mpe.isDone());
        assertEquals("Editing", mpe.getCurrentstage());
        assertNotNull(mpe.getSubject());
        //
        DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
        System.out.println(dmd);
        assertEquals(0, dmd.getChildren().size());

        ManagedProcessExecution.debugAll(); // DEBUG

        // C_CompleteStage_ChildCompleteAllStages

        mpe = ManagedProcessExecution.load(this.upn);
        //System.out.println("? currentactor " + mpe.getCurrentactor());

        // завершить этап редактирования и вызвать процесс Служебного Расследования
        mpe.completeCurrentStage();
        assertEquals("CallCP", mpe.getCurrentstage());
        mpe.reload();
        assertNull(mpe.getCurrentactor());

        // mpe.child proto is Protocol

        ManagedProcessExecution.debugAll(); // DEBUG

        UPN child = new UPN(mpe.getMetadataValueByKey("child"));
        ManagedProcessExecution mpeChild = ManagedProcessExecution.load(child);

        System.out.println("\tCHILD " + mpeChild.toString());

        assertEquals("Detective", mpeChild.getSubject().getPrototype().getOfClass());
        assertEquals(this.ofType, mpeChild.getSubject().getPrototype().getOfType());

        assertEquals("ProtocolEditing", mpeChild.getCurrentstage());
        mpeChild.completeCurrentStage();

        mpeChild = ManagedProcessExecution.load(mpeChild.getUPN());

        assertEquals("ProtocolExtendRisk", mpeChild.getCurrentstage());
        mpeChild.completeCurrentStage();
        // >>>

        //mpeChild = ManagedProcessExecution.load(mpeChild.getUPN());

        // теперь мы на вызванном процессе визирования на единственном этапе Decision (но на Vising в mpeChild (CP)

        //assertEquals("Vising", mpeChild.getCurrentstage());

        ManagedProcessExecution.debugAll(); // DEBUG

        UPN childVisaUPN = new UPN(mpeChild.getMetadataValueByKey("child"));
        ManagedProcessExecution mpeChildVisa = ManagedProcessExecution.load(childVisaUPN);

        assertEquals("Detective", mpeChildVisa.getSubject().getPrototype().getOfClass());
        assertEquals(this.ofType, mpeChildVisa.getSubject().getPrototype().getOfType());

        System.out.println("\t\tCHILD VISA " + mpeChildVisa.toString());
        assertEquals("Decision", mpeChildVisa.getCurrentstage());

        mpeChildVisa.completeCurrentStage();
        // >>>

        // reload & check
        mpeChildVisa = ManagedProcessExecution.load(mpeChildVisa.getUPN());
        assertTrue(mpeChildVisa.isDone());


        ManagedProcessExecution.debugAll(); // DEBUG

        // ?
        //mpeChild.completeCurrentStage();
        mpeChild = ManagedProcessExecution.load(mpeChild.getUPN());
        assertEquals("Approving", mpeChild.getCurrentstage());

        ManagedProcessExecution.debugAll(); // DEBUG

        // mpeChild.child proto is Approvement
        UPN childApprovingUPN = new UPN(mpeChild.getMetadataValueByKey("child"));
        ManagedProcessExecution mpeChildApproving = ManagedProcessExecution.load(childApprovingUPN);
        System.out.println("\t\tCHILD APPROVEMENT " + mpeChildApproving.toString());
        assertEquals("Detective", mpeChildApproving.getSubject().getPrototype().getOfClass());
        mpeChildApproving.completeCurrentStage(); // will do approvement.decision, detective.approving, detective.route(automated)
        // reload & check
        mpeChildApproving = ManagedProcessExecution.load(mpeChildApproving.getUPN());
        assertTrue(mpeChildApproving.isDone());
        assertNull(mpeChildApproving.getCurrentstage());

        int totalrows = ManagedProcessExecution.debugAll(); // DEBUG
        assertEquals(totalrows, 4);

        assertNotEquals("Route", mpeChild.getCurrentstage()); // ! Route is automated and done right after Approving out
        //System.out.println("\t\t\tMPE CHILD1 " + mpeChild.toString());
        // after child make parent done, parent needs to be reloaded!
        ManagedProcessExecution mpeChildReloaded = ManagedProcessExecution.load(mpeChild.getUPN());
        //System.out.println("\t\t\tMPE CHILD Reloaded " + mpeChildReloaded.toString());
        assertTrue(mpeChildReloaded.isDone());

        // child done = parent done
        ManagedProcessExecution mpeParent = ManagedProcessExecution.load(this.upn); // need to reload
        assertTrue(mpeParent.isDone());
        assertNull(mpeParent.getCurrentstage());
        assertNull(mpeParent.getCurrentactor());

        ManagedProcessExecution mpetest = ManagedProcessExecution.load(this.upn);
        DocumentMetadata dmdtest = Document.loadDocumentMetadata(mpetest.getSubject());
        System.out.println(dmdtest);
        dmdtest.getChildren().forEach((dchild) -> System.out.println(dchild));
        assertEquals(1, dmdtest.getChildren().size());
    }

    // in process ManagedProcessExecution mpec = mpe.getChild() > // isWhaitingForChildCompletion

    /*
    @Test
    public void D_ClaimCompleteStageAfterChildDone() throws Exception {
        System.out.println("========= 4 complete CallCP stage");
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        System.out.println(mpe);
        //System.out.println("? currentstage " + mpe.getCurrentstage());
        //assertEquals(mpe.getCurrentstage(), "CallCP");
        //mpe.completeCurrentStage();
        assertTrue(mpe.isDone());
        assertNull(mpe.getCurrentstage());
        assertNull(mpe.getCurrentactor());
    }
    */

    /*
    @Test(expected = ManagedProcessException.OperateOnDone.class)
    public void E_try_OperateOnDone() throws Exception {
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        mpe.completeCurrentStage();
    }
    */

    /*
    @Test
    public void F_check() throws Exception {
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
        System.out.println(dmd);
        dmd.getChildren().forEach((child) -> System.out.println(child));
        assertEquals(1, dmd.getChildren().size());
    }
    */


    /*
    @Test
    public void DtestDetectiveStart() throws Exception {
        System.out.println("========= B1 ManagedProcessExecution.create(ClaimsManagement-Claims-Detective, 10)");
        this.upn = ManagedProcessesCentral.getInstance().startProcess(Prototype.fromString("ClaimsManagement-Claims-Detective"), 10);
    }

    @Test
    public void EtestDetective_CompleteStage() throws Exception {
        System.out.println("========= B2 complete stage");
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        //System.out.println("? currentactor " + mpe.getCurrentactor());
        System.out.println("? " + mpe.getCurrentstage());
//        assertEquals(mpe.getCurrentstage(), "Editing");
        mpe.completeCurrentStage();
//        assertTrue(mpe.isDone());
        System.out.println("? " + mpe.getCurrentstage());
//        assertNull(mpe.getCurrentstage());
//        assertNull(mpe.getCurrentactor());
    }

    //@Test
    public void FtestDetective_CompleteStage2() throws Exception {
        System.out.println("========= B3 complete stage");
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        //System.out.println("? currentactor " + mpe.getCurrentactor());
        System.out.println("?wasstage " + mpe.getCurrentstage());
//        assertEquals(mpe.getCurrentstage(), "Editing");
        mpe.completeCurrentStage();
//        assertTrue(mpe.isDone());
        System.out.println("?nowstage " + mpe.getCurrentstage());
//        assertNull(mpe.getCurrentstage());
//        assertNull(mpe.getCurrentactor());
    }

    */

    /*
    @Test
    public void testManagedProcessExecution_CompleteStage2() throws Exception {
        System.out.println("========= 4 complete stage");
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        mpe.completeCurrentStage();
    }

    @Test(expected = ManagedProcessException.OperateOnDone.class)
    public void testManagedProcessExecution_CompleteStage3() throws Exception {
        System.out.println("========= 5 complete done process");
        ManagedProcessExecution mpe = ManagedProcessExecution.load(this.upn);
        mpe.completeCurrentStage();
    }
    */

}