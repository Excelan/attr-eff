package apptest;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.ManagedProcessesCentral;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import net.goldcut.network.HttpRequest;
import org.testng.Assert;
import org.testng.annotations.*;

import java.util.HashMap;
import java.util.Map;

public class DMSbaseTest extends Assert {

    @Test(enabled = true)
    public void dmsbase() throws Exception
    {
        ManagedProcessExecution.clearAll();
        ManagedProcessesCentral mpc = ManagedProcessesCentral.getInstance();

        // OLD MANUAL
        // URN subjectURN = Entity.createDraftBy(new URN(Prototype.fromString("ClaimsManagement:Claims:Claim")), 100); // initiator?

        // start process
        URN initiator = new URN("URN:Management:Post:Individual:1118804000");
        ManagedProcessExecution returntopme = null;

        Map<String, String> metadata = new HashMap<>();

        UPN upnSOP = ManagedProcessesCentral.getInstance().startProcessWithMetadata(Prototype.fromString("DMS:Regulation:SOP"), initiator, returntopme, Prototype.fromString("Document:Regulations:SOP"), null, metadata);

        ManagedProcessExecution mpeSOP = ManagedProcessExecution.load(upnSOP);
        mpeSOP.completeCurrentStage();

        mpeSOP = ManagedProcessExecution.load(upnSOP);
        mpeSOP.completeNestedCurrentStage();

        mpeSOP = ManagedProcessExecution.load(upnSOP);
        mpeSOP.completeNestedCurrentStage();

        mpeSOP = ManagedProcessExecution.load(upnSOP);
        assertTrue(mpeSOP.isDone());

        // create Document
//        String subjectPrototype = "Document:Regulations:SOP";
//        URN randomIdURN = new URN(Prototype.fromString(subjectPrototype));
//        URN subjectURN = Document.createDraftForProcessBy(randomIdURN, upnSOP, initiator);

//        System.out.println(subjectURN);

        // debug test all
        ManagedProcessExecution.debugAll();
    }

}
