package apptest;

import digital.erp.data.Entity;
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

public class TestUKD extends Assert {

    @Test(enabled = false)
    public void all() throws Exception
    {
        ManagedProcessExecution.clearAll();
        ManagedProcessesCentral mpc = ManagedProcessesCentral.getInstance();

        URN initiator = new URN("URN:Management:Post:Individual:1118804000");
        ManagedProcessExecution returntopme = null;

        URN asr = new URN("URN:Document:Regulations:ASR:530351829"); // 1 success: urn:Management:Post:Individual:1410977582
        URN sop = new URN("URN:Document:Regulations:SOP:743018759"); // 3 for
        Integer version = 1;

        Map<String, String> metadata = new HashMap<>();
        metadata.put("asr", asr.toString());
        metadata.put("sop", sop.toString());
        metadata.put("sopversion", version.toString());

        UPN upnUKD = ManagedProcessesCentral.getInstance().startProcessWithMetadata(Prototype.fromString("DMS:Regulation:UKD"), initiator, returntopme, null, sop, metadata);

        ManagedProcessExecution mpeUKD = ManagedProcessExecution.load(upnUKD);
        assertNotNull(mpeUKD.getMetadataValueByKey("sop"));
        assertEquals(mpeUKD.getMetadataValueByKey("sop"), sop.toString());
        assertNotNull(mpeUKD.getMetadataValueByKey("sopversion"));
        assertEquals(mpeUKD.getMetadataValueByKey("sopversion"), version.toString());



        /*
        assertEquals(mpeUKD.getCurrentstage(), "Configuring");
        //mpeUKD.postTestDataForCurrentStage();
        mpeUKD.completeCurrentStage();

        assertEquals(mpeUKD.getCurrentstage(), "Planning");

        mpeUKD.postTestDataForCurrentStage(); // set event date
        assertNotNull(Entity.directLoadDateForKeyIn("eventdate", mpeUKD.getSubject()));

        mpeUKD.completeNestedCurrentStage();

        mpeUKD = ManagedProcessExecution.load(mpeUKD.getUPN());

        assertNotNull(Entity.directLoadStringForKeyIn("printarchive", mpeUKD.getSubject()));

        assertEquals(mpeUKD.getCurrentstage(), "Print");
        */

        /*
        //mpeUKD.postTestDataForCurrentStage();

        mpeUKD.completeCurrentStage();

        assertEquals(mpeUKD.getCurrentstage(), "Issue");
        //mpeUKD.postTestDataForCurrentStage();

        //mpeUKD.completeCurrentStage();

        //assertTrue(mpeUKD.isDone());

        */



        ManagedProcessExecution.debugAll();
    }

}
