package apptest;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.Ticket;
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

import java.util.HashMap;
import java.util.Map;
import java.util.logging.Level;

import static apptest.Logging.logMessages;
import static apptest.Logging.printLevels;

public class TestSopStudyAttestationFull extends Assert {

    static UPN upn;
    static Long ta;

    @Test(enabled = false)
    public void all() throws Exception
    {

        ManagedProcessExecution.clearAll();

        ManagedProcessesCentral mpc = ManagedProcessesCentral.getInstance();

        URN initiator = new URN("URN:Management:Post:Individual:1118804000");
        ManagedProcessExecution returntopme = null;

        // TODO и процесс SOP начинать тут же, но нужно добавлять в документ тех, для кого будет нужно обучение (есть J метод для tarray, пока нет для list)

        // emulate sop.out
        URN sop = new URN("URN:Document:Regulations:SOP:885393915");

        Map<String, String> metadata = new HashMap<String, String>();
        metadata.put("sop", sop.toString());

        UPN upnStudy = ManagedProcessesCentral.getInstance().startProcessWithMetadata(Prototype.fromString("DMS:Regulation:Study"), initiator, returntopme, null, sop, metadata);
        // study from sop started

        ManagedProcessExecution mpeStudy = ManagedProcessExecution.load(upnStudy);
        assertNotNull(mpeStudy.getMetadataValueByKey("sop"));
        assertEquals(mpeStudy.getMetadataValueByKey("sop"), sop.toString());

        assertEquals(mpeStudy.getCurrentstage(), "Editing");
        mpeStudy.completeCurrentStage();

        mpeStudy =  ManagedProcessExecution.load(upnStudy);
        assertEquals(mpeStudy.getCurrentstage(), "Vising");
        //mpeStudy.completeCurrentStage();
        mpeStudy.completeNestedCurrentStage();

        mpeStudy =  ManagedProcessExecution.load(upnStudy);
        assertEquals(mpeStudy.getCurrentstage(), "Approving");
        //mpeStudy.completeCurrentStage();
        mpeStudy.completeNestedCurrentStage();

        mpeStudy =  ManagedProcessExecution.load(upnStudy);
        System.out.println(mpeStudy.getCurrentstage());
        System.out.println(mpeStudy.isDone());
        // study done


        // attestation started from study
        UPN upnAttestation = new UPN(mpeStudy.getMetadataValueByKey("firstAttestation"));
        System.out.println(upnAttestation);
        ManagedProcessExecution mpeAttestation = ManagedProcessExecution.load(upnAttestation);
        assertNotNull(mpeAttestation.getMetadataValueByKey("sop"));
        assertNotNull(mpeAttestation.getMetadataValueByKey("ta"));
        assertNotNull(Entity.directLoadLongForKeyIn("DocumentRegulationsTA", mpeAttestation.getSubject()));
        System.out.println(mpeAttestation.getMetadataValueByKey("ta"));
        System.out.println("TA CHECK: " + Entity.directLoadLongForKeyIn("DocumentRegulationsTA", mpeAttestation.getSubject()));
        System.out.println(mpeAttestation.getMetadataValueByKey("sop"));
        System.out.println("SOP CHECK: " + Entity.directLoadLongForKeyIn("DocumentRegulationsSOP", mpeAttestation.getSubject()));

        System.out.println("\tTickets total on AttendeesSelection: " + Ticket.getTotalCount());
        System.out.println("\tTickets current process on AttendeesSelection: " + Ticket.getTotalCountForProcess(upnAttestation));

        assertEquals(mpeAttestation.getCurrentstage(), "AttendeesSelection");
        mpeAttestation.completeCurrentStage();

        mpeAttestation =  ManagedProcessExecution.load(upnAttestation);
        assertEquals(mpeAttestation.getCurrentstage(), "Planing");
        System.out.println("\tTickets in planning before testing: " + Ticket.getTotalCount());
        //mpeAttestation.completeCurrentStage();
        mpeAttestation.completeNestedCurrentStage();


        mpeAttestation =  ManagedProcessExecution.load(upnAttestation);
        assertEquals(mpeAttestation.getCurrentstage(), "Testing");

        System.out.println("\tTickets on testing: " + Ticket.getTotalCount());

        mpeAttestation.completeNestedCurrentStage();

        mpeAttestation = ManagedProcessExecution.load(upnAttestation);

        // RouteOut done too
        assertTrue(mpeAttestation.isDone());

        //System.out.println(mpeAttestation.isDone());
        //System.out.println(mpeAttestation.getCurrentstage());

        System.out.println("upnStudy " + upnStudy + " " + Ticket.getTotalCountForProcess(upnStudy));
        System.out.println("upnAttestation " + upnAttestation + " " + Ticket.getTotalCountForProcess(upnAttestation));

        System.out.println("\tTickets after ALL: " + Ticket.getTotalCount());

        //assertEquals((int)Ticket.getTotalCount(), 10); // 9 + 1 on Attestation again
        //assertEquals((int)Ticket.getTotalCountForProcess(upnStudy), 1);
        //assertEquals((int)Ticket.getTotalCountForProcess(upnAttestation), 1);

        mpeAttestation =  ManagedProcessExecution.load(upnAttestation);
        String allPassedMDK = mpeAttestation.getMetadataValueByKey("allpassed");
        System.out.println("\tALL PASSED ON CURRENT 1 ATTESTATION: "+allPassedMDK);
        if (allPassedMDK != "yes") // не все сдали в первый раз
        {
            System.out.println("Attestation Next 2" + mpeAttestation.getMetadataValueByKey("next"));
            UPN upnAttestationAgain = new UPN(mpeAttestation.getMetadataValueByKey("next"));
            ManagedProcessExecution mpeAttestationAgain = ManagedProcessExecution.load(upnAttestationAgain);

            assertNotNull(Entity.directLoadLongForKeyIn("DocumentRegulationsTA", mpeAttestationAgain.getSubject()));
            System.out.println(mpeAttestationAgain.getMetadataValueByKey("ta"));
            System.out.println("TA CHECK: " + Entity.directLoadLongForKeyIn("DocumentRegulationsTA", mpeAttestationAgain.getSubject()));
            System.out.println(mpeAttestationAgain.getMetadataValueByKey("sop"));
            System.out.println("SOP CHECK: " + Entity.directLoadLongForKeyIn("DocumentRegulationsSOP", mpeAttestationAgain.getSubject()));

            mpeAttestationAgain.completeCurrentStage(); // complete AttendeesSelection
            mpeAttestationAgain =  ManagedProcessExecution.load(upnAttestationAgain);
            mpeAttestationAgain.completeNestedCurrentStage(); // complete Planing
            mpeAttestationAgain =  ManagedProcessExecution.load(upnAttestationAgain);
            mpeAttestationAgain.completeNestedCurrentStage(); // complete Testing
            mpeAttestationAgain =  ManagedProcessExecution.load(upnAttestationAgain);

            String allPassedMDK2 = mpeAttestationAgain.getMetadataValueByKey("allpassed");
            System.out.println("\tALL PASSED ON CURRENT 2 ATTESTATION: "+allPassedMDK2);
            if (allPassedMDK2 != "yes") // не все сдали во второй заход
            {
                System.out.println("Attestation Next 3" + mpeAttestationAgain.getMetadataValueByKey("next"));
                if (mpeAttestationAgain.getMetadataValueByKey("next") == null) throw new Exception("Prev is not passed by all by not have NEXT key");
                UPN upnAttestationAgain3 = new UPN(mpeAttestationAgain.getMetadataValueByKey("next"));
                ManagedProcessExecution mpeAttestationAgain3 = ManagedProcessExecution.load(upnAttestationAgain3);

                assertNotNull(Entity.directLoadLongForKeyIn("DocumentRegulationsTA", mpeAttestationAgain3.getSubject()));
                System.out.println(mpeAttestationAgain3.getMetadataValueByKey("ta"));
                System.out.println("TA CHECK: " + Entity.directLoadLongForKeyIn("DocumentRegulationsTA", mpeAttestationAgain3.getSubject()));
                System.out.println(mpeAttestationAgain3.getMetadataValueByKey("sop"));
                System.out.println("SOP CHECK: " + Entity.directLoadLongForKeyIn("DocumentRegulationsSOP", mpeAttestationAgain3.getSubject()));

                mpeAttestationAgain3.completeCurrentStage(); // complete AttendeesSelection
                mpeAttestationAgain3 =  ManagedProcessExecution.load(upnAttestationAgain3);
                mpeAttestationAgain3.completeNestedCurrentStage(); // complete Planing
                mpeAttestationAgain3 =  ManagedProcessExecution.load(upnAttestationAgain3);
                mpeAttestationAgain3.completeNestedCurrentStage(); // complete Testing
                mpeAttestationAgain3 =  ManagedProcessExecution.load(upnAttestationAgain3);

                String allPassedMDK3 = mpeAttestationAgain3.getMetadataValueByKey("allpassed");
                System.out.println("\tALL PASSED ON CURRENT 3 ATTESTATION: "+allPassedMDK3); // все сдали на третий раз
            }

        }

        ManagedProcessExecution.debugAll();
    }

}
