package apptest;

import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.ManagedProcessesCentral;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import org.testng.Assert;
import org.testng.annotations.Test;

public class TestComplaintWithVisa extends Assert {

    static UPN upn;
    static String ofType = "C_IS";


    @Test(enabled = false)
    public void allPendOnVisa() throws Exception
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

        // мы на этапе визирования
        mpeChildVisa.setMetadataKeyValue("decision", "cancel");


        // завершить визирование
        //mpeChildVisa.completeCurrentStage(); // !!!!!!!!!!!!!
        // >>>

    }

    @Test(enabled = false)
    public void allComplete() throws Exception
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

        // мы на этапе визирования
        mpeChildVisa.setMetadataKeyValue("decision", "cancel");


        // завершить визирование
        mpeChildVisa.completeCurrentStage(); // !!!!!!!!!!!!!
        // >>>

        // reload
        mpeChild = ManagedProcessExecution.load(mpeChild.getUPN());
        assertEquals(mpeChild.getMetadataValueByKey("decision"), "cancel");

        // reload & check
        mpeChildVisa = ManagedProcessExecution.load(mpeChildVisa.getUPN());
        assertTrue(mpeChildVisa.isDone());

        ManagedProcessExecution.debugAll(); // DEBUG

        mpeChild = ManagedProcessExecution.load(mpeChild.getUPN());

        assertEquals(mpeChild.getCurrentstage(), "ProtocolExtendRisk");
        mpeChild.completeCurrentStage();

        childVisaUPN = new UPN(mpeChild.getMetadataValueByKey("child"));
        mpeChildVisa = ManagedProcessExecution.load(childVisaUPN);
        mpeChildVisa.setMetadataKeyValue("decision", "vised");
        // завершить повторное визирование
        mpeChildVisa.completeCurrentStage(); // !!!!!!!!!!!!!

        // reload
        mpeChild = ManagedProcessExecution.load(mpeChild.getUPN());
        assertEquals(mpeChild.getCurrentstage(), "Approving");

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
        assertEquals(totalrows, 5);

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

}